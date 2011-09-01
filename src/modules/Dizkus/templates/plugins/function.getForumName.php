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
 * adminlink plugin
 * adds a link to the configuration of a category or forum
 *
 * @params $params['type'] string, either 'category' or 'forum'
 * @params $params['id']   int     category or forum id, depending of $type
 */ 
function smarty_function_getForumName($params, &$smarty) 
{
    extract($params);
    $forum = DBUtil::selectObjectByID('dizkus_forums', $id, 'forum_id');
    $url = ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $id));
    return '<a href="'.DataUtil::formatForDisplay($url).'">'.DataUtil::formatForDisplay($forum['forum_name']).'</a>';
}
