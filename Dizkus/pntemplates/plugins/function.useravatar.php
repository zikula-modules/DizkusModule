<?php
/**
* @param        array       $params      All attributes passed to this function from the template
* @param        object      &$smarty     Reference to the Smarty object
* @return       string      the url to display the users avatar
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
    } else {
        $useravatar = pnGetBaseURL() . $defaultgravatar;
    }

    if ($usegravatars == "yes") {
        if (!isset($params['rating'])) $params['rating'] = false;
        if (!isset($params['size'])) $params['size'] = 80;

        $avatarURL = 'http://www.gravatar.com/avatar.php?gravatar_id=' . md5($email);
        if ($params['rating'] && $params['rating'] != '') $avatarURL .= "&rating=".$params['rating'];
        if ($params['size'] && $params['size'] != '') $avatarURL .="&size=".$params['size'];
        $avatarURL .= "&default=".urlencode($useravatar);
        
    } else {
        $avatarURL = $useravatar;
    }

    return DataUtil::formatForDisplay($avatarURL);
}
