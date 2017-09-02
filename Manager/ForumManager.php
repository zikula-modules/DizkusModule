<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Security\Permission;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * Forum manager
 */
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
     * @var forumUserManagerService
     */
    private $forumUserManagerService;

    /**
     * managed forum.
     *
     * @var ForumEntity
     */
    private $_forum;

    /**
     * Doctrine Paginated
     *
     * @var ForumEntity
     */
    private $current_subforums;
    private $current_subforums_count;
    /**
     * Doctrine Paginated
     *
     * @var ForumEntity
     */
    private $current_topics;
    private $current_topics_count;
    private $_numberOfItems;

    /**
     * Collection view settings
     */
    private $_itemsPerPage;

    protected $name;

    /**
     * Construct the manager
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     * @param EntityManager $entityManager
     * @param CurrentUserApi $userApi
     * @param Permission $permission
     * @param VariableApi $variableApi
     * @param PermissionApi $permissionApi
     * @param ForumUserManager $forumUserManagerService
     */
    public function __construct(
            TranslatorInterface $translator,
            RouterInterface $router,
            RequestStack $requestStack,
            EntityManager $entityManager,
            CurrentUserApi $userApi,
            Permission $permission,
            VariableApi $variableApi,
            PermissionApi $permissionApi,
            ForumUserManager $forumUserManagerService
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
        $this->forumUserManagerService = $forumUserManagerService;

        $this->_itemsPerPage = $this->variableApi->get($this->name, 'topics_per_page');
    }

    /**
     * Construct the manager
     *
     * @param integer $id
     * @param ForumEntity $forum
     */
    public function getManager($id = null, ForumEntity $forum = null, $create = true)
    {
        if (isset($forum)) {
            // forum has been injected
            $this->_forum = $forum;
        } elseif ($id > 0) {
            $this->_forum = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $id);
        } elseif ($create) {
            $this->_forum = new ForumEntity();
        }

        return $this;
    }

    /**
     * Check if forum exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->_forum ? true : false;
    }

    /**
     * return page as array.
     *
     * @return int
     */
    public function getId()
    {
        return $this->_forum->getForum_id();
    }

    /**
     * return forum as doctrine2 object.
     *
     * @return ForumEntity
     */
    public function get()
    {
        return $this->_forum;
    }

    /**
     * Create the forum
     *
     * @param array $data page data
     */
    public function create()
    {
        return true;
    }

    /**
     * Update the forum
     *
     * @param array $data page data
     */
    public function update($data = null)
    {
        if ($data instanceof ForumEntity) {
            $this->_forum = $data;
        }
    }

    /**
     * Persist the forum
     *
     * @param array $data page data
     */
    public function store()
    {
        $this->entityManager->persist($this->_forum);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Delete the forum
     *
     * @param array $data page data
     */
    public function delete()
    {
        return $this;
    }

    /**
     * return page as array.
     *
     * @return array|bool false
     */
    public function toArray()
    {
        if (!$this->_forum) {
            return false;
        }

        return $this->_forum->toArray();
    }

    /**
     * return permissions
     *
     * @return bool false
     */
    public function getPermissions()
    {
        return $this->permission->get($this->_forum);
    }

    /**
     * get forum bread crumbs.
     *
     * @param bool $withoutCurrent show tree without the current item
     *
     * @return array
     */
    public function getBreadcrumbs($withoutCurrent = false)
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
                'url'   => $url,
                'title' => $forum->getName(), ];
        }
//        if ($withoutCurrent) {
//            // last element added in template instead
//            array_pop($output);
//        }

        return $output;
    }

    /**
     * Return topics of a forum as Doctrine Paginator
     * Here Forum becomes topics collection controller
     *
     * @return Paginator collection of paginated topics
     */
    public function getTopics($startNumber = 1)
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('t')
            ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
            ->where('t.forum = :forumId')
            ->setParameter('forumId', $this->_forum->getId())
            ->leftJoin('t.last_post', 'l')
            ->orderBy('t.sticky', 'DESC')
            ->addOrderBy('l.post_time', 'DESC')
            ->getQuery();

        $query->setFirstResult($startNumber - 1)
            ->setMaxResults($this->_itemsPerPage);
        $paginator = new Paginator($query, false);
        $this->current_topics_count = count($paginator);
        $this->current_topics = $paginator;

        return $this;
    }

    /**
     * Return topics of a forum as Doctrine Paginator
     * Here Forum becomes topics collection controller
     *
     * @return Paginator collection of paginated topics
     */
    public function getCurrentTopics()
    {
        //loaded using getTopics
        return $this->current_topics;
    }

    /**
     * Return topics of a forum as Doctrine Paginator
     * Here Forum becomes topics collection controller
     *
     * @return Paginator collection of paginated topics
     */
    public function getCurrentTopicsCount()
    {
        $this->current_topics_count;
    }

    /**
     * Return topics of a forum as Doctrine Paginator
     * Here Forum becomes topics collection controller
     *
     * @return Paginator collection of paginated topics
     */
    public function getTotalTopicsCount()
    {
        return $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->countForumTopics($this->_forum);
    }

    /**
     * increase read count.
     *
     * @return bool true
     */
    public function incrementReadCount()
    {
        $this->_forum->incrementCounter();

        return $this;
    }

    /**
     * Increase post count.
     */
    public function incrementPostCount()
    {
        $this->_forum->incrementPostCount();
        $this->modifyParentCount($this->_forum->getParent());

        return $this;
    }

    /**
     * decrease post count.
     */
    public function decrementPostCount()
    {
        $this->_forum->decrementPostCount();
        $this->modifyParentCount($this->_forum->getParent(), 'decrement');

        return $this;
    }

    /**
     * increase topic count.
     */
    public function incrementTopicCount()
    {
        $this->_forum->incrementTopicCount();
        $this->modifyParentCount($this->_forum->getParent(), 'increment', 'Topic');

        return $this;
    }

    /**
     * recursive method to modify parent forum's post or topic count.
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

        return $this;
    }

    public function setParentsLastPost($post)
    {
        $parents = $this->_forum->getParents();
        foreach ($parents as $parent) {
            $this->entityManager->persist($parent->setLast_post($post));
            $this->entityManager->flush();
        }

        return $this;
    }

    /**
     * Find last post by last topic post
     * This relays on topic last post is in sync
     * Recursive
     */
    public function resetLastPost($flush = false)
    {
        $this->entityManager
            ->getRepository('Zikula\DizkusModule\Entity\ForumEntity')
                ->resetLastPost($this->_forum, $flush);

        return $this;
    }

    /**
     * Is this forum a child of the provided forum?
     *
     * @param ForumEntity $forum
     *
     * @return bool
     */
    public function isChildOf(ForumEntity $forum)
    {
        return $this->get()->getLft() > $forum->getLft() && $this->get()->getRgt() < $forum->getRgt();
    }

    /**
     * get tree
     * format as array.
     *
     * @param int $id
     *
     * @return array
     */
    public function getParents($id = null, $includeLocked = true, $includeRoot = true)
    {
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
     * Format as array.
     *
     * @todo move to forum repository
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
     * Format flat ArrayResult for dropdowns.
     *
     * @param \ArrayAccess $input
     * @param int          $id
     * @param int          $level
     * @param bool         $includeLocked
     *
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
                    $output[$i['forum_id']] = $pre.$i['name'].'('.$i['forum_id'].')';
                }
                if (isset($i['__children'])) {
                    $output = $output + $this->getNode($i['__children'], $id, $level + 1, $includeLocked);
                }
            }
        }

        return $output;
    }
}
