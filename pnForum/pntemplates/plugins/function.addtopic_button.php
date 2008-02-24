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
 * addtopic_button
 * shows a button "new topic" depending in the lang files
 *
 *@params $params['cat_id'] int category id
 *@params $params['forum_id'] int forum id
 *@params $params['image']    string the image filename (without path)
 */
function smarty_function_addtopic_button($params, &$smarty)
{
    extract($params);
	unset($params);

    // set a default value
    if(!isset($image) || empty($image)) {
        $image = 'post.gif';
    }

    Loader::includeOnce('modules/pnForum/common.php');
    $out = "";
    if(allowedtowritetocategoryandforum($cat_id, $forum_id)) {
        $imagedata = pnf_getimagepath($image);
        if($imagedata == false) {
            $show = DataUtil::formatForDisplay(_PNFORUM_NEWTOPIC);
        } else {
            $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(_PNFORUM_NEWTOPIC) .'" ' . $imagedata['size'] . ' />';
        }
        $out = '<a title="' . DataUtil::formatForDisplay(_PNFORUM_NEWTOPIC) . '" href="'. DataUtil::formatForDisplay(pnModURL('pnForum', 'user', 'newtopic', array('forum'=>$forum_id))) . '">' . $show . '</a>';
	}
    return $out;
}
