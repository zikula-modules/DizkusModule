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
    public function getParents($id = null)
    {
        $repo = $this->entityManager->getRepository('Dizkus_Entity_Forums');
        $parents = $repo->childrenHierarchy();
        $output = $this->getNode($parents, $id);

        return $output;
    }

    private function getNode($input, $id, $level = 0)
    {
        $pre = str_repeat("-", $level*2);
        $output = array();
        foreach ($input as $i) {
            if ($id != $i['forum_id']) {
                $output[] = array(
                    'value' => $i['forum_id'],
                    'text'  => $pre.$i['forum_name']
                );
                if (isset($i['__children'])) {
                    $output = array_merge($output, $this->getNode($i['__children'], $id, $level+1));
                }
            }
        }

        return $output;
    }

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
