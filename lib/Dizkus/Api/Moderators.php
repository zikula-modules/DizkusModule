<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * This class provides the moderators api functions
 */
class Dizkus_Api_Moderators extends Zikula_AbstractApi
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
        $em = $this->getService('doctrine.entitymanager');
        $mods = array('users' => array(), 
            'groups' => array());
        
        // get moderator users
        $qb = $em->createQueryBuilder();
        $qb->select('m')
            ->from('Dizkus_Entity_Moderator_User', 'm')
            ->leftJoin('m.forumUser', 'u');
        if (!empty($forum_id)) {
            $qb->andWhere('m.forum = :forum')
                ->setParameter('forum', $forum_id);
        } else {
            $qb->groupBy('m.forumUser');
        }
        $moderatorUserCollection = $qb->getQuery()->getResult();

        if (is_array($moderatorUserCollection) && !empty($moderatorUserCollection)) {
            foreach ($moderatorUserCollection as $moderatorUser) {
                $mods['users'][$moderatorUser->getForumUser()->getUser()->getUid()] = $moderatorUser->getForumUser()->getUser()->getUname();
            }
        }

        // get moderator groups
        $qb = $em->createQueryBuilder();
        $qb->select('m')
            ->from('Dizkus_Entity_Moderator_Group', 'm')
            ->leftJoin('m.group', 'g');
        if (!empty($forum_id)) {
            $qb->andWhere('m.forum = :forum')
                ->setParameter('forum', $forum_id);
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