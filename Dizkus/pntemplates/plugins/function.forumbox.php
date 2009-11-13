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
 * forumbox
 * creates a dropdown list with all available forums for the user
 *
 */
function smarty_function_forumbox($params, &$smarty) 
{
	extract($params); 
	unset($params);

	if(!pnModAPILoad('Dizkus', 'admin')) {
		$smarty->trigger_error("Error! Could not load Dizkus administration API.");
	} 

	$out = "";
	$forums = pnModAPIFunc('Dizkus', 'admin', 'readforums');

	if(count($forums)>0) {
        Loader::includeOnce('modules/Dizkus/common.php');
		$out ='<select name="Dizkus_forum[]" id="Dizkus_forum[]" size="1">';
		$out.='<option value="" selected>'. _SRCHALLTOPICS .'</option>';
		foreach($forums as $forum) {
			if(allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
				$out .= '<option value="'.$forum['forum_id'].'">'.DataUtil::formatForDisplay($forum['cat_title']).'::'.DataUtil::formatForDisplay($forum['forum_name']).'</option>';
			}
        }
	    $out .= '</select>';
	    return $out;
    }
}
