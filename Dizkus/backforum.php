<?php
/**
 * forum backend (with permission check)
 * to be placed in the Zikula root
 * @version $Id$
 * @author Andreas Krapohl, Frank Schummertz, Arjen Tebbenhof [short urls]
 * @copyright 2005 by Dizkus Team
 * @package Dizkus
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.dizkus.com
 */

/**
 * initialize the Zikula environment
 */
include 'includes/pnAPI.php';
pnInit();

/**
 * load Dizkus specific support functions
 */
Loader::includeOnce('modules/Dizkus/common.php');

$forum_id =      FormUtil::getPassedValue('forum_id', null, 'GET');
$cat_id   =      FormUtil::getPassedValue('cat_id', null, 'GET');
$count    = (int)FormUtil::getPassedValue('count', 10, 'GET');
$feed     =      FormUtil::getPassedValue('feed', 'rss20', 'GET');
$user     =      FormUtil::getPassedValue('user', '', 'GET');

/**
 * get the short urls extensions
 */
// pnModURL already handles correct shorturls in Zikula 1.0

// get the module info
$baseurl = pnGetBaseURL();
$pnfinfo = pnModGetInfo(pnModGetIdFromName('Dizkus'));
$pnfname = $pnfinfo['displayname'];

/**
 * check for feed, if not set, use rss091 as default
 */
if(!empty($feed)) {
    // feed is set, check counter
    $count = (empty($count)) ? 10 : (int)$count;
} else {
    // set defaults
    $feed = 'rss20';
    $count = 10;
}

if(isset($forum_id) && !is_numeric($forum_id)) {
    die('backforum.php: invalid forum id "' . DataUtil::formatForDisplay($forum_id) . '"');
}
if(isset($cat_id) && !is_numeric($cat_id)) {
    die('backforum.php: invalid category id "' . DataUtil::formatForDisplay($cat_id) . '"');
}

/**
 * create pnRender object
 */
$pnr = pnRender::getInstance('Dizkus', false);

/**
 * check if template for feed exists
 */
$templatefile = 'dizkus_feed_' . DataUtil::formatForOS($feed) . '.html';
if(!$pnr->template_exists($templatefile)) {
    // silently stop working
    die('no template for ' . DataUtil::formatForDisplay($feed));
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
// form the url
$link = $baseurl.pnModURL('Dizkus', 'user', 'main');

$forumname = DataUtil::formatForDisplay($pnfname);
// default where clause => no where clause
$where = '';

/**
 * check for forum_id
 */
if(!empty($forum_id)) {
    $forum = pnModAPIFunc('Dizkus', 'user', 'readuserforums',
                          array('forum_id' => $forum_id));
    if(count($forum) == 0) {
        // not allowed to see forum
        pnShutDown();
    }
    $where = "AND t.forum_id = '" . (int)DataUtil::formatForStore($forum_id) . "' ";
    $link = $baseurl.pnModURL('Dizkus', 'user', 'viewforum', array('forum' => $forum_id));
    $forumname = $forum['forum_name'];
} elseif (!empty($cat_id)) {
    if(!SecurityUtil::checkPermission('Dizkus::', $cat_id . ':.*:', ACCESS_READ)) {
        pnShutDown();
    }
    $category = pnModAPIFunc('Dizkus', 'admin', 'readcategories',
                             array('cat_id' => $cat_id));
    if($category == false) {
        pnShutDown();
    }
    $where = "AND f.cat_id = '" . (int)DataUtil::formatForStore($cat_id) . "' ";
    $link = $baseurl.pnModURL('Dizkus', 'user', 'main', array('viewcat' => $cat_id));
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

pnModDBInfoLoad('Dizkus');
list($dbconn, $pntable) = dzkOpenDB();

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
               p.post_time,
               c.cat_id,
               c.cat_title
        FROM ".$pntable['dizkus_topics']." as t,
             ".$pntable['dizkus_forums']." as f,
             ".$pntable['dizkus_posts']." as p,
             ".$pntable['dizkus_categories']." as c
        WHERE t.forum_id = f.forum_id AND
              t.topic_last_post_id = p.post_id AND
              f.cat_id = c.cat_id
              $where
        ORDER BY p.post_time DESC
        LIMIT 100";
$result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
$result_postmax = $result->PO_RecordCount();

if ($result_postmax <= $count) {
    $count = $result_postmax;
}
$shown_results=0;
$posts_per_page  = pnModGetVar('Dizkus', 'posts_per_page');
$posts = array();

while ((list($topic_id, $topic_title, $topic_replies, $topic_last_post_id, $forum_id, $forum_name, $poster_id, $post_time, $cat_id, $cat_title) = $result->FetchRow())
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
        $post['time']               = $post_time;
        $post['unixtime']           = strtotime ($post['time']);
        $post['cat_id']             = $cat_id;
        $post['cat_title']          = $cat_title;
        $shown_results++;
        $start = ((ceil(($topic_replies + 1)  / $posts_per_page) - 1) * $posts_per_page);
        $post['post_url'] = $baseurl.pnModURL('Dizkus', 'user', 'viewtopic',
                                              array('topic' => $topic_id,
                                                    'start' => $start));

        $post['last_post_url'] = $post['post_url'] . "#pid" . $topic_last_post_id;
        array_push($posts, $post);
//        $result->MoveNext();
    }
}

dzkCloseDB($result);
$pnr->assign('posts', $posts);
$pnr->assign('now', time());

header("Content-Type: text/xml");
$pnr->display($templatefile);
