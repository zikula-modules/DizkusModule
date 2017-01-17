<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Helper;

use Doctrine\ORM\EntityManager;

/**
 * FavoritesHelper.
 *
 * @author Kaik
 */
class ModeratorsHelper
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
            EntityManager $entityManager
         ) {
        $this->name = 'ZikulaDizkusModule';
        $this->entityManager = $entityManager;
    }

    /**
     * Returns an array of all the moderators of a forum (including groups).
     *
     * @param int $forum_id Forum id.
     *
     * @return array containing the uid/gid as index and the user/group name as value
     */
    public function get($forum_id = false)
    {
        $mods = ['users' => [], 'groups' => []];
        if ($forum_id !== false) {
            // get array of parents
            $forum = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->find($forum_id);
            $conn = $this->entityManager->getConnection();
            // resort to brute SQL because no easy DQL way here.
            $sql = "SELECT parent FROM dizkus_forums WHERE forum_order <= {$forum->getLft()} AND rgt >= {$forum->getRgt()} AND parent > 1";
            $parents = $conn->fetchAll($sql);
        }
        // get moderator users
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('m')->from('Zikula\DizkusModule\Entity\ModeratorUserEntity', 'm')->leftJoin('m.forumUser', 'u');
        if ($forum_id !== false) {
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
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('m')->from('Zikula\DizkusModule\Entity\ModeratorGroupEntity', 'm')->leftJoin('m.group', 'g');
        if ($forum_id !== false) {
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
