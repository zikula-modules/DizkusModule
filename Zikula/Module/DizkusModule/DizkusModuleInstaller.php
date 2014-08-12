<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule;

use HookUtil;
use ModUtil;
use ZLanguage;
use System;
use Exception;
use Zikula\Module\DizkusModule\Entity\ForumEntity;
use Zikula\Module\DizkusModule\Entity\RankEntity;
use Zikula\Module\DizkusModule\Entity\ForumUserEntity;
use Zikula\Module\DizkusModule\Entity\ModeratorGroupEntity;
use Zikula\Module\DizkusModule\Connection\Pop3Connection;
use DoctrineHelper;

class DizkusModuleInstaller extends \Zikula_AbstractInstaller
{
    /**
     * Module name
     * (needed for static methods)
     * @var string
     */
    const MODULENAME = 'ZikulaDizkusModule';

    private $_entities = array(
        'Zikula\Module\DizkusModule\Entity\ForumEntity',
        'Zikula\Module\DizkusModule\Entity\PostEntity',
        'Zikula\Module\DizkusModule\Entity\TopicEntity',
        'Zikula\Module\DizkusModule\Entity\ForumUserFavoriteEntity',
        'Zikula\Module\DizkusModule\Entity\ForumUserEntity',
        'Zikula\Module\DizkusModule\Entity\ForumSubscriptionEntity',
        'Zikula\Module\DizkusModule\Entity\TopicSubscriptionEntity',
        'Zikula\Module\DizkusModule\Entity\RankEntity',
        'Zikula\Module\DizkusModule\Entity\ModeratorUserEntity',
        'Zikula\Module\DizkusModule\Entity\ModeratorGroupEntity',
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
        } catch (\Exception $e) {
            $this->request->getSession()->getFlashBag()->add('error', $e->getMessage());
            return false;
        }
        // ToDo: create FULLTEXT index
        // set the module vars
        $this->setVars(self::getDefaultVars());
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
        HookUtil::registerProviderBundles($this->version->getHookProviderBundles());
        // set up forum root (required)
        $forumRoot = new ForumEntity();
        $forumRoot->setName(ForumEntity::ROOTNAME);
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
        $food = new ForumEntity();
        $food->setName('Food');
        $food->setParent($forumRoot);
        $food->lock();
        $this->entityManager->persist($food);
        $fruits = new ForumEntity();
        $fruits->setName('Fruits');
        $fruits->setParent($food);
        $this->entityManager->persist($fruits);
        $vegetables = new ForumEntity();
        $vegetables->setName('Vegetables');
        $vegetables->setParent($food);
        $this->entityManager->persist($vegetables);
        $carrots = new ForumEntity();
        $carrots->setName('Carrots');
        $carrots->setParent($vegetables);
        $this->entityManager->persist($carrots);
        $this->entityManager->flush();
    }

    private function setUpSampleRanks()
    {
        //title, description, minimumCount, maximumCount, type, image
        $ranks = array(
            array(
                'title' => 'Level 1',
                'description' => 'New forum user',
                'minimumCount' => 1,
                'maximumCount' => 9,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'zerostar.gif'),
            array(
                'title' => 'Level 2',
                'description' => 'Basic forum user',
                'minimumCount' => 10,
                'maximumCount' => 49,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'onestar.gif'),
            array(
                'title' => 'Level 3',
                'description' => 'Moderate forum user',
                'minimumCount' => 50,
                'maximumCount' => 99,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'twostars.gif'),
            array(
                'title' => 'Level 4',
                'description' => 'Advanced forum user',
                'minimumCount' => 100,
                'maximumCount' => 199,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'threestars.gif'),
            array(
                'title' => 'Level 5',
                'description' => 'Expert forum user',
                'minimumCount' => 200,
                'maximumCount' => 499,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'fourstars.gif'),
            array(
                'title' => 'Level 6',
                'description' => 'Superior forum user',
                'minimumCount' => 500,
                'maximumCount' => 999,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'fivestars.gif'),
            array(
                'title' => 'Level 7',
                'description' => 'Senior forum user',
                'minimumCount' => 1000,
                'maximumCount' => 4999,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'spezstars.gif'),
            array(
                'title' => 'Legend',
                'description' => 'Legend forum user',
                'minimumCount' => 5000,
                'maximumCount' => 1000000,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'adminstars.gif'));
        foreach ($ranks as $rank) {
            $r = new RankEntity();
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
            $this->request->getSession()->getFlashBag()->add('error', $e->getMessage());
            return false;
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

            $this->request->getSession()->getFlashBag()->add('error', $this->__f('Notice: This version does not support upgrades from versions of Dizkus less than 3.1. Please upgrade to 3.1 before upgrading again to version %s.', $upgradeToVersion));
            return false;
        }
        switch ($oldversion) {
            case '3.1':
            case '3.1.0':
                ini_set('memory_limit', '194M');
                ini_set('max_execution_time', 86400);
                if (!$this->upgrade_to_4_0_0()) {
                    return false;
                }
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
        $dom = ZLanguage::getModuleDomain(self::MODULENAME);

        return array(
            'posts_per_page' => 15,
            'topics_per_page' => 15,
            'hot_threshold' => 20,
            'email_from' => System::getVar('adminmail'),
            'url_ranks_images' => "modules/Dizkus/Zikula/Module/DizkusModule/Resources/public/images/ranks",
            'post_sort_order' => 'ASC',
            'log_ip' => 'no',
            'extendedsearch' => 'no',
            'm2f_enabled' => 'no',
            'favorites_enabled' => 'yes',
            'removesignature' => 'no',
            'striptags' => 'yes',
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
            'indexTo' => '',
            'notifyAdminAsMod' => 2,
            'defaultPoster' => 2,
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
        $sql = 'SELECT * FROM ' . $prefix . 'dizkus_categories';
        $stmt = $connection->prepare($sql);
        try {
            $stmt->execute();
        } catch (Exception $e) {
            $this->request->getSession()->getFlashBag()->add('error', $e->getMessage() . $this->__f('There was a problem recognizing the existing Dizkus tables. Please confirm that your settings for prefix in $ZConfig[\'System\'][\'prefix\'] match the actual Dizkus tables in the database. (Current prefix loaded as `%s`)', $prefix));
            return false;
        }
        // remove the legacy hooks
        $sql = "DELETE FROM hooks WHERE tmodule='Dizkus' OR smodule='Dizkus'";
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        $this->upgrade_to_4_0_0_removeTablePrefixes($prefix);
        // update dizkus_forums to prevent errors in column indexes
        $sql = 'ALTER TABLE dizkus_forums MODIFY forum_last_post_id INT DEFAULT NULL';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $sql = 'UPDATE dizkus_forums SET forum_last_post_id = NULL WHERE forum_last_post_id = 0';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        // get all the pop3 connections & hook references for later re-entry
        $sql = 'SELECT forum_id AS id, forum_moduleref as moduleref, forum_pop3_active AS active, forum_pop3_server AS server, forum_pop3_port AS port, forum_pop3_login AS login,
                forum_pop3_password AS password, forum_pop3_interval AS `interval`, forum_pop3_lastconnect AS lastconnect, forum_pop3_pnuser as userid
                FROM dizkus_forums
                WHERE forum_pop3_active = 1';
        $forumdata = $connection->fetchAll($sql);
        // fetch topic_reference and decode to migrate below (if possible)
        $sql = 'SELECT topic_id, topic_reference
                FROM dizkus_topics
                WHERE topic_reference <> \'\'';
        $hookedTopicData = $connection->fetchAll($sql);
        // delete orphaned topics with no posts to maintain referential integrity
        $sql = "DELETE from dizkus_topics WHERE topic_last_post_id = 0";
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        // NOTE: do not delete users from the dizkus_users table - they must remain for data integrity
        // change default value of rank in dizkus_users from `0` to NULL
        $sql = "UPDATE dizkus_users SET rank=NULL WHERE rank=0";
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        // set rank to NULL where rank no longer available
        $sql = "UPDATE dizkus_users set rank=NULL WHERE rank NOT IN (SELECT DISTINCT rank_id from dizkus_ranks)";
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        // @todo ?? should do ->? 'ALTER TABLE  `dizkus_forum_favorites` ADD  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST';
        if (!$this->upgrade_to_4_0_0_renameColumns()) {
            return false;
        }
        // update all the tables to 4.0.0
        try {
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[0]));
            sleep(1);
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[1]));
            sleep(1);
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[2]));
            sleep(1);
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[3]));
            sleep(1);
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[4]));
            sleep(1);
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[5]));
            sleep(1);
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[6]));
            sleep(1);
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[7]));
            sleep(2);
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[8]));
            sleep(2);
            DoctrineHelper::updateSchema($this->entityManager, array($this->_entities[9]));
            sleep(2);
        } catch (Exception $e) {
            $this->request->getSession()->getFlashBag()->add('error', $e->getMessage());
            return false;
        }
        // migrate data from old formats
        $this->upgrade_to_4_0_0_migrateCategories();
        $this->upgrade_to_4_0_0_updatePosterData();
        $this->upgrade_to_4_0_0_migrateModGroups();
        $this->upgrade_to_4_0_0_migratePop3Connections($forumdata);
        // @todo use $forumdata to migrate forum modulerefs
        $this->upgrade_to_4_0_0_migrateHookedTopics($hookedTopicData);
        $sqls = array();
        $sqls[] = "UPDATE `dizkus_topics` SET `poster`=1 WHERE poster='-1'";
        $sqls[] = "UPDATE `dizkus_posts` SET `poster_id`=1 WHERE poster_id='-1'";
        $sqls[] = "DELETE FROM `dizkus_subscription` WHERE `user_id` < 2";
        $sqls[] = "DELETE FROM `dizkus_topic_subscription` WHERE `user_id` < 2";
        foreach ($sqls as $sql) {
            $stmt = $connection->prepare($sql);
            try {
                $stmt->execute();
            } catch (Exception $e) {
            }
        }
        $this->delVar('autosubscribe');
        $this->delVar('allowgravatars');
        $this->delVar('gravatarimage');
        $this->delVar('ignorelist_handling');
        $this->delVar('hideusers');
        $this->delVar('newtopicconfirmation');
        $this->delVar('slimforum');
        $defaultModuleVars = self::getDefaultVars();
        $this->setVar('url_ranks_images', $defaultModuleVars['url_ranks_images']);
        $this->setVar('fulltextindex', $defaultModuleVars['fulltextindex']); // disable until technology catches up with InnoDB
        $this->setVar('solved_enabled', $defaultModuleVars['solved_enabled']);
        $this->setVar('ajax', $defaultModuleVars['ajax']);
        $this->setVar('defaultPoster', $defaultModuleVars['defaultPoster']);
        $this->setVar('indexTo', $defaultModuleVars['indexTo']);
        $this->setVar('notifyAdminAsMod', $defaultModuleVars['notifyAdminAsMod']);
        //add note about url_ranks_images var
        $this->request->getSession()->getFlashBag()->add('status', $this->__('Double check your path variable setting for rank images in settings!'));

        // register new hooks and event handlers
        HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());
        HookUtil::registerProviderBundles($this->version->getHookProviderBundles());
        $this->request->getSession()->getFlashBag()->add('status', $this->__('The permission schemas "Dizkus_Centerblock::" and "Dizkus_Statisticsblock" were changed into "Dizkus::Centerblock" and "Dizkus::Statisticsblock". If you were using them please modify your permission table.'));

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
            'dizkus_categories',
            'dizkus_forum_mods',
            'dizkus_forums',
            'dizkus_posts',
            'dizkus_subscription',
            'dizkus_ranks',
            'dizkus_topics',
            'dizkus_topic_subscription',
            'dizkus_forum_favorites',
            'dizkus_users');
        foreach ($dizkusTables as $value) {
            $sql = 'RENAME TABLE ' . $prefix . $value . ' TO ' . $value;
            $stmt = $connection->prepare($sql);
            try {
                $stmt->execute();
            } catch (Exception $e) {
                $this->request->getSession()->getFlashBag()->add('error', $e);
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
        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_desc description TEXT DEFAULT NULL';
        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_topics topicCount INT UNSIGNED NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_posts postCount INT UNSIGNED NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_moduleref moduleref INT UNSIGNED NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_name `name` VARCHAR(150) NOT NULL DEFAULT \'\'';
        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_last_post_id last_post_id INT DEFAULT NULL';
        $sqls[] = 'ALTER TABLE dizkus_users CHANGE user_posts postCount INT UNSIGNED NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_users CHANGE user_lastvisit lastvisit DATETIME DEFAULT NULL';
        $sqls[] = 'ALTER TABLE dizkus_users CHANGE user_post_order postOrder INT(1) NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_users CHANGE user_rank rank INT UNSIGNED NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_posts CHANGE post_title title VARCHAR(255) NOT NULL';
        $sqls[] = 'ALTER TABLE dizkus_posts CHANGE post_msgid msgid VARCHAR(100) NOT NULL';
        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_title title VARCHAR(50) NOT NULL';
        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_desc description VARCHAR(255) NOT NULL';
        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_min minimumCount INT NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_max maximumCount INT NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_image image VARCHAR(255) NOT NULL';
        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_special `type` INT(2) NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_poster poster INT NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_title title VARCHAR(255) NOT NULL';
        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_status status INT NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_views viewCount INT NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_replies replyCount INT UNSIGNED NOT NULL DEFAULT 0';
        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_reference reference VARCHAR(60) NOT NULL';
        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_last_post_id last_post_id INT DEFAULT NULL';
        foreach ($sqls as $sql) {
            $stmt = $connection->prepare($sql);
            try {
                $stmt->execute();
            } catch (Exception $e) {
                $this->request->getSession()->getFlashBag()->add('error', $e);
                return false;
            }
        }
        return true;
    }

    /**
     * Migrate categories from 3.1 > 4.0.0
     *
     */
    private function upgrade_to_4_0_0_migrateCategories()
    {
        // set up forum root
        $forumRoot = new ForumEntity();
        $forumRoot->setName(ForumEntity::ROOTNAME);
        $forumRoot->lock();
        $this->entityManager->persist($forumRoot);
        $this->entityManager->flush();
        $connection = $this->entityManager->getConnection();
        // Move old categories into new tree as Forums
        $sql = 'SELECT * FROM dizkus_categories ORDER BY cat_order ASC';
        $categories = $connection->fetchAll($sql);
        $sqls = array();
        foreach ($categories as $category) {
            // create new category forum with old name
            $newCatForum = new ForumEntity();
            $newCatForum->setName($category['cat_title']);
            $newCatForum->setParent($forumRoot);
            $newCatForum->lock();
            $this->entityManager->persist($newCatForum);
            $this->entityManager->flush();
            // create sql to update parent on child forums
            $sqls[] = "UPDATE dizkus_forums SET parent = ".$newCatForum->getForum_id().", lvl=2 WHERE cat_id = ".$category['cat_id'];
        }
        // update child forums
        foreach ($sqls as $sql) {
            $connection->executeQuery($sql);
        }
        // correct the forum tree MANUALLY
        // we know that the forum can only be two levels deep (root -> parent -> child)
        $count = 1;
        $sqls = array();
        $categories = $connection->fetchAll("SELECT * FROM dizkus_forums WHERE lvl = 1");
        foreach($categories as $category) {
            $category['l'] = ++$count;
            $children = $connection->fetchAll("SELECT * FROM dizkus_forums WHERE parent = $category[forum_id]");
            foreach ($children as $child) {
                $left = ++$count;
                $right = ++$count;
                $sqls[] = "UPDATE dizkus_forums SET forum_order = $left, rgt = $right WHERE forum_id = $child[forum_id]";
            }
            $right = ++$count;
            $sqls[] = "UPDATE dizkus_forums SET forum_order = $category[l], rgt = $right WHERE forum_id = $category[forum_id]";
        }
        $right = ++$count;
        $sqls[] = "UPDATE dizkus_forums SET forum_order = 1, rgt = $right WHERE parent IS NULL";
        $sqls[] = "UPDATE dizkus_forums SET cat_id = 1 WHERE 1";

        foreach ($sqls as $sql) {
            $connection->executeQuery($sql);
        }

        // drop the old categories table
        $sql = 'DROP TABLE dizkus_categories';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
    }

    /**
     * Update Poster Data from 3.1 > 4.0.0
     *
     */
    private function upgrade_to_4_0_0_updatePosterData()
    {
        $connection = $this->entityManager->getConnection();
        $Posters = $connection->executeQuery("SELECT DISTINCT poster_id from dizkus_posts WHERE poster_id NOT IN (SELECT DISTINCT user_id FROM dizkus_users)");
        $newUserCount = 0;
        foreach ($Posters as $poster) {
            $posterId = $poster['poster_id'];
            if ($posterId > 0) {
                $forumUser = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumUserEntity')->find($posterId);
                // if a ForumUser cannot be found, create one
                if (!$forumUser) {
                    $forumUser = new ForumUserEntity($posterId);
                    $this->entityManager->persist($forumUser);
                    $newUserCount++;
                }
            }
            if ($newUserCount > 20) {
                $this->entityManager->flush();
                $newUserCount = 0;
            }
        }
        if ($newUserCount > 0) {
            $this->entityManager->flush();
        }
        ModUtil::apiFunc($this->name, 'Sync', 'all');
    }

    /**
     * Migrate the Moderator Groups out of the `dizkus_forum_mods` table and put
     * in the new `dizkus_forum_mods_group` table
     */
    private function upgrade_to_4_0_0_migrateModGroups()
    {
        $connection = $this->entityManager->getConnection();
        $sql = 'SELECT * FROM dizkus_forum_mods WHERE user_id > 1000000';
        $groups = $connection->fetchAll($sql);
        foreach ($groups as $group) {
            $groupId = $group['user_id'] - 1000000;
            $modGroup = new ModeratorGroupEntity();
            $coreGroup = $this->entityManager->find('Zikula\\Module\\GroupsModule\\Entity\\GroupEntity', $groupId);
            if ($coreGroup) {
                $modGroup->setGroup($coreGroup);
                $forum = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\ForumEntity', $group['forum_id']);
                $modGroup->setForum($forum);
                $this->entityManager->persist($modGroup);
            }
        }
        $this->entityManager->flush();
        // remove old group entries
        $sql = 'DELETE FROM dizkus_forum_mods WHERE user_id > 1000000';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
    }

    /**
     * migrate pop3 connection data from multiple columns to one object
     *
     * @param type $connections
     */
    private function upgrade_to_4_0_0_migratePop3Connections($connections)
    {
        foreach ($connections as $connectionData) {
            $connectionData['coreUser'] = $this->entityManager->getReference('Zikula\\Module\\UsersModule\\Entity\\UserEntity', $connectionData['userid']);
            $connection = new Pop3Connection($connectionData);
            $forum = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\ForumEntity', $connectionData['id']);
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
            $topic = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\TopicEntity', $row['topic_id']);
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
                        $subscriberArea = $this->entityManager->getRepository('Zikula\\Component\\HookDispatcher\\Storage\\Doctrine\\Entity\\HookAreaEntity')->findBy($searchCritera);
                        if (count($subscriberArea) != 1) {
                            // found either too many areas or none. cannot migrate
                            $topic->lock();
                        } else {
                            // finally set the information
                            $topic->setHookedModule($moduleInfo['name']);
                            $topic->setHookedAreaId($subscriberArea->getId());
                            $topic->setHookedObjectId($objectId);
                        }
                    }
                }
                $count++;
                if ($count > 20) {
                    $this->entityManager->flush();
                    $count = 0;
                }
            }
        }
        // flush remaining
        $this->entityManager->flush();
    }

}
