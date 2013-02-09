<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
Class Dizkus_Installer extends Zikula_AbstractInstaller
{

    private $_entities = array(
        'Dizkus_Entity_Forum',
        'Dizkus_Entity_Post',
        'Dizkus_Entity_Topic',
        'Dizkus_Entity_Favorites',
        'Dizkus_Entity_Poster',
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



        /*
          // create the hooks: create, delete, display.
          // everything else is not needed , at least not atm.
          //
          // createhook
          //
          if (!ModUtil::registerHook('item',
          'create',
          'API',
          'Dizkus',
          'hook',
          'createbyitem')) {
          return LogUtil::registerError($this->__f('Error! Could not create %s hook.', 'create'));
          }

          //
          // updatehook
          //
          if (!ModUtil::registerHook('item',
          'update',
          'API',
          'Dizkus',
          'hook',
          'updatebyitem')) {
          return LogUtil::registerError($this->__f('Error! Could not create %s hook.', 'update'));
          }

          //
          // deletehook
          //
          if (!ModUtil::registerHook('item',
          'delete',
          'API',
          'Dizkus',
          'hook',
          'deletebyitem')) {
          return LogUtil::registerError($this->__f('Error! Could not create %s hook.', 'delete'));
          }

          //
          // displayhook
          //
          if (!ModUtil::registerHook('item',
          'display',
          'GUI',
          'Dizkus',
          'hook',
          'showdiscussionlink')) {
          return LogUtil::registerError($this->__f('Error! Could not create %s hook.', 'display'));
          }
         */
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
            
        }



        // remove the hooks
        //
        // createhook
        //
        if (!ModUtil::unregisterHook('item', 'create', 'API', 'Dizkus', 'hook', 'createbyitem')) {
            return LogUtil::registerError($this->__f('Error! Could not delete %s hook.', 'create'));
        }

        //
        // updatehook
        //
        if (!ModUtil::unregisterHook('item', 'update', 'API', 'Dizkus', 'hook', 'updatebyitem')) {
            return LogUtil::registerError($this->__f('Error! Could not delete %s hook.', 'update'));
        }

        //
        // deletehook
        //
        if (!ModUtil::unregisterHook('item', 'delete', 'API', 'Dizkus', 'hook', 'deletebyitem')) {
            return LogUtil::registerError($this->__f('Error! Could not delete %s hook.', 'delete'));
        }

        //
        // displayhook
        //
        if (!ModUtil::unregisterHook('item', 'display', 'GUI', 'Dizkus', 'hook', 'showdiscussionlink')) {
            return LogUtil::registerError($this->__f('Error! Could not delete %s hook.', 'display'));
        }

        // remove module vars
        $this->delVars();

        // unregister hooks
        HookUtil::unregisterSubscriberBundles($this->version->getHookSubscriberBundles());

        // Deletion successful
        return true;
    }

    /**
     * create default categories - unfinished code, do not use
     */
    public function createdefaultcategory($regpath = '/__SYSTEM__/Modules/Dizkus')
    {
        // get the language file
        $lang = ZLanguage::getLanguageCode();

        // get the category path for which we're going to insert our place holder category
        $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
        $nCat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Dizkus');

        if (!$nCat) {
            // create placeholder for all our migrated categories
            $cat = new Categories_DBObject_Category ();
            $cat->setDataField('parent_id', $rootcat['id']);
            $cat->setDataField('name', 'Dizkus');
            $cat->setDataField('display_name', array($lang => 'Dizkus forums'));
            $cat->setDataField('display_desc', array($lang => $this->__('An integrated forum solution for Zikula which is simple to administer and use but that has an excellent feature set.')));
            $cat->setDataField('__ATTRIBUTES__', array('can_contain_posts' => false));
            if (!$cat->validate('admin')) {
                die('error 1');
            }
            $cat->insert();
            $cat->update();
        }

        // get the category path for which we are going to insert our upgraded Dizkus categories and forums
        $rootcat = CategoryUtil::getCategoryByPath($regpath);
        if ($rootcat) {
            // create an entry in the categories registry to the Main property
            $registry = new Categories_DBObject_Registry();
            $registry->setDataField('modname', 'Dizkus');
            $registry->setDataField('table', 'dizkus_topics');
            $registry->setDataField('property', 'dizkus_topics');
            $registry->setDataField('category_id', $rootcat['id']);
            $registry->insert();
        }

        return true;
    }

    /**
     * migrate old categories - unfinished code, do not use
     */
    public function migratecategories()
    {
        // force loading of user api file
        // pn_ModAPILoad('Dizkus', 'user', true);
        // pull all data from the old tables
        $tree = ModUtil::apiFunc('Dizkus', 'user', 'readcategorytree');

        // get the language file
        $langs = ZLanguage::getInstalledLanguages();

        // create the Main category and entry in the categories registry
        $this->createdefaultcategory();

        // get the category path for which we're going to insert our upgraded Dizkus categories
        $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Dizkus');

        // get last forum id. new categories start there
        $maxforumid = DBUtil::selectFieldMax('dizkus_forums', 'forum_id');

        // migrate our old categories
        //$categorymap = array();
        foreach ($tree as $oldcategory) {
            // increment max forum id
            $maxforumid++;
            $cat = new Categories_DBObject_Category();
            $cat->setDataField('parent_id', $rootcat['id']);
            $cat->setDataField('name', $oldcategory['cat_title']);
            $titlelangarray = array();
            foreach ($langs as $lang) {
                // for now all names get the same value
                $titlelangarray[$lang] = $oldcategory['cat_title'];
            }
            $cat->setDataField('display_name', $titlelangarray);
            $cat->setDataField('display_desc', $titlelangarray);
            if (!$cat->validate('admin')) {
                return false;
            }
            $cat->insert();
            $cat->setDataField('__ATTRIBUTES__', array('can_contain_posts' => false,
                'forum_id' => $maxforumid,
                'topic_count' => 0,
                'post_count' => 0,
                'last_post_id' => 0,
                'pop3_active' => 0,
                'pop3_server' => '',
                'pop3_port' => 0,
                'pop3_login' => '',
                'pop3_password' => '',
                'pop3_interval' => 0,
                'pop3_lastconnect' => 0,
                'pop3_pnuser' => '',
                'pop3_pnpassword' => '',
                'pop3_matchstring' => '',
                'moduleref' => 0,
                'pntopic' => 0)); // TODO: check if still in use    (fs)
            $cat->update();

            $newcatid = $cat->getDataField('id');

            // forums in this category
            foreach ($oldcategory['forums'] as $forum) {
                $fcat = new Categories_DBObject_Category();
                $fcat->setDataField('parent_id', $newcatid);
                $fcat->setDataField('name', $forum['forum_name']);

                $fnamelangarray = array();
                $fdesclangarray = array();
                foreach ($langs as $lang) {
                    // for now all fields get the same value
                    $fnamelangarray[$lang] = $forum['forum_name'];
                    $fdesclangarray[$lang] = $forum['forum_desc'];
                }

                $fcat->setDataField('display_name', $fnamelangarray);
                $fcat->setDataField('display_desc', $fdesclangarray);
                if (!$fcat->validate('admin')) {
                    return false;
                }
                $fcat->insert();
                $fcat->setDataField('__ATTRIBUTES__', array('can_contain_posts' => true,
                    'forum_id' => $forum['forum_id'],
                    'topic_count' => $forum['forum_topics'],
                    'post_count' => $forum['forum_posts'],
                    'last_post_id' => $forum['forum_last_post_id'],
                    'pop3_active' => $forum['forum_pop3_active'],
                    'pop3_server' => $forum['forum_pop3_server'],
                    'pop3_port' => $forum['forum_pop3_port'],
                    'pop3_login' => $forum['forum_pop3_login'],
                    'pop3_password' => $forum['forum_pop3_password'],
                    'pop3_interval' => $forum['forum_pop3_interval'],
                    'pop3_lastconnect' => $forum['forum_pop3_lastconnect'],
                    'pop3_pnuser' => $forum['forum_pop3_pnuser'],
                    'pop3_pnpassword' => $forum['forum_pop3_pnpassword'],
                    'pop3_matchstring' => $forum['forum_pop3_matchstring'],
                    'moduleref' => $forum['forum_moduleref'],
                    'pntopic' => $forum['forum_pntopic'])); // TODO: check if still in use    (fs)

                $fcat->update();
            }
        }

        return true;
    }

    public function upgrade($oldversion)
    {

        switch ($oldversion) {
            case '2.7.1':
                $this->upgrade_to_3_0();
                break;

            case '3.0':
                $this->upgrade_to_3_1();
                break;

            case '3.1':
                $this->upgrade_to_4_0_0();
                break;
        }
    }

    /**
     * upgrade to 3.0
     *
     */
    public function upgrade_to_3_0()
    {
        // rename the old pnForum tablenames to Dizkus tablenames
        $tables = array('pnforum_categories' => 'dizkus_categories',
            'pnforum_forum_mods' => 'dizkus_forum_mods',
            'pnforum_forums' => 'dizkus_forums',
            'pnforum_posts' => 'dizkus_posts',
            'pnforum_posts_text' => 'dizkus_posts_text',
            'pnforum_ranks' => 'dizkus_ranks',
            'pnforum_subscription' => 'dizkus_subscription',
            'pnforum_topics' => 'dizkus_topics',
            'pnforum_users' => 'dizkus_users',
            'pnforum_topic_subscription' => 'dizkus_topic_subscription',
            'pnforum_forum_favorites' => 'dizkus_forum_favorites');

        $dbconn = DBConnectionStack::getConnection();
        $dict = NewDataDictionary($dbconn);
        $prefix = System::getVar('prefix');
        foreach ($tables as $oldtable => $newtable) {
            $sqlarray = $dict->RenameTableSQL($prefix . '_' . $oldtable, $prefix . '_' . $newtable);
            $result = $dict->ExecuteSQLArray($sqlarray);
            $success = ($result == 2);
            if (!$success) {
                $dberrmsg = $dbconn->ErrorNo() . ' - ' . $dbconn->ErrorMSg();
                LogUtil::registerError($this->__("Error! The renaming of table '%1$s' to '%2$s' failed: %3$s.", array($oldtable, $$newtable, $dberrmsg)));
            }
        }

        // add some columns to the post table - with DBUtil this is a one-liner, you just have to
        // define the new columns in the pntables array, see pntables.php
        DBUtil::changeTable('dizkus_posts');

        // remove obsolete module vars
        $this->delVar('pnForum', 'posticon');
        $this->delVar('pnForum', 'firstnew_image');

        $oldvars = ModUtil::getVar('pnForum');
        foreach ($oldvars as $varname => $oldvar) {
            // update path to rank images - simply replace pnForum with Dizkus
            if ($varname == 'url_ranks_images') {
                $oldvar = str_replace('pnForum', 'Dizkus', $oldvar);
            }
            ModUtil::setVar('Dizkus', $varname, $oldvar);
        }
        ModUtil::delVar('pnForum');

        // update hooks
        $ztables = DBUtil::getTables();
        $hookstable = $ztables['hooks'];
        $hookscolumn = $ztables['hooks_column'];

        $sql = 'UPDATE ' . $hookstable . ' SET ' . $hookscolumn['smodule'] . '=\'Dizkus\' WHERE ' . $hookscolumn['smodule'] . '=\'pnForum\'';
        $res = DBUtil::executeSQL($sql);
        if ($res === false) {
            return LogUtil::registerError($this->__("Error! A problem was encountered while upgrading the source module for hooks ('smodule')."));
        }

        $sql = 'UPDATE ' . $hookstable . ' SET ' . $hookscolumn['tmodule'] . '=\'Dizkus\' WHERE ' . $hookscolumn['tmodule'] . '=\'pnForum\'';
        $res = DBUtil::executeSQL($sql);
        if ($res === false) {
            return LogUtil::registerError($this->__("Error! A problem was encountered while upgrading the target module for hooks ('tmodule')."));
        }

        // introduce new module variable
        $this->setVar('signaturemanagement', 'no');
        $this->setVar('sendemailswithsqlerrors', 'no');
        $this->setVar('showtextinsearchresults', 'no');
        $this->setVar('minsearchlength', 3);
        $this->setVar('maxsearchlength', 30);

        $this->setVar('ignorelist_handling', 'medium');
        return true;
    }

    /**
     * upgrade to 3.1
     */
    public function upgrade_to_3_1()
    {
        // merge posts and posts_text table
        ModUtil::dbInfoLoad('Dizkus');

        $ztable = DBUtil::getTables();

        $poststable = $ztable['dizkus_posts'];
        $postscolumn = $ztable['dizkus_posts_column'];
        $poststexttable = $ztable['dizkus_posts_text'];
        $poststextcolumn = $ztable['dizkus_posts_text_column'];

        // change table structures
        DBUtil::changeTable('dizkus_posts');
        DBUtil::changeTable('dizkus_ranks');

        DBUtil::dropColumn('dizkus_topics', 'topic_notify');
        DBUtil::dropColumn('dizkus_topics', 'sticky_label');
        DBUtil::dropColumn('dizkus_topics', 'poll_id');
        DBUtil::dropColumn('dizkus_forums', 'forum_access');
        DBUtil::dropColumn('dizkus_forums', 'forum_type');
        DBUtil::dropColumn('dizkus_topic_subscription', 'forum_id');

        // add some missing index fields, all named 'id' if not existing
        DBUtil::executeSQL('ALTER TABLE ' . $ztable['dizkus_topic_subscription'] . ' ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        DBUtil::executeSQL('ALTER TABLE ' . $ztable['dizkus_forum_mods'] . ' ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');

        // due to a bug in 3.0 no primary key has been added to the dizkus_users table upon creation, we will add this now 
        $res = DBUtil::executeSQL('SHOW COLUMNS FROM ' . $ztable['dizkus_users']);
        $id_exists = false;
        foreach ($res as $resline) {
            //(array) 0:
            //   1. (string) 0 = id
            //   2. (string) 1 = int(11)
            //   3. (string) 2 = NO
            //   4. (string) 3 = PRI
            //   5. (NULL) 4 = (none)
            //   6. (string) 5 = auto_increment
            if ($resline[0] == 'user_id' && $resline[3] == 'PRI') {
                // found id
                $id_exists = true;
                break;
            }
        }
        if (!$id_exists) {
            DBUtil::executeSQL('ALTER TABLE ' . $ztable['dizkus_users'] . ' ADD PRIMARY KEY(user_id)');
        }

        // move all posting text from post_text to posts table and remove the post_text table - never knew why this has been split
        $sql = 'UPDATE ' . $poststable . ' AS p  
                SET p.' . $postscolumn['post_text'] . '= ( 
                    SELECT pt1.' . $poststextcolumn['post_text'] . ' 
                    FROM ' . $poststexttable . ' AS pt1
                    WHERE pt1.' . $poststextcolumn['post_id'] . '=p.' . $poststextcolumn['post_id'] . ')
                WHERE EXISTS (
                    SELECT pt.' . $poststextcolumn['post_text'] . ' 
                    FROM ' . $poststexttable . ' AS pt 
                    WHERE pt.' . $poststextcolumn['post_id'] . '=p.' . $poststextcolumn['post_id'] . ')';

        if (DBUtil::executeSQL($sql) != true) {
            LogUtil::registerError($this->__("Error! Could not upgrade the table '%s'.", 'dizkus_posts'));
        }

        // remove obsolete table
        DBUtil::dropTable('dizkus_posts_text');

        // remove obsolete module variables
        $this->delVar('sendemailswithsqlerrors');
        $this->delVar('default_lang');

        // $this->migratecategories();
        // drop old tables
        //
        // this will be done when the upgrade is finished and working - just before the release
        //
        // DBUtil::dropTable('dizkus_categories');
        // DBUtil::dropTable('dizkus_forums');
        // introduce new module variable
        $this->setVar('allowgravatars', 1);
        $this->setVar('gravatarimage', 'gravatar.gif');

        return true;
    }

    /**
     * upgrade to 4.0.0
     */
    public function upgrade_to_4_0_0()
    {
        // remove pn from images/rank folder
        $this->setVar('url_ranks_images', "modules/Dizkus/images/ranks");


        // remove the legacy hooks
        //
        // createhook
        //
        if (!ModUtil::unregisterHook('item', 'create', 'API', 'Dizkus', 'hook', 'createbyitem')) {
            return LogUtil::registerError($this->__f('Error! Could not delete %s hook.', 'create'));
        }
        //
        // updatehook
        //
        if (!ModUtil::unregisterHook('item', 'update', 'API', 'Dizkus', 'hook', 'updatebyitem')) {
            return LogUtil::registerError($this->__f('Error! Could not delete %s hook.', 'update'));
        }
        //
        // deletehook
        //
        if (!ModUtil::unregisterHook('item', 'delete', 'API', 'Dizkus', 'hook', 'deletebyitem')) {
            return LogUtil::registerError($this->__f('Error! Could not delete %s hook.', 'delete'));
        }
        //
        // displayhook
        //
        if (!ModUtil::unregisterHook('item', 'display', 'GUI', 'Dizkus', 'hook', 'showdiscussionlink')) {
            return LogUtil::registerError($this->__f('Error! Could not delete %s hook.', 'display'));
        }




        /* ModUtil::dbInfoLoad('Settings');
          $tables = DBUtil::getTables();

          $objtable   = $tables['objectdata_attributes'];
          $objcolumn  = $tables['objectdata_attributes_column'];
          $userstable  = $tables['dizkus_users'];
          $userscolumn = $tables['dizkus_users_column'];

          // One sql per user property to move all data from user_data table to the attributes table
          // This is the most efficient way to do this. During a test upgrade this took less than 0.3 secs for 6700
          // users and >15K of properties.
          foreach ($userscolumn as $uc) {
          if ($uc <> 'user_id') {
          $uc = DataUtil::formatforStore($uc);
          // Set cr_date and lu_date to now, cr_uid and lu_uid will be the uid of the user the attributes belong to
          $timestring = date('Y-m-d H:i:s');
          $sql = "INSERT INTO " . $objtable . " (" . $objcolumn['attribute_name'] . ",
          " . $objcolumn['object_type'] . ",
          " . $objcolumn['object_id'] . ",
          " . $objcolumn['value'] . ",
          " . $objcolumn['cr_date'] . ",
          " . $objcolumn['cr_uid'] . ",
          " . $objcolumn['lu_date'] . ",
          " . $objcolumn['lu_uid'] . ")
          SELECT 'dizkus_" . $uc . "',
          'users',
          " . $userscolumn['user_id'] . ",
          " . $userscolumn[$uc] . ",
          '" . $timestring . "',
          " . $userscolumn['user_id'] . ",
          '" . $timestring . "',
          " . $userscolumn['user_id'] . "
          FROM " . $userstable;
          DBUtil::executeSQL($sql);
          }
          } */


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

        // Update poster_ip field length
        DBUtil::changeTable('dizkus_posts');

        // done - now drop the dizkus_users table
        //DBUtil::dropTable('dizkus_users');

        $this->delVar('autosubscribe');
        $this->delVar('allowgravatars');
        $this->delVar('gravatarimage');

        LogUtil::registerStatus($this->__('The permission schemas "Dizkus_Centerblock::" and "Dizkus_Statisticsblock" were changed into "Dizkus::Centerblock" and "Dizkus::Statisticsblock". If you were using them please modify your permission table.'));

        return true;
    }

}
