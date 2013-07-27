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

        // set up forum root
        $forumRoot = new Dizkus_Entity_Forum();
        $forumRoot->setName(Dizkus_Entity_Forum::ROOTNAME);
        $this->entityManager->persist($forumRoot);

        // set up example forums
        $food = new Dizkus_Entity_Forum();
        $food->setName('Food');
        $food->setParent($forumRoot);
        $this->entityManager->persist($food);

        $fruits = new Dizkus_Entity_Forum();
        $fruits->setName('Fruits');
        $fruits->setParent($food);
        $this->entityManager->persist($fruits);

        $vegetables = new Dizkus_Entity_Forum();
        $vegetables->setName('Vegetables');
        $vegetables->setParent($food);
        $this->entityManager->persist($vegetables);

        $carrots = new Dizkus_Entity_Forum();
        $carrots->setName('Carrots');
        $carrots->setParent($vegetables);
        $this->entityManager->persist($carrots);

        $this->entityManager->flush();
        // end set up

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
            case '3.1.0':
                $this->upgrade_to_4_0_0();
                break;
        }

        return true;
    }

    /**
     * upgrade to 4.0.0
     */
    private function upgrade_to_4_0_0()
    {
        // do a check here for tables containing the prefix and fail if existing tables cannot be found.
        $configPrefix = $this->serviceManager['prefix'];
        $prefix = !empty($configPrefix) ? $configPrefix . '_' : '';
        $connection = $this->entityManager->getConnection();
        $sql = "SELECT * FROM " . $prefix . "dizkus_categories";
        $stmt = $connection->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            return LogUtil::registerError($e->getMessage() . $this->__f("There was a problem recognizing the existing Dizkus tables. Please confirm that your settings for prefix in \$ZConfig['System']['prefix'] match the actual Dizkus tables in the database. (Current prefix loaded as `%s`)", $prefix));
        }

        // remove the legacy hooks
        ModUtil::unregisterHook('item', 'create', 'API', 'Dizkus', 'hook', 'createbyitem');
        ModUtil::unregisterHook('item', 'update', 'API', 'Dizkus', 'hook', 'updatebyitem');
        ModUtil::unregisterHook('item', 'delete', 'API', 'Dizkus', 'hook', 'deletebyitem');
        ModUtil::unregisterHook('item', 'display', 'GUI', 'Dizkus', 'hook', 'showdiscussionlink');

        $this->upgrade_to_4_0_0_removeTablePrefixes($prefix);

        // update dizkus_forums to prevent errors in column indexes
        $sql = "ALTER TABLE dizkus_forums MODIFY forum_last_post_id INT DEFAULT NULL";
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $sql = "UPDATE dizkus_forums SET forum_last_post_id = NULL WHERE forum_last_post_id = 0";
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        // get all the pop3 connections for later re-entry
        $sql = "SELECT forum_id AS id, forum_pop3_active AS active, forum_pop3_server AS server, forum_pop3_port AS port, forum_pop3_login AS login,
                forum_pop3_password AS password, forum_pop3_interval AS interval, forum_pop3_lastconnect AS lastconnect, forum_pop3_pnuser as userid
                FROM dizkus_forums
                WHERE forum_pop3_active = 1";
        $pop3connections = $connection->fetchAll($sql);

        // update all the tables to 4.0.0
        try {
            DoctrineHelper::updateSchema($this->entityManager, $this->_entities);
        } catch (Exception $e) {
            return LogUtil::registerError($e->getMessage());
        }

        // migrate dataa from old formats
        $this->upgrade_to_4_0_0_migrateCategories();
        $this->upgrade_to_4_0_0_updatePosterData();
        $this->upgrade_to_4_0_0_migrateModGroups();
        $this->upgrade_to_4_0_0_migratePop3Connections($pop3connections);
        $this->upgrade_to_4_0_0_renameColumns();

        $this->delVar('autosubscribe');
        $this->delVar('allowgravatars');
        $this->delVar('gravatarimage');
        // remove pn from images/rank folder
        $this->setVar('url_ranks_images', "modules/Dizkus/images/ranks");

        LogUtil::registerStatus($this->__('The permission schemas "Dizkus_Centerblock::" and "Dizkus_Statisticsblock" were changed into "Dizkus::Centerblock" and "Dizkus::Statisticsblock". If you were using them please modify your permission table.'));

        return true;
    }

    /**
     * remove all table prefixes
     */
    private function upgrade_to_4_0_0_removeTablePrefixes($prefix)
    {
        $connection = $this->entityManager->getConnection();
        // remove table prefixes
        $dizkusTables = array(
            'dizkus_categories', // unused afterwards...
            'dizkus_forum_mods',
            'dizkus_forums',
            'dizkus_posts',
            'dizkus_subscription',
            'dizkus_ranks',
            'dizkus_topics',
            'dizkus_topic_subscription',
            'dizkus_forum_favorites',
            'dizkus_users'
        );
        foreach ($dizkusTables as $value) {
            $sql = 'RENAME TABLE ' . $prefix . $value . ' TO ' . $value;
            $stmt = $connection->prepare($sql);
            try {
                $stmt->execute();
            } catch (Exception $e) {
                LogUtil::registerError($e);
            }
        }
    }

    /**
     * rename some table columns
     * This must be done before updateSchema takes place
     */
    private function upgrade_to_4_0_0_renameColumns()
    {
        $connection = $this->entityManager->getConnection();
        $sqls = array();

        // a list of column changes
        $sqls[] = "ALTER TABLE dizkus_forums CHANGE forum_desc description TEXT DEFAULT NULL";
        $sqls[] = "ALTER TABLE dizkus_forums CHANGE forum_topics topicCount INT UNSIGNED NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_forums CHANGE forum_posts postCount INT UNSIGNED NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_forums CHANGE forum_moduleref moduleref INT UNSIGNED NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_forums CHANGE forum_name name VARCHAR(150) NOT NULL DEFAULT ''";
        $sqls[] = "ALTER TABLE dizkus_forums CHANGE forum_last_post_id last_post_id INT DEFAULT NULL";
        $sqls[] = "ALTER TABLE dizkus_users CHANGE user_posts postCount INT UNSIGNED NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_users CHANGE user_lastvisit lastvisit DATETIME DEFAULT NULL";
        $sqls[] = "ALTER TABLE dizkus_users CHANGE user_post_order postOrder INT(1) NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_users CHANGE user_rank rank INT UNSIGNED NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_posts CHANGE post_title title VARCHAR(255) NOT NULL";
        $sqls[] = "ALTER TABLE dizkus_posts CHANGE post_msgid msgid VARCHAR(100) NOT NULL";
        $sqls[] = "ALTER TABLE dizkus_ranks CHANGE rank_title title VARCHAR(50) NOT NULL";
        $sqls[] = "ALTER TABLE dizkus_ranks CHANGE rank_desc description VARCHAR(255) NOT NULL";
        $sqls[] = "ALTER TABLE dizkus_ranks CHANGE rank_min minimumCount INT NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_ranks CHANGE rank_max maximumCount INT NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_ranks CHANGE rank_image image VARCHAR(255) NOT NULL";
        $sqls[] = "ALTER TABLE dizkus_ranks CHANGE rank_special type INT(2) NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_topics CHANGE topic_poster poster INT NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_topics CHANGE topic_title title VARCHAR(255) NOT NULL";
        $sqls[] = "ALTER TABLE dizkus_topics CHANGE topic_status status INT NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_topics CHANGE topic_views viewCount INT NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_topics CHANGE topic_replies replyCount INT UNSIGNED NOT NULL DEFAULT 0";
        $sqls[] = "ALTER TABLE dizkus_topics CHANGE topic_reference reference VARCHAR(60) NOT NULL";
        $sqls[] = "ALTER TABLE dizkus_topics CHANGE topic_last_post_id last_post_id INT DEFAULT NULL";
        
        foreach ($sqls as $sql) {
            $stmt = $connection->prepare($sql);
            try {
                $stmt->execute();
            } catch (Exception $e) {
                LogUtil::registerError($e);
            }
        }
    }

    /**
     * Migrate categories from 3.1 > 4.0.0
     *
     */
    private function upgrade_to_4_0_0_migrateCategories()
    {
        // set up forum root
        $forumRoot = new Dizkus_Entity_Forum();
        $forumRoot->setName(Dizkus_Entity_Forum::ROOTNAME);
        $this->entityManager->persist($forumRoot);
        $this->entityManager->flush();

        $connection = $this->entityManager->getConnection();

        // Move old categories into new tree as Forums
        $sql = "SELECT * FROM dizkus_categories ORDER BY cat_order ASC";
        $categories = $connection->fetchAll($sql);
        foreach ($categories as $category) {
            // create new category forum with old name
            $newCatForum = new Dizkus_Entity_Forum();
            $newCatForum->setName($category['cat_title']);
            $newCatForum->setParent($forumRoot);
            $this->entityManager->persist($newCatForum);

            // set parent of existing forums to new category forum
            $where = array('root' => $category['cat_id']);
            $forums = $this->entityManager->getRepository('Dizkus_Entity_Forum')->findBy($where);
            foreach ($forums as $forum) {
                $forum->setParent($newCatForum);
                $this->entityManager->persist($forum);
            }
        }
        $this->entityManager->flush();

        // drop the old categories table
        $sql = "DROP TABLE dizkus_categories";
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        return;
    }

    /**
     * Update Poster Data from 3.1 > 4.0.0
     *
     */
    private function upgrade_to_4_0_0_updatePosterData()
    {
        // get all the old posts
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
                ->from('Dizkus_Entity_Post', 'p')
                ->groupBy('p.poster');
        $posts = $qb->getQuery()->getArrayResult();

        foreach ($posts as $post) {
            if ($post['poster_id'] > 0) {
                $forumUser = $this->entityManager->getRepository('Dizkus_Entity_ForumUser')->find($post['poster_id']);
                // if a ForumUser cannot be found, create one
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

        return;
    }

    /**
     * Migrate the Moderator Groups out of the `dizkus_forum_mods` table and put
     * in the new `dizkus_forum_mods_group` table
     */
    private function upgrade_to_4_0_0_migrateModGroups()
    {
        $connection = $this->entityManager->getConnection();
        $sql = "SELECT * FROM dizkus_forum_mods WHERE user_id > 1000000";
        $groups = $connection->fetchAll($sql);
        foreach ($groups as $group) {
            $groupId = $group['user_id'] - 1000000;
            $modGroup = new Dizkus_Entity_Moderator_Group();
            $coreGroup = $this->entityManager->find('Zikula\Module\GroupsModule\Entity\GroupEntity', $groupId);
            if ($coreGroup) {
                $modGroup->setGroup($coreGroup);
                $forum = $this->entityManager->find('Dizkus_Entity_Forum', $group['forum_id']);
                $modGroup->setForum($forum);
                $this->entityManager->persist($modGroup);
            }
        }
        $this->entityManager->flush();

        // dremove old group entries
        $sql = "DELETE FROM dizkus_forum_mods WHERE user_id > 1000000";
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        return;
    }

    /**
     * migrate pop3 connection data from multiple columns to one object
     *
     * @param type $connections
     */
    private function upgrade_to_4_0_0_migratePop3Connections($connections)
    {
        foreach ($connections as $connectionData) {
            $connectionData['coreUser'] = $this->entityManager->find('Zikula\Module\UsersModule\Entity\UserEntity', $connectionData['userid']);
            $connection = new Dizkus_Connection_Pop3($connectionData);
            $forum = $this->entityManager->find('Dizkus_Entity_Forum', $connectionData['id']);
            $forum->setPop3Connection($connection);
        }
        $this->entityManager->flush();
    }

}
