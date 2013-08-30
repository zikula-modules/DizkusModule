<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Dizkus\Api;

use Dizkus\Entity\ForumEntity;
use SecurityUtil;
use UserUtil;
use LogUtil;
use ModUtil;
use Dizkus\Manager\ForumUserManager;
use Dizkus\Manager\ForumManager;

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
            $forumRoot = $this->entityManager->getRepository('Dizkus\Entity\ForumEntity')->findOneBy(array('name' => ForumEntity::ROOTNAME));
        }
        $parents = $this->entityManager->getRepository('Dizkus\Entity\ForumEntity')->childrenHierarchy($forumRoot);
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
        $repo = $this->entityManager->getRepository('Dizkus\Entity\ForumEntity');
        $query = $this->entityManager->createQueryBuilder()->select('node')->from('Dizkus\Entity\ForumEntity', 'node')->orderBy('node.root, node.lft', 'ASC')->where('node.lvl > 0')->getQuery();
        $tree = $repo->buildTree($query->getArrayResult());

        return $this->getNode($tree, null);
    }

    /**
     * Format ArrayResult for usage in {formdropdownlist}
     *
     * @param  ArrayAccess $input
     * @param  integer     $id
     * @param  integer     $level
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
        $forums = $this->entityManager->getRepository('Dizkus\Entity\ForumEntity')->findAll();
        foreach ($forums as $forum) {
            $parent = $forum->getParent();
            $parentId = isset($parent) ? $parent->getForum_id() : null;
            $forumId = $forum->getForum_id();
            if (SecurityUtil::checkPermission('Dizkus::', "{$parentId}:{$forumId}:", ACCESS_READ, $userId)) {
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
        $forumSubscription = $this->entityManager->getRepository('Dizkus\Entity\ForumSubscriptionEntity')->findOneBy(array(
            'forum' => $args['forum'],
            'forumUser' => UserUtil::getVar('uid')));

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
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
        // Permission check
        $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead', $args['forum']));
        $managedForumUser = new ForumUserManager($args['user_id']);
        $searchParams = array(
            'forum' => $args['forum'],
            'forumUser' => $managedForumUser->get());
        $forumSubscription = $this->entityManager->getRepository('Dizkus\Entity\ForumSubscriptionEntity')->findOneBy($searchParams);
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
     */
    public function unsubscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
        if (empty($args['forum'])) {
            return LogUtil::registerArgsError();
        }
        // Permission check
        $this->throwForbiddenUnless(ModUtil::apiFunc($this->name, 'Permission', 'canRead', $args['forum']));
        $managedForumUser = new ForumUserManager($args['user_id']);
        if (isset($args['forum'])) {
            $forumSubscription = $this->entityManager->getRepository('Dizkus\Entity\ForumSubscriptionEntity')->findOneBy(array(
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
     * @params $args['uid'] User id (optional)
     *
     * @returns ForumSubscriptionEntity collection, may be empty
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
     * modify unser/forum association
     *
     * @param  integer $args['forum_id']
     * @return boolean
     */
    public function modify($args)
    {
        if (empty($args['forum_id'])) {
            return LogUtil::registerArgsError();
        }
        $managedForumUser = new ForumUserManager(UserUtil::getVar('uid'));
        $managedForum = new ForumManager($args['forum_id']);
        switch ($args['action']) {
            case 'addToFavorites':
                $managedForumUser->get()->addFavoriteForum($managedForum->get());
                break;
            case 'removeFromFavorites':
                $forumUserFavorite = $this->entityManager->getRepository('Dizkus\Entity\ForumUserFavoriteEntity')->findOneBy(array('forum' => $managedForum->get(), 'forumUser' => $managedForumUser->get()));
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
