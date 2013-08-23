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
 * readlastposts
 * reads the last $maxposts postings of forum $forum_id and assign them in a
 * variable lastposts and the number of them in lastpostcount
 *
 * @params maxposts (int) number of posts to read, default = 5
 * @params forum_id (int) forum_id, if not set, all forums
 * @params user_id  (int) -1 = last postings of current user, otherwise its treated as an user_id
 * @params canread (bool) if set, only the forums that we have read access to [** flag is no longer supported, this is the default settings for now **]
 * @params favorites (bool) if set, only the favorite forums
 * @params show_m2f (bool) if set show postings from mail2forum forums
 * @params show_rss (bool) if set show postings from rss2forum forums
 *
 */
function smarty_function_readlastposts($params, Zikula_View $view)
{
    $params = $params['params'];
    $maxposts = (isset($params['maxposts']) && is_numeric($params['maxposts']) && $params['maxposts'] > 0) ? $params['maxposts'] : 5;
    // hard limit maxposts to 100 to be safe
    $maxposts = ($maxposts > 100) ? 100 : $maxposts;

    $loggedIn = UserUtil::isLoggedIn();
    $uid = ($loggedIn == true) ? UserUtil::getVar('uid') : 1;

    $whereforum = array();
    if (!empty($params['forum_id']) && is_numeric($params['forum_id'])) {
        // get the forum and check permissions
        $managedForum = new Dizkus_Manager_Forum($params['forum_id']);
        if (!ModUtil::apiFunc('Dizkus', 'Permission', 'canRead', $managedForum->get())) {
            $view->assign('lastpostcount', 0);
            $view->assign('lastposts', array());
            return;
        }
        $whereforum[] = $params['forum_id'];
    } else if (!isset($params['favorites'])) {
        // no special forum_id set, get all forums the user is allowed to read
        // and build the where part of the sql statement
        $userforums = ModUtil::apiFunc('Dizkus', 'forum', 'getForumIdsByPermission');
        if (!is_array($userforums) || count($userforums) == 0) {
            // error or user is not allowed to read any forum at all
            $view->assign('lastpostcount', 0);
            $view->assign('lastposts', array());
            return;
        }
        $whereforum = $userforums;
    }

    $wherefavorites = array();
    // only do this if $favorites is set and $whereforum is empty
    // and the user is logged in.
    // (Anonymous doesn't have favorites)
    $managedForumUser = null;
    $post_sort_order = ModUtil::getVar('Dizkus', 'post_sort_order');
    if (isset($params['favorites']) && $params['favorites'] && empty($whereforum) && $loggedIn) {
        // get the favorites
        $managedForumUser = new Dizkus_Manager_ForumUser($uid);
        $favoriteForums = $managedForumUser->get()->getFavoriteForums();
        foreach ($favoriteForums as $forum) {
            if (ModUtil::apiFunc('Dizkus', 'Permission', 'canRead', $forum)) {
                $wherefavorites[] = $forum->getForum()->getForum_id();
            }
        }
        $post_sort_order = $managedForumUser->getPostOrder();
    }

//    DISABLED UNTIL m2f and rss2f are reactivated
//    $wherespecial = array(0);
//    // if show_m2f is set we show contents of m2f forums where.
//    // forum_pop3_active is set to 1
//    if (isset($params['show_m2f']) && $params['show_m2f'] == true) {
//        $wherespecial[] = 1;
//    }
//    // if show_rss is set we show contents of rss2f forums where.
//    // forum_pop3_active is set to 2
//    if (isset($params['show_rss']) && $params['show_rss'] == true) {
//        $wherespecial[] = 2;
//    }

    /** @var $em Doctrine\ORM\EntityManager */
    $em = $view->getContainer()->get('doctrine.entitymanager');
    $qb = $em->createQueryBuilder();
    $qb->select(array('t', 'f', 'p', 'fu'))
            ->from('Dizkus_Entity_Topic', 't')
            ->innerJoin('t.forum', 'f')
            ->innerJoin('t.last_post', 'p')
            ->innerJoin('p.poster', 'fu');
    if (!empty($whereforum)) {
        $qb->andWhere('t.forum IN (:forum)')
                ->setParameter('forum', $whereforum);
    }
    if (!empty($wherefavorites)) {
        $qb->andWhere('t.forum IN (:forum)')
                ->setParameter('forum', $wherefavorites);
    }
//    DISABLED UNTIL m2f and rss2f are reactivated
//    if (!empty($wherespecial)) {
//        $qb->andWhere('f.forum_pop3_active IN (:special)')
//                ->setParameter('special', $wherespecial);
//    }
    if (!empty($params['user_id'])) {
        $whereUserId = ($params['user_id'] == -1 && $loggedIn) ? $uid : $params['user_id'];
        $qb->andWhere('fu.uid = :id)')
                ->setParameter('id', $whereUserId);
    }
    $qb->orderBy('t.topic_time', 'DESC');
    $qb->setMaxResults($maxposts);
    $topics = $qb->getQuery()->getResult();

    $lastposts = array();
    if (!empty($topics)) {
        $posts_per_page = ModUtil::getVar('Dizkus', 'posts_per_page');
        /* @var $topic Dizkus_Entity_Topic */
        foreach ($topics as $topic) {
            $lastpost = array();
            $lastpost['title'] = DataUtil::formatforDisplay($topic->getTitle());
            $lastpost['replyCount'] = DataUtil::formatforDisplay($topic->getReplyCount());
            $lastpost['name'] = DataUtil::formatforDisplay($topic->getForum()->getName());
            $lastpost['forum_id'] = DataUtil::formatforDisplay($topic->getForum()->getForum_id());
            $lastpost['cat_title'] = DataUtil::formatforDisplay($topic->getForum()->getParent()->getName());

            $start = 1;
            if ($post_sort_order == "ASC") {
                $start = ((ceil(($topic->getReplyCount() + 1) / $posts_per_page) - 1) * $posts_per_page) + 1;
            }

            if ($topic->getPoster()->getUser_id() != 1) {
                $user_name = $topic->getPoster()->getUser()->getUname();
                if ($user_name == "") {
                    // user deleted from the db?
                    $user_name = ModUtil::getVar('Users', 'anonymous');
                }
            } else {
                $user_name = ModUtil::getVar('Users', 'anonymous');
            }
            $lastpost['poster_name'] = DataUtil::formatForDisplay($user_name);
            // @todo see ticket #184 maybe this should be using UserApi::dzkVarPrepHTMLDisplay ????
            $lastpost['post_text'] = DataUtil::formatForDisplay(nl2br($topic->getLast_post()->getPost_text()));
            $lastpost['posted_time'] = DateUtil::formatDatetime($topic->getTopic_time(), 'datetimebrief');
            $lastpost['last_post_url'] = DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic->getTopic_id(),
                                'start' => $start)));
            $lastpost['last_post_url_anchor'] = $lastpost['last_post_url'] . "#pid" . $topic->getLast_post()->getPost_id();

            array_push($lastposts, $lastpost);
        }
    }

    $view->assign('lastpostcount', count($lastposts));
    $view->assign('lastposts', $lastposts);
    return;
}
