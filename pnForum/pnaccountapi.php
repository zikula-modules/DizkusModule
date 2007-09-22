<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id: pnuser.php 804 2007-09-14 18:00:46Z landseer $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

/**
 * Return an array of items to show in the your account panel
 *
 * @params   uname   string   the user name
 * @return   array   array of items, or false on failure
 */
function pnForum_accountapi_getall($args)
{
    // the array that will hold the options
    $items = null;

    // show link for users only
    if(!pnUserLoggedIn()) {
        // not logged in
        return $items;
    }

    $uname = (isset($args['uname'])) ? $args['uname'] : pnUserGetVar('uname');
    // does this user exist?
    if(pnUserGetIDFromName($uname)==false) {
        // user does not exist
        return $items;
    }

    // Create an array of links to return
    if(SecurityUtil::checkPermission('pnForum::', '::', ACCESS_OVERVIEW)) {
        pnModLangLoad('pnForum', 'user'); 
        $items = array(array('url'     => pnModURL('pnForum', 'user', 'prefs'),
                             'title'   => _PNFORUM_FORUM,
                             'icon'    => 'icon_forumprefs.gif'));
    }

    // Return the items
    return $items;
}
