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

use Zikula\DizkusModule\Entity\ForumEntity;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\UsersModule\Api\CurrentUserApi;
use Zikula\ExtensionsModule\Api\VariableApi;

use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\DizkusModule\Security\Permission;



class ForumManager
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
     * @var PermissionApi
     */    
    private $permissionApi;    
    
    /**
     * managed forum
     * @var ForumEntity
     */
    private $_forum;
    
    private $_itemsPerPage;
    private $_numberOfItems;
    
    protected $name;
    
    public function __construct(
            TranslatorInterface $translator,
            RouterInterface $router,
            RequestStack $requestStack,
            EntityManager $entityManager,
            CurrentUserApi $userApi,
            Permission $permission,
            VariableApi $variableApi,
            PermissionApi $permissionApi
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
        $this->permissionApi = $permissionApi;        
        
        $this->_itemsPerPage = $this->variableApi->get($this->name, 'topics_per_page');       
        
    }

    /**
     * 
     */
    public function getManager($id = null, ForumEntity $forum = null)
    {
        if (isset($forum)) {
            // forum has been injected
            $this->_forum = $forum;
        } elseif ($id > 0) {
            $this->_forum = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $id);
        } else {
            $this->_forum = new ForumEntity();
        }
        
        return $this;
    }

    /**
     * Check if forum exists
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->_forum ? true : false;
    }

    /**
     * return page as array
     *
     * @return array|boolean false
     */
    public function toArray()
    {
        if (!$this->_forum) {
            return false;
        }

        return $this->_forum->toArray();
    }

    /**
     * return page as array
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_forum->getForum_id();
    }

    /**
     * return forum as doctrine2 object
     *
     * @return ForumEntity
     */
    public function get()
    {
        return $this->_forum;
    }

    public function getPermissions()
    {
        return $this->permission->get($this->_forum);
    }

    /**
     * get forum bread crumbs
     *
     * @param boolean $withoutCurrent show tree without the current item
     *
     * @return array
     */
    public function getBreadcrumbs($withoutCurrent = true)
    {
        if ($this->_forum->getLvl() == 0) {
            // already root
            return [];
        }
        $forums = $this->entityManager
            ->getRepository('Zikula\DizkusModule\Entity\ForumEntity')
            ->getPath($this->_forum);
        $output = [];
        foreach ($forums as $key => $forum) {
            if ($key == 0) {
                continue;
            }
            $url = $this->router->generate('zikuladizkusmodule_forum_viewforum', ['forum' => $forum->getForum_id()]);
            $output[] = [
                'url' => $url,
                'title' => $forum->getName()];
        }
        if ($withoutCurrent) {
            // last element added in template instead
            array_pop($output);
        }

        return $output;
    }

    /**
     * return posts of a forum as doctrine2 object
     *
     * @return Paginator collection of paginated topics
     */
    public function getTopics($startNumber = 1)
    {
        $id = $this->_forum->getForum_id();
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from('Zikula\DizkusModule\Entity\TopicEntity', 'p')
            ->where('p.forum = :forumId')
            ->setParameter('forumId', $id)
            ->leftJoin('p.last_post', 'l')
            ->orderBy('p.sticky', 'DESC')
            ->addOrderBy('l.post_time', 'DESC')
            ->getQuery();
        $query->setFirstResult($startNumber - 1)
            ->setMaxResults($this->_itemsPerPage);
        $paginator = new Paginator($query, false);
        $this->_numberOfItems = count($paginator);

        return $paginator;
    }

    /**
     * get the pager
     *
     * @return array
     */
    public function getPager()
    {
        return [
            'itemsperpage' => $this->_itemsPerPage,
            'numitems' => $this->_numberOfItems];
    }

    /**
     * increase read count
     *
     * @return boolean true
     */
    public function incrementReadCount()
    {
        $this->_forum->incrementCounter();
        $this->entityManager->flush();

        return true;
    }

    /**
     * Increase post count
     */
    public function incrementPostCount()
    {
        $this->_forum->incrementPostCount();
        $this->modifyParentCount($this->_forum->getParent());
        $this->entityManager->flush();
    }

    /**
     * decrease post count
     */
    public function decrementPostCount()
    {
        $this->_forum->decrementPostCount();
        $this->modifyParentCount($this->_forum->getParent(), 'decrement');
        $this->entityManager->flush();
    }

    /**
     * increase topic count
     */
    public function incrementTopicCount()
    {
        $this->_forum->incrementTopicCount();
        $this->modifyParentCount($this->_forum->getParent(), 'increment', 'Topic');
        $this->entityManager->flush();
    }

    /**
     * recursive method to modify parent forum's post or topic count
     */
    private function modifyParentCount(ForumEntity $parentForum, $direction = 'increment', $entity = 'Post')
    {
        $direction = in_array($direction, ['increment', 'decrement']) ? $direction : 'increment';
        $entity = in_array($entity, ['Post', 'Topic']) ? $entity : 'Post';
        $method = "{$direction}{$entity}Count";
        $parentForum->{$method}();
        $grandParent = $parentForum->getParent();
        if (isset($grandParent)) {
            $this->modifyParentCount($grandParent, $direction, $entity);
        }
    }

    public function setLastPost($post)
    {
        $this->_forum->setLast_post($post);
        $this->entityManager->flush();
    }

    /**
     * store the forum
     *
     * @param array $data page data
     */
    public function store($data)
    {
        $this->_forum->merge($data);
        $this->entityManager->persist($this->_forum);
        $this->entityManager->flush();
    }

    /**
     * Is the current user (provided user) a forum moderator?
     *
     * @param  integer $uid (optional, default: null)
     * @return boolean
     */
    public function isModerator($uid = null)
    {
        if (!isset($uid)) {
            $loggedIn = $this->userApi->isLoggedIn();
            if(!$loggedIn){
                return false;  
            }
            $uid = $loggedIn ? $this->request->getSession()->get('uid') : 1;

        }
        // check zikula perms
        if ($this->permissionApi->hasPermission($this->name, $this->_forum->getForum_id() . '::', ACCESS_MODERATE)) {
         //   return true;
        }
        $moderatorUsers = $this->_forum->getModeratorUsersAsIdArray(true);
        if (in_array($uid, $moderatorUsers)) {
          //  return true;
        }
        $gids = $this->_forum->getModeratorGroupsAsIdArray(true);
        if (empty($gids)) {
         //   return false;
        }
        // is this user in any of the groups?
        $dql = 'SELECT m FROM Zikula\\UsersModule\\Entity\\UserEntity m
            WHERE m.uid = :uid';
        $user = $this->entityManager
            ->createQuery($dql)
            ->setParameter('uid', $uid)
            ->setMaxResults(1)
            ->getOneOrNullResult();
          
        $groupMembership = [];
        foreach($user->getGroups()->toArray() as $group){
              if(in_array($group->getGid(), $gids)){
                $groupMembership[] = $group->getGid();   
              }
        }
          
        return count($groupMembership) > 0 ? true : false;
    }

    /**
     * Is this forum a child of the provided forum?
     *
     * @param  ForumEntity $forum
     * @return boolean
     */
    public function isChildOf(ForumEntity $forum)
    {
        return $this->get()->getLft() > $forum->getLft() && $this->get()->getRgt() < $forum->getRgt();
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
        $managedForum = $this->getManager($forum); //new ForumManager($forum);
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
     * get tree
     * format as array 
     *
     * @param  integer $id
     * @return array
     */
    public function getParents($id = null, $includeLocked = true, $includeRoot = true )
    {
//        $id = isset($args['id']) ? $args['id'] : null;
//        $includeLocked = isset($args['includeLocked']) ? $args['includeLocked'] : true;
//        $includeRoot = isset($args['includeRoot']) && $args['includeRoot'] == false ? false : true;
        if (!$includeRoot) {
            $forumRoot = null;
        } else {
            $forumRoot = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->findOneBy(['name' => ForumEntity::ROOTNAME]);
        }
        $parents = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->childrenHierarchy($forumRoot);
        $output = $this->getNode($parents, $id, 0, $includeLocked);

        return $output;
    }

    /**
     * Get all tree nodes that are not root
     * Format as array
     *
     * @return array
     */
    public function getAllChildren()
    {
        $repo = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumEntity');
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('node')
            ->from('Zikula\DizkusModule\Entity\ForumEntity', 'node')
            ->orderBy('node.root, node.lft', 'ASC')
            ->where('node.lvl > 0')
            ->getQuery();
        $tree = $repo->buildTree($query->getArrayResult());

        return $this->getNode($tree, null);
    }

    /**
     * Format ArrayResult for usage in {formdropdownlist}
     *
     * @param  \ArrayAccess $input
     * @param  integer $id
     * @param  integer $level
     * @param bool $includeLocked
     * @return array
     */
    private function getNode($input, $id, $level = 0, $includeLocked = true)
    {
        $pre = str_repeat('-', $level * 2);
        $output = [];
        foreach ($input as $i) {
            if ($id != $i['forum_id']) {
                // only include results if
                if ($i['status'] == ForumEntity::STATUS_LOCKED && $includeLocked || $i['status'] == ForumEntity::STATUS_UNLOCKED) {
                    if ($i['name'] == ForumEntity::ROOTNAME) {
                        $i['name'] = $this->__('Forum Index (top level)');
                    }
                    $output[] = [
                        'value' => $i['forum_id'],
                        'text' => $pre . $i['name']];
                }
                if (isset($i['__children'])) {
                    $output = array_merge($output, $this->getNode($i['__children'], $id, $level + 1, $includeLocked));
                }
            }
        }

        return $output;
    }
    
    /**
     * gets the last $maxforums forums
     *
     * @param mixed[] $params {
     *      @type int maxforums    number of forums to read, default = 5
     *                      }
     * 
     * @return array $topForums
     * 
     * 
     * @todo Maybe move to count helper ?
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
