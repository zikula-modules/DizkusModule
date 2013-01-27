<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * This class provides the userdata api functions
 */
class Dizkus_Api_UserData extends Zikula_AbstractApi
{

    private $_online;

    /**
     * user array
     *
     * @var array
     */
    private $usersarray = array();

    public function __construct()
    {
        $this->_online = array();
    }

    /**
     * get_userdata_from_id
     *
     * This function dynamically reads all fields of the <prefix>_users
     * tables. When ever data fields are added there, they will be read too without any change here.
     *
     * @param int $userid The users id (uid).
     *
     * @return array of userdata information
     */
    public function getFromId($userid = null)
    {
        if (is_null($userid)) {
            // core bug #2462 workaround, dangerous, if the guest user id changed....
            $userid = 1;
        }


        if (isset($this->usersarray[$userid])) {
            return $this->usersarray[$userid];
        }

        $makedummy = false;
        // get the core user data
        $userdata = UserUtil::getVars($userid);

        if ($userdata == false) {
            // create a dummy user basing on Anonymous
            // necessary for some socks :-)
            $userdata = UserUtil::getVars(1);
            $makedummy = true;
            $userdata = array_merge($userdata, array(
                'user_posts' => 0,
                'user_rank' => 0,
                'user_level' => 0,
                'user_lastvisit' => 0,
                'user_favorites' => 0,
                'user_post_order' => 0)
            );
        } else {
            // create some items that might be missing
            if (!array_key_exists('user_rank', $userdata)) {
                $userdata['user_rank'] = 0;
            }
            if (!array_key_exists('user_posts', $userdata)) {
                $userdata['user_posts'] = 0;
            }
        }



        // set some basic data
        $userdata['moderate'] = false;
        $userdata['reply'] = false;
        $userdata['seeip'] = false;

        //
        // extract attributes if existing, also necessary for the Dizkus attributes to the users table
        //
        if (array_key_exists('__ATTRIBUTES__', $userdata) && is_array($userdata['__ATTRIBUTES__'])) {
            foreach ($userdata['__ATTRIBUTES__'] as $attributename => $attributevalue) {
                if (substr($attributename, 0, 7) == 'dizkus_') {
                    // cut off the dizkus_ form
                    $userdata[substr($attributename, 7, strlen($attributename))] = $attributevalue;
                } else {
                    $userdata[$attributename] = $attributevalue;
                }
            }
        }

        if (!array_key_exists('signature', $userdata)) {
            $userdata['signature'] = '';
        }
        //
        // get the users group membership
        //
        //$userdata['groups'] = ModUtil::apiFunc('Groups', 'user', 'getusergroups', array('uid' => $userdata['uid']));
        $userdata['groups'] = array();

        //
        // get the users rank
        $userdata = ModUtil::apiFunc($this->name, 'Rank', 'addToUserData', $userdata);


        // user online status
        $userdata['online'] = $this->getUserOnlineStatus($userdata['uid']);

        if ($makedummy == true) {
            // we create a dummy user, so we need to adjust some of the information
            // gathered so far
            $userdata['name'] = $this->__('**unknown user**');
            $userdata['uname'] = $this->__('**unknown user**');
            $userdata['email'] = '';
            $userdata['femail'] = '';
            $userdata['url'] = '';
            $userdata['groups'] = array();
        } else {
            $this->usersarray[$userid] = $userdata;
        }

        return $userdata;
    }

    /**
     * getUserOnlineStatus
     *
     * Check if a user is online
     *
     * @param array $args Arguments array.
     *
     * @return boolean True if online
     */
    public function getUserOnlineStatus($args)
    {
        //int $uid The users id
        if (empty($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
        }

        if (array_key_exists($args['uid'], $this->_online)) {
            return $this->_online[$args['uid']];
        }

        $ztable = DBUtil::getTables();
        $activetime = DateUtil::getDateTime(time() - (System::getVar('secinactivemins') * 60));
        $where = $ztable['session_info_column']['uid'] . " = '" . $args['uid'] . "'
                  AND " . $ztable['session_info_column']['lastused'] . " > '" . DataUtil::formatForStore($activetime) . "'";
        $sessioninfo = DBUtil::selectObject('session_info', $where);

        $isOnline = ($sessioninfo['uid'] == $args['uid']) ? true : false;
        $this->_online[$args['uid']] = $isOnline;
        return $isOnline;
    }

}
