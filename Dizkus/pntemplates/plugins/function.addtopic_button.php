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
 * addtopic_button
 * shows a button "new topic" depending in the lang files
 *
 * @params $params['cat_id'] int category id
 * @params $params['forum_id'] int forum id
 * @params $params['image']    string the image filename (without path)
 */
function smarty_function_addtopic_button($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    // set a default value
    if (!isset($params['image']) || empty($params['image'])) {
        $params['image'] = 'post.gif';
    }

    Loader::includeOnce('modules/Dizkus/common.php');
    $out = "";
    if (allowedtowritetocategoryandforum($params['cat_id'], $params['forum_id'])) {
        $imagedata = dzk_getimagepath($params['image']);
        if ($imagedata == false) {
            $show = DataUtil::formatForDisplay(__('New topic', $dom));
        } else {
            $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(__('New topic', $dom)) .'" ' . $imagedata['size'] . ' />';
        }
        $out = '<a title="' . DataUtil::formatForDisplay(__('New topic', $dom)) . '" href="'. DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'newtopic', array('forum'=> $params['forum_id']))) . '">' . $show . '</a>';
	}
    return $out;
}
