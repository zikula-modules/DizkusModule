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
 *	Initialize a new install of the Dizkus module
 *
 *	This function will initialize a new installation of Dizkus.
 *	It is accessed via the Zikula Admin interface and should
 *	not be called directly.
 */

function Dizkus_init()
{
    if(version_compare(PN_VERSION_NUM, '1.0.0', '<')) {
        // no SessionUtil::setVar here because this line is for <.9
        pnSessionSetVar('errormsg', _DZK_ZIKULA10ISREQUIRED);
        return false;
    }
    
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

    // creating posts text table
    if (!DBUtil::createTable('dizkus_posts_text')) {
        Dizkus_delete();
        return false;
    }

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

    if(createHooks() == false) {
        return false;
    }

	// forum settings
	pnModSetVar('Dizkus', 'posts_per_page', 15);
	pnModSetVar('Dizkus', 'topics_per_page', 15);
	pnModSetVar('Dizkus', 'hot_threshold', 20);
	pnModSetVar('Dizkus', 'email_from', pnConfigGetVar('adminmail'));
	pnModSetVar('Dizkus', 'default_lang', 'iso-8859-1');
	pnModSetVar('Dizkus', 'url_ranks_images', "modules/Dizkus/pnimages/ranks");
	pnModSetVar('Dizkus', 'post_sort_order', 'ASC');
	pnModSetVar('Dizkus', 'log_ip', 'yes');
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
    pnModSetVar('Dizkus', 'forum_disabled_info', _DZK_DISABLED_INFO);

    // Initialisation successful
    return true;

}

/**
 *	Deletes an install of the Dizkus module
 *
 *	This function removes Dizkus from your
 *	Zikula install and should be accessed via
 *	the Zikula Admin interface
 */

function Dizkus_delete()
{
    if (!DBUtil::dropTable('dizkus_categories')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_forum_mods')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_forums')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_forum_favorites')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_posts')) {
        return false;
    }
    if (!DBUtil::dropTable('dizkus_posts_text')) {
        return false;
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
        return LogUtil::registerError(_DZK_FAILEDTODELETEHOOK . ' (create)');
    }

    //
    // updatehook
    //
    if (!pnModUnRegisterHook('item', 'update', 'API', 'Dizkus', 'hook', 'updatebyitem')) {
        return LogUtil::registerError(_DZK_FAILEDTODELETEHOOK . ' (update)');
    }

    //
    // deletehook
    //
    if (!pnModUnRegisterHook('item', 'delete', 'API', 'Dizkus', 'hook', 'deletebyitem')) {
        return LogUtil::registerError(_DZK_FAILEDTODELETEHOOK . ' (delete)');
    }

    //
    // displayhook
    //
    if (!pnModUnRegisterHook('item', 'display', 'GUI', 'Dizkus', 'hook', 'showdiscussionlink')) {
        return LogUtil::registerError(_DZK_FAILEDTODELETEHOOK . ' (display)');
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
    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_DZK_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $oldversion = FormUtil::getPassedValue('oldversion', 0, 'GETPOST');
    
    extract($args);
    unset($args);

    global $modversion;
    Loader::includeOnce('modules/Dizkus/pnversion.php');
    
    $authid = SecurityUtil::generateAuthKey('Modules');
    switch($oldversion) {
        case '2.7.1':
            $templatefile = 'dizkus_upgrade_30.html';
            break;
        default:
            // no interactive upgrade for version < 2.7
            // or latest step reached
           	$smarty =& new Smarty;
           	$smarty->compile_dir = pnConfigGetVar('temp') . '/pnRender_compiled';
           	$smarty->cache_dir = pnConfigGetVar('temp') . '/pnRender_cache';
           	$smarty->use_sub_dirs = false;
           	$smarty->clear_compiled_tpl();
            return pnRedirect(pnModURL('Modules', 'admin', 'upgrade', array('authid' => $authid )));
    }

    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign('oldversion', $oldversion);
    $pnr->assign('authid', $authid);
    return $pnr->fetch($templatefile);
}

/**
 * interactiveupgrade_to_3_0
 *
 */
function Dizkus_init_interactiveupgrade_to_3_0()
{
    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_DZK_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $submit = FormUtil::getPassedValue('submit', null, 'GETPOST');

    if(!empty($submit)) {
        $result = Dizkus_upgrade_to_3_0();
        if($result<>true) {
            return showforumerror(_DZK_TO30_FAILED, __FILE__, __LINE__);
        }
        return pnRedirect(pnModURL('Dizkus', 'init', 'interactiveupgrade', array('oldversion' => '2.5' )));
    }
    return pnRedirect(pnModURL('Modules', 'admin', 'view'));
}

/**
 * upgrade to 3.0
 *
 */
function Dizkus_upgrade_to_3_0()
{        
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
    foreach($tables as $oldtable => $newtable) {
        $sqlarray = $dict->RenameTableSQL($prefix.'_'.$oldtable, $prefix.'_'.$newtable);
        $result   = $dict->ExecuteSQLArray($sqlarray);
        $success  = ($result==2);
        if (!$success) {
            $dberrmsg = $dbconn->ErrorNo().' - '.$dbconn->ErrorMSg();
            LogUtil::registerError (_RENAMETABLEFAILED. " ($tablename, $result, $dberrmsg)");
        }
    }
    
    // add some columns to the post table - with DBUtil this is a one-liner, you just have to
    // define the new columns in the pntables array, see pntables.php
    DBUtil::changeTable('dizkus_posts');

    // remove obsolete module vars
	pnModDelVar('Dizkus', 'posticon');
	pnModDelVar('Dizkus', 'firstnew_image');
	

    $oldvars = pnModGetVar('pnForum');
    foreach ($oldvars as $varname => $oldvar) {
    	// update path to rank images - simply replace pnForum with Dizkus
    	if($varname == 'url_ranks_images') {
    	    $oldvar = str_replace('pnForum', 'Dizkus', $oldvar);
    	}
        pnModSetVar('Dizkus', $varname, $oldvar);
    }
    pnModDelVar('pnForum');
    
    // update hooks
    $pntables = pnDBGetTables();
    $hookstable  = $pntables['hooks'];
    $hookscolumn = $pntables['hooks_column'];
    $sql = 'UPDATE ' . $hookstable . ' SET ' . $hookscolumn['smodule'] . '=\'Dizkus\' WHERE ' . $hookscolumn['smodule'] . '=\'pnForum\'';
    $res   = DBUtil::executeSQL ($sql);
    if ($res === false) {
        return LogUtil::registerError(_DZK_FAILEDTOUPGRADEHOOK . ' (smodule)');
    }
    
    $sql = 'UPDATE ' . $hookstable . ' SET ' . $hookscolumn['tmodule'] . '=\'Dizkus\' WHERE ' . $hookscolumn['tmodule'] . '=\'pnForum\'';
    $res   = DBUtil::executeSQL ($sql);
    if ($res === false) {
        return LogUtil::registerError(_DZK_FAILEDTOUPGRADEHOOK . ' (tmodule)');
    }

    return true;
}

function createHooks()
{
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
        return LogUtil::registerError(_DZK_FAILEDTOCREATEHOOK . ' (create)');
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
        return LogUtil::registerError(_DZK_FAILEDTOCREATEHOOK . ' (update)');
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
        return LogUtil::registerError(_DZK_FAILEDTOCREATEHOOK . ' (delete)');
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
        return LogUtil::registerError(_DZK_FAILEDTOCREATEHOOK . ' (display)');
    }
    return true;
}
