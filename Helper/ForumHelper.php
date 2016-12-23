<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Helper;

use DataUtil;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Security\Permission;

/**
 * FavoritesHelper
 *
 * @author Kaik
 */
class ForumHelper {
    
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
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
            RequestStack $requestStack,
            EntityManager $entityManager,
            CurrentUserApi $userApi,
            Permission $permission,
            TranslatorInterface $translator
         ) {
        
        $this->name = 'ZikulaDizkusModule';
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->userApi = $userApi;
        $this->permission = $permission;
        $this->translator = $translator;             
    }
 

    /**
     * Get forum subscriptions of a user
     *
     * @param $user_id  User id (optional)
     *
     * @return \Zikula\Module\DizkusModule\Entity\ForumSubscriptionEntity collection, may be empty
     */
    public function getSubscriptions($user_id = null)
    {
        if (empty($user_id)) {
            $loggedIn = $this->userApi->isLoggedIn();
            if(!$loggedIn){
                throw new AccessDeniedException();   
            }
            $user_id = $loggedIn ? $this->request->getSession()->get('uid') : 1;
        }
        $managedForumUser = new ForumUserManager($user_id);

        return $managedForumUser->get()->getForumSubscriptions();
    }   
    
    /**
     * Get forum subscription status
     *
     * @param int $forum The forum
     * @param int $user_id The user id (optional).
     *
     * @return boolean True if the user is subscribed or false if not
     */
    public function isSubscribed($forum, $user_id)
    {
        if (empty($forum)) {
            throw new \InvalidArgumentException();
        }
        if (empty($user_id)) {
            $loggedIn = $this->userApi->isLoggedIn();
            $user_id = $loggedIn ? $this->request->getSession()->get('uid') : 1;
        }       
        $forumSubscription = $this->entityManager
            ->getRepository('Zikula\DizkusModule\Entity\ForumSubscriptionEntity')
            ->findOneBy([
                'forum' => $forum,
                'forumUser' => $user_id]
            );

        return isset($forumSubscription);
    }

    /**
     * subscribe a forum
     *
     * @param int $forum The forum
     * @param int $user_id The user id (optional: needs ACCESS_ADMIN).
     *
     * @return boolean
     */
    public function subscribe($forum, $user_id = null)
    {
        if (isset($user_id) && !$this->permission->canAdministrate()) {
            throw new AccessDeniedException();
        } else {
            $loggedIn = $this->userApi->isLoggedIn();
            if(!$loggedIn){
                throw new AccessDeniedException();   
            }
            $user_id = $loggedIn ? $this->request->getSession()->get('uid') : 1;
        }
        // Permission check
        if (!$this->permission->canRead(['forum' => $forum])) {
            throw new AccessDeniedException();
        }
        $managedForumUser = new ForumUserManager($user_id);
        $searchParams = [
            'forum' => $forum,
            'forumUser' => $managedForumUser->get()];
        $forumSubscription = $this->entityManager
            ->getRepository('Zikula\DizkusModule\Entity\ForumSubscriptionEntity')
            ->findOneBy($searchParams);
        if (!$forumSubscription) {
            $forum = $this->entityManager
            ->getRepository('Zikula\DizkusModule\Entity\ForumEntity')
            ->findOneBy(['forum_id' => $forum]);
            $managedForumUser->get()->addForumSubscription($forum);
            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * Unsubscribe a forum
     *
     * @param int $forum The forum
     * @param int $user_id The user id (optional: needs ACCESS_ADMIN).
     *
     * @return boolean
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function unsubscribe($forum, $user_id = null)
    {
        if (isset($user_id) && !$this->permission->canAdministrate()) {
            throw new AccessDeniedException();
        } else {
            $loggedIn = $this->userApi->isLoggedIn();
            $user_id = $loggedIn ? $this->request->getSession()->get('uid') : 1;
        }
        // Permission check
        if (!$this->permission->canRead(['forum' => $forum])) {
            throw new AccessDeniedException();
        }
        $managedForumUser = new ForumUserManager($user_id);
        if (isset($forum)) {
            $forumSubscription = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumSubscriptionEntity')->findOneBy([
                'forum' => $forum,
                'forumUser' => $managedForumUser->get()]);
            $managedForumUser->get()->removeForumSubscription($forumSubscription);
        }
        $this->entityManager->flush();

        return true;
    }  

    /**
     * modify user/forum association
     *
     * @param  integer $forum
     * @param  string $action = 'addToFavorites'|'removeFromFavorites'|'subscribe'|'unsubscribe'
     *
     * @return boolean
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function modify($forum, $action)
    {
        if (empty($forum) || empty($action)) {
            throw new \InvalidArgumentException();
        }
        $managedForumUser = new ForumUserManager();
        $managedForum = new ForumManager($forum);
        switch ($action) {
            case 'addToFavorites':
                $managedForumUser->get()->addFavoriteForum($managedForum->get());
                break;
            case 'removeFromFavorites':
                $forumUserFavorite = $this->entityManager
                    ->getRepository('Zikula\DizkusModule\Entity\ForumUserFavoriteEntity')
                    ->findOneBy([
                        'forum' => $managedForum->get(),
                        'forumUser' => $managedForumUser->get()]
                    );
                $managedForumUser->get()->removeFavoriteForum($forumUserFavorite);
                break;
            case 'subscribe':
                $this->subscribe(['forum' => $managedForum->get()]);
                break;
            case 'unsubscribe':
                $this->unsubscribe(['forum' => $managedForum->get()]);
                break;
        }
        $this->entityManager->flush();

        return true;
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
            ->from('Zikula\DizkusModule\Entity\ForumEntity', 'f')
            ->orderBy('f.lvl', 'DESC')
            ->addOrderBy('f.postCount', 'DESC');
        $qb->setMaxResults($forumMax);
        $forums = $qb->getQuery()->getResult();

        $topForums = [];
        if (!empty($forums)) {
            foreach ($forums as $forum) {
                if ($this->permission->canRead($forum)) {
                    $topforum = $forum->toArray();
                    $topforum['name'] = DataUtil::formatForDisplay($forum->getName());
                    $parent = $forum->getParent();
                    $parentName = isset($parent) ? $parent->getName() : $this->translator->__('Root');
                    $topforum['cat_title'] = DataUtil::formatForDisplay($parentName);
                    array_push($topForums, $topforum);
                }
            }
        }

        return $topForums;
    }
    
}
