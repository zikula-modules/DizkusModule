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
     * Returns an array of all the moderators of a forum
     *
     * @param array $args Arguments array.
     *        int   $args['forum_id'] Forums id.
     *
     * @return array containing the pn_uid as index and the users name as value
     */
    public function get($args)
    {
        return array();

        $forum_id = isset($args['forum_id']) ? $args['forum_id'] : null;

        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('u.uid, u.uname')
                ->from('Dizkus_Entity_Moderators', 'm')
                ->leftJoin('m.user_data', 'u')
                ->where('m.user_id < 1000000');

        if (!empty($forum_id)) {
            $qb->andWhere('m.forum_id = :forum_id')
                    ->setParameter('forum_id', $forum_id);
        } else {
            $qb->groupBy('m.user_id');
        }
        $result = $qb->getQuery()->getArrayResult();


        $mods = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $user) {
                $mods[$user['uid']] = $user['uname'];
            }
        }

        $ztable = DBUtil::getTables();
        if (!empty($forum_id)) {
            $sql = 'SELECT g.name, g.gid
                    FROM ' . $ztable['groups'] . ' g, ' . $ztable['dizkus_forum_mods'] . " f
                    WHERE f.forum_id = '" . DataUtil::formatForStore($forum_id) . "' AND g.gid = f.user_id-1000000
                    AND f.user_id > 1000000";
        } else {
            $sql = 'SELECT g.name, g.gid
                    FROM ' . $ztable['groups'] . ' g, ' . $ztable['dizkus_forum_mods'] . ' f
                    WHERE g.gid = f.user_id-1000000
                    AND f.user_id > 1000000
                    GROUP BY f.user_id';
        }
        $res = DBUtil::executeSQL($sql);
        $colarray = array('gname', 'gid');
        $result = DBUtil::marshallObjects($res, $colarray);

        if (is_array($result) && !empty($result)) {
            foreach ($result as $group) {
                $mods[$group['gid'] + 1000000] = $group['gname'];
            }
        }

        return $mods;
    }

}