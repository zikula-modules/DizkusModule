<?php
/**
 * Copyright Dizkus Team 2012.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\DizkusModule\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\Entity\ForumUserEntity;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Entity\TopicEntity;
use Zikula\DizkusModule\Security\Permission;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Api\CurrentUserApi;

class ForumUserManager
{
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * managed forum user.
     *
     * @var ForumUserEntity
     */
    private $_forumUser;

    private $loggedIn = false;

    protected $name;

    public function __construct(
    TranslatorInterface $translator, RouterInterface $router, RequestStack $requestStack, EntityManager $entityManager, CurrentUserApi $userApi, Permission $permission, VariableApi $variableApi
    )
    {
        $this->name = 'ZikulaDizkusModule';
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->userApi = $userApi;
        $this->permission = $permission;
        $this->variableApi = $variableApi;
    }

    //General object manager methods
    /**
     * get manager.
     *
     * @param int  $uid    user id (optional: defaults to current user)
     * @param bool $create create the ForumUser if does not exist (default: true)
     */
    public function getManager($uid = null)
    {
        if (empty($uid)) {
            $uid = $this->userApi->isLoggedIn() ? $this->request->getSession()->get('uid') : 1; // zikula guest account
        }

        $this->_forumUser = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumUserEntity', $uid);

        if ($this->exists()) {
            $this->loggedIn = true;
        } else {
            $this->_forumUser = new ForumUserEntity();
            //last try there is zikula user
            $zuser = $this->entityManager->find('Zikula\UsersModule\Entity\UserEntity', $uid);
            if($zuser){
                $this->_forumUser->setUser($zuser);
                $this->entityManager->persist($this->_forumUser);
                $this->entityManager->flush();
                $this->loggedIn = true;
            }
        }

        return $this;
    }

    /**
     * Check if user exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->_forumUser ? true : false;
    }


    /**
     * return topic as doctrine2 object.
     *
     * @return ForumUserEntity
     */
    public function get()
    {
        return $this->_forumUser;
    }


    /**
     * return topic as doctrine2 object.
     *
     * @return string
     */
    public function getUserName()
    {
        if($this->isAnonymous()){
           return 'Anonymous';
        } else {
            return $this->_forumUser->getUser()->getUname();
        }
    }

    /**
     * return topic as doctrine2 object.
     *
     * @return ForumUserEntity
     */
    public function getId()
    {
        return $this->_forumUser->getUserId();
    }

    /**
     * persist and flush.
     *
     * @param array $data forum user data
     */
    public function store($data)
    {
        $this->_forumUser->merge($data);
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();
    }

    /**
     * return topic as array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_forumUser->toArray();
    }

    /**
     * return forum user logged in status.
     *
     * @return ForumUserEntity
     */
    public function isLoggedIn()
    {
        return ($this->loggedIn && $this->getId() > 1) ? true : false;
    }

    /**
     * return forum user logged in status.
     *
     * @return ForumUserEntity
     */
    public function isOnline()
    {
        return ($this->loggedIn && $this->getId() > 1) ? true : false;
    }

    /**
     * return forum user logged in status.
     *
     * @return ForumUserEntity
     */
    public function isAnonymous()
    {
        return ($this->loggedIn && $this->getId() == 1) ? true : false;
    }

    public function getCurrentPosition()
    {
        //app.request.attributes.get('_route') ;
        return $this->request->attributes->get('_route');
    }


    //Posts collection display settings

    /**
     * postOrder.
     *
     * @return string
     */
    public function getPostOrder()
    {
        return ($this->_forumUser->getPostOrder() == 1) ? 'ASC' : 'DESC';
    }

    /**
     * set postOrder.
     *
     * @param string $sort asc|desc
     */
    public function setPostOrder($sort)
    {
        $this->_forumUser->setPostOrder($sort);
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();
    }

    // forums collection display settings

    /**
     * Set forum view setting
     *
     * @param string $setting
     */
    public function getForumViewSettings()
    {
        return $this->_forumUser->getDisplayOnlyFavorites();
    }

    /**
     * Set forum view setting
     *
     * @param string $setting
     */
    public function setForumViewSettings($setting)
    {
        if($setting){
                $this->_forumUser->showFavoritesOnly();
        }else {
                $this->_forumUser->showAllForums();
        }

        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();

    }

   // Signature management

    /**
     * Get user signature.
     *
     * @param string
     */
    public function getSignature()
    {
        return $this->_forumUser->getUser()->getAttributes()->offsetExists('signature') ? $this->_forumUser->getUser()->getAttributeValue('signature') : '';
    }

    /**
     * Set user signature.
     *
     * @param string $signature
     */
    public function setSignature($signature)
    {
        $zuser = $this->_forumUser->getUser();
        $zuser->setAttribute('signature', $signature);
        $this->entityManager->persist($zuser);
        $this->entityManager->flush();
    }


    // Subscriptions

    /**
     * Change the value of Autosubscribe setting.
     *
     * @param bool $value
     */
    public function setAutosubscribe($value)
    {
        $this->_forumUser->setAutosubscribe($value);
        $this->entityManager->flush();
    }


    //Forum subscriptions


     /**
     * Subscribe a forum.
     *
     * @param obj $page  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function getForumSubscriptionsCollection()
    {
        return $this->_forumUser->getForumSubscriptions();
    }



    /**
     * Subscribe a forum.
     *
     * @param obj $forum  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function subscribeForum(ForumEntity $forum)
    {
        $forumSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumSubscriptionEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get(),]);

        if ($forumSubscription) {
            return true; // nothing to do
        }

        $this->_forumUser->addForumSubscription($forum);
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Unsubscribe a forum.
     *
     * @param int $forum  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function unsubscribeForum($forum)
    {
        $forumSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumSubscriptionEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get(),]);

        if (!$forumSubscription) {
            return true; //nothing to do
        }

        $this->_forumUser->removeForumSubscription($forumSubscription);
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();

        return true;
    }

     /**
     * Is forum subscribed.
     *
     * @param int $forum  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function isForumSubscribed($forum)
    {
        $forumSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumSubscriptionEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get(),]);

        if ($forumSubscription) {
            return $forumSubscription;
        }

        return false;
    }

    //Topic Subscriptions

     /**
     * Subscribe a forum.
     *
     * @param obj $page  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function getTopicSubscriptionsCollection()
    {
        return $this->_forumUser->getTopicSubscriptions();
    }
    /**
     * Subscribe a topic.
     *
     * @param obj $topic  The topic
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function subscribeTopic(TopicEntity $topic)
    {
        $topicSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')->findOneBy([
            'topic' => $topic,
            'forumUser' => $this->get(),]);

        if ($topicSubscription) {
            return true;
        }

        $this->_forumUser->addTopicSubscription($topicSubscription);
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Unsubscribe a topic.
     *
     * @param int $topic  The topic
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function unsubscribeFromTopic($topic)
    {
        $topicSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')->findOneBy([
            'topic' => $topic,
            'forumUser' => $this->get(),]);

        if ($topicSubscription) {
            return true;
        }

        $this->_forumUser->removeTopicSubscription($topicSubscription);
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();

        return true;
    }

     /**
     * Is topic subscribed.
     *
     * @param int $topic  Thetopic
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function isTopicSubscribed($topic)
    {
        $topicSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicSubscriptionEntity')->findOneBy([
            'topic' => $topic,
            'forumUser' => $this->get(),]);

        if ($topicSubscription) {
            return true;
        }

        return false;
    }

    //Favorites

     /**
     * Subscribe a forum.
     *
     * @param obj $page  The forum
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
     */
    public function getFavoriteForumsCollection()
    {
        return $this->_forumUser->getFavoriteForums();
    }

    public function addFavoriteForum(ForumEntity $forum)
    {
        $forumIsFav = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumUserFavoriteEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get(),]);

        if ($forumIsFav) {
            return true;
        }

        $this->_forumUser->addFavoriteForum($forum);
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();

        return true;
    }

    public function removeFavoriteForum($forum)
    {
        $forumIsFav = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumUserFavoriteEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get(),]);

        if (!$forumIsFav) {
            return true;
        }

        $this->_forumUser->removeFavoriteForum($forumIsFav);
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();

        return true;
    }

    public function isForumFavorite($forum)
    {
        $forumIsFav = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumUserFavoriteEntity')->findOneBy([
            'forum' => $forum,
            'forumUser' => $this->get(),]);

        if ($forumIsFav) {
            return true;
        }

        return false;
    }

    // Topics

    /**
     * retrieve all user topics.
     *
     * @param $offset
     *  integer 'offset' pager offset
     *
     * @return array
     */
    public function getTopics($offset = 0)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'l')
            ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
            ->leftJoin('t.last_post', 'l')
            ->leftJoin('t.posts', 'p')
            ->orderBy('l.post_time', 'DESC');

        $qb->where('t.poster = :uid');

        $qb->setParameter('uid', $this->getId());

        $limit = $this->variableApi->get('ZikulaDizkusModule', 'topics_per_page');
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);
        $topics = new Paginator($qb);
        $pager = [
            'numitems'     => $topics->count(),
            'itemsperpage' => $limit, ];

        return [$topics, $pager];
    }

    // Posts

    /**
     * retrieve all my posts
     *
     * @param $offset integer 'offset' pager offset
     *
     * @return array
     */
    public function getPosts($offset = 0)
    {

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
            ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
            ->orderBy('p.post_time', 'DESC');
        $qb->where('p.poster = :uid');
        $qb->setParameter('uid', $this->getId());
        $limit = $this->variableApi->get('ZikulaDizkusModule', 'posts_per_page'); //$this->variableApi->get($perPageVar);
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);
        $posts = new Paginator($qb);
        $pager = [
            'numitems'     => $posts->count(),
            'itemsperpage' => $limit, ];

        return [$posts, $pager];
    }

    /**
     * lastvisit.
     *
     * reads the cookie, updates it and returns the last visit date in unix timestamp
     *
     * @param none
     *
     * @return unix timestamp last visit date
     */
    public function getLastVisit()
    {
        /**
         * set last visit cookies and get last visit time
         * set LastVisit cookie, which always gets the current time and lasts one year.
         */
        $path = $this->request->getBasePath();
        if (empty($path)) {
            $path = '/';
        } elseif (substr($path, -1, 1) != '/') {
            $path .= '/';
        }
        $time = time();

        //CookieUtil::setCookie('DizkusLastVisit', "{$time}", $time + 31536000, $path, null, null, false);
        $response = new Response();
        $cookie = new Cookie('DizkusLastVisit', $time, $time + 31536000);
        $response->headers->setCookie($cookie);

        //$lastVisitTemp = CookieUtil::getCookie('DizkusLastVisitTemp', false, null);
        $lastVisitTemp = $this->request->cookies->get('DizkusLastVisit');

        $temptime = empty($lastVisitTemp) ? $time : $lastVisitTemp;
        // set LastVisitTemp cookie, which only gets the time from the LastVisit and lasts for 30 min
        //CookieUtil::setCookie('DizkusLastVisitTemp', "{$temptime}", time() + 1800, $path, null, null, false);

        $cookie2 = new Cookie('DizkusLastVisitTemp', $temptime, $time + 1800);
        $response->headers->setCookie($cookie2);

        return $temptime;
    }

    /**
     * Get user activity based on same ip usage.
     *
     * @param int $ip The posters IP.
     *
     * @return array with information
     */
    public function getUserActivity($ip)
    {
        $viewip = [
            'poster_ip' => $ip,
            'poster_host' => ($ip != 'unrecorded') ? gethostbyaddr($ip) : $this->__('Host unknown'),
        ];
        $dql = 'SELECT p
            FROM Zikula\DizkusModule\Entity\PostEntity p
            WHERE p.poster_ip = :pip
            GROUP BY p.poster';
        $query = $this->entityManager->createQuery($dql)->setParameter('pip', $ip);
        $posts = $query->getResult();
        foreach ($posts as $post) {
            /* @var $post \Zikula\Module\DizkusModule\Entity\PostEntity */
            $coreUser = $post->getPoster()->getUser();
            $viewip['users'][] = [
                'uid' => $post->getPoster()->getUser_id(),
                'uname' => $coreUser['uname'],
                'postcount' => $post->getPoster()->getPostCount(),];
        }

        return $viewip;
    }

}






//    /**
//     * Old userApi Below.
//     */
//    /**
//     * insert rss.
//     *
//     * @see rss2dizkus.php - only used there
//     *
//     * @param $args['forum']    array with forum data
//     * @param $args['items']    array with feed data as returned from Feeds module
//     *
//     * @return bool true or false
//
//    public function insertrss($args)
//    {
//        if (!$args['forum'] || !$args['items']) {
//            return false;
//        }
//        foreach ($args['items'] as $item) {
//            // create the reference
//            $dateTimestamp = $item->get_date('Y-m-d H:i:s');
//            if (empty($dateTimestamp)) {
//                $reference = md5($item->get_link());
//                $dateTimestamp = date('Y-m-d H:i:s', time());
//            } else {
//                $reference = md5($item->get_link() . '-' . $dateTimestamp);
//            }
//            $topicTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimestamp);
//            // Checking if the forum already has that news.
//            $topic = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->findOneBy(['reference' => $reference]);
//            if (!isset($topic)) {
//                // Not found, add the feed item
//                $subject = $item->get_title();
//                // create message
//                $message = '<strong>' . $this->__('Summary') . ' :</strong>\\n\\n' . $item->get_description() . '\\n\\n<a href="' . $item->get_link() . '">' . $item->get_title() . '</a>\\n\\n';
//                // store message
//                $newManagedTopic = new TopicManager();
//                $data = [
//                    'title' => $subject,
//                    'message' => $message,
//                    'topic_time' => $topicTime,
//                    'forum_id' => $args['forum']['forum_id'],
//                    'attachSignature' => false,
//                    'subscribe_topic' => false,
//                    'reference' => $reference,];
//                $newManagedTopic->prepare($data);
//                $topicId = $newManagedTopic->create();
//                if (!$topicId) {
//                    // An error occured
//                    return false;
//                }
//            }
//        }
//
//        return true;
//    }
// */


//    public function isSpam(PostEntity $post)
//    {
//        $user = $post->getPoster()->getUser();
//        $args = [
//            'author' => $user['uname'], // use 'viagra-test-123' to test
//            'authoremail' => $user['email'],
//            'content' => $post->getPost_text(),
//        ];
//        // Akismet
//        if (ModUtil::available('Akismet')) {
//            return ModUtil::apiFunc('Akismet', 'user', 'isspam', $args);
//        }
//
//        return false;
//    }

//    /**
//     * Check if the useragent is a bot (blacklisted).
//     *
//     * @return bool
//     */
//    public function useragentIsBot()
//    {
//        // check the user agent - if it is a bot, return immediately
//        $robotslist = [
//            'ia_archiver',
//            'googlebot',
//            'mediapartners-google',
//            'yahoo!',
//            'msnbot',
//            'jeeves',
//            'lycos',];
//        $request = ServiceUtil::get('request');
//        $useragent = $request->server->get('HTTP_USER_AGENT');
//        for ($cnt = 0; $cnt < count($robotslist); $cnt++) {
//            if (strpos(strtolower($useragent), $robotslist[$cnt]) !== false) {
//                return true;
//            }
//        }
//
//        return false;
//    }

//    /**
//     * dzkVarPrepHTMLDisplay
//     * removes the  [code]...[/code] before really calling DataUtil::formatForDisplayHTML().
//     */
//    public function dzkVarPrepHTMLDisplay($text)
//    {
//        // remove code tags
//        $codecount1 = preg_match_all('/\\[code(.*)\\](.*)\\[\\/code\\]/si', $text, $codes1);
//        for ($i = 0; $i < $codecount1; $i++) {
//            $text = preg_replace('/(' . preg_quote($codes1[0][$i], '/') . ')/', " DIZKUSCODEREPLACEMENT{$i} ", $text, 1);
//        }
//
//
//        // the real work
//        $text = nl2br(DataUtil::formatForDisplayHTML($text));
//        // re-insert code tags
//        for ($i = 0; $i < $codecount1; $i++) {
//            // @todo should use htmlentities here???? dzkstriptags too vvv
//            $text = preg_replace("/ DIZKUSCODEREPLACEMENT{$i} /", $codes1[0][$i], $text, 1);
//        }
//
//        return $text;
//    }




/**
     * @todo move to Twig?
     *
     * Truncate text to desired length to nearest word.
     *
     * @see http://stackoverflow.com/a/9219884/2600812
     *
     * @param string $text
     * @param int    $chars
     *
     * @return string
     */
//    public static function truncate($text, $chars = 25)
//    {
//        $originalText = $text;
//        $text = $text . ' ';
//        $text = substr($text, 0, $chars);
//        $text = substr($text, 0, strrpos($text, ' '));
//        $text = strlen($originalText) == strlen($text) ? $text : $text . '...';
//
//        return $text;
//    }