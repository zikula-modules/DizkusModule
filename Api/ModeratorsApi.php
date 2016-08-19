<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Api;

/**
 * This class provides the moderators api functions
 */
class ModeratorsApi extends \Zikula_AbstractApi
{

    /**
     * Returns an array of all the moderators of a forum (including groups)
     *
     * @param array $args Arguments array.
     *        int   $args['forum_id'] Forums id.
     *
     * @return array containing the uid/gid as index and the user/group name as value
     */
    public function get($args)
    {
        $forum_id = isset($args['forum_id']) ? $args['forum_id'] : null;
        $em = parent::get('doctrine.entitymanager');
        $mods = array('users' => array(), 'groups' => array());
        if (isset($forum_id)) {
            // get array of parents
            $forum = $em->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->find($forum_id);
            $conn = $em->getConnection();
            // resort to brute SQL because no easy DQL way here.
            $sql = "SELECT parent FROM dizkus_forums WHERE forum_order <= {$forum->getLft()} AND rgt >= {$forum->getRgt()} AND parent > 1";
            $parents = $conn->fetchAll($sql);
        }
        // get moderator users
        $qb = $em->createQueryBuilder();
        $qb->select('m')->from('Zikula\DizkusModule\Entity\ModeratorUserEntity', 'm')->leftJoin('m.forumUser', 'u');
        if (!empty($forum_id)) {
            $qb->where('m.forum = :forum')->setParameter('forum', $forum_id);
            // check parents also
            if (!empty($parents)) {
                $qb->orWhere('m.forum IN (:forums)')->setParameter('forums', $parents);
            }
        } else {
            $qb->groupBy('m.forumUser');
        }
        $moderatorUserCollection = $qb->getQuery()->getResult();
        if (is_array($moderatorUserCollection) && !empty($moderatorUserCollection)) {
            foreach ($moderatorUserCollection as $moderatorUser) {
                $coreUser = $moderatorUser->getForumUser()->getUser();
                $mods['users'][$coreUser['uid']] = $coreUser['uname'];
            }
        }
        // get moderator groups
        $qb = $em->createQueryBuilder();
        $qb->select('m')->from('Zikula\DizkusModule\Entity\ModeratorGroupEntity', 'm')->leftJoin('m.group', 'g');
        if (!empty($forum_id)) {
            $qb->where('m.forum = :forum')->setParameter('forum', $forum_id);
            // check parents also
            if (!empty($parents)) {
                $qb->orWhere('m.forum IN (:forums)')->setParameter('forums', $parents);
            }
        } else {
            $qb->groupBy('m.group');
        }
        $moderatorGroupCollection = $qb->getQuery()->getResult();
        if (is_array($moderatorGroupCollection) && !empty($moderatorGroupCollection)) {
            foreach ($moderatorGroupCollection as $moderatorGroup) {
                $mods['groups'][$moderatorGroup->getGroup()->getGid()] = $moderatorGroup->getGroup()->getName();
            }
        }

        return $mods;
    }

}
