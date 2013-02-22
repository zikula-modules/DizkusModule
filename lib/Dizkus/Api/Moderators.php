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
        return array();

        $forum_id = isset($args['forum_id']) ? $args['forum_id'] : null;
        $em = $this->getService('doctrine.entitymanager');
        $mods = array();
        
        // get moderator users
        $qb = $em->createQueryBuilder();
        $qb->select('m.forumUser')
                ->from('Dizkus_Entity_Moderator_User', 'm');;
        if (!empty($forum_id)) {
            $qb->andWhere('m.forum = :forum')
                ->setParameter('forum', $forum_id);
        } else {
            $qb->groupBy('m.forumUser');
        }
        $forumUsers = $qb->getQuery()->getResult();

        if (is_array($forumUsers) && !empty($forumUsers)) {
            foreach ($forumUsers as $forumUser) {
                $mods[$forumUser->getUser()->getUid()] = $forumUser->getUser()->getUname();
            }
        }

        // get moderator groups
        $qb = $em->createQueryBuilder();
        $qb->select('g.group')
                ->from('Dizkus_Entity_Moderator_Group', 'g');;
        if (!empty($forum_id)) {
            $qb->andWhere('g.forum = :forum')
                ->setParameter('forum', $forum_id);
        } else {
            $qb->groupBy('g.group');
        }
        $coreGroups = $qb->getQuery()->getResult();

        if (is_array($coreGroups) && !empty($coreGroups)) {
            foreach ($coreGroups as $coreGroup) {
                $mods[$coreGroup->getGid()] = $coreGroup->getName();
            }
        }

        return $mods;
    }

}