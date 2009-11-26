<?php

/**
 * readtopforums
 * reads the last $maxforums forums and assign them in a
 * variable topforums and the number of them in topforumscount
 *
 * @params maxforums (int) number of forums to read, default = 5
 *
 */
function smarty_function_readtopforums($params, &$smarty) 
{
    extract($params); 
	unset($params);

    Loader::includeOnce('modules/Dizkus/common.php');
    // get some enviroment
    list($dbconn, $pntable) = dzkOpenDB();

    $forummax = (!empty($maxforums)) ? $maxforums : 5;
    
    $sql = "SELECT f.forum_id, 
                   f.forum_name, 
                   f.forum_topics, 
                   f.forum_posts, 
                   c.cat_title,
                   c.cat_id
          FROM ".$pntable['dizkus_forums']." AS f, 
               ".$pntable['dizkus_categories']." AS c
          WHERE f.cat_id = c.cat_id
          ORDER BY forum_posts DESC";

    $result = dzkSelectLimit($dbconn, $sql, $forummax, false, __FILE__, __LINE__);
    $result_forummax = $result->PO_RecordCount();
    if ($result_forummax <= $forummax) {
        $forummax = $result_forummax;
    }

    $topforums = array();
    while (list($forum_id, $forum_name, $forum_topics, $forum_posts, $cat_title, $cat_id) = $result->FetchRow()) {
        if (allowedtoreadcategoryandforum($cat_id, $forum_id)) {
            $topforum = array();
            $topforum['forum_id'] = $forum_id;
            $topforum['forum_name'] = DataUtil::formatForDisplay($forum_name);
            $topforum['forum_topics'] = $forum_topics;
            $topforum['forum_posts'] = $forum_posts;
            $topforum['cat_title'] = DataUtil::formatForDisplay($cat_title);
            $topforum['cat_id'] = $cat_id;
            array_push($topforums, $topforum);
        }
    }
    dzkCloseDB($result);
    $smarty->assign('topforumscount', count($topforums));
    $smarty->assign('topforums', $topforums);
    return;
}
