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
 * subscribeforum_button plugin
 * adds the subscribe forum button
 *
 *@params $params['cat_id'] int category id
 *@params $params['forum_id'] int forum id
 *@params $params['return_to'] string url to return to after subscribing, necessary because
 *                                    the subscription page can be reached from several places
 *@params $params['image_subscribe']    string the image filename (without path)
 *@params $params['image_unsubscribe']    string the image filename (without path)
 */
function smarty_function_subscribeforum_button($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($params);
	  unset($params);

    if(!isset($image_subscribe) || empty($image_subscribe)) {
        $image_subscribe = 'f_abo_on.gif';
    }
    if(!isset($image_unsubscribe) || empty($image_unsubscribe)) {
        $image_unsubscribe = 'f_abo_off.gif';
    }

    $userid = pnUserGetVar('uid');
    $out = '';
    if (pnUserLoggedIn()) {
        Loader::includeOnce('modules/Dizkus/common.php');
        if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            if(pnModAPIFunc('Dizkus', 'user', 'get_forum_subscription_status',
                            array('userid'=>$userid,
                                  'forum_id'=>$forum_id))==false) {
                $imagedata = dzk_getimagepath($image_subscribe);
                if($imagedata == false) {
                    $show = DataUtil::formatForDisplay(__('Subscribe forum', $dom));
                } else {
                    $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(__('Subscribe forum', $dom)) .'" ' . $imagedata['size'] . ' />';
                }
                $out = '<a title="' . DataUtil::formatForDisplay(__('Subscribe forum', $dom)) . '" href="' . DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'prefs', array('act'=>'subscribe_forum', 'forum'=>$forum_id, 'return_to'=>$return_to))) . '">' . $show . '</a>';
            } else {
                $imagedata = dzk_getimagepath($image_unsubscribe);
                if($imagedata == false) {
                    $show = DataUtil::formatForDisplay(__('Unsubscribe forum', $dom));
                } else {
                    $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(__('Unsubscribe forum', $dom)) .'" ' . $imagedata['size'] . ' />';
                }
                $out = '<a title="' . DataUtil::formatForDisplay(__('Unsubscribe forum', $dom)) . '" href="' . DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'prefs', array('act'=>'unsubscribe_forum', 'forum'=>$forum_id, 'return_to'=>$return_to))) . '">' . $show . '</a>';
            }
        }
    }
    return $out;
}
