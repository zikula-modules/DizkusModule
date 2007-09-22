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
	        $out .= '<a title="'. pnVarPrepForDisplay(_PNFORUM_PROFILE) . ': ' . pnVarPrepForDisplay($mod_name) . '" href="user.php?op=userinfo&amp;uname='.pnVarPrepForDisplay($mod_name).'">'.pnVarPrepForDisplay($mod_name).'</a>';
	    } else {
	        $out .= pnVarPrepForDisplay($mod_name);
	    }
	    $count++;
    }
    return $out;
}
