<?php

/**
 * post_order_button plugin
 * adds the post_order button
 *
 *@params $params['topic_id'] int forum id
 *@params $params['return_to'] string url to return to after subscribing, necessary because
 *                                    the subscription page can be reached from several places
 *@params $params['image_ascending']    string the image filename (without path)
 *@params $params['image_descending']    string the image filename (without path)
 */
function smarty_function_post_order_button($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($params);
	  unset($params);

    if (!isset($image_ascending) || empty($image_ascending)) {
        $image_ascending = 'postorderasc.gif';
    }
    if (!isset($image_descending) || empty($image_descending)) {
        $image_descending = 'postorderdesc.gif';
    }

    // initialize the variable
    $out = '';

    // if we don't know what topic we came from and we don't have a return_to
    // parameter passed then send them back to the main forum list
    if (!isset($topic_id) && !isset($return_to)) {
        $topic_id = false;
        $return_to = 'main';
    }

    // if we have a numeric topic but no return_to then set the return
    // to viewtopic.  Otherwise return to the main forum view.
    if (empty($return_to) && is_numeric($topic_id)) {
        $return_to = 'viewtopic';
    } else {
        $topic_id = false;
        if (!isset($return_to)) {
            $return_to = 'main';
        }
    }
    if (pnUserLoggedIn()) {
        $post_order = pnModAPIFunc('Dizkus','user','get_user_post_order');
        if ($post_order == 'ASC' ) {
            $imagedata = dzk_getimagepath($image_ascending);
            if ($imagedata == false) {
                $show = DataUtil::formatForDisplay(__('Display newest post first', $dom));
            } else {
                $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(__('Change post order', $dom)) .'" ' . $imagedata['size'] . ' />';
            }
        } else {
            $imagedata = dzk_getimagepath($image_descending);
            if ($imagedata == false) {
                $show = DataUtil::formatForDisplay(__('Display oldest post first', $dom));
            } else {
                $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(__('Change post order', $dom)) .'" ' . $imagedata['size'] . ' />';
            }
        }
        $out = '<a title="' . DataUtil::formatForDisplay(__('Change post order', $dom)) . '" href="' . DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'prefs', array('act'=>'change_post_order', 'topic'=>$topic_id, 'return_to'=>$return_to))) . '">' . $show . '</a>';
    }
    return $out;
}
