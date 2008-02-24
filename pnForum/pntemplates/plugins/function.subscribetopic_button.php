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
 * subscribetopic_button plugin
 * adds the subscribe topic button
 *
 *@params $params['cat_id'] int category id
 *@params $params['forum_id'] int forum id
 *@params $params['topic_id'] int topic id
 *@params $params['image_subscribe']    string the image filename (without path)
 *@params $params['image_unsubscribe']    string the image filename (without path)
 */
function smarty_function_subscribetopic_button($params, &$smarty)
{
    extract($params);
	unset($params);

    if(!isset($image_subscribe) || empty($image_subscribe)) {
        $image_subscribe = 't_abo_on.gif';
    }
    if(!isset($image_unsubscribe) || empty($image_unsubscribe)) {
        $image_unsubscribe = 't_abo_off.gif';
    }

    $userid = pnUserGetVar('uid');
    $out = '';
    if (pnUserLoggedIn()) {
        Loader::includeOnce('modules/pnForum/common.php');
        if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            if(pnModAPIFunc('pnForum', 'user', 'get_topic_subscription_status',
                            array('userid'=>$userid,
                                  'topic_id'=>$topic_id))==false) {
                $imagedata = pnf_getimagepath($image_subscribe);
                if($imagedata == false) {
                    $show = DataUtil::formatForDisplay(_PNFORUM_SUBSCRIBE_TOPIC);
                } else {
                    $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplayHTML(_PNFORUM_SUBSCRIBE_TOPIC) .'" ' . $imagedata['size'] . ' />';
                }
                $out = '<a title="' . DataUtil::formatForDisplay(_PNFORUM_SUBSCRIBE_TOPIC) . '" href="' . DataUtil::formatForDisplayHTML(pnModURL('pnForum', 'user', 'prefs', array('act'=>'subscribe_topic', 'topic'=>$topic_id))) . '">' . $show . '</a>';
            } else {
                $imagedata = pnf_getimagepath($image_unsubscribe);
                if($imagedata == false) {
                    $show = DataUtil::formatForDisplay(_PNFORUM_UNSUBSCRIBE_TOPIC);
                } else {
                    $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplayHTML(_PNFORUM_UNSUBSCRIBE_TOPIC) .'" ' . $imagedata['size'] . ' />';
                }
                $out = '<a title="' . DataUtil::formatForDisplay(_PNFORUM_UNSUBSCRIBE_TOPIC) . '" href="' . DataUtil::formatForDisplayHTML(pnModURL('pnForum', 'user', 'prefs', array('act'=>'unsubscribe_topic', 'topic'=>$topic_id))) . '">' . $show . '</a>';
            }
        }
    }
    return $out;
}
