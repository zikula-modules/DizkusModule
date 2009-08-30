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

$dom = ZLanguage::getModuleDomain('Dizkus');

/**
 * get the short urls extensions
 */
// pnModURL already handles correct shorturls in Zikula 1.0

// get the module info
$dzkinfo = pnModGetInfo(pnModGetIdFromName('Dizkus'));
$dzkname = $dzkinfo['displayname'];

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
    die(DataUtil::formatForDisplay(__f('backforum.php: invalid forum id %s', $forum_id, $dom)));
}
if(isset($cat_id) && !is_numeric($cat_id)) {
    die(DataUtil::formatForDisplay(__f('backforum.php: invalid category id %s', $cat_id, $dom)));
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
    die(DataUtil::formatForDisplay(__f('no template found for feed type %s', $feed, $dom)));
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
$link = pnModURL('Dizkus', 'user', 'main', null, null, null, true);

$forumname = DataUtil::formatForDisplay($dzkname);
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
    $where = 'AND t.forum_id = ' . (int)DataUtil::formatForStore($forum_id) . ' ';
    $link = pnModURL('Dizkus', 'user', 'viewforum', array('forum' => $forum_id), null, null, true);
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
    $where = 'AND f.cat_id = ' . (int)DataUtil::formatForStore($cat_id) . ' ';
    $link = pnModURL('Dizkus', 'user', 'main', array('viewcat' => $cat_id), null, null, true);
    $forumname = $category['cat_title'];

} elseif (isset($uid) && ($uid<>false)) {
    $where = 'AND p.poster_id=' . $uid . ' ';
} else {
    $userforums = pnModAPIFunc('Dizkus', 'user', 'readuserforums');
    // now create a very simple array of forum_ids only. we do not need
    // all the other stuff in the $userforums array entries
    $allowedforums = array_map('_get_forum_ids', $userforums);
    $where = ' AND f.forum_id IN (' . DataUtil::formatForStore(implode(',', $allowedforums)) . ') ';
}    

$pnr->assign('forum_name', $forumname);
$pnr->assign('forum_link', $link);
$pnr->assign('sitename', pnConfigGetVar('sitename'));
$pnr->assign('adminmail', pnConfigGetVar('adminmail'));

/**
 * get database information
 */

pnModDBInfoLoad('Dizkus');

$pntable = pnDBGetTables();

/**
 * SQL statement to fetch last 10 topics
 */
$topicscols = DBUtil::_getAllColumnsQualified('dizkus_topics', 't');
$sql = 'SELECT '.$topicscols.',
                 f.forum_name,
                 p.poster_id,
                 p.post_time,
                 c.cat_id,
                 c.cat_title
        FROM '.$pntable['dizkus_topics'].' as t,
             '.$pntable['dizkus_forums'].' as f,
             '.$pntable['dizkus_posts'].' as p,
             '.$pntable['dizkus_categories'].' as c
        WHERE t.forum_id = f.forum_id AND
              t.topic_last_post_id = p.post_id AND
              f.cat_id = c.cat_id
             '.$where.'
        ORDER BY p.post_time DESC
        LIMIT ' . DataUtil::formatForStore($count);

$posts_per_page  = pnModGetVar('Dizkus', 'posts_per_page');

$res = DBUtil::executeSQL($sql);

$colarray = DBUtil::getColumnsArray ('dizkus_topics');
$colarray[] = 'forum_name';
$colarray[] = 'poster_id';
$colarray[] = 'post_time';
$colarray[] = 'cat_id';
$colarray[] = 'cat_title';

$posts = DBUtil::marshallObjects($res, $colarray);

$keys = array_keys($posts);
foreach ($keys as $key) {
    $posts[$key]['time'] = $posts[$key]['post_time'];
    $posts[$key]['unixtime'] = strtotime ($posts[$key]['post_time']);
    $start = (int)((ceil(($posts[$key]['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page);
    $posts[$key]['post_url'] = pnModURL('Dizkus', 'user', 'viewtopic',
                                 array('topic' => $posts[$key]['topic_id'],
                                       'start' => $start), 
                                 null, null, true);
    $posts[$key]['last_post_url'] = pnModURL('Dizkus', 'user', 'viewtopic',
                                      array('topic' => $posts[$key]['topic_id'],
                                            'start' => $start), 
                                      null, "pid" . $posts[$key]['topic_last_post_id'], true);
    $posts[$key]['rsstime'] = strftime('%a, %d %b %Y %H:%M:%S %Z', $posts[$key]['post_unixtime']);
}

$pnr->assign('posts', $posts);
$pnr->assign('now', time());
$pnr->assign('lastbuilddate', strftime('%a, %d %b %Y %H:%M:%S %Z', time()));
$pnr->assign('dizkusinfo', $dzkinfo);

header("Content-Type: text/xml");
$pnr->display($templatefile);
