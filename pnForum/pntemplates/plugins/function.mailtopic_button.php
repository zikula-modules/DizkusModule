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
 * mailtopic_button plugin
 * adds the mail topic button
 *
 *@params $params['cat_id'] int category id
 *@params $params['forum_id'] int forum id
 *@params $params['topic_id'] int topic id
 *@params $params['image']    string the image filename (without path)
 */
function smarty_function_mailtopic_button($params, &$smarty)
{
    extract($params);
	unset($params);

    // set a default value
    if(!isset($image) || empty($image)) {
        $image = 'sendto.gif';
    }

    include_once('modules/pnForum/common.php');
    $out = '';
    if(allowedtowritetocategoryandforum($cat_id, $forum_id)) {
        $imagedata = pnf_getimagepath($image);
        if($imagedata == false) {
            $show = DataUtil::formatForDisplay(_PNFORUM_EMAIL_TOPIC);
        } else {
            $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(_PNFORUM_EMAIL_TOPIC) .'" ' . $imagedata['size'] . ' />';
        }
	    $out = '<a title="' . DataUtil::formatForDisplay(_PNFORUM_EMAIL_TOPIC) . '" href="'. DataUtil::formatForDisplay(pnModURL('pnForum', 'user', 'emailtopic', array('topic'=>$topic_id))) . '">' . $show . '</a>';
	}
    return $out;
}
