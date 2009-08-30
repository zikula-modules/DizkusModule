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
 * jumpbox plugin
 * creates a dropdown list with all available forums for the current user.
 * seleting a forum issue a direct forward to the viewforum() function
 *
 */
function smarty_function_jumpbox($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($params);
	  unset($params);

    if(!pnModAPILoad('Dizkus', 'admin')) {
        $smarty->trigger_error("loading Dizkus adminapi failed");
        return;
    }

    $out = "";
    $forums = pnModAPIFunc('Dizkus', 'admin', 'readforums');
    if(count($forums)>0) {
        Loader::includeOnce('modules/Dizkus/common.php');
        $out ='<form action="' . DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewforum')) . '" class="dzk_form dzk_float_right" method="get">
               <label for="dizkus_forum"><strong>' . DataUtil::formatForDisplay(__('Forum', $dom)) . ': </strong></label>
               <select name="forum" id="dizkus_forum" onchange="location.href=this.options[this.selectedIndex].value">
	             <option value="'.DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'main')).'">' . DataUtil::formatForDisplay(__('- select forum -', $dom)) . '</option>';
        foreach($forums as $forum) {
            if(allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id'])) {
            	$out .= '<option value="' . DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewforum', array('forum' => $forum['forum_id']))) . '">' . DataUtil::formatForDisplay($forum['cat_title']) . '&nbsp;::&nbsp;' . DataUtil::formatForDisplay($forum['forum_name']) . '</option>';
            }
        }
        $out .= '</select>
                 </form>';
    }
    return $out;

}
