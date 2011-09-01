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
    $forummax = (!empty($params['maxforums'])) ? $params['maxforums'] : 5;
    
    ModUtil::dbInfoLoad('Dizkus');
    $ztable = DBUtil::getTables();
    $sql = "SELECT f.forum_id, 
                   f.forum_name, 
                   f.forum_topics, 
                   f.forum_posts, 
                   c.cat_title,
                   c.cat_id
          FROM ".$ztable['dizkus_forums']." AS f, 
               ".$ztable['dizkus_categories']." AS c
          WHERE f.cat_id = c.cat_id
          ORDER BY forum_posts DESC";

    $res = DBUtil::executeSQL($sql, -1, $forummax);
    $colarray = array('forum_id', 'forum_name', 'forum_topics', 'forum_posts', 'cat_title', 'cat_id');
    $result    = DBUtil::marshallObjects($res, $colarray);

    $result_forummax = count($result);
    if ($result_forummax <= $forummax) {
        $forummax = $result_forummax;
    }

    $topforums = array();
    if (is_array($result) && !empty($result)) {
        foreach ($result as $topforum) {
            if (allowedtoreadcategoryandforum($topforum['cat_id'], $topforum['forum_id'])) {
                $topforum['forum_name'] = DataUtil::formatForDisplay($topforum['forum_name']);
                $topforum['cat_title'] = DataUtil::formatForDisplay($topforum['cat_title']);
                array_push($topforums, $topforum);
            }
        }
    }

    $smarty->assign('topforumscount', count($topforums));
    $smarty->assign('topforums', $topforums);
    return;
}
