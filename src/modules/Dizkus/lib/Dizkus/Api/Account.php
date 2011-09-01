<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Account extends Zikula_AbstractApi {
    
/**
 * Return an array of items to show in the your account panel
 *
 * @params   uname   string   the user name
 * @return   array   array of items, or false on failure
 */
    public function getall($args)
    {
        // the array that will hold the options
        $items = array();
    
        // show link for users only
        if (!UserUtil::isLoggedIn()) {
            // not logged in
            return $items;
        }
    
        $uname = (isset($args['uname'])) ? $args['uname'] : UserUtil::getVar('uname');
        // does this user exist?
        if (UserUtil::getIDFromName($uname) == false) {
            // user does not exist
            return $items;
        }
    
        // Create an array of links to return
        $userforums = ModUtil::apiFunc('Dizkus', 'user', 'readuserforums');
        if (count($userforums) <> 0) {
            $items[] = array('url'     => ModUtil::url('Dizkus', 'user', 'prefs'),
                             'title'   => $this->__('Forum'),
                             'icon'    => 'icon_forumprefs.gif');
        }
    
        // Return the items
        return $items;
    }
}
