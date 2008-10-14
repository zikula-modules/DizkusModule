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

/**
 * Return an array of items to show in the your account panel
 *
 * @params   uname   string   the user name
 * @return   array   array of items, or false on failure
 */
function Dizkus_accountapi_getall($args)
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
    if(SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_OVERVIEW)) {
        pnModLangLoad('Dizkus', 'user'); 
        $items = array(array('url'     => pnModURL('Dizkus', 'user', 'prefs'),
                             'title'   => _DZK_FORUM,
                             'icon'    => 'icon_forumprefs.gif'));
    }

    // Return the items
    return $items;
}
