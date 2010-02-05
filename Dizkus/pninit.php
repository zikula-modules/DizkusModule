<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

Loader::includeOnce('modules/Dizkus/common.php');

/**
 *  Initialize a new install of the Dizkus module
 *
 *  This function will initialize a new installation of Dizkus.
 *  It is accessed via the Zikula Admin interface and should
 *  not be called directly.
 */
function Dizkus_init()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (version_compare(PN_VERSION_NUM, '1.2.0', '<')) {
        SessionUtil::setVar('errormsg', __('Error! This version of the Dizkus module requires Zikula 1.2.0 or later. Installation has been stopped because this requirement is not met.', $dom));
        return false;
    }

    // TODO move this to a loop
    // creating categories table
    if (!DBUtil::createTable('dizkus_categories')) {
        return false;
    }

    // creating forum_mods table
    if (!DBUtil::createTable('dizkus_forum_mods')) {
        Dizkus_delete();
        return false;
    }

    // creating forums table
    if (!DBUtil::createTable('dizkus_forums')) {
        Dizkus_delete();
        return false;
    }

    // creating posts table
    if (!DBUtil::createTable('dizkus_posts')) {
        Dizkus_delete();
        return false;
    }
/*
    // creating posts text table
//  if (!DBUtil::createTable('dizkus_posts_text')) {
        Dizkus_delete();
        return false;
    }
*/
    // creating subscription table
    if (!DBUtil::createTable('dizkus_subscription')) {
        Dizkus_delete();
        return false;
    }

    // creating ranks table
    if (!DBUtil::createTable('dizkus_ranks')) {
        Dizkus_delete();
        return false;
    }

    // creating topics table
    if (!DBUtil::createTable('dizkus_topics')) {
        Dizkus_delete();
        return false;
    }

    // creating users table
    if (!DBUtil::createTable('dizkus_users')) {
        Dizkus_delete();
        return false;
    }

    // creating topic_subscription table (new in 1.7.5)
    if (!DBUtil::createTable('dizkus_topic_subscription')) {
        Dizkus_delete();
        return false;
    }

    if (!DBUtil::createTable('dizkus_forum_favorites')) {
        Dizkus_delete();
        return false;
    }

    // create the hooks: create, delete, display.
    // everything else is not needed , at least not atm.
    //
    // createhook
    //
    if (!pnModRegisterHook('item',
                           'create',
                           'API',
                           'Dizkus',
                           'hook',
                           'createbyitem')) {
        return LogUtil::registerError(__f('Error! Could not create %s hook.', 'create'), $dom);
    }

    //
    // updatehook
    //
    if (!pnModRegisterHook('item',
                           'update',
                           'API',
                           'Dizkus',
                           'hook',
                           'updatebyitem')) {
        return LogUtil::registerError(__f('Error! Could not create %s hook.', 'update'), $dom);
    }

    //
    // deletehook
    //
    if (!pnModRegisterHook('item',
                           'delete',
                           'API',
                           'Dizkus',
                           'hook',
                           'deletebyitem')) {
        return LogUtil::registerError(__f('Error! Could not create %s hook.', 'delete'), $dom);
    }

    //
    // displayhook
    //
    if (!pnModRegisterHook('item',
                           'display',
                           'GUI',
                           'Dizkus',
                           'hook',
                           'showdiscussionlink')) {
        return LogUtil::registerError(__f('Error! Could not create %s hook.', 'display'), $dom);
    }
    
    // create FULLTEXT index 
    if (strtolower($GLOBALS['PNConfig']['DBInfo']['default']['dbtabletype']) <> 'innodb') {
        // FULLTEXT does not work an innodb - by design
        // for now we assume that it works with all other table types, if not, please open a ticket
        $ztables      = pnDBGetTables();
        $topicstable  = DataUtil::formatForStore($ztables['dizkus_topics']);
        $topictitle   = DataUtil::formatForStore($ztables['dizkus_topics_column']['topic_title']);
        $res1 = DBUtil::executeSQL('ALTER TABLE ' . $topicstable . ' ADD FULLTEXT ' . $topictitle . ' (' . $topictitle . ')');
        
        $poststable = DataUtil::formatForStore($ztables['dizkus_posts']);
        $poststext  = DataUtil::formatForStore($ztables['dizkus_posts_column']['post_text']);
        $res2 = DBUtil::executeSQL('ALTER TABLE ' . $poststable . ' ADD FULLTEXT ' . $poststext . ' (' . $poststext . ')');

        if ($res1 == true && $res2 == true) {
            pnModSetVar('Dizkus', 'fulltextindex', 'yes');
        } else {
            pnModSetVar('Dizkus', 'fulltextindex', 'no');
        }
    }
    
    // forum settings
    pnModSetVar('Dizkus', 'posts_per_page', 15);
    pnModSetVar('Dizkus', 'topics_per_page', 15);
    pnModSetVar('Dizkus', 'hot_threshold', 20);
    pnModSetVar('Dizkus', 'email_from', pnConfigGetVar('adminmail'));
    pnModSetVar('Dizkus', 'default_lang', 'UTF-8');
    pnModSetVar('Dizkus', 'url_ranks_images', "modules/Dizkus/pnimages/ranks");
    pnModSetVar('Dizkus', 'post_sort_order', 'ASC');
    pnModSetVar('Dizkus', 'log_ip', 'no');
    pnModSetVar('Dizkus', 'slimforum', 'no');
    pnModSetVar('Dizkus', 'hideusers', 'no');
    pnModSetVar('Dizkus', 'removesignature', 'no');
    pnModSetVar('Dizkus', 'striptags', 'no');
    pnModSetVar('Dizkus', 'deletehookaction', 'lock');
    // 2.5
    pnModSetVar('Dizkus', 'extendedsearch', 'no');
    pnModSetVar('Dizkus', 'm2f_enabled', 'yes');
    pnModSetVar('Dizkus', 'favorites_enabled', 'yes');
    pnModSetVar('Dizkus', 'hideusers', 'no');
    pnModSetVar('Dizkus', 'removesignature', 'no');
    pnModSetVar('Dizkus', 'striptags', 'no');
    // 2.6
    pnModSetVar('Dizkus', 'deletehookaction', 'lock');
    pnModSetVar('Dizkus', 'rss2f_enabled', 'yes');
    // 2.7
    pnModSetVar('Dizkus', 'shownewtopicconfirmation', 'no');
    pnModSetVar('Dizkus', 'timespanforchanges', 24);
    pnModSetVar('Dizkus', 'forum_enabled', 'yes');
    pnModSetVar('Dizkus', 'forum_disabled_info', __('Sorry! The forums are currently off-line for maintenance. Please try later.', $dom));
    // 3.0
    pnModSetVar('Dizkus', 'autosubscribe', 'no');
    pnModSetVar('Dizkus', 'newtopicconfirmation', 'no');
    pnModSetVar('Dizkus', 'signaturemanagement', 'no');
    pnModSetVar('Dizkus', 'signature_start', '');
    pnModSetVar('Dizkus', 'signature_end', '');
    pnModSetVar('Dizkus', 'showtextinsearchresults', 'yes');
    pnModSetVar('Dizkus', 'ignorelist_handling', 'medium');
    pnModSetVar('Dizkus', 'minsearchlength', 3);
    pnModSetVar('Dizkus', 'maxsearchlength', 30);

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
function Dizkus_delete()
{
    $tables = DBUtil::metaTables(true, true, '%dizkus%');
    $ztables = pnDBGetTables();
    $dom = ZLanguage::getModuleDomain('Dizkus');    

    if (in_array($ztables['dizkus_categories'], $tables)) {
        if (!DBUtil::dropTable('dizkus_categories')) {
            return false;
        }
    }
    if (!DBUtil::dropTable('dizkus_forum_mods')) {
        return false;
    }

    if (in_array($ztables['dizkus_forums'], $tables)) {
        if (!DBUtil::dropTable('dizkus_forums')) {
            return false;
        }
    }
    if (!DBUtil::dropTable('dizkus_forum_favorites')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_posts')) {
        return false;
    }

    if (in_array($ztables['dizkus_posts_text'], $tables)) {
        if (!DBUtil::dropTable('dizkus_posts_text')) {
            return false;
        }
    }
    if (!DBUtil::dropTable('dizkus_subscription')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_ranks')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_topics')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_users')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_topic_subscription')) {
        return false;
    }

    // remove the hooks
    //
    // createhook
    //
    if (!pnModUnRegisterHook('item', 'create', 'API', 'Dizkus', 'hook', 'createbyitem')) {
        return LogUtil::registerError(__f('Error! Could not delete %s hook.', 'create'), $dom);        
    }

    //
    // updatehook
    //
    if (!pnModUnRegisterHook('item', 'update', 'API', 'Dizkus', 'hook', 'updatebyitem')) {
        return LogUtil::registerError(__f('Error! Could not delete %s hook.', 'update'), $dom);        
    }

    //
    // deletehook
    //
    if (!pnModUnRegisterHook('item', 'delete', 'API', 'Dizkus', 'hook', 'deletebyitem')) {
        return LogUtil::registerError(__f('Error! Could not delete %s hook.', 'delete'), $dom);        
    }

    //
    // displayhook
    //
    if (!pnModUnRegisterHook('item', 'display', 'GUI', 'Dizkus', 'hook', 'showdiscussionlink')) {
        return LogUtil::registerError(__f('Error! Could not delete %s hook.', 'display'), $dom);        
    }

    // remove module vars
    pnModDelVar('Dizkus');

    // Deletion successful
    return true;
}

/**
 * interactiveupgrade
 *
 *
 */
function Dizkus_init_interactiveupgrade($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');    

    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
      return showforumerror(__('Error! No permission for this action.', $dom), __FILE__, __LINE__);
    }

    $oldversion = FormUtil::getPassedValue('oldversion', isset($args['oldversion']) ? $args['oldversion'] : 0, 'GETPOST');

    Loader::includeOnce('modules/Dizkus/pnversion.php');

    $authid = SecurityUtil::generateAuthKey('Modules');
    switch ($oldversion)
    {
        case '2.7.1':
            $templatefile = 'dizkus_upgrade_30.html';
            break;

        case '3.0':
            $templatefile = 'dizkus_upgrade_31.html';
            break;

        default:
            // no interactive upgrade for version < 2.7
            // or latest step reached
            // FIXME pnRender API call instead?
            $smarty =& new Smarty;
            $smarty->compile_dir  = pnConfigGetVar('temp') . '/pnRender_compiled';
            $smarty->cache_dir    = pnConfigGetVar('temp') . '/pnRender_cache';
            $smarty->use_sub_dirs = false;
            $smarty->clear_compiled_tpl();
            return pnRedirect(pnModURL('Modules', 'admin', 'upgrade', array('authid' => $authid )));
    }

    $render = pnRender::getInstance('Dizkus', false, null, true);

    $render->assign('oldversion', $oldversion);
    $render->assign('authid', $authid);

    return $render->fetch($templatefile);
}

/**
 * interactiveupgrade_to_3_0
 *
 */
function Dizkus_init_interactiveupgrade_to_3_0()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');    

    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return showforumerror(__('Error! No permission for this action.', $dom), __FILE__, __LINE__);
    }

    $submit = FormUtil::getPassedValue('submit', null, 'GETPOST');

    if (!empty($submit)) {
        $result = Dizkus_upgrade_to_3_0();
        if ($result<>true) {
            return showforumerror(_('Error! The upgrade to Dizkus 3.0 failed.'), __FILE__, __LINE__);
        }
        return pnRedirect(pnModURL('Dizkus', 'init', 'interactiveupgrade', array('oldversion' => '3.0' )));
    }

    return pnRedirect(pnModURL('Modules', 'admin', 'view'));
}

/**
 * upgrade to 3.0
 *
 */
function Dizkus_upgrade_to_3_0()
{        
    $dom = ZLanguage::getModuleDomain('Dizkus');    

    // rename the old pnForum tablenames to Dizkus tablenames
    $tables = array('pnforum_categories'         => 'dizkus_categories',
                    'pnforum_forum_mods'         => 'dizkus_forum_mods',
                    'pnforum_forums'             => 'dizkus_forums',
                    'pnforum_posts'              => 'dizkus_posts',
                    'pnforum_posts_text'         => 'dizkus_posts_text',
                    'pnforum_ranks'              => 'dizkus_ranks',
                    'pnforum_subscription'       => 'dizkus_subscription',
                    'pnforum_topics'             => 'dizkus_topics',
                    'pnforum_users'              => 'dizkus_users',
                    'pnforum_topic_subscription' => 'dizkus_topic_subscription',
                    'pnforum_forum_favorites'    => 'dizkus_forum_favorites');

    $dbconn = DBConnectionStack::getConnection();
    $dict   = NewDataDictionary($dbconn);
    $prefix = pnConfigGetVar('prefix');
    foreach($tables as $oldtable => $newtable)
    {
        $sqlarray = $dict->RenameTableSQL($prefix.'_'.$oldtable, $prefix.'_'.$newtable);
        $result   = $dict->ExecuteSQLArray($sqlarray);
        $success  = ($result==2);
        if (!$success) {
            $dberrmsg = $dbconn->ErrorNo().' - '.$dbconn->ErrorMSg();
            LogUtil::registerError (__("Error! The renaming of table '%1$s' to '%2$s' failed: %3$s.", array($oldtable, $$newtable, $dberrmsg), $dom));
        }
    }

    // add some columns to the post table - with DBUtil this is a one-liner, you just have to
    // define the new columns in the pntables array, see pntables.php
    DBUtil::changeTable('dizkus_posts');

    // remove obsolete module vars
    pnModDelVar('pnForum', 'posticon');
    pnModDelVar('pnForum', 'firstnew_image');

    $oldvars = pnModGetVar('pnForum');
    foreach ($oldvars as $varname => $oldvar)
    {
        // update path to rank images - simply replace pnForum with Dizkus
        if ($varname == 'url_ranks_images') {
            $oldvar = str_replace('pnForum', 'Dizkus', $oldvar);
        }
        pnModSetVar('Dizkus', $varname, $oldvar);
    }
    pnModDelVar('pnForum');

    // update hooks
    $pntables    = pnDBGetTables();
    $hookstable  = $pntables['hooks'];
    $hookscolumn = $pntables['hooks_column'];

    $sql = 'UPDATE ' . $hookstable . ' SET ' . $hookscolumn['smodule'] . '=\'Dizkus\' WHERE ' . $hookscolumn['smodule'] . '=\'pnForum\'';
    $res = DBUtil::executeSQL ($sql);
    if ($res === false) {
        return LogUtil::registerError(__("Error! A problem was encountered while upgrading the source module for hooks ('smodule')."));
    }

    $sql = 'UPDATE ' . $hookstable . ' SET ' . $hookscolumn['tmodule'] . '=\'Dizkus\' WHERE ' . $hookscolumn['tmodule'] . '=\'pnForum\'';
    $res = DBUtil::executeSQL ($sql);
    if ($res === false) {
        return LogUtil::registerError(__("Error! A problem was encountered while upgrading the target module for hooks ('tmodule')."));
    }

    // introduce new module variable
    pnModSetVar('Dizkus', 'signaturemanagement', 'no'); 
    pnModSetVar('Dizkus', 'sendemailswithsqlerrors', 'no');
    pnModSetVar('Dizkus', 'showtextinsearchresults', 'no');
    pnModSetVar('Dizkus', 'minsearchlength', 3);
    pnModSetVar('Dizkus', 'maxsearchlength', 30);

    pnModSetVar('Dizkus', 'ignorelist_handling', 'medium');
    return true;
}

/**
 * interactiveupgrade_to_3_1
 */
function Dizkus_init_interactiveupgrade_to_3_1()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');    

    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
      return showforumerror(__('Error! No permission for this action.', $dom), __FILE__, __LINE__);
    }

    $submit = FormUtil::getPassedValue('submit', null, 'GETPOST');

    if (!empty($submit)) {
        $result = Dizkus_upgrade_to_3_1();
        if ($result<>true) {
            return showforumerror(__('Error! Could not upgrade to Dizkus 3.1.', $dom), __FILE__, __LINE__);
        }
        return pnRedirect(pnModURL('Dizkus', 'init', 'interactiveupgrade', array('oldversion' => '3.1' )));
    }

    return pnRedirect(pnModURL('Modules', 'admin', 'view'));
}

/**
 * upgrade to 3.1
 */
function Dizkus_upgrade_to_3_1()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');    

    // merge posts and posts_text table
    pnModDBInfoLoad('Dizkus');

    $pntable = pnDBGetTables();

    $poststable      = $pntable['dizkus_posts'];
    $postscolumn     = $pntable['dizkus_posts_column'];
    $poststexttable  = $pntable['dizkus_posts_text'];
    $poststextcolumn = $pntable['dizkus_posts_text_column'];

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
    if (!_id_exists($pntable['dizkus_users'])) {
        DBUtil::executeSQL('ALTER TABLE '. $pntable['dizkus_users'] .' ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }
    if (!_id_exists($pntable['dizkus_topic_subscription'])) {
        DBUtil::executeSQL('ALTER TABLE '. $pntable['dizkus_topic_subscription'] .' ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }
    if (!_id_exists($pntable['dizkus_forum_favorites'])) {
        DBUtil::executeSQL('ALTER TABLE '. $pntable['dizkus_forum_favorites'] .' ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }
    if (!_id_exists($pntable['dizkus_forum_mods'])) {
        DBUtil::executeSQL('ALTER TABLE '. $pntable['dizkus_forum_mods'] .' ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }

    // move all posting text from post_text to posts table and remove the post_text table - never knew why this has been split
    $sql = 'UPDATE ' . $poststable . ' AS p  
            SET p.' . $postscolumn['post_text'] . '= ( 
                SELECT pt1.' . $poststextcolumn['post_text'] . ' 
                FROM ' . $poststexttable . ' AS pt1
                WHERE pt1.' . $poststextcolumn['post_id'] . '=p.' . $poststextcolumn['post_id'] .')
            WHERE EXISTS (
                SELECT pt.' . $poststextcolumn['post_text'] . ' 
                FROM ' . $poststexttable . ' AS pt 
                WHERE pt.' . $poststextcolumn['post_id'] . '=p.' . $poststextcolumn['post_id'] .')';

    if (DBUtil::executeSQL($sql) != true) {
        LogUtil::registerError (__("Error! Could not upgrade the table '%s'.", 'dizkus_posts', $dom));
    }

    // remove obsolete table
    DBUtil::dropTable('dizkus_posts_text');

    pnModDelVar('Dizkus', 'sendemailswithsqlerrors');
    
    // _dizkus_migratecategories();

    // drop old tables
    //
    // this will be done when the upgrade is finished and working - just before the release
    //
    // DBUtil::dropTable('dizkus_categories');
    // DBUtil::dropTable('dizkus_forums');
    // DBUtil::dropTable('dizkus_posts_text');

    return true;
}

/**
 * create default categories - unfinished code, do not use
 */
function _dizkus_createdefaultcategory($regpath = '/__SYSTEM__/Modules/Dizkus')
{
    $dom = ZLanguage::getModuleDomain('Dizkus');    

    // load necessary classes
    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    // get the language file
    $lang = ZLanguage::getLanguageCode();

    // get the category path for which we're going to insert our place holder category
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
    $nCat    = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Dizkus');

    if (!$nCat) {
        // create placeholder for all our migrated categories
        $cat = new PNCategory ();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', 'Dizkus');
        $cat->setDataField('display_name', array($lang => 'Dizkus forums')); 
        $cat->setDataField('display_desc', array($lang => __('An integrated forum solution for Zikula which is simple to administer and use but that has an excellent feature set.', $dom))); 
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
        $registry = new PNCategoryRegistry();
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
function _dizkus_migratecategories()
{
    // force loading of user api file
    pnModAPILoad('Dizkus', 'user', true);
    
    // pull all data from the old tables
    $tree = pnModAPIFunc('Dizkus', 'user', 'readcategorytree');

    // load necessary classes
    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    // get the language file
    $langs = LanguageUtil::getInstalledLanguages();

    // create the Main category and entry in the categories registry
    _dizkus_createdefaultcategory();

    // get the category path for which we're going to insert our upgraded Dizkus categories
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Dizkus');

    // get last forum id. new categories start there
    $maxforumid = DBUtil::selectFieldMax('dizkus_forums', 'forum_id');

    // migrate our old categories
    //$categorymap = array();
    foreach ($tree as $oldcategory) {
        // increment max forum id
        $maxforumid++;
        $cat = new PNCategory();
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
            $fcat = new PNCategory();
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

/**
 * utility function: check if id column exists
 *
 */
function _id_exists($tablename)
{
    $res = DBUtil::executeSQL('SHOW COLUMNS FROM '. $tablename);
    $id_exists = false;
    foreach($res as $resline) {
        //(array) 0:
        //   1. (string) 0 = id
        //   2. (string) 1 = int(11)
        //   3. (string) 2 = NO
        //   4. (string) 3 = PRI
        //   5. (NULL) 4 = (none)
        //   6. (string) 5 = auto_increment
        if($resline[0] == 'id' || $resline[3] == 'PRI' || $resline[5] == 'auto_increment') {
            // found id
            $id_exists = true;
            break;
        }
    }
    return $id_exists;
}
