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
 * postanchor plugin
 * adds an anchor to the url to directly jump to a special posting inside a thread
 *
 * @params $params['postings']  int number of postings in this thread
 * @params $params['min']       int minimum number of postings needed before adding an anchor
 *                                 default = 2
 * @params $params['post_id']   int post id
 * @params $params['assign']    string(optional) if set, thr result is assigned to this
 *                                              variable and not returned
 *
 *
 **************************************************************
 *
 * This plugin is deprecated, do not use it in your templates!!
 *
 **************************************************************
 */
function smarty_function_postanchor($params, &$smarty)
{
    extract($params);
	unset($params);

    if (empty($post_id)) { return; }
    if (empty($postings) || $postings==0) { return; }
    if (empty($min)) {
        $min = pnModGetVar('Dizkus', 'min_postings_for_anchor');
        $min = (!empty($min)) ? $min : 2;
    }

    $anchor = "";
    if ($postings >= $min) {
        $anchor = "#pid$post_id";
    }

    if (!empty($assign)) {
        $smarty->assign($assign, $anchor);
        return;
    }
    return $anchor;
}
