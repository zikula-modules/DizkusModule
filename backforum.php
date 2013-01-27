<?php
/**
 * forum backend (with permission check)
 * to be placed in the Zikula root
 * @version $Id$
 * @author Andreas Krapohl, Frank Schummertz, Arjen Tebbenhof [short urls]
 * @copyright 2005 by Dizkus Team
 * @package Dizkus
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link https://github.com/zikula-modules/Dizkus
 */

/**
 * initialize the Zikula environment
 */
include 'lib/bootstrap.php';
$core->init();

/**
 * load Dizkus specific support functions
 */
include_once 'modules/Dizkus/bootstrap.php';

$forum_id =      FormUtil::getPassedValue('forum_id', null, 'GET');
$cat_id   =      FormUtil::getPassedValue('cat_id', null, 'GET');
$count    = (int)FormUtil::getPassedValue('count', 10, 'GET');
$feed     =      FormUtil::getPassedValue('feed', 'rss20', 'GET');
$user     =      FormUtil::getPassedValue('user', '', 'GET');

// get the module info
$dzkinfo = ModUtil::getInfo(ModUtil::getIdFromName('Dizkus'));
$dzkname = $dzkinfo['displayname'];

/**
 * check for feed, if not set, use rss20 as default
 */
if (!empty($feed)) {
    // feed is set, check counter
    $count = (empty($count)) ? 10 : (int)$count;
} else {
    // set defaults
    $feed = 'rss20';
    $count = 10;
}

if (isset($forum_id) && !is_numeric($forum_id)) {
    die(DataUtil::formatForDisplay(__f('Error! In \'backforum.php\', an invalid forum ID %s was encountered.', $forum_id)));
}
if (isset($cat_id) && !is_numeric($cat_id)) {
    die(DataUtil::formatForDisplay(__f('Error! In \'backforum.php\', an invalid category ID %s was encountered.', $cat_id)));
}

/**
 * create Renderer object
 */
$render = Zikula_View::getInstance('Dizkus', false);

/**
 * check if template for feed exists
 */
$templatefile = 'feed/' . DataUtil::formatForOS($feed) . '.tpl';
if (!$render->template_exists($templatefile)) {
    // silently stop working
    die(DataUtil::formatForDisplay(__f('Error! Could not find a template for an %s-type feed.', $feed)));
}

/**
 * get user id
 */
if (!empty($user)) {
    $uid = UserUtil::getIDFromName($user);
}

/**
 * set some defaults
 */
// form the url
$link = ModUtil::url('Dizkus', 'user', 'main', array(), null, null, true);

$forumname = DataUtil::formatForDisplay($dzkname);
// default where clause => no where clause
$where = '';

/**
 * check for forum_id
 */
if (!empty($forum_id)) {
    $forum = ModUtil::apiFunc('Dizkus', 'user', 'readuserforums', array('forum_id' => $forum_id));
    if (count($forum) == 0) {
        // not allowed to see forum
        System::shutDown();
    }
    $where = 'AND t.forum_id = ' . (int)DataUtil::formatForStore($forum_id) . ' ';
    $link = ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id), null, null, true);
    $forumname = $forum['forum_name'];

} elseif (!empty($cat_id)) {
    if (!SecurityUtil::checkPermission('Dizkus::', $cat_id . ':.*:', ACCESS_READ)) {
        System::shutDown();
    }
    $category = ModUtil::apiFunc('Dizkus', 'admin', 'readcategories',
                             array('cat_id' => $cat_id));
    if ($category == false) {
        System::shutDown();
    }
    $where = 'AND f.cat_id = ' . (int)DataUtil::formatForStore($cat_id) . ' ';
    $link = ModUtil::url('Dizkus', 'user', 'main', array('viewcat' => $cat_id), null, null, true);
    $forumname = $category['cat_title'];

} elseif (isset($uid) && ($uid<>false)) {
    $where = 'AND p.poster_id=' . $uid . ' ';
} else {
    $userforums = ModUtil::apiFunc('Dizkus', 'user', 'readuserforums');
    // now create a very simple array of forum_ids only. we do not need
    // all the other stuff in the $userforums array entries
    $allowedforums = array_map('_get_forum_ids', $userforums);
    if (count($allowedforums) > 0) {
        $where = ' AND f.forum_id IN (' . DataUtil::formatForStore(implode(',', $allowedforums)) . ') ';
    }
}    

$render->assign('forum_name', $forumname);
$render->assign('forum_link', $link);
$render->assign('sitename', System::getVar('sitename'));
$render->assign('adminmail', System::getVar('adminmail'));
$render->assign('current_date', date(DATE_RSS));
$render->assign('current_language', ZLanguage::getLocale());

/**
 * get database information
 */
ModUtil::dbInfoLoad('Dizkus');
$ztable = DBUtil::getTables();

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
        FROM '.$ztable['dizkus_topics'].' as t,
             '.$ztable['dizkus_forums'].' as f,
             '.$ztable['dizkus_posts'].' as p,
             '.$ztable['dizkus_categories'].' as c
        WHERE t.forum_id = f.forum_id AND
              t.topic_last_post_id = p.post_id AND
              f.cat_id = c.cat_id
             '.$where.'
        ORDER BY p.post_time DESC
        LIMIT ' . DataUtil::formatForStore($count);

$posts_per_page  = ModUtil::getVar('Dizkus', 'posts_per_page');

$res = DBUtil::executeSQL($sql);

$colarray = DBUtil::getColumnsArray ('dizkus_topics');
$colarray[] = 'forum_name';
$colarray[] = 'poster_id';
$colarray[] = 'post_time';
$colarray[] = 'cat_id';
$colarray[] = 'cat_title';

$posts = DBUtil::marshallObjects($res, $colarray);

$keys = array_keys($posts);
foreach ($keys as $key)
{
    $posts[$key]['time'] = $posts[$key]['post_time'];
    $posts[$key]['unixtime'] = strtotime ($posts[$key]['post_time']);
    $start = (int)((ceil(($posts[$key]['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page);

    $posts[$key]['post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                 array('topic' => $posts[$key]['topic_id'],
                                       'start' => $start), 
                                 null, null, true);

    $posts[$key]['last_post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                      array('topic' => $posts[$key]['topic_id'],
                                            'start' => $start), 
                                      null, "pid" . $posts[$key]['topic_last_post_id'], true);

    //$posts[$key]['rsstime'] = strftime('%a, %d %b %Y %H:%M:%S %Z', $posts[$key]['post_unixtime']);
    $posts[$key]['rsstime'] = date(DATE_RSS, $posts[$key]['unixtime']);
}

$render->assign('posts', $posts);
$render->assign('dizkusinfo', $dzkinfo);

header("Content-Type: text/xml");
$render->display($templatefile);
