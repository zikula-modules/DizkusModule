<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
// type, id, assign (optional)
/**
 * boardstats plugin
 * reads some statistics
 *
 * @param $params['type']   string see below
 * @param $params['id']     int    id, depending on $type
 * @param $params['assign'] string (optional) assign the result instead of returning it
 *
 * Possible values of $type and $id and what they deliver
 * ------------------------------------------------------
 * 'all' (id not important): total number of postings in all forums
 * 'topic' (id = topic id) : total number of posts in the given topic
 * 'forumposts' (id = forum id): total number of postings in the given forum
 * 'forumtopics' (id= forum id): total number of topics in the given forum
 */
function smarty_function_boardstats($params, Zikula_View $view)
{
    $dizkusModuleName = "ZikulaDizkusModule";
    $type = (!empty($params['type'])) ? $params['type'] : 'all';
    $id = (!empty($params['id'])) ? $params['id'] : '0';

    $count = ModUtil::apiFunc($dizkusModuleName, 'user', 'countstats', array('id' => $id,
                'type' => $type));

    if (!empty($params['assign'])) {
        $view->assign($params['assign'], $count);

        return;
    }

    return $count;
}