<?php

/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */
namespace Zikula\DizkusModule\Helper;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * ImportHelper
 *
 * @author Kaik
 */
class ImportHelper
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
            RequestStack $requestStack,
            EntityManager $entityManager
         ) {
        $this->name = 'ZikulaDizkusModule';
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
    }

    /**
     * remove all table prefixes.
     */
    public function getTablesForPrefix($prefix = 'import_')
    {
        $connection = $this->entityManager->getConnection();
        // remove table prefixes
        $importTables = [];
        foreach ($connection->getSchemaManager()->listTables() as $tableDetails) {
            if (strpos($tableDetails->getName(), $prefix) !== false) {
                $importTables[$tableDetails->getName()]['elements'] = $connection->fetchAll('SELECT * FROM '. $tableDetails->getName());
                $importTables[$tableDetails->getName()]['status'] = $this->checkTableStatus($tableDetails, $prefix);
            }
        }

        return $importTables;
    }

    /**
     *
     */
    public function checkTableStatus($tableDetails, $prefix)
    {
        if (!in_array(str_replace($prefix, "",$tableDetails->getName()), $this->getSupportedTables())) {
            return false;
        }

        return $this->checkTableColumns($tableDetails, $prefix);
    }

    /**
     *
     */
    public function getSupportedTables()
    {
        return ['dizkus_categories',
                'dizkus_forum_mods',
                'dizkus_forums',
                'dizkus_posts',
                'dizkus_subscription',
                'dizkus_ranks',
                'dizkus_topics',
                'dizkus_topic_subscription',
                'dizkus_forum_favorites',
                'dizkus_users'
        ];
    }

    /**
     *
     */
    public function checkTableColumns($tableDetails, $prefix)
    {
        if ($this->check310TableCompatibility($tableDetails, $prefix)) {
            return '3.1.0';
        }

        return false;
    }

    /**
     *
     */
    public function check310TableCompatibility($tableDetails, $prefix)
    {
        $tablesWithColumns = [
                'dizkus_categories' => ['cat_id',
                                    'cat_title',
                                    'cat_order'
                ],
                'dizkus_forum_mods' => ['id',
                                    'forum_id',
                                    'user_id'
                ],
                'dizkus_forums' => ['forum_id',
                                    'forum_name',
                                    'forum_desc',
                                    'forum_topics',
                                    'forum_posts',
                                    'forum_last_post_id',
                                    'cat_id',
                                    'is_subforum',
                                    'forum_order',
                                    'forum_pop3_active',
                                    'forum_pop3_server',
                                    'forum_pop3_port',
                                    'forum_pop3_login',
                                    'forum_pop3_password',
                                    'forum_pop3_interval',
                                    'forum_pop3_lastconnect',
                                    'forum_pop3_pnuser',
                                    'forum_pop3_pnpassword',
                                    'forum_pop3_matchstring',
                                    'forum_moduleref',
                                    'forum_pntopic'
                ],
                'dizkus_posts' => ['post_id',
                                   'topic_id',
                                   'forum_id',
                                   'poster_id',
                                   'post_time',
                                   'poster_ip',
                                   'post_msgid',
                                   'post_text',
                                   'post_title'
                ],
                'dizkus_subscription' => ['msg_id',
                                          'forum_id',
                                          'user_id'
                ],
                'dizkus_ranks' => ['rank_id',
                                   'rank_title',
                                   'rank_min',
                                   'rank_max',
                                   'rank_special',
                                   'rank_image',
                                   'rank_desc'
                ],
                'dizkus_topics' => ['topic_id',
                                    'topic_title',
                                    'topic_poster',
                                    'topic_time',
                                    'topic_views',
                                    'topic_replies',
                                    'topic_last_post_id',
                                    'forum_id',
                                    'topic_status',
                                    'sticky',
                                    'topic_reference'
                ],
                'dizkus_topic_subscription' => ['id',
                                                'topic_id',
                                                'user_id'
                ],
                'dizkus_forum_favorites' => ['forum_id',
                                             'user_id'
                ],
                'dizkus_users' => ['user_id',
                                   'user_posts',
                                   'user_rank',
                                   'user_level',
                                   'user_lastvisit',
                                   'user_favorites',
                                   'user_post_order'
                ],
        ];

        if (key_exists(str_replace($prefix, "",$tableDetails->getName()), $tablesWithColumns)) {
            $importTableColumns = array_keys($tableDetails->getColumns());
            $supportedColumns = $tablesWithColumns[str_replace($prefix, "",$tableDetails->getName())];
//            dump($importTableColumns);
//            dump($supportedColumns);
            // exact fields order checl
            return array_diff($importTableColumns, $supportedColumns) === array_diff($supportedColumns, $importTableColumns);
        }

        return false;
    }

}



    /**
//     * upgrade to 4.0.0.
//     */
//    private function upgrade_to_4_0_0()
//    {
////        // update dizkus_forums to prevent errors in column indexes
////        $sql = 'ALTER TABLE dizkus_forums MODIFY forum_last_post_id INT DEFAULT NULL';
////        $stmt = $connection->prepare($sql);
////        $stmt->execute();
////
////        $sql = 'UPDATE dizkus_forums SET forum_last_post_id = NULL WHERE forum_last_post_id = 0';
////        $stmt = $connection->prepare($sql);
////        $stmt->execute();
//
////        // get all the pop3 connections & hook references for later re-entry
////        $sql = 'SELECT forum_id AS id, forum_moduleref as moduleref, forum_pop3_active AS active, forum_pop3_server AS server, forum_pop3_port AS port, forum_pop3_login AS login,
////                forum_pop3_password AS password, forum_pop3_interval AS `interval`, forum_pop3_lastconnect AS lastconnect, forum_pop3_pnuser as userid
////                FROM dizkus_forums
////                WHERE forum_pop3_active = 1';
////        $forumdata = $connection->fetchAll($sql);
////
////        // fetch topic_reference and decode to migrate below (if possible)
////        $sql = 'SELECT topic_id, topic_reference
////                FROM dizkus_topics
////                WHERE topic_reference <> \'\'';
////        $hookedTopicData = $connection->fetchAll($sql);
//
//        // delete orphaned topics with no posts to maintain referential integrity
////        $sql = 'DELETE from dizkus_topics WHERE topic_last_post_id = 0';
////        $stmt = $connection->prepare($sql);
////        $stmt->execute();
//
//        // @todo ?? should do ->? 'ALTER TABLE  `dizkus_forum_favorites` ADD  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST';
////        if (!$this->upgrade_to_4_0_0_renameColumns()) {
////            return false;
////        }
//
////        // NOTE: do not delete users from the dizkus_users table - they must remain for data integrity
////        // change default value of rank in dizkus_users from `0` to NULL
////        $sql = 'UPDATE dizkus_users SET rank=NULL WHERE rank=0';
////        $stmt = $connection->prepare($sql);
////        $stmt->execute();
////        // set rank to NULL where rank no longer available
////        $sql = 'UPDATE dizkus_users set rank=NULL WHERE rank NOT IN (SELECT DISTINCT rank_id from dizkus_ranks)';
////        $stmt = $connection->prepare($sql);
////        $stmt->execute();
//
//        // update all the tables to 4.0.0
//        try {
//            $this->schemaTool->update($this->entities);
//            //sleep(1);
////            $this->schemaTool->update(['Zikula\DizkusModule\Entity\PostEntity']);
//////            sleep(1);
////            $this->schemaTool->update([$this->entities[2]]);
//////            sleep(1);
////            $this->schemaTool->update([$this->entities[3]]);
//////            sleep(1);
////            $this->schemaTool->update([$this->entities[4]]);
//////            sleep(1);
////            $this->schemaTool->update([$this->entities[5]]);
//////            sleep(1);
////            $this->schemaTool->update([$this->entities[6]]);
//////            sleep(1);
////            $this->schemaTool->update([$this->entities[7]]);
//////            sleep(2);
////            $this->schemaTool->update([$this->entities[8]]);
//////            sleep(2);
////            $this->schemaTool->update([$this->entities[9]]);
////            sleep(2);
//        } catch (\Exception $e) {
//            $this->addFlash('error', $e->getMessage());
//
//            return false;
//        }
////        // migrate data from old formats
////        $this->upgrade_to_4_0_0_migrateCategories();
////        $this->upgrade_to_4_0_0_updatePosterData();
////        $this->upgrade_to_4_0_0_migrateModGroups();
////        $this->upgrade_to_4_0_0_migratePop3Connections($forumdata);
////        // @todo use $forumdata to migrate forum modulerefs
////        //$this->upgrade_to_4_0_0_migrateHookedTopics($hookedTopicData);
////        $sqls = [];
////        $sqls[] = "UPDATE `dizkus_topics` SET `poster`=1 WHERE poster='-1'";
////        $sqls[] = "UPDATE `dizkus_posts` SET `poster_id`=1 WHERE poster_id='-1'";
////        $sqls[] = 'DELETE FROM `dizkus_subscription` WHERE `user_id` < 2';
////        $sqls[] = 'DELETE FROM `dizkus_topic_subscription` WHERE `user_id` < 2';
////        $sqls[] = 'DELETE FROM `dizkus_topic_subscription` WHERE topic_id NOT IN (SELECT topic_id from dizkus_topics)';
////        foreach ($sqls as $sql) {
////            $stmt = $connection->prepare($sql);
////            try {
////                $stmt->execute();
////            } catch (\Exception $e) {
////            }
////        }
////        $this->delVar('autosubscribe');
////        $this->delVar('allowgravatars');
////        $this->delVar('gravatarimage');
////        $this->delVar('ignorelist_handling');
////        $this->delVar('hideusers');
////        $this->delVar('newtopicconfirmation');
////        $this->delVar('slimforum');
////        $defaultModuleVars = self::getDefaultVars($this->variableApi->getAll('ZConfig'));
////        $this->setVar('url_ranks_images', $defaultModuleVars['url_ranks_images']);
////        $this->setVar('fulltextindex', $defaultModuleVars['fulltextindex']); // disable until technology catches up with InnoDB
////        $this->setVar('solved_enabled', $defaultModuleVars['solved_enabled']);
////        $this->setVar('ajax', $defaultModuleVars['ajax']);
////        $this->setVar('defaultPoster', $defaultModuleVars['defaultPoster']);
////        $this->setVar('indexTo', $defaultModuleVars['indexTo']);
////        $this->setVar('notifyAdminAsMod', $defaultModuleVars['notifyAdminAsMod']);
////        //add note about url_ranks_images var
////        $this->addFlash('status', $this->__('Double check your path variable setting for rank images in settings!'));
////
////        // register new hooks and event handlers
////        $this->hookApi->installSubscriberHooks($this->bundle->getMetaData());
////        $this->hookApi->installProviderHooks($this->bundle->getMetaData());
////        // update block settings
////        $mid = $connection->fetchColumn("SELECT DISTINCT id from modules WHERE name='Dizkus' OR name='".$this->name."'");
////        if (!empty($mid) && is_int($mid)) {
////            $sql = "UPDATE blocks SET bkey='RecentPostsBlock', content='a:0:{}' WHERE bkey='CenterBlock' AND mid=$mid";
////            $stmt = $connection->prepare($sql);
////            $stmt->execute();
////            $sql = "UPDATE blocks SET content='a:0:{}' WHERE bkey='StatisticsBlock' AND mid=$mid";
////            $stmt = $connection->prepare($sql);
////            $stmt->execute();
////        }
////        $repArray = ['Dizkus_Centerblock::', 'Dizkus_Statisticsblock', "{$this->name}::RecentPostsBlock", "{$this->name}::StatisticsBlock"];
////        $this->addFlash('status', $this->__f('The permission schemas %1$s and %2$s were changed into %3$s and %4$s, respectively. If you were using them please modify your permission table.', $repArray));
//
//        return false;
//    }
//
//    /**
//     * remove all table prefixes.
//     */
//    public function changeTablePrefixes($prefix)
//    {
//        $connection = $this->entityManager->getConnection();
//        // remove table prefixes
//        $dizkusTables = [
//            'dizkus_categories',
//            'dizkus_forum_mods',
//            'dizkus_forums',
//            'dizkus_posts',
//            'dizkus_subscription',
//            'dizkus_ranks',
//            'dizkus_topics',
//            'dizkus_topic_subscription',
//            'dizkus_forum_favorites',
//            'dizkus_users', ];
//        foreach ($dizkusTables as $value) {
//            $sql = 'RENAME TABLE '.$prefix.$value.' TO '.$value;
//            $stmt = $connection->prepare($sql);
//            try {
//                $stmt->execute();
//            } catch (\Exception $e) {
//                $this->addFlash('error', $e);
//            }
//        }
//    }
//
////    /**
////     * rename some table columns
////     * This must be done before updateSchema takes place.
////     */
////    private function upgrade_to_4_0_0_renameColumns()
////    {
////        $connection = $this->entityManager->getConnection();
////        $sqls = [];
////        // a list of column changes
////        //Forum
////        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_desc description TEXT DEFAULT NULL';
////        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_topics topicCount INT UNSIGNED NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_posts postCount INT UNSIGNED NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_moduleref moduleref INT UNSIGNED NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_name `name` VARCHAR(150) NOT NULL DEFAULT \'\'';
////        $sqls[] = 'ALTER TABLE dizkus_forums CHANGE forum_last_post_id last_post_id INT DEFAULT NULL';
////        //User
////        $sqls[] = 'ALTER TABLE dizkus_users CHANGE user_posts postCount INT UNSIGNED NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_users CHANGE user_lastvisit lastvisit DATETIME DEFAULT NULL';
////        $sqls[] = 'ALTER TABLE dizkus_users CHANGE user_post_order postOrder INT(1) NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_users CHANGE user_rank rank INT UNSIGNED NOT NULL DEFAULT 0';
////        //Post
////        $sqls[] = 'ALTER TABLE dizkus_posts CHANGE post_title title VARCHAR(255) NOT NULL';
////        $sqls[] = 'ALTER TABLE dizkus_posts CHANGE post_msgid msgid VARCHAR(100) NOT NULL';
////        //Ranks
////        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_title title VARCHAR(50) NOT NULL';
////        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_desc description VARCHAR(255) NOT NULL';
////        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_min minimumCount INT NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_max maximumCount INT NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_image image VARCHAR(255) NOT NULL';
////        $sqls[] = 'ALTER TABLE dizkus_ranks CHANGE rank_special `type` INT(2) NOT NULL DEFAULT 0';
////        // Topic
////        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_poster poster INT NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_title title VARCHAR(255) NOT NULL';
////        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_status status INT NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_views viewCount INT NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_replies replyCount INT UNSIGNED NOT NULL DEFAULT 0';
////        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_reference reference VARCHAR(60) NOT NULL';
////        $sqls[] = 'ALTER TABLE dizkus_topics CHANGE topic_last_post_id last_post_id INT DEFAULT NULL';
////
////        foreach ($sqls as $sql) {
////            $stmt = $connection->prepare($sql);
////            try {
////                $stmt->execute();
////            } catch (\Exception $e) {
////                $this->addFlash('error', $e);
////
////                return false;
////            }
////        }
////
////        return true;
////    }
////
////    /**
////     * Migrate categories from 3.1 > 4.0.0.
////     */
////    private function upgrade_to_4_0_0_migrateCategories()
////    {
////        // set up forum root
////        $forumRoot = new ForumEntity();
////        $forumRoot->setName(ForumEntity::ROOTNAME);
////        $forumRoot->lock();
////        $this->entityManager->persist($forumRoot);
////        $this->entityManager->flush();
////        $connection = $this->entityManager->getConnection();
////        // Move old categories into new tree as Forums
////        $sql = 'SELECT * FROM dizkus_categories ORDER BY cat_order ASC';
////        $categories = $connection->fetchAll($sql);
////        $sqls = [];
////        foreach ($categories as $category) {
////            // create new category forum with old name
////            $newCatForum = new ForumEntity();
////            $newCatForum->setName($category['cat_title']);
////            $newCatForum->setParent($forumRoot);
////            $newCatForum->lock();
////            $this->entityManager->persist($newCatForum);
////            $this->entityManager->flush();
////            // create sql to update parent on child forums
////            $sqls[] = 'UPDATE dizkus_forums SET parent = '.$newCatForum->getForum_id().', lvl=2 WHERE cat_id = '.$category['cat_id'];
////        }
////        // update child forums
////        foreach ($sqls as $sql) {
////            $connection->executeQuery($sql);
////        }
////        // correct the forum tree MANUALLY
////        // we know that the forum can only be two levels deep (root -> parent -> child)
////        $count = 1;
////        $sqls = [];
////        $categories = $connection->fetchAll('SELECT * FROM dizkus_forums WHERE lvl = 1');
////        foreach ($categories as $category) {
////            $category['l'] = ++$count;
////            $children = $connection->fetchAll("SELECT * FROM dizkus_forums WHERE parent = $category[forum_id]");
////            foreach ($children as $child) {
////                $left = ++$count;
////                $right = ++$count;
////                $sqls[] = "UPDATE dizkus_forums SET forum_order = $left, rgt = $right WHERE forum_id = $child[forum_id]";
////            }
////            $right = ++$count;
////            $sqls[] = "UPDATE dizkus_forums SET forum_order = $category[l], rgt = $right WHERE forum_id = $category[forum_id]";
////        }
////        $right = ++$count;
////        $sqls[] = "UPDATE dizkus_forums SET forum_order = 1, rgt = $right WHERE parent IS NULL";
////        $sqls[] = 'UPDATE dizkus_forums SET cat_id = 1 WHERE 1';
////
////        foreach ($sqls as $sql) {
////            $connection->executeQuery($sql);
////        }
////
////        // drop the old categories table
////        $sql = 'DROP TABLE dizkus_categories';
////        $stmt = $connection->prepare($sql);
////        $stmt->execute();
////    }
////
////    /**
////     * Update Poster Data from 3.1 > 4.0.0.
////     */
////    private function upgrade_to_4_0_0_updatePosterData()
////    {
////        $connection = $this->entityManager->getConnection();
////        $Posters = $connection->executeQuery('SELECT DISTINCT poster_id from dizkus_posts WHERE poster_id NOT IN (SELECT DISTINCT user_id FROM dizkus_users)');
////        $newUserCount = 0;
////        foreach ($Posters as $poster) {
////            $posterId = $poster['poster_id'];
////            if ($posterId > 0) {
////                $forumUser = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumUserEntity')->find($posterId);
////                // if a ForumUser cannot be found, create one
////                if (!$forumUser) {
////                    $forumUser = new ForumUserEntity($posterId);
////                    $this->entityManager->persist($forumUser);
////                    ++$newUserCount;
////                }
////            }
////            if ($newUserCount > 20) {
////                $this->entityManager->flush();
////                $newUserCount = 0;
////            }
////        }
////        if ($newUserCount > 0) {
////            $this->entityManager->flush();
////        }
////        $this->container->get('zikula_dizkus_module.synchronization_helper')->all();
////    }
////
////    /**
////     * Migrate the Moderator Groups out of the `dizkus_forum_mods` table and put
////     * in the new `dizkus_forum_mods_group` table.
////     */
////    private function upgrade_to_4_0_0_migrateModGroups()
////    {
////        $connection = $this->entityManager->getConnection();
////        $sql = 'SELECT * FROM dizkus_forum_mods WHERE user_id > 1000000';
////        $groups = $connection->fetchAll($sql);
////        foreach ($groups as $group) {
////            $groupId = $group['user_id'] - 1000000;
////            $modGroup = new ModeratorGroupEntity();
////            $coreGroup = $this->entityManager->find('Zikula\\GroupsModule\\Entity\\GroupEntity', $groupId);
////            if ($coreGroup) {
////                $modGroup->setGroup($coreGroup);
////                $forum = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $group['forum_id']);
////                $modGroup->setForum($forum);
////                $this->entityManager->persist($modGroup);
////            }
////        }
////        $this->entityManager->flush();
////        // remove old group entries
////        $sql = 'DELETE FROM dizkus_forum_mods WHERE user_id > 1000000';
////        $stmt = $connection->prepare($sql);
////        $stmt->execute();
////    }
////
////    /**
////     * migrate pop3 connection data from multiple columns to one object.
////     *
////     * @param type $connections
////     */
////    private function upgrade_to_4_0_0_migratePop3Connections($connections)
////    {
////        foreach ($connections as $connectionData) {
////            $connectionData['coreUser'] = $this->entityManager->getReference('Zikula\\UsersModule\\Entity\\UserEntity', $connectionData['userid']);
////            $connection = new Pop3Connection($connectionData);
////            $forum = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $connectionData['id']);
////            $forum->setPop3Connection($connection);
////        }
////        $this->entityManager->flush();
////    }
////
////    /**
////     * migrate hooked topics data to maintain hook connection with original object.
////     *
////     * This routine will only attempt to migrate references where the topic_reference field
////     * looks like `moduleID-objectId` -> e.g. '14-57'. If the field contains any underscores
////     * the topic will be locked and the reference left unmigrated. This is mainly because
////     * modules that use that style of reference are not compatible with Core 1.4.0+
////     * anyway and so migrating their references would be pointless.
////     *
////     * Additionally, if the subscriber module has more than one subscriber area, then migration is
////     * also impossible (which to choose?) so the topic is locked and reference left
////     * unmigrated also.
////     *
////     * @param array $rows
////     */
////    private function upgrade_to_4_0_0_migrateHookedTopics($rows)
////    {
////        $count = 0;
////        foreach ($rows as $row) {
////            $topic = $this->entityManager->find('Zikula\DizkusModule\Entity\TopicEntity', $row['topic_id']);
////            if (isset($topic)) {
////                if (strpos($row['topic_reference'], '_') !== false) {
////                    // reference contains an unsupported underscore, lock the topic
////                    $topic->lock();
////                } else {
////                    list($moduleId, $objectId) = explode('-', $row['topic_reference']);
////                    //$moduleInfo = ModUtil::getInfo($moduleId);
////                    $module = $this->entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionEntity')->find($moduleId);
////                    if ($module) {
////                        $searchCritera = [
////                            'owner' => $module->getName(),
////                            'areatype' => 's',
////                            'category' => 'ui_hooks', ];
////                        $subscriberArea = $this->entityManager->getRepository('Zikula\\Component\\HookDispatcher\\Storage\\Doctrine\\Entity\\HookAreaEntity')->findBy($searchCritera);
////                        if (count($subscriberArea) != 1) {
////                            // found either too many areas or none. cannot migrate
////                            $topic->lock();
////                        } else {
////                            // finally set the information
////                            $topic->setHookedModule($module->getName());
////                            $topic->setHookedAreaId($subscriberArea->getId());
////                            $topic->setHookedObjectId($objectId);
////                        }
////                    }
////                }
////
////                ++$count;
////                if ($count > 20) {
////                    $this->entityManager->flush();
////                    $count = 0;
////                }
////            }
////        }
////        // flush remaining
////        $this->entityManager->flush();
////    }