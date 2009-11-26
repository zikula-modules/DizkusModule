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
    $dom = ZLanguage::getModuleDomain('Dizkus');    

    extract($params);
	  unset($params);

    // set a default value
    if (!isset($image) || empty($image)) {
        $image = 'sendto.gif';
    }

    Loader::includeOnce('modules/Dizkus/common.php');
    $out = '';
    if (allowedtowritetocategoryandforum($cat_id, $forum_id)) {
        $imagedata = dzk_getimagepath($image);
        if ($imagedata == false) {
            $show = DataUtil::formatForDisplay(__('Send as e-mail', $dom));
        } else {
            $show = '<img src="' . $imagedata['path'] . '" alt="' . DataUtil::formatForDisplay(__('Send as e-mail', $dom)) .'" ' . $imagedata['size'] . ' />';
        }
	    $out = '<a title="' . DataUtil::formatForDisplay(__('Send as e-mail', $dom)) . '" href="'. DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'emailtopic', array('topic' => $topic_id))) . '">' . $show . '</a>';
	}
    return $out;
}
