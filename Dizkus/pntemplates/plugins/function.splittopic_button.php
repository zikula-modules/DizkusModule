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
 * splittopic_button plugin
 * adds the split topic button
 *
 *@params $params['cat_id'] int category id
 *@params $params['forum_id'] int forum id
 *@params $params['post_id'] int post id
 *@params $params['image']    string the image filename (without path)
 */
function smarty_function_splittopic_button($params, &$smarty)
{
    extract($params);
	unset($params);

    // set a default value
    if(!isset($image) || empty($image)) {
        $image = 'splitit.gif';
    }

    Loader::includeOnce('modules/Dizkus/common.php');
    if(allowedtomoderatecategoryandforum($cat_id, $forum_id)) {
        if($imagedata == false) {
            $show = DataUtil::formatForDisplay(_DZK_SPLITTOPIC_TITLE);
        } else {
            $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(_DZK_SPLITTOPIC_TITLE) .'" ' . $imagedata['size'] . ' />';
        }
        $out = '<a title="' . DataUtil::formatForDisplay(_DZK_SPLITTOPIC_TITLE) . '" href="'. DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'splittopic', array('post'=>$post_id))) . '">' . $show . '</a>';
    }
    return $out;
}
