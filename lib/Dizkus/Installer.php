<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
class Dizkus_Installer extends Zikula_AbstractInstaller
{

    private $_entities = array(
        'Dizkus_Entity_Forum',
        'Dizkus_Entity_Post',
        'Dizkus_Entity_Topic',
        'Dizkus_Entity_ForumUserFavorite',
        'Dizkus_Entity_ForumUser',
        'Dizkus_Entity_Moderator_User',
        'Dizkus_Entity_Moderator_Group',
        'Dizkus_Entity_ForumSubscription',
        'Dizkus_Entity_TopicSubscription',
        'Dizkus_Entity_Rank'
    );

    /**
     *  Initialize a new install of the Dizkus module
     *
     *  This function will initialize a new installation of Dizkus.
     *  It is accessed via the Zikula Admin interface and should
     *  not be called directly.
     */
    public function install()
    {

        try {
            DoctrineHelper::createSchema($this->entityManager, $this->_entities);
        } catch (Exception $e) {
            return LogUtil::registerError($e->getMessage());
        }

        // ToDo: create FULLTEXT index
        // forum settings
        $this->setVar('posts_per_page', 15);
        $this->setVar('topics_per_page', 15);
        $this->setVar('hot_threshold', 20);
        $this->setVar('email_from', System::getVar('adminmail'));
        $this->setVar('url_ranks_images', "modules/Dizkus/images/ranks");
        $this->setVar('post_sort_order', 'ASC');
        $this->setVar('log_ip', 'no');
        $this->setVar('slimforum', 'no');
        // 2.5
        $this->setVar('extendedsearch', 'no');
        $this->setVar('m2f_enabled', 'yes');
        $this->setVar('favorites_enabled', 'yes');
        $this->setVar('hideusers', 'no');
        $this->setVar('removesignature', 'no');
        $this->setVar('striptags', 'no');
        // 2.6
        $this->setVar('deletehookaction', 'lock');
        $this->setVar('rss2f_enabled', 'yes');
        // 2.7
        $this->setVar('shownewtopicconfirmation', 'no');
        $this->setVar('timespanforchanges', 24);
        $this->setVar('forum_enabled', 'yes');
        $this->setVar(
                'forum_disabled_info', $this->__('Sorry! The forums are currently off-line for maintenance. Please try later.')
        );
        // 3.0
        $this->setVar('autosubscribe', 'no');
        $this->setVar('newtopicconfirmation', 'no');
        $this->setVar('signaturemanagement', 'no');
        $this->setVar('signature_start', '');
        $this->setVar('signature_end', '');
        $this->setVar('showtextinsearchresults', 'yes');
        $this->setVar('ignorelist_handling', 'medium');
        $this->setVar('minsearchlength', 3);
        $this->setVar('maxsearchlength', 30);
        // 3.2

        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
        HookUtil::registerProviderBundles($this->version->getHookProviderBundles());

        // set up example forums
        $food = new Dizkus_Entity_Forum();
        $food->setforum_name('Food');

        $fruits = new Dizkus_Entity_Forum();
        $fruits->setforum_name('Fruits');
        $fruits->setParent($food);

        $vegetables = new Dizkus_Entity_Forum();
        $vegetables->setforum_name('Vegetables');
        $vegetables->setParent($food);

        $carrots = new Dizkus_Entity_Forum();
        $carrots->setforum_name('Carrots');
        $carrots->setParent($vegetables);

        $this->entityManager->persist($food);
        $this->entityManager->persist($fruits);
        $this->entityManager->persist($vegetables);
        $this->entityManager->persist($carrots);
        $this->entityManager->flush();
        // end set up example

        // Initialisation successful
        return true;
    }

    /**
     *  Deletes an install of the Dizkus module
     *
     *  This function removes Dizkus from your
     *  Zikula install and should be accessed via
     *  the Zikula Admin interface
     */
    public function uninstall()
    {
        try {
            DoctrineHelper::dropSchema($this->entityManager, $this->_entities);
        } catch (Exception $e) {
            return LogUtil::registerError($e->getMessage());
        }

        // remove module vars
        $this->delVars();

        // unregister hooks
        HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());
        HookUtil::unregisterProviderBundles($this->version->getHookProviderBundles());

        // Deletion successful
        return true;
    }

    public function upgrade($oldversion)
    {
        // Only support upgrade from version 3.1 and up. Notify users if they have a version below that one.
        if (version_compare($oldversion, '3.1', '<')) {
            // Inform user about error, and how he can upgrade to $modversion
            $upgradeToVersion = $this->version->getVersion();
            return LogUtil::registerError($this->__f('Notice: This version does not support upgrades from versions of Dizkus less than 3.1. Please upgrade to 3.1 before upgrading again to version %s.', $upgradeToVersion));
        }

        switch ($oldversion) {

            case '3.1':
                $this->upgrade_to_4_0_0();
                break;
        }
    }

    /**
     * upgrade to 4.0.0
     */
    public function upgrade_to_4_0_0()
    {
        // remove pn from images/rank folder
        $this->setVar('url_ranks_images', "modules/Dizkus/images/ranks");

        // remove the legacy hooks
        ModUtil::unregisterHook('item', 'create', 'API', 'Dizkus', 'hook', 'createbyitem');
        ModUtil::unregisterHook('item', 'update', 'API', 'Dizkus', 'hook', 'updatebyitem');
        ModUtil::unregisterHook('item', 'delete', 'API', 'Dizkus', 'hook', 'deletebyitem');
        ModUtil::unregisterHook('item', 'display', 'GUI', 'Dizkus', 'hook', 'showdiscussionlink');

        // remove table prefixes
        $dizkusTables = array(
            'dizkus_categories',
            'dizkus_forum_mods',
            'dizkus_forums',
            'dizkus_posts',
            'dizkus_subscription',
            'dizkus_ranks',
            'dizkus_topics',
            'dizkus_topic_subscription',
            'dizkus_forum_favorites',
            'dizkus_forum_users'
        );
        $prefix = $this->serviceManager['prefix'];
        $connection = Doctrine_Manager::getInstance()->getConnection('default');
        foreach ($dizkusTables as $value) {
            $sql = 'RENAME TABLE ' . $prefix . '_' . $value . ' TO ' . $value;
            $stmt = $connection->prepare($sql);
            try {
                $stmt->execute();
            } catch (Exception $e) {
                LogUtil::registerError($e);
            }
        }
        try {
            DoctrineHelper::updateSchema($this->entityManager, $this->_entities);
        } catch (Exception $e) {
            return LogUtil::registerError($e->getMessage());
        }

        $this->delVar('autosubscribe');
        $this->delVar('allowgravatars');
        $this->delVar('gravatarimage');

        LogUtil::registerStatus($this->__('The permission schemas "Dizkus_Centerblock::" and "Dizkus_Statisticsblock" were changed into "Dizkus::Centerblock" and "Dizkus::Statisticsblock". If you were using them please modify your permission table.'));

        // TODO: the existing Group/Forum relations need to be migrated from the `dizkus_forum_mods` table (user_id > 1000000)
        //       to the new `dizkus_forum_mods_group` table (use normal group id, so subtract 1000000?)
        // TODO: need to remove forum_id column from posts table
        return true;
    }

    /**
     * import function?
     *
     */
    public function m()
    {
        DoctrineHelper::updateSchema($this->entityManager, array('Dizkus_Entity_Forum'));

        // import new tree
        $order = array('cat_order' => 'ASC');
        $categories = $this->entityManager->getRepository('Dizkus_Entity_310_Category')->findBy(array(), $order);
        foreach ($categories as $category) {
            $newCatForum = new Dizkus_Entity_Forum();
            $newCatForum->setForum_name($category->getcat_title());
            $this->entityManager->persist($newCatForum);

            $where = array('root' => $category->getcat_id());
            $forums = $this->entityManager->getRepository('Dizkus_Entity_Forum')->findBy($where);
            foreach ($forums as $forum) {
                $forum->setParent($newCatForum);
                $this->entityManager->persist($forum);
            }
        }
        $this->entityManager->flush();

        // create missing poster data
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
                ->from('Dizkus_Entity_310_Post', 'p')
                ->groupBy('p.poster_id');
        $posts = $qb->getQuery()->getArrayResult();

        foreach ($posts as $post) {
            if ($post['poster_id'] > 0) {
                $forumUser = $this->entityManager->getRepository('Dizkus_Entity_ForumUser')->find($post['poster_id']);
                if (!$forumUser) {
                    $forumUser = new Dizkus_Entity_ForumUser();
                    $coreUser = $this->entityManager->find('Zikula\Module\UsersModule\Entity\UserEntity', $post['poster_id']);
                    $forumUser->setUser($coreUser);
                    $this->entityManager->persist($forumUser);
                }
            }
        }
        $this->entityManager->flush();

        ModUtil::apiFunc('Dizkus', 'Sync', 'all');

        return ' ';
    }

}
