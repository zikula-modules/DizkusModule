<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
class Dizkus_Api_Forum extends Zikula_AbstractApi
{

    /**
     * get tree 
     * format as array suitable for {formdropdownlist}
     * 
     * @param integer $id
     * @return array
     */
    public function getParents($id = null)
    {
        $repo = $this->entityManager->getRepository('Dizkus_Entity_Forum');
        $parents = $repo->childrenHierarchy();
        $output = $this->getNode($parents, $id);

        return $output;
    }
    
    /**
     * Get all tree nodes that are not root categories
     * Format as array suitable for {formdropdownlist}
     * 
     * @return array
     */
    public function getAllChildren()
    {
        $repo = $this->entityManager->getRepository('Dizkus_Entity_Forum');
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('node')
            ->from('Dizkus_Entity_Forum', 'node')
            ->orderBy('node.root, node.lft', 'ASC')
            ->where('node.lvl > 0')
            ->getQuery();
        $tree = $repo->buildTree($query->getArrayResult());
        return $this->getNode($tree, null);
    }

    /**
     * Format ArrayResult for usage in {formdropdownlist}
     * 
     * @param ArrayAccess $input
     * @param integer $id
     * @param integer $level
     * @return array
     */
    private function getNode($input, $id, $level = 0)
    {
        $pre = str_repeat("-", $level * 2);
        $output = array();
        foreach ($input as $i) {
            if ($id != $i['forum_id']) {
                $output[] = array(
                    'value' => $i['forum_id'],
                    'text' => $pre . $i['forum_name']
                );
                if (isset($i['__children'])) {
                    $output = array_merge($output, $this->getNode($i['__children'], $id, $level + 1));
                }
            }
        }

        return $output;
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
        $forumSubscription = $this->entityManager->getRepository('Dizkus_Entity_ForumSubscription')->findOneBy(array(
            'forum' => $args['forum'],
            'forumUser' => UserUtil::getVar('uid')
        ));

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
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
        
        // TODO: perms check?
        
        $managedForumUser = new Dizkus_Manager_ForumUser($args['user_id']);
        $managedForumUser->get()->addForumSubscription($args['forum']);
        $this->entityManager->flush();
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
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }

        if (empty($args['forum'])) {
            return LogUtil::registerArgsError();
        }
        
        // TODO: Permission check?

        $managedForumUser = new Dizkus_Manager_ForumUser($args['user_id']);
        if (isset($args['forum'])) {
            $forumSubscription = $this->entityManager->getRepository('Dizkus_Entity_ForumSubscription')->findOneBy(array(
                'forum' => $args['forum'],
                'forumUser' => $managedForumUser->get()
            ));
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
     * @returns Dizkus_Entity_ForumSubscription collection, may be empty
     */
    public function getSubscriptions($args)
    {
        if (empty($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
        }
        $managedForumUser = new Dizkus_Manager_ForumUser($args['uid']);
        return $managedForumUser->get()->getForumSubscriptions();
    }

    /**
     * getCategory
     *
     * Determines the category that a forum belongs to.
     *
     * @param int $forum_id The forum id to find the category of.
     *
     * @return int|boolean on success, false on failure
     */
    public function getCategory($forum_id)
    {
        if (!is_numeric($forum_id)) {
            return false;
        }

        return (int)$this->entityManager->find('Dizkus_Entity_Forum', $forum_id)->getcat_id();
    }

    /**
     * getForum
     *
     * Return forum entity information as an array
     *
     * @param int $forum_id The forum id to find the category of.
     *
     * @return int|boolean on success, false on failure
     */
    public function getForum($forum_id)
    {
        if (!is_numeric($forum_id)) {
            return false;
        }
        return $this->entityManager->find('Dizkus_Entity_Forum', $forum_id)->toArray();
    }

    /**
     * delete child topics
     *
     * @return array
     */
    public function deleteChildTopics($forum_id)
    {
        $find = array('forum_id' => $forum->getforum_id());
        $topics = $this->entityManager->getRepository('Dizkus_Entity_Topic')->findBy($find);
        foreach ($topics as $topic) {
            ModUtil::apiFunc($this->name, 'Topic', 'delete', $topic);
        }
    }

    /**
     * modify unser/forum association
     * 
     * @param integer $args['forum_id']
     * @return boolean
     */
    public function modify($args)
    {
        if (empty($args['forum_id'])) {
            return LogUtil::registerArgsError();
        }
        $managedForumUser = new Dizkus_Manager_ForumUser(UserUtil::getVar('uid'));
        $managedForum = new Dizkus_Manager_Forum($args['forum_id']);

        switch ($args['action']) {
            case 'addToFavorites':
                $managedForumUser->get()->addFavoriteForum($managedForum->get());
                break;
            case 'removeFromFavorites':
                $forumUserFavorite = $this->entityManager->getRepository('Dizkus_Entity_ForumUserFavorite')->findOneBy(array(
                    'forum' => $managedForum->get(),
                    'forumUser' => $managedForumUser->get()
                ));
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
