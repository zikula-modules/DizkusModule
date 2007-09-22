<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

// type, id, assign (optional)
/**
 * boardstats plugin
 * reads some statistics by calling the pnForum_userapi_boardstats() function
 *
 *@params $params['type']   string see below
 *@params $params['id']     int    id, depending on $type
 *@params $params['assign'] string (optional) assign the result instead of returning it
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
    extract($params); 
	unset($params);

    if(!pnModAPILoad('pnForum', 'user')) {
        $smarty->trigger_error("loading pnForum userapi failed");
        return;
    } 

    $type = (!empty($type)) ? $type : "all";
    $id   = (!empty($id)) ? $id : "0";
    
    $count = pnModAPIFunc('pnForum', 'user', 'boardstats',
                          array('id'   => $id,
                                'type' => $type));
    if(!empty($assign)) {
        $smarty->assign($assign, $count);
        return;
    }
    return $count;
}
