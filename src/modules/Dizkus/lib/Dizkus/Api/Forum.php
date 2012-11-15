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
     * Get forum subscription status
     *
     * @param array $args The argument array.
     *        int $args['user_id'] The users uid.
     *        int $args['forum_id'] The forums id.
     *
     * @return boolean True if the user is subscribed or false if not
     */
    public function getSubscriptionStatus($args)
    {
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(s.msg_id)')
           ->from('Dizkus_Entity_ForumSubscriptions', 's')
           ->where('s.user_id = :user')
           ->setParameter('user', $args['user_id'])
           ->andWhere('s.forum_id = :forum')
           ->setParameter('forum', $args['forum_id'])
           ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * subscribe
     *
     * @param array $args The argument array.
     *       int $args['forum_id'] The forums id.
     *       int $args['user_id'] The users id (needs ACCESS_ADMIN).
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

        $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums', array('forum_id' => $args['forum_id']));
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $forum)) {
            return LogUtil::registerPermissionError();
        }

        if ($this->getSubscriptionStatus($args) == false) {
            // add user only if not already subscribed to the forum
            // we can use the args parameter as-is
            $item = new Dizkus_Entity_ForumSubscriptions();
            $data = array('user_id' => $args['user_id'], 'forum_id' => $args['forum_id']);
            $item->merge($data);
            $this->entityManager->persist($item);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }

    /**
     * unsubscribe
     *
     * Unsubscribe a forum
     *
     * @param array $args The argument array.
     *        int $args['forum_id'] The forums id, if empty then we unsubscribe all forums.
     *        int $args['user_id'] The users id (needs ACCESS_ADMIN).
     *
     * @return boolean
     */
    public function unsubscribe($args)
    {
        if (isset($args['user_id'])) {
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }

        if (empty($args['forum_id'])) {
            return LogUtil::registerArgsError();
        }

        $subscription = $this->entityManager
                             ->getRepository('Dizkus_Entity_ForumSubscriptions')
                             ->findOneBy(array('user_id' => $args['user_id'], 'forum_id' => $args['forum_id'])
        );
        $this->entityManager->remove($subscription);
        $this->entityManager->flush();

        return true;
    }

    /**
     * unsubscribeById
     *
     * Unsubscribe a forum by forum id.
     *
     * @param int $id The topic id.
     *
     * @return boolean
     */
    public function unsubscribeById($id)
    {
        $subscription = $this->entityManager->find('Dizkus_Entity_ForumSubscriptions', $id);
        $this->entityManager->remove($subscription);
        $this->entityManager->flush();
        return true;
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
        return (int)$this->entityManager->find('Dizkus_Entity_Forums', $forum_id)->getcat_id();
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
        return $this->entityManager->find('Dizkus_Entity_Forums', $forum_id)->toArray();
    }


    /**
     * getForumTree
     *
     * Determines the forum tree.
     *
     * @return array
     */
    public function getTree()
    {
        $parents = array();
        $categories = ModUtil::apiFunc('Dizkus', 'Category', 'getAll');
        foreach ($categories as $key => $category) {
            $parents[] = array(
                'id'        => $category['cat_id'],
                'name'      => $category['cat_title'],
                'subforums' => $this->getSubTree($category['cat_id'], true)
            );
        }
        return $parents;
    }

    /**
     * getForumTree
     *
     * Determines a forum subtree.
     *
     * @return array
     */
    private function getSubTree($parent_id, $category = false)
    {

        if ($category) {
            $find = array('cat_id' => $parent_id, 'parent_id' => 0);
        } else {
            $find = array('parent_id' => $parent_id);
        }

        $output = array();
        $forums = $this->entityManager->getRepository('Dizkus_Entity_Forums')->findBy($find, array('forum_order' => 'ASC'));
        foreach ($forums as $forum) {
            $output[] = array(
                'id'        => $forum->getforum_id(),
                'name'      => $forum->getforum_name(),
                'subforums' => $this->getSubTree($forum->getforum_id()),
            );
        }
        return $output;
    }

    /**
     * getForumTree
     *
     * Determines the forum tree.
     *
     * @return array
     */
    public function getTreeAsDropdownList($showCategories = true)
    {
        $parents = array();

        if (is_array($showCategories) && count($showCategories) == 0) {
            $categories = ModUtil::apiFunc('Dizkus', 'Category', 'getAll');
            foreach ($categories as $category) {
                $parents[] = array('value' => 'c'.$category['cat_id'], 'text' => $category['cat_title']);
                $parents = array_merge($parents, $this->getSubTreeAsDropdownList($category['cat_id'], 0, true));
            }
        } else {
            $parents = array_merge($parents, $this->getSubTreeAsDropdownList(0, -1));
        }
        return $parents;
    }

    /**
     * getForumTree
     *
     * Determines a forum subtree.
     *
     * @return array
     */
    private function getSubTreeAsDropdownList($parent_id, $level, $category = false)
    {
        if ($category) {
            $find = array('cat_id' => $parent_id, 'parent_id' => 0);
        } else {
            $find = array('parent_id' => $parent_id);
        }

        $output = array();
        $forums = $this->entityManager->getRepository('Dizkus_Entity_Forums')->findBy($find);
        foreach ($forums as $forum) {
            $output[] = array(
                         'value' => $forum->getforum_id(),
                         'text' => str_repeat("--", $level+1).$forum->getforum_name()
                        );
            $output = array_merge($output, $this->getSubTreeAsDropdownList($forum->getforum_id(),$level+1));
        }
        return $output;
    }

    /**
     * getForumTree
     *
     * Determines a forum subtree.
     *
     * @return array
     */
    public function getHighestOrder($parentId)
    {
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('MAX(f.forum_order)')
            ->from('Dizkus_Entity_Forums', 'f')
            ->where('f.parent_id = :parentId')
            ->setParameter('parentId', $parentId);
        $highestOrder = $qb->getQuery()->getArrayResult();
        if (!$highestOrder) {
            return 1;
        } else {
            return $highestOrder[0][1]+1;
        }

    }

    /**
     * get forum bread crumbs
     *
     * @param array $args The argument array.
     *
     * @return strimg
     */
    public function getBreadcrumbs($args)
    {
        $breadcrumbs = array();
        $view = Zikula_View::getInstance('Dizkus');


        if (!isset($args['parent'])) {
            return $view->fetch('user/breadcrumbs.tpl');
        }

        $f = $this->request->query->get('func', null);

        if ($f == 'movetopic' || $f == 'deletetopic') {
            $topic = ModUtil::apiFunc($this->name, 'Topic', 'read0', $args['parent']);
            $breadcrumbs[] = array(
                'url'   => ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $topic['topic_id'])),
                'title' => $topic['topic_title']
            );
            $forum = array('parent_id' => $topic['forum_id']);
        } else if (is_array($args['parent'])) {
            $forum = $args['parent'];
        } else {
            $forum = array('parent_id' => $args['parent']);
        }

        $i = 0;
        while ($forum['parent_id'] != 0 && $i < 10) {

            $forum = $this->getForum($forum['parent_id']);
            $breadcrumbs[] = array(
                'url'   => ModUtil::url($this->name, 'user', 'viewforum', array('forum' => $forum['forum_id'])),
                'title' => $forum['forum_name']
            );
            $i++;

        }

        $category = ModUtil::apiFunc($this->name, 'Category', 'get', $forum['cat_id']);
        $breadcrumbs[] = array(
            'url'   => ModUtil::url($this->name, 'user', 'main', array('viewcat' => $forum['cat_id'])),
            'title' => $category['cat_title']
        );

        return $view->assign('breadcrumbs', array_reverse($breadcrumbs))
                    ->assign('templatetitle', $args['templatetitle'])
                    ->fetch('user/breadcrumbs.tpl');
    }

    /**
     * delete forum
     *
     * @return array
     */
    public function delete($forum)
    {
        // check if it input is a doctrine array
        if (!is_array($forum)) {
            $forum = $this->entityManager->getRepository('Dizkus_Entity_Forums')->findOneBy($forum);
        }

        // delete sub forums
        $find = array('parent_id' => $forum->getforum_id());
        $subforums = $this->entityManager->getRepository('Dizkus_Entity_Forums')->findBy($find);
        foreach ($subforums as $subforum) {
            $this->delete($subforum);
        }

        // delete topics
        $this->deleteChildTopics($forum->getforum_id());

        // delete forum
        $this->entityManager->remove($forum);

        $this->entityManager->flush();
    }

    /**
     * delete child topics
     *
     * @return array
     */
    public function deleteChildTopics($forum_id)
    {
        $find = array('forum_id' => $forum->getforum_id());
        $topics = $this->entityManager->getRepository('Dizkus_Entity_Topics')->findBy($find);
        foreach ($topics as $topic) {
            ModUtil::apiFunc($this->name, 'Topic', 'delete', $topic);
        }

    }
}
