<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

Loader::includeOnce('modules/pnForum/common.php');

/**
 *	Initialize a new install of the pnForum module
 *
 *	This function will initialize a new installation of pnForum.
 *	It is accessed via the PostNuke Admin interface and should
 *	not be called directly.
 */

function pnForum_init()
{
    if(version_compare('0.8.0.0', PN_VERSION_NUM, '<')) {
        pnSessionSetVar('errormsg', _PNFORUM_DOT8ISREQUIRED);
        return false;
    }
    
    // creating categories table
    if (!DBUtil::createTable('pnforum_categories')) {
        return false;
    }

    // creating forum_mods table
    if (!DBUtil::createTable('pnforum_forum_mods')) {
        pnForum_delete();
        return false;
    }

    // creating forums table
    if (!DBUtil::createTable('pnforum_forums')) {
        pnForum_delete();
        return false;
    }

    // creating posts table
    if (!DBUtil::createTable('pnforum_posts')) {
        pnForum_delete();
        return false;
    }

    // creating posts text table
    if (!DBUtil::createTable('pnforum_posts_text')) {
        pnForum_delete();
        return false;
    }

    // creating subscription table
    if (!DBUtil::createTable('pnforum_subscription')) {
        pnForum_delete();
        return false;
    }

    // creating ranks table
    if (!DBUtil::createTable('pnforum_ranks')) {
        pnForum_delete();
        return false;
    }

    // creating topics table
    if (!DBUtil::createTable('pnforum_topics')) {
        pnForum_delete();
        return false;
    }

    // creating users table
    if (!DBUtil::createTable('pnforum_users')) {
        pnForum_delete();
        return false;
    }

	// creating topic_subscription table (new in 1.7.5)
    if (!DBUtil::createTable('pnforum_topic_subscription')) {
        pnForum_delete();
        return false;
    }

    if (!DBUtil::createTable('pnforum_forum_favorites')) {
        pnForum_delete();
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
                           'pnForum',
                           'hook',
                           'createbyitem')) {
        return LogUtil::registerError(_PNFORUM_FAILEDTOCREATEHOOK . ' (create)');
    }

    //
    // updatehook
    //
    if (!pnModRegisterHook('item',
                           'update',
                           'API',
                           'pnForum',
                           'hook',
                           'updatebyitem')) {
        return LogUtil::registerError(_PNFORUM_FAILEDTOCREATEHOOK . ' (update)');
    }

    //
    // deletehook
    //
    if (!pnModRegisterHook('item',
                           'delete',
                           'API',
                           'pnForum',
                           'hook',
                           'deletebyitem')) {
        return LogUtil::registerError(_PNFORUM_FAILEDTOCREATEHOOK . ' (delete)');
    }

    //
    // displayhook
    //
    if (!pnModRegisterHook('item',
                           'display',
                           'GUI',
                           'pnForum',
                           'hook',
                           'showdiscussionlink')) {
        return LogUtil::registerError(_PNFORUM_FAILEDTOCREATEHOOK . ' (display)');
    }

	// forum settings
	pnModSetVar('pnForum', 'posts_per_page', 15);
	pnModSetVar('pnForum', 'topics_per_page', 15);
	pnModSetVar('pnForum', 'hot_threshold', 20);
	pnModSetVar('pnForum', 'email_from', pnConfigGetVar('adminmail'));
	pnModSetVar('pnForum', 'default_lang', 'iso-8859-1');
	pnModSetVar('pnForum', 'url_ranks_images', "modules/pnForum/pnimages/ranks");
	pnModSetVar('pnForum', 'posticon', "modules/pnForum/pnimages/posticon.gif");
	pnModSetVar('pnForum', 'firstnew_image', "modules/pnForum/pnimages/firstnew.gif");
	pnModSetVar('pnForum', 'post_sort_order', 'ASC');
	pnModSetVar('pnForum', 'log_ip', 'yes');
	pnModSetVar('pnForum', 'slimforum', 'no');
	pnModSetVar('pnForum', 'hideusers', 'no');
	pnModSetVar('pnForum', 'removesignature', 'no');
	pnModSetVar('pnForum', 'striptags', 'no');
    pnModSetVar('pnForum', 'deletehookaction', 'lock');
    // 2.5
    pnModSetVar('pnForum', 'extendedsearch', 'no');
    pnModSetVar('pnForum', 'm2f_enabled', 'yes');
    pnModSetVar('pnForum', 'favorites_enabled', 'yes');
	pnModSetVar('pnForum', 'hideusers', 'no');
	pnModSetVar('pnForum', 'removesignature', 'no');
	pnModSetVar('pnForum', 'striptags', 'no');
    // 2.6
    pnModSetVar('pnForum', 'deletehookaction', 'lock');
    pnModSetVar('pnForum', 'rss2f_enabled', 'yes');
    // 2.7
    pnModSetVar('pnForum', 'shownewtopicconfirmation', 'no');
    pnModSetVar('pnForum', 'timespanforchanges', 24);
    pnModSetVar('pnForum', 'forum_enabled', 'yes');
    pnModSetVar('pnForum', 'forum_disabled_info', _PNFORUM_DISABLED_INFO);

    // Initialisation successful
    return true;

}

/**
 *	Deletes an install of the pnForum module
 *
 *	This function removes pnForum from your
 *	PostNuke install and should be accessed via
 *	the PostNuke Admin interface
 */

function pnForum_delete()
{
    if (!DBUtil::dropTable('pnforum_categories')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_forum_mods')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_forums')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_forum_favorites')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_posts')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_posts_text')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_subscription')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_ranks')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_topics')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_users')) {
        return false;
    }
    if (!DBUtil::dropTable('pnforum_topic_subscription')) {
        return false;
    }

    // remove the hooks
    //
    // createhook
    //
    if (!pnModUnRegisterHook('item', 'create', 'API', 'pnForum', 'hook', 'createbyitem')) {
        return LogUtil::registerError(_PNFORUM_FAILEDTODELETEHOOK . ' (create)');
    }

    //
    // updatehook
    //
    if (!pnModUnRegisterHook('item', 'update', 'API', 'pnForum', 'hook', 'updatebyitem')) {
        return LogUtil::registerError(_PNFORUM_FAILEDTODELETEHOOK . ' (update)');
    }

    //
    // deletehook
    //
    if (!pnModUnRegisterHook('item', 'delete', 'API', 'pnForum', 'hook', 'deletebyitem')) {
        return LogUtil::registerError(_PNFORUM_FAILEDTODELETEHOOK . ' (delete)');
    }

    //
    // displayhook
    //
    if (!pnModUnRegisterHook('item', 'display', 'GUI', 'pnForum', 'hook', 'showdiscussionlink')) {
        return LogUtil::registerError(_PNFORUM_FAILEDTODELETEHOOK . ' (display)');
    }

	// remove module vars
	pnModDelVar('pnForum');

    // Deletion successful
    return true;
}


/**
 * interactiveupgrade
 *
 *
 */
function pnForum_init_interactiveupgrade($args)
{
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $oldversion = FormUtil::getPassedValue('oldversion', 0, 'GETPOST');
    
    extract($args);
    unset($args);

    global $modversion;
    Loader::includeOnce('modules/pnForum/pnversion.php');
    
    $authid = pnSecGenAuthKey('Modules');
    switch($oldversion) {
        case '2.7':
            $templatefile = 'pnforum_upgrade_30.html';
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

    $pnr = pnRender::getInstance('pnForum', false, null, true);
    $pnr->assign('oldversion', $oldversion);
    $pnr->assign('authid', $authid);
    return $pnr->fetch($templatefile);
}

/**
 * interactiveupgrade_to_3_0
 *
 */
function pnForum_init_interactiveupgrade_to_3_0()
{
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $submit = FormUtil::getPassedValue('submit', null, 'GETPOST');

    if(!empty($submit)) {
        $result = pnForum_upgrade_to_3_0();
        if($result<>true) {
            return showforumerror(_PNFORUM_TO30_FAILED, __FILE__, __LINE__);
        }
        return pnRedirect(pnModURL('pnForum', 'init', 'interactiveupgrade', array('oldversion' => '2.5' )));
    }
    return pnRedirect(pnModURL('Modules', 'admin', 'view'));
}

/**
 * upgrade to 3.0
 *
 */
function pnForum_upgrade_to_3_0()
{
    return true;
}
