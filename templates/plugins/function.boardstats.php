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
 * reads some statistics by calling the Dizkus_userapi_boardstats() function
 *
 * @params $params['type']   string see below
 * @params $params['id']     int    id, depending on $type
 * @params $params['assign'] string (optional) assign the result instead of returning it
 *
 * Possible values of $type and $id and what they deliver
 * ------------------------------------------------------
 * 'all' (id not important): total number of postings in all categories and forums
 * 'topic' (id = topic id) : total number of posts in the given topic
 * 'forumposts' (id = forum id): total number of postings in the given forum
 * 'forumtopics' (id= forum id): total number of topics in the given forum
 * 'category' (id not important): total number of categories
 */
function smarty_function_boardstats($params, &$smarty) 
{
    $type = (!empty($params['type'])) ? $params['type'] : 'all';
    $id   = (!empty($params['id'])) ? $params['id'] : '0';
    
    $count = ModUtil::apiFunc('Dizkus', 'user', 'boardstats',
                          array('id'   => $id,
                                'type' => $type));

    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $count);
        return;
    }

    return $count;
}
