<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Api;

use UserUtil;
use ModUtil;
use DataUtil;
use DateUtil;
use Zikula\Module\DizkusModule\Manager\ForumManager;
use Zikula\Module\DizkusModule\Manager\ForumUserManager;

class BlockApi extends \Zikula_AbstractApi
{
    /**
     * gets the last $maxPosts postings of forum $forum_id
     *
     * @param mixed[] $params {
     *      @type int maxposts    number of posts to read, default = 5
     *      @type int forum_id    forum_id, if not set, all forums
     *      @type int user_id     -1 = last postings of current user, otherwise its treated as an user_id
     *      @type bool canread    if set, only the forums that we have read access to [** flag is no longer supported, this is the default settings for now **]
     *      @type bool favorites  if set, only the favorite forums
     *      @type bool show_m2f   if set show postings from mail2forum forums
     *      @type bool show_rss   if set show postings from rss2forum forums
     *                      }
     * 
     * @return array $lastposts
     */
    public function getLastPosts($params)
    {
        $maxPosts = (isset($params['maxposts']) && is_numeric($params['maxposts']) && $params['maxposts'] > 0) ? $params['maxposts'] : 5;
        // hard limit maxposts to 100 to be safe
        $maxPosts = ($maxPosts > 100) ? 100 : $maxPosts;

        $loggedIn = UserUtil::isLoggedIn();
        $uid = ($loggedIn == true) ? UserUtil::getVar('uid') : 1;

        $whereForum = array();
        if (!empty($params['forum_id']) && is_numeric($params['forum_id'])) {
            // get the forum and check permissions
            $managedForum = new ForumManager($params['forum_id']);
            if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $managedForum->get())) {

                return array();
            }
            $whereForum[] = $params['forum_id'];
        } elseif (!isset($params['favorites'])) {
            // no special forum_id set, get all forums the user is allowed to read
            // and build the where part of the sql statement
            $userForums = ModUtil::apiFunc($this->name, 'forum', 'getForumIdsByPermission');
            if (!is_array($userForums) || count($userForums) == 0) {
                // error or user is not allowed to read any forum at all

                return array();
            }
            $whereForum = $userForums;
        }

        $whereFavorites = array();
        // only do this if $favorites is set and $whereForum is empty
        // and the user is logged in.
        // (Anonymous doesn't have favorites)
        $managedForumUser = null;
        $postSortOrder = ModUtil::getVar($this->name, 'post_sort_order');
        if (isset($params['favorites']) && $params['favorites'] && empty($whereForum) && $loggedIn) {
            // get the favorites
            $managedForumUser = new ForumUserManager($uid);
            $favoriteForums = $managedForumUser->get()->getFavoriteForums();
            foreach ($favoriteForums as $forum) {
                if (ModUtil::apiFunc($this->name, 'Permission', 'canRead', $forum)) {
                    $whereFavorites[] = $forum->getForum()->getForum_id();
                }
            }
            $postSortOrder = $managedForumUser->getPostOrder();
        }

//    DISABLED UNTIL m2f and rss2f are reactivated
//    $whereSpecial = array(0);
//    // if show_m2f is set we show contents of m2f forums where.
//    // forum_pop3_active is set to 1
//    if (isset($params['show_m2f']) && $params['show_m2f'] == true) {
//        $whereSpecial[] = 1;
//    }
//    // if show_rss is set we show contents of rss2f forums where.
//    // forum_pop3_active is set to 2
//    if (isset($params['show_rss']) && $params['show_rss'] == true) {
//        $whereSpecial[] = 2;
//    }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select(array('t', 'f', 'p', 'fu'))
            ->from('Zikula\Module\DizkusModule\Entity\TopicEntity', 't')
            ->innerJoin('t.forum', 'f')
            ->innerJoin('t.last_post', 'p')
            ->innerJoin('p.poster', 'fu');
        if (!empty($whereForum)) {
            $qb->andWhere('t.forum IN (:forum)')
                ->setParameter('forum', $whereForum);
        }
        if (!empty($whereFavorites)) {
            $qb->andWhere('t.forum IN (:forum)')
                ->setParameter('forum', $whereFavorites);
        }
//    DISABLED UNTIL m2f and rss2f are reactivated
//    if (!empty($whereSpecial)) {
//        $qb->andWhere('f.forum_pop3_active IN (:special)')
//                ->setParameter('special', $whereSpecial);
//    }
        if (!empty($params['user_id'])) {
            $whereUserId = ($params['user_id'] == -1 && $loggedIn) ? $uid : $params['user_id'];
            $qb->andWhere('fu.uid = :id)')
                ->setParameter('id', $whereUserId);
        }
        $qb->orderBy('t.topic_time', 'DESC');
        $qb->setMaxResults($maxPosts);
        $topics = $qb->getQuery()->getResult();

        $lastPosts = array();
        if (!empty($topics)) {
            $posts_per_page = ModUtil::getVar($this->name, 'posts_per_page');
            /* @var $topic \Zikula\Module\DizkusModule\Entity\TopicEntity */
            foreach ($topics as $topic) {
                $lastPost = array();
                $lastPost['title'] = DataUtil::formatforDisplay($topic->getTitle());
                $lastPost['replyCount'] = DataUtil::formatforDisplay($topic->getReplyCount());
                $lastPost['name'] = DataUtil::formatforDisplay($topic->getForum()->getName());
                $lastPost['forum_id'] = DataUtil::formatforDisplay($topic->getForum()->getForum_id());
                $lastPost['cat_title'] = DataUtil::formatforDisplay($topic->getForum()->getParent()->getName());

                $start = 1;
                if ($postSortOrder == "ASC") {
                    $start = ((ceil(($topic->getReplyCount() + 1) / $posts_per_page) - 1) * $posts_per_page) + 1;
                }

                if ($topic->getPoster()->getUser_id() != 1) {
                    $coreUser = $topic->getLast_post()->getPoster()->getUser();
                    $user_name = $coreUser['uname'];
                    if (empty($user_name)) {
                        // user deleted from the db?
                        $user_name = ModUtil::getVar('Users', 'anonymous'); // @todo replace with "deleted user"?
                    }
                } else {
                    $user_name = ModUtil::getVar('Users', 'anonymous');
                }
                $lastPost['poster_name'] = DataUtil::formatForDisplay($user_name);
                // @todo see ticket #184 maybe this should be using UserApi::dzkVarPrepHTMLDisplay ????
                $lastPost['post_text'] = DataUtil::formatForDisplay(nl2br($topic->getLast_post()->getPost_text()));
                $lastPost['posted_time'] = DateUtil::formatDatetime($topic->getLast_post()->getPost_time(), 'datetimebrief');
                $lastPost['last_post_url'] = DataUtil::formatForDisplay(ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $topic->getTopic_id(),
                    'start' => $start)));
                $lastPost['last_post_url_anchor'] = $lastPost['last_post_url'] . "#pid" . $topic->getLast_post()->getPost_id();
                $lastPost['word'] = $topic->getReplyCount() > 1 ? $this->__('Last') : $this->__('New');

                array_push($lastPosts, $lastPost);
            }
        }

        return $lastPosts;
    }

    /**
     * gets the last $maxforums forums
     *
     * @param mixed[] $params {
     *      @type int maxforums    number of forums to read, default = 5
     *                      }
     * 
     * @return array $topForums
     */
    public function getTopForums($params)
    {
        $forumMax = (!empty($params['maxforums'])) ? $params['maxforums'] : 5;

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('f')
            ->from('Zikula\Module\DizkusModule\Entity\ForumEntity', 'f')
            ->orderBy('f.lvl', 'DESC')
            ->addOrderBy('f.postCount', 'DESC');
        $qb->setMaxResults($forumMax);
        $forums = $qb->getQuery()->getResult();

        $topForums = array();
        if (!empty($forums)) {
            foreach ($forums as $forum) {
                if (ModUtil::apiFunc($this->name, 'Permission', 'canRead', $forum)) {
                    $topforum = $forum->toArray();
                    $topforum['name'] = DataUtil::formatForDisplay($forum->getName());
                    $parent = $forum->getParent();
                    $parentName = isset($parent) ? $parent->getName() : $this->__('Root');
                    $topforum['cat_title'] = DataUtil::formatForDisplay($parentName);
                    array_push($topForums, $topforum);
                }
            }
        }

        return $topForums;
    }

    /**
     * gets the top $maxposters users depending on their post count
     *
     * @param mixed[] $params {
     *      @type int maxposters    number of users to read, default = 3
     *      @type int months        number months back to search, default = 6
     *                      }
     *
     * @return array $topPosters
     */
    public function getTopPosters($params)
    {
        $posterMax = (!empty($params['maxposters'])) ? $params['maxposters'] : 3;
        $months = (!empty($params['months'])) ? $params['months'] : 6;

        $qb = $this->entityManager->createQueryBuilder();
        $timePeriod = new \DateTime();
        $timePeriod->modify("-$months months");
        $qb->select('u')
            ->from('Zikula\Module\DizkusModule\Entity\ForumUserEntity', 'u')
            ->where('u.user_id > 1')
            ->andWhere('u.lastvisit > :timeperiod')
            ->setParameter('timeperiod', $timePeriod)
            ->orderBy('u.postCount', 'DESC');
        $qb->setMaxResults($posterMax);
        $forumUsers = $qb->getQuery()->getResult();

        $topPosters = array();
        if (!empty($forumUsers)) {
            foreach ($forumUsers as $forumUser) {
                $coreUser = $forumUser->getUser();
                $topPosters[] = array(
                    'user_name' => DataUtil::formatForDisplay($coreUser['uname']),
                    // for BC reasons
                    'postCount' => DataUtil::formatForDisplay($forumUser->getPostCount()),
                    'user_id' => DataUtil::formatForDisplay($forumUser->getUser_id()));
            }
        }

        return $topPosters;
    }
}
