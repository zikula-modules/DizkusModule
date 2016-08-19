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

use SecurityUtil;
use UserUtil;
use ModUtil;
use Zikula\Module\DizkusModule\Entity\ForumEntity;
use Zikula\Module\DizkusModule\Manager\ForumUserManager;
use Zikula\Module\DizkusModule\Manager\ForumManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ForumApi extends \Zikula_AbstractApi
{

    /**
     * get tree
     * format as array suitable for {formdropdownlist}
     *
     * @param  integer $id
     * @return array
     */
    public function getParents($args)
    {
        $id = isset($args['id']) ? $args['id'] : null;
        $includeLocked = isset($args['includeLocked']) ? $args['includeLocked'] : true;
        $includeRoot = isset($args['includeRoot']) && $args['includeRoot'] == false ? false : true;
        if ($includeRoot) {
            $forumRoot = null;
        } else {
            $forumRoot = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->findOneBy(array('name' => ForumEntity::ROOTNAME));
        }
        $parents = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->childrenHierarchy($forumRoot);
        $output = $this->getNode($parents, $id, 0, $includeLocked);

        return $output;
    }

    /**
     * Get all tree nodes that are not root
     * Format as array suitable for {formdropdownlist}
     *
     * @return array
     */
    public function getAllChildren()
    {
        $repo = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity');
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('node')
            ->from('Zikula\Module\DizkusModule\Entity\ForumEntity', 'node')
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
        $output = array();
        foreach ($input as $i) {
            if ($id != $i['forum_id']) {
                // only include results if
                if ($i['status'] == ForumEntity::STATUS_LOCKED && $includeLocked || $i['status'] == ForumEntity::STATUS_UNLOCKED) {
                    if ($i['name'] == ForumEntity::ROOTNAME) {
                        $i['name'] = $this->__('Forum Index (top level)');
                    }
                    $output[] = array(
                        'value' => $i['forum_id'],
                        'text' => $pre . $i['name']);
                }
                if (isset($i['__children'])) {
                    $output = array_merge($output, $this->getNode($i['__children'], $id, $level + 1, $includeLocked));
                }
            }
        }

        return $output;
    }

    /**
     * Get the ids of all the forums the user is allowed to see
     *
     * @param  integer $args['parent']
     * @param  integer $args['userId']
     * @return array
     */
    public function getForumIdsByPermission($args)
    {
        $parent = isset($args['parent']) ? $args['parent'] : null;
        $userId = isset($args['userId']) ? $args['userId'] : null;
        $ids = array();
        $forums = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->findAll();
        /** @var $forum ForumEntity */
        foreach ($forums as $forum) {
            $parent = $forum->getParent();
            $parentId = isset($parent) ? $parent->getForum_id() : null;
            $forumId = $forum->getForum_id();
            if (SecurityUtil::checkPermission($this->name . '::', "{$parentId}:{$forumId}:", ACCESS_READ, $userId)) {
                $ids[] = $forumId;
            }
        }

        return $ids;
    }

    /**
     * Get forum subscription status
     *
     * @param array $args The argument array.
     *        int $args['forum'] The forum
     *
     * @return boolean True if the user is subscribed or false if not
     */
    public function isSubscribed($args)
    {
        $forumSubscription = $this->entityManager
            ->getRepository('Zikula\Module\DizkusModule\Entity\ForumSubscriptionEntity')
            ->findOneBy(array(
                'forum' => $args['forum'],
                'forumUser' => UserUtil::getVar('uid'))
            );

        return isset($forumSubscription);
    }

    /**
     * subscribe a forum
     *
     * @param array $args The argument array.
     *       int $args['forum'] The forum
     *       int $args['user_id'] The user id (optional: needs ACCESS_ADMIN).
     *
     * @return boolean
     */
    public function subscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $args['forum'])) {
            throw new AccessDeniedException();
        }
        $managedForumUser = new ForumUserManager($args['user_id']);
        $searchParams = array(
            'forum' => $args['forum'],
            'forumUser' => $managedForumUser->get());
        $forumSubscription = $this->entityManager
            ->getRepository('Zikula\Module\DizkusModule\Entity\ForumSubscriptionEntity')
            ->findOneBy($searchParams);
        if (!$forumSubscription) {
            $managedForumUser->get()->addForumSubscription($args['forum']);
            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * Unsubscribe a forum
     *
     * @param array $args The argument array.
     *        int $args['forum'] The forum
     *        int $args['user_id'] The user id (optional: needs ACCESS_ADMIN).
     *
     * @return boolean
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function unsubscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
        if (empty($args['forum'])) {
            throw new \InvalidArgumentException();
        }
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $args['forum'])) {
            throw new AccessDeniedException();
        }
        $managedForumUser = new ForumUserManager($args['user_id']);
        if (isset($args['forum'])) {
            $forumSubscription = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumSubscriptionEntity')->findOneBy(array(
                'forum' => $args['forum'],
                'forumUser' => $managedForumUser->get()));
            $managedForumUser->get()->removeForumSubscription($forumSubscription);
        }
        $this->entityManager->flush();

        return true;
    }

    /**
     * Get forum subscriptions of a user
     *
     * @param $args['uid'] User id (optional)
     *
     * @return \Zikula\Module\DizkusModule\Entity\ForumSubscriptionEntity collection, may be empty
     */
    public function getSubscriptions($args)
    {
        if (empty($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
        }
        $managedForumUser = new ForumUserManager($args['uid']);

        return $managedForumUser->get()->getForumSubscriptions();
    }

    /**
     * modify user/forum association
     *
     * @param  $args
     *  integer 'forum'
     *  string 'action' = 'addToFavorites'|'removeFromFavorites'|'subscribe'|'unsubscribe'
     *
     * @return boolean
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function modify($args)
    {
        if (empty($args['forum']) || empty($args['action'])) {
            throw new \InvalidArgumentException();
        }
        $managedForumUser = new ForumUserManager();
        $managedForum = new ForumManager($args['forum']);
        switch ($args['action']) {
            case 'addToFavorites':
                $managedForumUser->get()->addFavoriteForum($managedForum->get());
                break;
            case 'removeFromFavorites':
                $forumUserFavorite = $this->entityManager
                    ->getRepository('Zikula\Module\DizkusModule\Entity\ForumUserFavoriteEntity')
                    ->findOneBy(array(
                        'forum' => $managedForum->get(),
                        'forumUser' => $managedForumUser->get())
                    );
                $managedForumUser->get()->removeFavoriteForum($forumUserFavorite);
                break;
            case 'subscribe':
                $this->subscribe(array('forum' => $managedForum->get()));
                break;
            case 'unsubscribe':
                $this->unsubscribe(array('forum' => $managedForum->get()));
                break;
        }
        $this->entityManager->flush();

        return true;
    }

}
