<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
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
function smarty_function_readstatistics($params, Zikula_View $view) 
{
    $view->assign('total_categories', ModUtil::apiFunc('Dizkus', 'user', 'boardstats', array('type' => 'category')));
    $view->assign('total_topics', ModUtil::apiFunc('Dizkus', 'user', 'boardstats', array('type' => 'alltopics')));
    $view->assign('total_posts', ModUtil::apiFunc('Dizkus', 'user', 'boardstats', array('type' => 'allposts')));
    $view->assign('total_forums', ModUtil::apiFunc('Dizkus', 'user', 'boardstats', array('type' => 'forum')));
    $view->assign('last_user', ModUtil::apiFunc('Dizkus', 'user', 'boardstats', array('type' => 'lastuser')));
    return;
}
