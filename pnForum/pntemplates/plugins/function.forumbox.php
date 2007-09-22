<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
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

	if(!pnModAPILoad('pnForum', 'admin')) {
		$smarty->trigger_error("loading pnForum adminapi failed");
	} 

	$out = "";
	$forums = pnModAPIFunc('pnForum', 'admin', 'readforums');

	if(count($forums)>0) {
        include_once('modules/pnForum/common.php');
		$out ='<select name="pnForum_forum[]" id="pnForum_forum[]" size="1">';
		$out.='<option value="" selected>'. _SRCHALLTOPICS .'</option>';
		foreach($forums as $forum) {
			if(allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
				$out .= '<option value="'.$forum['forum_id'].'">'.pnVarPrepForDisplay($forum['cat_title']).'::'.pnVarPrepForDisplay($forum['forum_name']).'</option>';
			}
        }
	    $out .= '</select>';
	    return $out;
    }
}
