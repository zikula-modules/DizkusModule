<?php
/**
 * forum backend (with permission check)
 * to be placed in the Zikula root
 * @author Andreas Krapohl, Frank Schummertz, Arjen Tebbenhof [short urls]
 * @copyright 2005 by Dizkus Team
 * @package Dizkus
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link https://github.com/zikula-modules/Dizkus
 */

/**
 * initialize the Zikula environment
 */
use Zikula_Request_Http as Request;

include 'lib/bootstrap.php';

$request = Request::createFromGlobals();
$core->getContainer()->set('request', $request);
$core->init();

$forum_id = $request->query->get('forum_id', null);
$cat_id = $request->query->get('cat_id', null);
$count = (int)$request->query->get('count', 10);
$feed = $request->query->get('feed', 'rss20');
$user = $request->query->get('user', null);

// get the module info
$dzkinfo = ModUtil::getInfo(ModUtil::getIdFromName('Dizkus'));
$dzkname = $dzkinfo['displayname'];

if (isset($forum_id) && !is_numeric($forum_id)) {
    die(DataUtil::formatForDisplay(__f('Error! In \'backforum.php\', an invalid forum ID %s was encountered.', $forum_id)));
}
if (isset($cat_id) && !is_numeric($cat_id)) {
    die(DataUtil::formatForDisplay(__f('Error! In \'backforum.php\', an invalid category ID %s was encountered.', $cat_id)));
}

/**
 * instanciate Zikula_View object
 */
$view = Zikula_View::getInstance('Dizkus', false);

/**
 * check if template for feed exists
 */
$templatefile = 'feed/' . DataUtil::formatForOS($feed) . '.tpl';
if (!$view->template_exists($templatefile)) {
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
$where = array();

/**
 * check for forum_id
 */
if (!empty($forum_id)) {
    $managedForum = new Dizkus_Manager_Forum($forum_id);
    if (!SecurityUtil::checkPermission('Dizkus::', ":$forum_id:", ACCESS_READ)) {
        System::shutDown();
    }
    $where = array('t.forum', (int)DataUtil::formatForStore($forum_id), '=');
    $link = ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id), null, null, true);
    $forumname = $managedForum->get()->getForum_name();
} elseif (!empty($cat_id)) {
    $managedForum = new Dizkus_Manager_Forum($cat_id);
    if (!SecurityUtil::checkPermission('Dizkus::', $cat_id . ':.*:', ACCESS_READ)) {
        System::shutDown();
    }
    $where = array('t.parent', (int)DataUtil::formatForStore($cat_id), '=');
    $link = ModUtil::url('Dizkus', 'user', 'viewforum', array('viewcat' => $cat_id), null, null, true);
    $forumname = $managedForum->get()->getParent()->getForum_name();
} elseif (isset($uid) && ($uid<>false)) {
    $where = array('p.poster', ' $uid', '=');
} else {
    $allowedforums = ModUtil::apiFunc('Dizkus', 'forum', 'getForumIdsByPermission');
    if (count($allowedforums) > 0) {
        $where = array('f.forum', DataUtil::formatForStore($allowedforums), 'IN');
    }
}

$view->assign('forum_name', $forumname);
$view->assign('forum_link', $link);
$view->assign('sitename', System::getVar('sitename'));
$view->assign('adminmail', System::getVar('adminmail'));
$view->assign('current_date', date(DATE_RSS));
$view->assign('current_language', ZLanguage::getLocale());

/* @var $_em \Doctrine\ORM\EntityManager */
$_em = ServiceUtil::getService('doctrine.entitymanager');
$qb = $_em->createQueryBuilder();
$qb->select('t, f, p, fu')
        ->from('Dizkus_Entity_Topic', 't')
        ->join('t.forum', 'f')
        ->join('t.last_post', 'p')
        ->join('p.poster', 'fu');
if (!empty($where)) {
    if ($where[2] == 'IN') {
        $qb->expr()->in($where[0], $where[1]);
    } else {
        $qb->where("$where[0] $where[2] :param")
                ->setParameter('param', $where[1]);
    }
}
$qb->orderBy('t.topic_time', 'DESC')
        ->setMaxResults($count);
$topics = $qb->getQuery()->getResult();

$posts_per_page  = ModUtil::getVar('Dizkus', 'posts_per_page');
$posts = array();
$i = 0;
foreach ($topics as $topic)
{
    /* @var $topic Dizkus_Entity_Topic */
    $posts[$i]['topic_title'] = $topic->getTopic_title();
    $posts[$i]['cat_title'] = $topic->getForum()->getParent()->getForum_name();
    $posts[$i]['forum_name'] = $topic->getForum()->getForum_name();
    $posts[$i]['time'] = $topic->getTopic_time();
    $posts[$i]['unixtime'] = $topic->getTopic_time()->format('U');
    $start = (int)((ceil(($topic->getTopic_replies() + 1)  / $posts_per_page) - 1) * $posts_per_page);
    $posts[$i]['post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                 array('topic' => $topic->getTopic_id(),
                                       'start' => $start), 
                                 null, null, true);
    $posts[$i]['last_post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                      array('topic' => $topic->getTopic_id(),
                                            'start' => $start), 
                                      null, "pid" . $topic->getLast_post()->getPost_id(), true);
    $posts[$i]['rsstime'] = $topic->getTopic_time()->format(DATE_RSS);
    $i++;
}

$view->assign('posts', $posts);
$view->assign('dizkusinfo', $dzkinfo);

header("Content-Type: text/xml");
$view->display($templatefile);
