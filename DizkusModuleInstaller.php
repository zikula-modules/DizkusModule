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

namespace Zikula\DizkusModule;

use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\Core\AbstractExtensionInstaller;

class DizkusModuleInstaller extends AbstractExtensionInstaller
{
    /**
     * Module name
     * (needed for static methods).
     *
     * @var string
     */
    const MODULENAME = 'ZikulaDizkusModule';

    private $entities = [
        'Zikula\DizkusModule\Entity\ForumEntity',
        'Zikula\DizkusModule\Entity\PostEntity',
        'Zikula\DizkusModule\Entity\TopicEntity',
        'Zikula\DizkusModule\Entity\ForumUserFavoriteEntity',
        'Zikula\DizkusModule\Entity\ForumUserEntity',
        'Zikula\DizkusModule\Entity\ForumSubscriptionEntity',
        'Zikula\DizkusModule\Entity\TopicSubscriptionEntity',
        'Zikula\DizkusModule\Entity\RankEntity',
        'Zikula\DizkusModule\Entity\ModeratorUserEntity',
        'Zikula\DizkusModule\Entity\ModeratorGroupEntity',
    ];

    //import
    private $importTables = [
            'dizkus_categories',
            'dizkus_forum_mods',
            'dizkus_forums',
            'dizkus_posts',
            'dizkus_subscription',
            'dizkus_ranks',
            'dizkus_topics',
            'dizkus_topic_subscription',
            'dizkus_forum_favorites',
            'dizkus_users',
    ];

    /**
     *  Initialize a new install of the Dizkus module.
     *
     *  This function will initialize a new installation of Dizkus.
     *  It is accessed via the Zikula Admin interface and should
     *  not be called directly.
     */
    public function install()
    {
        try {
            $this->schemaTool->create($this->entities);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());

            return false;
        }
        // ToDo: create FULLTEXT index
        // set the module vars
        $this->setVars(self::getDefaultVars());
        $this->hookApi->installSubscriberHooks($this->bundle->getMetaData());
        $this->hookApi->installProviderHooks($this->bundle->getMetaData());
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
     * Set up example forums on install.
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
        $ranks = [
            [
                'title' => 'Level 1',
                'description' => 'New forum user',
                'minimumCount' => 1,
                'maximumCount' => 9,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'zerostar.gif', ],
            [
                'title' => 'Level 2',
                'description' => 'Basic forum user',
                'minimumCount' => 10,
                'maximumCount' => 49,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'onestar.gif', ],
            [
                'title' => 'Level 3',
                'description' => 'Moderate forum user',
                'minimumCount' => 50,
                'maximumCount' => 99,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'twostars.gif', ],
            [
                'title' => 'Level 4',
                'description' => 'Advanced forum user',
                'minimumCount' => 100,
                'maximumCount' => 199,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'threestars.gif', ],
            [
                'title' => 'Level 5',
                'description' => 'Expert forum user',
                'minimumCount' => 200,
                'maximumCount' => 499,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'fourstars.gif', ],
            [
                'title' => 'Level 6',
                'description' => 'Superior forum user',
                'minimumCount' => 500,
                'maximumCount' => 999,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'fivestars.gif', ],
            [
                'title' => 'Level 7',
                'description' => 'Senior forum user',
                'minimumCount' => 1000,
                'maximumCount' => 4999,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'spezstars.gif', ],
            [
                'title' => 'Legend',
                'description' => 'Legend forum user',
                'minimumCount' => 5000,
                'maximumCount' => 1000000,
                'type' => RankEntity::TYPE_POSTCOUNT,
                'image' => 'adminstars.gif', ], ];
        foreach ($ranks as $rank) {
            $r = new RankEntity();
            $r->merge($rank);
            $this->entityManager->persist($r);
        }
        $this->entityManager->flush();
    }

    /**
     *  Deletes an install of the Dizkus module.
     *
     *  This function removes Dizkus from your
     *  Zikula install and should be accessed via
     *  the Zikula Admin interface
     */
    public function uninstall()
    {
        try {
            $this->schemaTool->drop($this->entities);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());

            return false;
        }
        // remove module vars
        $this->delVars();
        // unregister hooks
        $this->hookApi->uninstallSubscriberHooks($this->bundle->getMetaData());
        $this->hookApi->uninstallProviderHooks($this->bundle->getMetaData());
        // Deletion successful
        return true;
    }

    public function upgrade($oldversion)
    {
        // Only support upgrade from version 3.1 and up. Notify users if they have a version below that one.
        if (version_compare($oldversion, '3.1', '<')) {
            // Inform user about error, and how he can upgrade to $modversion
            $upgradeToVersion = $this->bundle->getMetaData()->getVersion();

            $this->addFlash('error', $this->__f('Notice: This version does not support upgrades from versions of Dizkus less than 3.1. Please upgrade to 3.1 before upgrading again to version %s.', $upgradeToVersion));

            return false;
        }
        switch ($oldversion) {
            case '3.1':
            case '3.1.0':
            case '3.2.0':
                if (!$this->upgrade_settings()) {
                    return false;
                }

                $connection = $this->entityManager->getConnection();
                $dbName = $this->container->getParameter('database_name');
                $connection->executeQuery("DELETE FROM $dbName.`hook_area` WHERE `owner` = 'Dizkus'");
                $connection->executeQuery("DELETE FROM $dbName.`hook_binding` WHERE `sowner` = 'Dizkus'");
                $connection->executeQuery("DELETE FROM $dbName.`hook_runtime` WHERE `sowner` = 'Dizkus'");
                $connection->executeQuery("DELETE FROM $dbName.`hook_subscriber` WHERE `owner` = 'Dizkus'");

                $prefix = $this->container->hasParameter('prefix') ? $this->container->getParameter('prefix') : '';
                $schemaManager = $connection->getSchemaManager();
                $schema = $schemaManager->createSchema();
                if (!$schema->hasTable($prefix.'dizkus_categories')) {
                    $this->addFlash('error', $e->getMessage().$this->__f('There was a problem recognizing the existing Dizkus tables. Please confirm that your settings for prefix in $ZConfig[\'System\'][\'prefix\'] match the actual Dizkus tables in the database. (Current prefix loaded as `%s`)', ['%s' => $prefix]));

                    return false;
                }

                $name = $prefix.'dizkus_users';
                if (!$schema->hasTable($name)) {
                    // 3.2.0 users dummy table
                    $table = $schema->createTable($name);
                    $table->addColumn('user_id', 'integer');
                    $table->addColumn('user_posts', 'integer');
                    $table->addColumn('user_rank', 'integer');
                    $table->addColumn('user_level', 'integer');
                    $table->addColumn('user_lastvisit', 'datetime');
                    $table->addColumn('user_favorites', 'integer');
                    $table->addColumn('user_post_order', 'integer');
                    $sql = $schemaManager->getDatabasePlatform()->getCreateTableSQL($table);
                    $statement = $connection->prepare($sql[0]);
                    $statement->execute();
                }

                if ($prefix != '') {
                    $this->removeTablePrefixes($prefix);
                }
                // mark tables for import
                $upgrade_mark = str_replace('.', '_', $oldversion) . '_';
                $this->markTablesForImport($upgrade_mark);
                // add upgrading info for later
                $this->setVar('upgrading', str_replace('.', '_', $oldversion));
                //install module now
                try {
                    $this->schemaTool->create($this->entities);
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());

                    return false;
                }

                $this->hookApi->installSubscriberHooks($this->bundle->getMetaData());
                $this->hookApi->installProviderHooks($this->bundle->getMetaData());
                // set up forum root (required)
                $forumRoot = new ForumEntity();
                $forumRoot->setName(ForumEntity::ROOTNAME);
                $forumRoot->lock();
                $this->entityManager->persist($forumRoot);

                break;
            case '4.0.0':

                if (!$this->upgrade_settings()) {
                    return false;
                }
                // reinstall hooks
                $connection = $this->entityManager->getConnection();
                $dbName = $this->container->getParameter('database_name');
                $connection->executeQuery("DELETE FROM $dbName.`hook_area` WHERE `owner` = 'ZikulaDizkusModule'");
                $connection->executeQuery("DELETE FROM $dbName.`hook_binding` WHERE `sowner` = 'ZikulaDizkusModule'");
                $connection->executeQuery("DELETE FROM $dbName.`hook_runtime` WHERE `sowner` = 'ZikulaDizkusModule'");
                $connection->executeQuery("DELETE FROM $dbName.`hook_subscriber` WHERE `owner` = 'ZikulaDizkusModule'");

                $this->hookApi->installSubscriberHooks($this->bundle->getMetaData());
                $this->hookApi->installProviderHooks($this->bundle->getMetaData());

                break;
        }

        return true;
    }

    /**
     * remove all table prefixes.
     */
    public function removeTablePrefixes($prefix)
    {
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->getSchemaManager();
        $schema = $schemaManager->createSchema();
        // remove table prefixes
        foreach ($this->importTables as $value) {
            if (!$schema->hasTable($prefix.$value)) {
                continue;
            }

            $sql = 'RENAME TABLE '.$prefix.$value.' TO '.$value;
            $stmt = $connection->prepare($sql);

            try {
                $stmt->execute();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage().$this->__f('There was a problem recognizing the existing Dizkus tables. Please confirm that your prefix match the actual Dizkus tables in the database. (Current prefix loaded as `%s`)', ['%s' => $prefix]));

                return false;
            }
        }
    }

    /**
     * Mark tables for import with import_ prefix
     */
    public function markTablesForImport($prefix)
    {
        $connection = $this->entityManager->getConnection();
        $schemaManager = $connection->getSchemaManager();
        $schema = $schemaManager->createSchema();
        // remove table prefixes
        foreach ($this->importTables as $value) {
            if (!$schema->hasTable($prefix.$value)) {
                continue;
            }

            $sql = 'RENAME TABLE '.$value.' TO '.$prefix.$value;
            $stmt = $connection->prepare($sql);

            try {
                $stmt->execute();
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage().$this->__f('There was a problem recognizing the existing Dizkus tables. Please confirm that your prefix match the actual Dizkus tables in the database. (Current prefix loaded as `%s`)', ['%s' => $prefix]));

                return false;
            }
        }
    }

    /**
     * get the default module var values.
     *
     * @return array
     */
    public static function getDefaultVars()
    {
        return [
            'posts_per_page' => 15,
            'topics_per_page' => 15,
            'hot_threshold' => 20,
            'email_from' => '', // use system default email
            'url_ranks_images' => "ranks",
            'post_sort_order' => 'ASC',
            'log_ip' => false,
            'extendedsearch' => false,
            'm2f_enabled' => false,
            'favorites_enabled' => true,
            'removesignature' => false,
            'striptags' => true,
            'hooks' => ['providers' => [], 'subscribers' => []],
            'rss2f_enabled' => false,
            'timespanforchanges' => 24,
            'forum_enabled' => true,
            'forum_disabled_info' => 'Sorry! The forums are currently off-line for maintenance. Please try later.',
            'signaturemanagement' => false,
            'signature_start' => '--',
            'signature_end' => '--',
            'showtextinsearchresults' => true,
            'minsearchlength' => 3,
            'maxsearchlength' => 30,
            'fulltextindex' => false,
            'solved_enabled' => true,
            'ajax' => true,
            'striptagsfromemail' => false,
            'indexTo' => '',
            'notifyAdminAsMod' => 2,
            'defaultPoster' => 2,
            'onlineusers_moderatorcheck' => false,
            'forum_subscriptions_enabled' => false,
            'topic_subscriptions_enabled' => false,
        ];
    }

    /**
     * Upgrade settings to current version (to_4_1_0)
     */
    private function upgrade_settings()
    {
        $currentModVars = $this->getVars();
        $defVars = $this->getDefaultVars();

        foreach ($defVars as $key => $defVar) {
            if (array_key_exists($key, $currentModVars)) {
                $type = gettype($defVar);
                switch ($type) {
                    case 'boolean':
                        if (in_array($currentModVars[$key], ['yes', 'no'])) {
                            $var = $currentModVars[$key] == 'yes' ? true : false;
                        } else {
                            $var = (boolval($currentModVars[$key]));
                        }

                        break;
                    default:
                        $var = $defVar;

                        break;
                }
            }
            $this->setVar($key, $var);
        }

        return true;
    }

    /**
     * find the relative path of this script from current full path.
     *
     * @param string $path default __DIR__
     * @param string $from default 'modules'
     *
     * @return string
     */
    public static function generateRelativePath($path = __DIR__, $from = 'modules')
    {
        $path = realpath($path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);
        foreach ($parts as $part) {
            if ($part == $from) {
                return $path;
            } else {
                $path = substr($path, strlen($part.DIRECTORY_SEPARATOR));
            }
        }

        return $path;
    }
}
