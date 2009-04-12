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
    $out = '';
    if(isset($params['moderators']) && is_array($params['moderators'])) {
        foreach($params['moderators'] as $mod_id => $mod_name) {
            if($count > 0) {
	            $out .= ", ";
	        }
	        if($mod_id < 1000000) {
	            $out .= '<a title="'. DataUtil::formatForDisplay(_DZK_PROFILE) . ': ' . DataUtil::formatForDisplay($mod_name) . '" href="' . DataUtil::formatForDisplay(pnModURL('Profile', 'user', 'view', array('uname' => $mod_name))) . '">' . DataUtil::formatForDisplay($mod_name) . '</a>';
	        } else {
	            $out .= DataUtil::formatForDisplay($mod_name);
	        }
	        $count++;
        }
    }
    return $out;
}
