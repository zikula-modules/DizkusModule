<?php

/**
 * listforummods plugin
 * lists a forum mods
 *
 *@param $params['moderators'] array with key=userid, value=username of moderators
 *
 */
function smarty_function_listforummods($params, &$smarty)
{
    extract($params);
	unset($params);

    $out = "";
    foreach($moderators as $mod_id=>$mod_name) {
        if($count > 0) {
	        $out .= ", ";
	    }
	    if($mod_id < 1000000) {
	        $out .= '<a title="'. DataUtil::formatForDisplay(_PNFORUM_PROFILE) . ': ' . DataUtil::formatForDisplay($mod_name) . '" href="user.php?op=userinfo&amp;uname='.DataUtil::formatForDisplay($mod_name).'">'.DataUtil::formatForDisplay($mod_name).'</a>';
	    } else {
	        $out .= DataUtil::formatForDisplay($mod_name);
	    }
	    $count++;
    }
    return $out;
}
