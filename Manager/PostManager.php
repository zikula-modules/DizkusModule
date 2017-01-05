<?php

/**
 * Copyright Dizkus Team 2012
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Dizkus
 * @see https://github.com/zikula-modules/Dizkus
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\DizkusModule\Manager;


use DataUtil;
use DateUtil;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\ExtensionsModule\Api\VariableApi;

use Zikula\DizkusModule\Entity\ForumUserEntity;
use Zikula\DizkusModule\Entity\PostEntity;
use Zikula\DizkusModule\Helper\SynchronizationHelper;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Manager\TopicManager;
use Zikula\DizkusModule\Security\Permission;

class PostManager
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;
    
    /**
     * @var RequestStack
     */    
    private $requestStack;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CurrentUserApi
     */    
    private $userApi;

    /**
     * @var Permission
     */    
    private $permission;

    /**
     * @var VariableApi
     */
    private $variableApi;
    
    /**
     * @var VariableApi
     */
    private $forumManagerService;    
    
    /**
     * @var VariableApi
     */
    private $topicManagerService;
    
    /**
     * @var synchronizationHelper
     */
    private $synchronizationHelper;
    
    /**
     * managed post
     * @var PostEntity
     */
    private $_post;

    /**
     * Post topic
     * @var TopicManager
     */
    private $_topic;
    
    public function __construct(
            TranslatorInterface $translator,
            RouterInterface $router,
            RequestStack $requestStack,
            EntityManager $entityManager,
            CurrentUserApi $userApi,
            Permission $permission,
            VariableApi $variableApi,
            ForumManager $forumManagerService,
            TopicManager $topicManagerService,
            SynchronizationHelper $synchronizationHelper
         ) {
        
        $this->name = 'ZikulaDizkusModule';
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->userApi = $userApi;
        $this->permission = $permission;
        $this->variableApi = $variableApi;
        $this->forumManagerService = $forumManagerService;
        $this->topicManagerService = $topicManagerService;
        $this->synchronizationHelper = $synchronizationHelper;        
    }

    /**
     * Start managing
     */
    public function getManager($id = null)
    {
        if ($id > 0) {
            $this->_post = $this->entityManager->find('Zikula\DizkusModule\Entity\PostEntity', $id);
            $this->_topic = $this->topicManagerService->getManager(null, $this->_post->getTopic()); //new TopicManager(null, $this->_post->getTopic());
        } else {
            $this->_post = new PostEntity();
        }
        
        return $this;
    }
    
    /**
     * return page as array
     *
     * @return mixed array or false
     */
    public function toArray($title = true)
    {
        if (!$this->_post) {
            return false;
        }
        $output = $this->_post->toArray();
        $output['topic_subject'] = $this->_topic->getTitle();
        $output = array_merge($output, $this->_topic->getPermissions());

        return $output;
    }

    public function getId()
    {
        return $this->_post->getPost_id();
    }

    public function getTopicId()
    {
        return $this->_topic->getId();
    }

    /**
     * get the Post entity
     *
     * @return PostEntity
     */
    public function get()
    {
        return $this->_post;
    }

    /**
     * update the post
     *
     * @return boolean
     */
    public function update($data = null)
    {
        if (!is_null($data)) {
            $this->_post->merge($data);
        }
        // update topic
        $this->entityManager->persist($this->_post);
        $this->entityManager->flush();
    }

    /**
     * create a post from provided data but do not yet persist
     */
    public function create($data = null)
    {
        if (!is_null($data)) {
            $this->_topic = $this->topicManagerService->getManager($data['topic_id']); //new TopicManager($data['topic_id']);
            $this->_post->setTopic($this->_topic->get());
            unset($data['topic_id']);
            $this->_post->merge($data);
        } else {
            throw new \InvalidArgumentException('Cannot create Post, no data provided.');
        }
        // increment poster posts
//        $uid = UserUtil::getVar('uid');
        // assign anonymous creations to the admin
//        $uid = !$uid ? ModUtil::getVar($this->name, 'defaultPoster', 2) : $uid;
        
        $uid = $this->userApi->isLoggedIn() ? $this->request->getSession()->get('uid') : $this->variableApi->get($this->name, 'defaultPoster', 2) ;
        
        $forumUser = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumUserEntity', $uid);
        if (!$forumUser) {
            $forumUser = new ForumUserEntity($uid);
        }
        $this->_post->setPoster($forumUser);
    }

    /**
     * persist the post and update related entities to reflect new post
     */
    public function persist()
    {
        $this->_post->getPoster()->incrementPostCount();
        // increment topic posts
        $this->_topic->setLastPost($this->_post);
        $this->_topic->incrementRepliesCount();
        // update topic time to last post time
        $this->_topic->get()->setTopic_time($this->_post->getPost_time());
        // increment forum posts
        $managedForum = $this->forumManagerService->getManager(null, $this->_topic->get()->getForum()); //new ForumManager(null, $this->_topic->get()->getForum());
        $managedForum->incrementPostCount();
        $managedForum->setLastPost($this->_post);
        $this->entityManager->persist($this->_post);
        $this->entityManager->flush();
    }

    /**
     * delete a post
     *
     * @return boolean
     */
    public function delete()
    {
        // preserve post_id
        $id = $this->_post->getPost_id();
        $topicLastPostId = $this->_topic->get()->getLast_post()->getPost_id();
        $managedForum = $this->forumManagerService->getManager($this->_topic->getForumId()); //new ForumManager($this->_topic->getForumId());
        $forumLastPostId = $managedForum->get()->getLast_post()->getPost_id();
        // decrement user posts
        $this->_post->getPoster()->decrementPostCount();
        // remove the post
        $this->entityManager->getRepository('Zikula\DizkusModule\Entity\PostEntity')->manualDelete($id);
        // decrement forum post count
        $managedForum->decrementPostCount();
        // decrement replies count
        $this->_topic->decrementRepliesCount();
        $this->entityManager->flush();
        // resetLastPost in topic and forum if required
        if ($id == $topicLastPostId) {
            $this->_topic->resetLastPost(true);
        }
        if ($id == $forumLastPostId) {
            $this->synchronizationHelper->forumLastPost($managedForum->get(), true);            
//          ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', array('forum' => $managedForum->get(), 'flush' => true));
        }
    }

    /**
     * get_latest_posts
     *
     * @param $args['selorder'] int 1-6, see below
     * @param $args['nohours'] int posting within these hours
     * @param $args['unanswered'] int 0 or 1(= postings with no answers)
     * @param $args['last_visit_unix'] string the users last visit data as unix timestamp
     * @param $args['limit'] int limits the numbers hits read (per list), defaults and limited to 250
     * @return array (postings, mail2forumpostings, rsspostings, text_to_display)
     */
    public function getLatest($args)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'l')
            ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
            ->leftJoin('t.last_post', 'l')
            ->orderBy('l.post_time', 'DESC');
        // sql part per selected time frame
        switch ($args['selorder']) {
            case '2':
                // today
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', new DateTime('today'));
                $text = $this->translator->__('Today');
                break;
            case '3':
                // since yesterday
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', new DateTime('yesterday'));
                $text = $this->translator->__('Since yesterday');
                break;
            case '4':
                // lastweek
                $qb->where('l.post_time > :wheretime')->setParameter('wheretime', new DateTime('-1 week'));
                $text = $this->translator->__('In the last week');
                break;
            default:
                // default is case '1'
                // no break - process as case '1' ...
            case '1':
                // last 24 hours
                $args['nohours'] = 24;
                // no break - process as case 5 ...
            case '5':
                // last x hours
                // maximum two weeks back = 2 * 24 * 7 hours
                if (isset($args['nohours']) && $args['nohours'] > 336) {
                    $args['nohours'] = 336;
                }
                $qb->where('l.post_time > :wheretime')
                    ->setParameter('wheretime', new DateTime('-' . $args['nohours'] . ' hours'));
                $text = DataUtil::formatForDisplay($this->translator->__f('In the last %s hours', $args['nohours']));
                break;
            case '6':
                // last visit
                $lastVisit = DateTime::createFromFormat('U', $args['last_visit_unix']);
                $qb->where('l.post_time > :wheretime')
                    ->setParameter('wheretime', $lastVisit);
                $text = DataUtil::formatForDisplay($this->translator->__f('Since your last visit on %s', DateUtil::formatDatetime($args['last_visit_unix'], 'datetimebrief')));
                break;
            case 'unanswered':
                $qb->where('t.replyCount = 0');
                $text = $this->translator->__('Unanswered');
                break;
            case 'unsolved':
                $qb->where('t.solved = :status')
                    ->setParameter('status', -1);
                $text = $this->translator->__('Unsolved');
                break;
        }
        $qb->setFirstResult(0)->setMaxResults(10);
        $topics = new Paginator($qb);
        $pager = [
            'numitems' => count($topics),
            'itemsperpage' => 10];

        return [$topics, $text, $pager];
    }

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

        $loggedIn = $this->userApi->isLoggedIn();
        $uid = $loggedIn ? $this->request->getSession()->get('uid') : 1;

        $whereForum = [];
        if (!empty($params['forum_id']) && is_numeric($params['forum_id'])) {
            // get the forum and check permissions
            $managedForum = $this->forumManagerService->getManager($params['forum_id']); //new ForumManager($params['forum_id']);
            if (!$this->permission->canRead($managedForum->get())) {
                return [];
            }
            $whereForum[] = $params['forum_id'];
        } elseif (!isset($params['favorites'])) {
            // no special forum_id set, get all forums the user is allowed to read
            // and build the where part of the sql statement
            $userForums = $this->permission->getForumIdsByPermission(['userId' => $uid]);
            if (!is_array($userForums) || count($userForums) == 0) {
                // error or user is not allowed to read any forum at all
                return [];
            }
            $whereForum = $userForums;
        }

        $whereFavorites = [];
        // only do this if $favorites is set and $whereForum is empty
        // and the user is logged in.
        // (Anonymous doesn't have favorites)
        $managedForumUser = null;
        $postSortOrder = $this->variableApi->get($this->name, 'post_sort_order');
        if (isset($params['favorites']) && $params['favorites'] && empty($whereForum) && $loggedIn) {
            // get the favorites
            $managedForumUser = new ForumUserManager($uid);
            $favoriteForums = $managedForumUser->get()->getFavoriteForums();
            foreach ($favoriteForums as $forum) {
                if ($this->permission->canRead($forum)) {
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
        $qb->select(['t', 'f', 'p', 'fu'])
            ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
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

        $lastPosts = [];
        if (!empty($topics)) {
            $posts_per_page = $this->variableApi->get($this->name, 'posts_per_page');
            /* @var $topic \Zikula\Module\DizkusModule\Entity\TopicEntity */
            foreach ($topics as $topic) {
                $lastPost = [];
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
                        $user_name = $this->variableApi->get('ZikulaUsersModule', 'anonymous'); // @todo replace with "deleted user"?
                    }
                } else {
                    $user_name = $this->variableApi->get('ZikulaUsersModule', 'anonymous');
                }
                $lastPost['poster_name'] = DataUtil::formatForDisplay($user_name);
                // @todo see ticket #184 maybe this should be using UserApi::dzkVarPrepHTMLDisplay ????
                $lastPost['post_text'] = DataUtil::formatForDisplay(nl2br($topic->getLast_post()->getPost_text()));
                $lastPost['posted_time'] = DateUtil::formatDatetime($topic->getLast_post()->getPost_time(), 'datetimebrief');
                $lastPost['last_post_url'] = DataUtil::formatForDisplay($this->router->generate('zikuladizkusmodule_topic_viewtopic', [
                    'topic' => $topic->getTopic_id(),
                    'start' => $start]));
                $lastPost['last_post_url_anchor'] = $lastPost['last_post_url'] . "#pid" . $topic->getLast_post()->getPost_id();
                $lastPost['word'] = $topic->getReplyCount() >= 1 ? $this->translator->__('Last') : $this->translator->__('New');

                array_push($lastPosts, $lastPost);
            }
        }

        return $lastPosts;
    }
 
    /**
     * retrieve all my posts or topics
     *
     * @param $args
     *  string 'action' = 'posts'|'topics'
     *  integer 'offset' pager offset
     *
     * @return array
     */
    public function search($args)
    {
        $args['action'] = !empty($args['action']) && in_array($args['action'], ['posts', 'topics']) ? $args['action'] : 'posts';
        $args['offset'] = !empty($args['offset']) ? $args['offset'] : 0;
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'l')
            ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
            ->leftJoin('t.last_post', 'l')
            ->leftJoin('t.posts', 'p')
            ->orderBy('l.post_time', 'DESC');
        if ($args['action'] == 'topics') {
            $qb->where('t.poster = :uid');
        } else {
            $qb->where('p.poster = :uid');
        }
        
        $uid = $this->userApi->isLoggedIn() ? $this->request->getSession()->get('uid') : 1 ;        
        
        $qb->setParameter('uid', $uid);
        $perPageVar = $args['action'] . '_per_page';
        $limit = $this->getVar($perPageVar);
        $qb->setFirstResult($args['offset'])
            ->setMaxResults($limit);
        $topics = new Paginator($qb);
        $pager = [
            'numitems' => $topics->count(),
            'itemsperpage' => $limit];

        return [$topics, $pager];
    }

    /**
     * movepost
     *
     * @param $args['post_id']
     * @param $args['old_topic_id']
     * @param $args['to_topic_id']
     *
     * @return int count of posts in destination topic
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function move($args)
    {
        $old_topic_id = isset($args['old_topic_id']) ? $args['old_topic_id'] : null;
        $to_topic_id = isset($args['to_topic_id']) ? $args['to_topic_id'] : null;
        $post_id = isset($args['post_id']) ? $args['post_id'] : null;
        if (!isset($old_topic_id) || !isset($to_topic_id) || !isset($post_id)) {
            throw new \InvalidArgumentException();
        }
        $managedOriginTopic = $this->topicManagerService->getManager($old_topic_id);  //new TopicManager($old_topic_id);
        $managedDestinationTopic = $this->topicManagerService->getManager($to_topic_id); //new TopicManager($to_topic_id);
        $managedPost = $this->getManager($post_id); //new PostManager();
        $managedOriginTopic->get()->getPosts()->removeElement($managedPost->get());
        $managedPost->get()->setTopic($managedDestinationTopic->get());
        $managedDestinationTopic->get()->addPost($managedPost->get());
        $managedOriginTopic->decrementRepliesCount();
        $managedDestinationTopic->incrementRepliesCount();
        $managedPost->get()->updatePost_time();
        $this->entityManager->flush();
        
        $this->synchronizationHelper->topicLastPost($managedOriginTopic->get(), false); 
        $this->synchronizationHelper->topicLastPost($managedDestinationTopic->get(), true);        
        
//        ModUtil::apiFunc($this->name, 'sync', 'topicLastPost', [
//            'topic' => $managedOriginTopic->get(),
//            'flush' => false]);
//        ModUtil::apiFunc($this->name, 'sync', 'topicLastPost', [
//            'topic' => $managedDestinationTopic->get(),
//            'flush' => true]);

        return $managedDestinationTopic->getPostCount();
    }

    /**
     * Checks if the given message isn't too long.
     *
     * @param $message The message to check.
     *
     * @return bool False if the message is to long, else true.
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function checkMessageLength($message)
    {
        if (!isset($message)) {
            throw new \InvalidArgumentException();
        }
        if (strlen($message) + 8 > 65535) {
            return false;
        }

        return true;
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
            ->from('Zikula\DizkusModule\Entity\ForumUserEntity', 'u')
            ->where('u.user_id > 1')
            ->andWhere('u.lastvisit > :timeperiod')
            ->setParameter('timeperiod', $timePeriod)
            ->orderBy('u.postCount', 'DESC');
        $qb->setMaxResults($posterMax);
        $forumUsers = $qb->getQuery()->getResult();

        $topPosters = [];
        if (!empty($forumUsers)) {
            foreach ($forumUsers as $forumUser) {
                $coreUser = $forumUser->getUser();
                $topPosters[] = [
                    'user_name' => DataUtil::formatForDisplay($coreUser['uname']),
                    // for BC reasons
                    'postCount' => DataUtil::formatForDisplay($forumUser->getPostCount()),
                    'user_id' => DataUtil::formatForDisplay($forumUser->getUser_id())];
            }
        }

        return $topPosters;
    } 
    
}
