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
 * {getRankByPostCount posts=$post.poster.postCount ranks=$ranks assign='posterRank'}
 */
function smarty_function_getRankByPostCount($params, Zikula_View $view)
{
    $posts = !empty($params['posts']) ? $params['posts'] : 0;
    if (!isset($params['ranks'])) {
        return LogUtil::registerArgsError();
    }

    $posterRank = null;

    foreach ($params['ranks'] as $rank) {
        if (($posts >= $rank->getMinimumCount()) && ($posts <= $rank->getMaximumCount())) {
            $posterRank = $rank;
        }
    }

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $posterRank);
    } else {
        return $posterRank;
    }
}
