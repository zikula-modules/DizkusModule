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
 * favoriteforum_button plugin
 * adds the add to favorites forum button
 *
 *@params $params['forum_id'] int forum id
 *@params $params['return_to'] string url to return to after subscribing, necessary because
 *                                    the subscription page can be reached from several places
 *@params $params['image_addfavorite']    string the image filename (without path)
 *@params $params['image_remfavorite']    string the image filename (without path)
 */
function smarty_function_favoriteforum_button($params, &$smarty)
{
    extract($params);
	unset($params);

    if(!isset($image_addfavorite) || empty($image_addfavorite)) {
        $image_addfavorite = 'add2favorites.gif';
    }
    if(!isset($image_remfavorite) || empty($image_remfavorite)) {
        $image_remfavorite = 'removefavorite.gif';
    }

    $out = '';
    if (pnUserLoggedIn() && (pnModGetVar('pnForum', 'favorites_enabled')=='yes') ) {
        Loader::includeOnce('modules/pnForum/common.php');
        $userid = pnUserGetVar('uid');
        if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            if(pnModAPIFunc('pnForum', 'user', 'get_forum_favorites_status',
                            array('userid'=>$userid,
                                  'forum_id'=>$forum_id))==false) {
                $imagedata = pnf_getimagepath($image_addfavorite);
                if($imagedata == false) {
                    $show = DataUtil::formatForDisplay(_PNFORUM_ADD_FAVORITE_FORUM);
                } else {
                    $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(_PNFORUM_ADD_FAVORITE_FORUM) .'" ' . $imagedata['size'] . ' />';
                }
                $out = '<a title="' . DataUtil::formatForDisplay(_PNFORUM_ADD_FAVORITE_FORUM) . '" href="' . DataUtil::formatForDisplay(pnModURL('pnForum', 'user', 'prefs', array('act'=>'add_favorite_forum', 'forum'=>$forum_id, 'return_to'=>$return_to))) . '">' . $show . '</a>';
            } else {
                $imagedata = pnf_getimagepath($image_remfavorite);
                if($imagedata == false) {
                    $show = DataUtil::formatForDisplay(_PNFORUM_REMOVE_FAVORITE_FORUM);
                } else {
                    $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(_PNFORUM_REMOVE_FAVORITE_FORUM) .'" ' . $imagedata['size'] . ' />';
                }
                $out = '<a title="' . DataUtil::formatForDisplay(_PNFORUM_REMOVE_FAVORITE_FORUM) . '" href="' . DataUtil::formatForDisplay(pnModURL('pnForum', 'user', 'prefs', array('act'=>'remove_favorite_forum', 'forum'=>$forum_id, 'return_to'=>$return_to))) . '">' . $show . '</a>';
            }
        }
    }
    return $out;
}
