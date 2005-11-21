<?php
/**
 * forum backend (with permission check)
 * to be placed in the PostNuke root
 * @version $Id$
 * @author Andreas Krapohl, Frank Schummertz
 * @copyright 2005 by pnForum Team
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 */

/**
 * initialize the PostNuke environment
 */
include 'includes/pnAPI.php';
pnInit();

/**
 * load pnForum specific support functions
 */
include_once 'modules/pnForum/common.php';

list($count, $forum_id, $cat_id, $feed, $user) = pnVarCleanFromInput('count', 'forum_id', 'cat_id', 'feed', 'user');

/**
 * check for feed, if not set, use rss091 as default
 */
if(!empty($feed)) {
    // feed is set, check counter
    $count = (empty($count)) ? 10 : (int)$count;
} else {
    // set defaults
    $feed = 'rss091';
    $count = 5;
}

if(isset($forum_id) && !is_numeric($forum_id)) {
    die('backforum.php: invalid forum id "' . pnVarPrepForDisplay($forum_id) . '"');
}
if(isset($cat_id) && !is_numeric($cat_id)) {
    die('backforum.php: invalid category id "' . pnVarPrepForDisplay($cat_id) . '"');
}

/**
 * create pnRender object
 */
$pnr =& new pnRender('pnForum');
$pnr->caching = false;

/**
 * check if template for feed exists
 */
$templatefile = 'pnforum_feed_' . pnVarPrepForOS($feed) . '.html';
if(!$pnr->template_exists($templatefile)) {
    // silently stop working
    exit;
}

/**
 * get user id
 */
if(!empty($user)) {
    $uid = pnUserGetIDFromName($user);
}

/**
 * set some defaults
 */
$link = pnModURL('pnForum', 'user', 'main');
$forumname = 'Forum';
// default where clause => no where clause
$where = '';

/**
 * check for forum_id
 */
if(!empty($forum_id)) {
    $forum = pnModAPIFunc('pnForum', 'user', 'readuserforums',
                          array('forum_id' => $forum_id));
    if(count($forum) == 0) {
        // not allowed to see forum
        exit;
    }
    $where = "AND t.forum_id = '" . (int)pnVarPrepForStore($forum_id) . "' ";
    $link = pnModURL('pnForum', 'user', 'viewforum', array('forum' => $forum_id));
    $forumname = $forum['forum_name'];
} elseif (!empty($cat_id)) {
    if(!pnSecAuthAction(0, 'pnForum::', $cat_id . ':.*:', ACCESS_READ)) {
        exit;
    }
    $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                             array('cat_id' => $cat_id));
    if($category == false) {
        exit;
    }
    $where = "AND f.cat_id = '" . (int)pnVarPrepForStore($cat_id) . "' ";
    $link = pnModURL('pnForum', 'user', 'main', array('viewcat' => $cat_id));
    $forumname = $category['cat_title'];
    
} elseif (isset($uid) && ($uid<>false)) {
    $where = "AND p.poster_id=" . $uid . " ";
}

$pnr->assign('forum_name', $forumname);
$pnr->assign('forum_link', $link);
$pnr->assign('sitename', pnConfigGetVar('sitename'));
$pnr->assign('adminmail', pnConfigGetVar('adminmail'));

/**
 * get database information
 */

pnModDBInfoLoad('pnForum');
list($dbconn, $pntable) = pnfOpenDB();

/**
 * SQL statement to fetch last 10 topics
 */
$sql = "SELECT t.topic_id,
               t.topic_title,
               t.topic_replies,
               t.topic_last_post_id,
               f.forum_id,
               f.forum_name,
               p.poster_id,
               c.cat_id,
               c.cat_title
        FROM ".$pntable['pnforum_topics']." as t,
             ".$pntable['pnforum_forums']." as f,
             ".$pntable['pnforum_posts']." as p,
             ".$pntable['pnforum_categories']." as c
        WHERE t.forum_id = f.forum_id AND
              t.topic_last_post_id = p.post_id AND
              f.cat_id = c.cat_id
              $where
        ORDER BY t.topic_time DESC
        LIMIT 100";
$result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
$result_postmax = $result->PO_RecordCount();

if ($result_postmax <= $count) {
    $count = $result_postmax;
}
$shown_results=0;
$posts_per_page  = pnModGetVar('pnForum', 'posts_per_page');
$posts = array();

while ((list($topic_id, $topic_title, $topic_replies, $topic_last_post_id, $forum_id, $forum_name, $poster_id, $cat_id, $cat_title) = $result->FetchRow())
              && ($shown_results < $count) ) {
    if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
        $post = array();
        $post['topic_id']           = $topic_id;
        $post['topic_title']        = $topic_title;
        $post['topic_replies']      = $topic_replies;
        $post['topic_last_post_id'] = $topic_last_post_id;
        $post['forum_id']           = $forum_id;
        $post['forum_name']         = $forum_name;
        $post['poster_id']          = $poster_id;
        $post['cat_id']             = $cat_id;
        $post['cat_title']          = $cat_title;
        $shown_results++;
        $start = ((ceil(($topic_replies + 1)  / $posts_per_page) - 1) * $posts_per_page);
        $post['post_url'] = pnModURL('pnForum', 'user', 'viewtopic',
                                     array('topic' => $topic_id,
                                           'start' => $start));
        $post['last_post_url'] = $post['post_url'] . "#pid" . $topic_last_post_id;
        array_push($posts, $post);
        $result->MoveNext();
    }
}
pnfCloseDB($result);
$pnr->assign('posts', $posts);

header("Content-Type: text/xml");
$pnr->display($templatefile);

?>