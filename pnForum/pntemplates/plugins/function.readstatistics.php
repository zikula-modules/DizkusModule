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

/**
 * readstatistics
 * reads some statistics of the forum 
 * results are assign to
 *
 * $total_categories: total number of categories
 * $total_topics    : total number of topics 
 * $total_posts     : total number of posts
 * $total_forums    : total number of forums
 */
function smarty_function_readstatistics($params, &$smarty) 
{
    extract($params); 
	unset($params);

    include_once('modules/pnForum/common.php');
    // get some environment
    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT SUM(forum_topics) AS total_topics, 
          SUM(forum_posts) AS total_posts, 
          COUNT(*) AS total_forums
          FROM ".$pntable['pnforum_forums'];
          
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    list ($total_topics,$total_posts,$total_forums) = $result->fields;
    pnfCloseDB($result);
    
    $sql = "SELECT COUNT(*) FROM ".$pntable['pnforum_categories']."";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);        
    list ($total_categories) = $result->fields;
    pnfCloseDB($result);
        
    $smarty->assign('total_categories', $total_categories);
    $smarty->assign('total_topics', $total_topics);
    $smarty->assign('total_posts', $total_posts);
    $smarty->assign('total_forums', $total_forums);
    return;
}
