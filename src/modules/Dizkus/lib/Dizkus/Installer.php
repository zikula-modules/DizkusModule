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
    /**
     *  Initialize a new install of the Dizkus module
     *
     *  This function will initialize a new installation of Dizkus.
     *  It is accessed via the Zikula Admin interface and should
     *  not be called directly.
     */
    public function install()
    {
        // no gettext here as we are not sure which way of gettext to use 
        //if (version_compare(System::VERSION_NUM, '1.3.0-dev', '<')) {
          //  return LogUtil::registerError($this->__('Error! This version of the Dizkus module requires Zikula 1.3.0 or later. Installation has been stopped because this requirement is not met.'));
        //}
    
        // TODO move this to a loop
        // creating categories table
        if (!DBUtil::createTable('dizkus_categories')) {
            return false;
        }
    
        // creating forum_mods table
        if (!DBUtil::createTable('dizkus_forum_mods')) {
            $this->uninstall();
            return false;
        }
    
        // creating forums table
        if (!DBUtil::createTable('dizkus_forums')) {
            $this->uninstall();
            return false;
        }
    
        // creating posts table
        if (!DBUtil::createTable('dizkus_posts')) {
            $this->uninstall();
            return false;
        }
    
        // creating subscription table
        if (!DBUtil::createTable('dizkus_subscription')) {
            $this->uninstall();
            return false;
        }
    
        // creating ranks table
        if (!DBUtil::createTable('dizkus_ranks')) {
            $this->uninstall();
            return false;
        }
    
        // creating topics table
        if (!DBUtil::createTable('dizkus_topics')) {
            $this->uninstall();
            return false;
        }
/*    
        // creating users table
        if (!DBUtil::createTable('dizkus__users')) {
            $this->uninstall();
            return false;
        }
*/    
        // creating topic_subscription table (new in 1.7.5)
        if (!DBUtil::createTable('dizkus_topic_subscription')) {
            $this->uninstall();
            return false;
        }
    
        if (!DBUtil::createTable('dizkus_forum_favorites')) {
            $this->uninstall();
            return false;
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
        // create FULLTEXT index 
        if (strtolower($GLOBALS['ZConfig']['DBInfo']['default']['dbtabletype']) <> 'innodb') {
            // FULLTEXT does not work an innodb - by design
            // for now we assume that it works with all other table types, if not, please open a ticket
            $ztables      = DBUtil::getTables();
            $topicstable  = DataUtil::formatForStore($ztables['dizkus_topics']);
            $topictitle   = DataUtil::formatForStore($ztables['dizkus_topics_column']['topic_title']);
            $res1 = DBUtil::executeSQL('ALTER TABLE ' . $topicstable . ' ADD FULLTEXT ' . $topictitle . ' (' . $topictitle . ')');
            
            $poststable = DataUtil::formatForStore($ztables['dizkus_posts']);
            $poststext  = DataUtil::formatForStore($ztables['dizkus_posts_column']['post_text']);
            $res2 = DBUtil::executeSQL('ALTER TABLE ' . $poststable . ' ADD FULLTEXT ' . $poststext . ' (' . $poststext . ')');
    
            if ($res1 == true && $res2 == true) {
                ModUtil::setVar('Dizkus', 'fulltextindex', 'yes');
            } else {
                ModUtil::setVar('Dizkus', 'fulltextindex', 'no');
            }
        }
        
        // forum settings
        $this->setVar('posts_per_page', 15);
        $this->setVar('topics_per_page', 15);
        $this->setVar('hot_threshold', 20);
        $this->setVar('email_from', System::getVar('adminmail'));
        $this->setVar('url_ranks_images', "modules/Dizkus/images/ranks");
        $this->setVar('post_sort_order', 'ASC');
        $this->setVar('log_ip', 'no');
        $this->setVar('slimforum', 'no');
        $this->setVar('hideusers', 'no');
        $this->setVar('removesignature', 'no');
        $this->setVar('striptags', 'no');
        $this->setVar('deletehookaction', 'lock');
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
        $this->setVar('forum_disabled_info', $this->__('Sorry! The forums are currently off-line for maintenance. Please try later.'));
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
        $tables = DBUtil::metaTables(true, true, '%dizkus%');
        $ztables = DBUtil::getTables();
    
        if (in_array($ztables['dizkus_categories'], $tables)) {
            if (!DBUtil::dropTable('dizkus_categories')) {
                return false;
            }
        }
    
        if (in_array($ztables['dizkus_forum_mods'], $tables)) {
            if (!DBUtil::dropTable('dizkus_forum_mods')) {
                return false;
            }
        }
    
        if (in_array($ztables['dizkus_forums'], $tables)) {
            if (!DBUtil::dropTable('dizkus_forums')) {
                return false;
            }
        }
        
        if (in_array($ztables['dizkus_forum_favorites'], $tables)) {
            if (!DBUtil::dropTable('dizkus_forum_favorites')) {
                return false;
            }
        }
        
        if (in_array($ztables['dizkus_posts'], $tables)) {
            if (!DBUtil::dropTable('dizkus_posts')) {
                return false;
            }
        }
    
        if (in_array($ztables['dizkus_posts_text'], $tables)) {
            if (!DBUtil::dropTable('dizkus_posts_text')) {
                return false;
            }
        }
    
        if (in_array($ztables['dizkus_subscription'], $tables)) {
            if (!DBUtil::dropTable('dizkus_subscription')) {
                return false;
            }
        }
        
        if (in_array($ztables['dizkus_ranks'], $tables)) {
            if (!DBUtil::dropTable('dizkus_ranks')) {
                return false;
            }
        }
        
        if (in_array($ztables['dizkus_topics'], $tables)) {
            if (!DBUtil::dropTable('dizkus_topics')) {
                return false;
            }
        }
/*        
        if (in_array($ztables['dizkus__users'], $tables)) {
            if (!DBUtil::dropTable('dizkus__users')) {
                return false;
            }
        }
*/        
        if (in_array($ztables['dizkus_topic_subscription'], $tables)) {
            if (!DBUtil::dropTable('dizkus_topic_subscription')) {
                return false;
            }
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
        $nCat    = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Dizkus');
    
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
        $langs = LanguageUtil::getInstalledLanguages();
    
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
                                                       'forum_id'          => $maxforumid,
                                                       'topic_count'       => 0,
                                                       'post_count'        => 0,
                                                       'last_post_id'      => 0,
                                                       'pop3_active'       => 0,
                                                       'pop3_server'       => '',
                                                       'pop3_port'         => 0,
                                                       'pop3_login'        => '',
                                                       'pop3_password'     => '',
                                                       'pop3_interval'     => 0,
                                                       'pop3_lastconnect'  => 0,
                                                       'pop3_pnuser'       => '',
                                                       'pop3_pnpassword'   => '',
                                                       'pop3_matchstring'  => '',
                                                       'moduleref'         => 0,
                                                       'pntopic'           => 0)); // TODO: check if still in use    (fs)
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
                                                            'forum_id'          => $forum['forum_id'],
                                                            'topic_count'       => $forum['forum_topics'],
                                                            'post_count'        => $forum['forum_posts'],
                                                            'last_post_id'      => $forum['forum_last_post_id'],
                                                            'pop3_active'       => $forum['forum_pop3_active'],
                                                            'pop3_server'       => $forum['forum_pop3_server'],
                                                            'pop3_port'         => $forum['forum_pop3_port'],
                                                            'pop3_login'        => $forum['forum_pop3_login'],
                                                            'pop3_password'     => $forum['forum_pop3_password'],
                                                            'pop3_interval'     => $forum['forum_pop3_interval'],
                                                            'pop3_lastconnect'  => $forum['forum_pop3_lastconnect'],
                                                            'pop3_pnuser'       => $forum['forum_pop3_pnuser'],
                                                            'pop3_pnpassword'   => $forum['forum_pop3_pnpassword'],
                                                            'pop3_matchstring'  => $forum['forum_pop3_matchstring'],
                                                            'moduleref'         => $forum['forum_moduleref'],
                                                            'pntopic'           => $forum['forum_pntopic'])); // TODO: check if still in use    (fs)
    
                $fcat->update();
            }
        }
    
        return true;
    }

    public function upgrade($oldversion) {
        return true;
    }
}
