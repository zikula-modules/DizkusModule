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
 * useravatar plugin
 * retrieves the avatar url
 *
 * @param $params['uid'] int user-id
 *
 */
function smarty_function_useravatar($params, &$smarty)
{
    if (!isset($params['uid'])) {
        $smarty->trigger_error("Error! Missing 'uid' attribute for useravatar.");
        return false;
    }

    $email           = pnUserGetVar('email', $params['uid']);
    $avatar          = pnUserGetVar('avatar', $params['uid']);
    $avatarpath      = pnModGetVar('Users', 'avatarpath');
    $usegravatars    = pnModGetVar('Dizkus', 'usegravatars', 'yes');
    $defaultgravatar = pnModGetVar('Dizkus', 'defaultgravatar', 'modules/Dizkus/pnimages/gravatar_80.jpg');

    if (isset($avatar) && !empty($avatar) && $avatar != 'blank.gif' && $avatar !='gravatar.gif') {
        $useravatar = pnGetBaseURL() . $avatarpath . '/' . $avatar;

        if ($usegravatars == "yes") {
            if (!isset($params['rating'])) $params['rating'] = false;
            if (!isset($params['size'])) $params['size'] = 80;

            $avatarURL = 'http://www.gravatar.com/avatar.php?gravatar_id=' . md5($email);
            if (isset($params['rating']) && !empty($params['rating'])) $avatarURL .= "&rating=".$params['rating'];
            if (isset($params['size']) && !empty($params['size'])) $avatarURL .="&size=".$params['size'];
            $avatarURL .= "&default=".urlencode($useravatar);
        } else {
            $avatarURL = $useravatar;
        }
    } else {
        $avatarURL = pnGetBaseURL() . $defaultgravatar;
    }

    return DataUtil::formatForDisplay($avatarURL);
}