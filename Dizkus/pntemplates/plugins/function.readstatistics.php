<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * readstatistics
 * reads some statistics of the forum 
 * results are assigned to
 *
 * $total_categories: total number of categories
 * $total_topics    : total number of topics 
 * $total_posts     : total number of posts
 * $total_forums    : total number of forums
 * $last_user       : newest user
 */
function smarty_function_readstatistics($params, &$smarty) 
{
    $smarty->assign('total_categories', pnModAPIFunc('Dizkus', 'user', 'boardstats', array('type' => 'category')));
    $smarty->assign('total_topics', pnModAPIFunc('Dizkus', 'user', 'boardstats', array('type' => 'alltopics')));
    $smarty->assign('total_posts', pnModAPIFunc('Dizkus', 'user', 'boardstats', array('type' => 'allposts')));
    $smarty->assign('total_forums', pnModAPIFunc('Dizkus', 'user', 'boardstats', array('type' => 'forum')));
    $smarty->assign('last_user', pnModAPIFunc('Dizkus', 'user', 'boardstats', array('type' => 'lastuser')));
    return;
}
