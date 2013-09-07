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
 * $total_topics    : total number of topics
 * $total_posts     : total number of posts
 * $total_forums    : total number of forums
 * $last_user       : newest user
 */
function smarty_function_readstatistics($params, Zikula_View $view)
{
    $dizkusModuleName = "ZikulaDizkusModule";
    $view->assign('total_topics', ModUtil::apiFunc($dizkusModuleName, 'user', 'countstats', array('type' => 'alltopics')));
    $view->assign('total_posts', ModUtil::apiFunc($dizkusModuleName, 'user', 'countstats', array('type' => 'allposts')));
    $view->assign('total_forums', ModUtil::apiFunc($dizkusModuleName, 'user', 'countstats', array('type' => 'forum')));
    $view->assign('last_user', ModUtil::apiFunc($dizkusModuleName, 'user', 'countstats', array('type' => 'lastuser')));

    return;
}
