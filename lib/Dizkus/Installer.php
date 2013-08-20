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

        // set the module vars
        $this->setVars(self::getDefaultVars());

        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
        HookUtil::registerProviderBundles($this->version->getHookProviderBundles());
        EventUtil::registerPersistentModuleHandler('Dizkus', 'installer.module.uninstalled', array('Dizkus_HookHandlers', 'moduleDelete'));
        EventUtil::registerPersistentModuleHandler('Dizkus', 'module_dispatch.service_links', array('Dizkus_HookHandlers', 'servicelinks'));
        EventUtil::registerPersistentModuleHandler('Dizkus', 'controller.method_not_found', array('Dizkus_HookHandlers', 'dizkushookconfig'));
        EventUtil::registerPersistentModuleHandler('Dizkus', 'controller.method_not_found', array('Dizkus_HookHandlers', 'dizkushookconfigprocess'));

        // set up forum root (required)
        $forumRoot = new Dizkus_Entity_Forum();
        $forumRoot->setName(Dizkus_Entity_Forum::ROOTNAME);
        $forumRoot->lock();
        $this->entityManager->persist($forumRoot);

        // set up EXAMPLE forums
        $this->setUpExampleForums($forumRoot);

        // set up sample ranks
        $this->setUpSampleRanks();

        // Initialisation successful
        return true;
    }

    /**
     * Set up example forums on install
     */
    private function setUpExampleForums($forumRoot)
    {
        $food = new Dizkus_Entity_Forum();
        $food->setName('Food');
        $food->setParent($forumRoot);
        $food->lock();
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
    }

    private function setUpSampleRanks()
    {
        //title, description, minimumCount, maximumCount, type, image
        $ranks = array(
            array('title' => 'Level 1',
                'description' => 'New forum user',
                'minimumCount' => 1,
                'maximumCount' => 9,
                'type' => Dizkus_Entity_Rank::TYPE_POSTCOUNT,
                'image' => 'zerostar.gif'),
            array('title' => 'Level 2',
                'description' => 'Basic forum user',
                'minimumCount' => 10,
                'maximumCount' => 49,
                'type' => Dizkus_Entity_Rank::TYPE_POSTCOUNT,
                'image' => 'onestar.gif'),
            array('title' => 'Level 3',
                'description' => 'Moderate forum user',
                'minimumCount' => 50,
                'maximumCount' => 99,
                'type' => Dizkus_Entity_Rank::TYPE_POSTCOUNT,
                'image' => 'twostars.gif'),
            array('title' => 'Level 4',
                'description' => 'Advanced forum user',
                'minimumCount' => 100,
                'maximumCount' => 199,
                'type' => Dizkus_Entity_Rank::TYPE_POSTCOUNT,
                'image' => 'threestars.gif'),
            array('title' => 'Level 5',
                'description' => 'Expert forum user',
                'minimumCount' => 200,
                'maximumCount' => 499,
                'type' => Dizkus_Entity_Rank::TYPE_POSTCOUNT,
                'image' => 'fourstars.gif'),
            array('title' => 'Level 6',
                'description' => 'Superior forum user',
                'minimumCount' => 500,
                'maximumCount' => 999,
                'type' => Dizkus_Entity_Rank::TYPE_POSTCOUNT,
                'image' => 'fivestars.gif'),
            array('title' => 'Level 7',
                'description' => 'Senior forum user',
                'minimumCount' => 1000,
                'maximumCount' => 4999,
                'type' => Dizkus_Entity_Rank::TYPE_POSTCOUNT,
                'image' => 'spezstars.gif'),
            array('title' => 'Legend',
                'description' => 'Legend forum user',
                'minimumCount' => 5000,
                'maximumCount' => 1000000,
                'type' => Dizkus_Entity_Rank::TYPE_POSTCOUNT,
                'image' => 'adminstars.gif'),
        );
        foreach ($ranks as $rank) {
            $r = new Dizkus_Entity_Rank();
            $r->merge($rank);
            $this->entityManager->persist($r);
        }
        $this->entityManager->flush();
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

        // unregister handlers
        EventUtil::unregisterPersistentModuleHandlers('Dizkus');

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
     * get the default module var values
     * 
     * @return array
     */
    public static function getDefaultVars()
    {
        $dom = ZLanguage::getModuleDomain('Dizkus');
        return array(
            'posts_per_page' => 15,
            'topics_per_page' => 15,
            'hot_threshold' => 20,
            'email_from' => System::getVar('adminmail'),
            'url_ranks_images' => 'modules/Dizkus/images/ranks',
            'post_sort_order' => 'ASC', // means oldest to newest
            'log_ip' => 'no',
            'extendedsearch' => 'no',
            'm2f_enabled' => 'no',
            'favorites_enabled' => 'yes',
            'removesignature' => 'no',
            'striptags' => 'yes', // default to yes for security
            'deletehookaction' => 'lock',
            'rss2f_enabled' => 'no',
            'timespanforchanges' => 24,
            'forum_enabled' => 'yes',
            'forum_disabled_info' => __('Sorry! The forums are currently off-line for maintenance. Please try later.', $dom),
            'signaturemanagement' => 'no',
            'signature_start' => '--',
            'signature_end' => '--',
            'showtextinsearchresults' => 'yes',
            'minsearchlength' => 3,
            'maxsearchlength' => 30,
            'fulltextindex' => 'no',
            'solved_enabled' => true,
            'ajax' => true,
            'striptagsfromemail' => false,
        );
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

        // get all the pop3 connections & hook references for later re-entry
        $sql = "SELECT forum_id AS id, forum_moduleref as moduleref, forum_pop3_active AS active, forum_pop3_server AS server, forum_pop3_port AS port, forum_pop3_login AS login,
                forum_pop3_password AS password, forum_pop3_interval AS interval, forum_pop3_lastconnect AS lastconnect, forum_pop3_pnuser as userid
                FROM dizkus_forums
                WHERE forum_pop3_active = 1";
        $forumdata = $connection->fetchAll($sql);

        // fetch topic_reference and decode to migrate below (if possible)
        $sql = "SELECT topic_id, topic_reference
                FROM dizkus_topics
                WHERE topic_reference <> ''";
        $hookedTopicData = $connection->fetchAll($sql);

        // update all the tables to 4.0.0
        try {
            DoctrineHelper::updateSchema($this->entityManager, $this->_entities);
        } catch (Exception $e) {
            return LogUtil::registerError($e->getMessage());
        }

        // migrate data from old formats
        $this->upgrade_to_4_0_0_migrateCategories();
        $this->upgrade_to_4_0_0_updatePosterData();
        $this->upgrade_to_4_0_0_migrateModGroups();
        $this->upgrade_to_4_0_0_migratePop3Connections($forumdata);
        // @todo use $forumdata to migrate forum modulerefs
        $this->upgrade_to_4_0_0_migrateHookedTopics($hookedTopicData);
        $this->upgrade_to_4_0_0_renameColumns();

        $this->delVar('autosubscribe');
        $this->delVar('allowgravatars');
        $this->delVar('gravatarimage');
        $this->delVar('ignorelist_handling');
        $this->delVar('hideusers');
        $this->delVar('newtopicconfirmation');
        $this->delVar('slimforum');
        // remove pn from images/rank folder
        $this->setVar('url_ranks_images', "modules/Dizkus/images/ranks");
        $this->setVar('fulltextindex', 'no'); // disable until technology catches up with InnoDB
        $this->setVar('solved_enabled', true);
        $this->setVar('ajax', true);

        // register new hooks and event handlers
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
        HookUtil::registerProviderBundles($this->version->getHookProviderBundles());
        EventUtil::registerPersistentModuleHandler('Dizkus', 'installer.module.uninstalled', array('Dizkus_HookHandlers', 'moduleDelete'));
        EventUtil::registerPersistentModuleHandler('Dizkus', 'module_dispatch.service_links', array('Dizkus_HookHandlers', 'servicelinks'));
        EventUtil::registerPersistentModuleHandler('Dizkus', 'controller.method_not_found', array('Dizkus_HookHandlers', 'dizkushookconfig'));
        EventUtil::registerPersistentModuleHandler('Dizkus', 'controller.method_not_found', array('Dizkus_HookHandlers', 'dizkushookconfigprocess'));

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
        $forumRoot->lock();
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
            $newCatForum->lock();
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

        // remove old group entries
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

    /**
     * migrate hooked topics data to maintain hook connection with original object
     *
     * This routine will only attempt to migrate references where the topic_reference field
     * looks like `moduleID-objectId` -> e.g. '14-57'. If the field contains any underscores
     * the topic will be locked and the reference left unmigrated. This is mainly because
     * modules that use that style of reference are not compatible with Core 1.3.6+
     * anyway and so migrating their references would be pointless.
     *
     * Additionally, if the subscriber module has more than one subscriber area, then migration is
     * also impossible (which to choose?) so the topic is locked and reference left
     * unmigrated also.
     *
     * @param array $rows
     */
    private function upgrade_to_4_0_0_migrateHookedTopics($rows)
    {
        $count = 0;
        foreach ($rows as $row) {
            $topic = $this->entityManager->find('Dizkus_Entity_Topic', $row['topic_id']);
            if (isset($topic)) {
                if (strpos($row['topic_reference'], '_') !== false) {
                    // reference contains an unsupported underscore, lock the topic
                    $topic->lock();
                } else {
                    list($moduleId, $objectId) = explode('-', $row['topic_reference']);
                    $moduleInfo = ModUtil::getInfo($moduleId);
                    if ($moduleInfo) {
                        $searchCritera = array(
                            'owner' => $moduleInfo['name'],
                            'areatype' => 's',
                            'category' => 'ui_hooks');
                        $subscriberArea = $this->entityManager->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')->findBy($searchCritera);
                        if (count($subscriberArea) <> 1) {
                            // found either too many areas or none. cannot migrate
                            $topic->lock();
                        } else {
                            // finally set the information
                            $topic->setHookedModule($moduleInfo['name']);
                            $topic->setHookedAreaId($subscriberArea->getId());
                            $topic->setHookedObjectId($objectId);
                        }
                    } // if $moduleinfo
                }
                $count++;
                if ($count > 20) {
                    $this->entityManager->flush();
                    $count = 0;
                }
            } // if $topic
        }
        // flush remaining
        $this->entityManager->flush();
    }
}
