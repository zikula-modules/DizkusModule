<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class ForumUserRepository extends EntityRepository
{
    /**
     * Get an array of users where uname matching text fragment(s)
     *
     * @param array $fragments
     * @param int   $limit
     *
     * @return array
     */
    public function getUsersByFragments($fragments = [], $limit = -1)
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Zikula\\UsersModule\\Entity\\UserEntity', 'u');
        $rsm->addFieldResult('u', 'uname', 'uname');
        $rsm->addFieldResult('u', 'uid', 'uid');
        $sql = 'SELECT u.uid, u.uname FROM users u WHERE ';
        $subSql = [];
        foreach ($fragments as $fragment) {
            $subSql[] = 'u.uname REGEXP \'(' . $fragment . ')\'';
        }
        $sql .= implode(' OR ', $subSql);
        $sql .= ' ORDER BY u.uname ASC';
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        $users = $this->_em->createNativeQuery($sql, $rsm)->getResult();

        return $users;
    }

    /**
     * Gets the top max posters users depending on their post count
     *
     * @param int limit     number of users to read, default = 3
     * @param int months    number months back to search, default = 6
     *
     * @return array $posters
     */
    public function getTopPosters($limit = 3, $months = 6)
    {
        $qb = $this->_em->createQueryBuilder();
        $timePeriod = new \DateTime();
        $timePeriod->modify("-$months months");
        $qb->select('u')
            ->from('Zikula\DizkusModule\Entity\ForumUserEntity', 'u')
            ->where('u.user_id > 1')
            ->andWhere('u.lastvisit > :timeperiod')
            ->setParameter('timeperiod', $timePeriod)
            ->orderBy('u.postCount', 'DESC');
        $qb->setMaxResults($limit);
        $posters = $qb->getQuery()->getResult();

        return $posters;
    }

    /**
     * Read the users who are online
     * This function returns an array (ig assign is used) or four variables
     * numguests : number of guests online
     * numusers: number of users online
     * total: numguests + numusers
     * unames: array of 'uid', (int, userid), 'uname' (string, username) and 'admin' (boolean, true if users is a moderator)
     * Available parameters:
     *   - checkgroups:  If set, checks if the users found are in the moderator groups (perforance issue!) default is no group check.
     *
     * @author       Frank Chestnut
     *
     * @since        10/10/2005
     *
     * @param array                   $params All attributes passed to this function from the template
     * @param object      Zikula_View $view   Reference to the Smarty object
     *
     * @return array
     */
    public function onlineUsers($checkgroups = false)
    {
        // set some defaults
        $numguests = 0;
        $numusers = 0;
        $unames = [];

//      Moderators
//        $mods = ['users' => [], 'groups' => []];
//        if ($forum_id !== false) {
//            // get array of parents
//            $forum = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->find($forum_id);
//            $conn = $this->entityManager->getConnection();
//            // resort to brute SQL because no easy DQL way here.
//            $sql = "SELECT parent FROM dizkus_forums WHERE forum_order <= {$forum->getLft()} AND rgt >= {$forum->getRgt()} AND parent > 1";
//            $parents = $conn->fetchAll($sql);
//        }
//        // get moderator users
//        $qb = $this->entityManager->createQueryBuilder();
//        $qb->select('m')->from('Zikula\DizkusModule\Entity\ModeratorUserEntity', 'm')->leftJoin('m.forumUser', 'u');
//        if ($forum_id !== false) {
//            $qb->where('m.forum = :forum')->setParameter('forum', $forum_id);
//            // check parents also
//            if (!empty($parents)) {
//                $qb->orWhere('m.forum IN (:forums)')->setParameter('forums', $parents);
//            }
//        } else {
//            $qb->groupBy('m.forumUser');
//        }
//        $moderatorUserCollection = $qb->getQuery()->getResult();
//        if (is_array($moderatorUserCollection) && !empty($moderatorUserCollection)) {
//            foreach ($moderatorUserCollection as $moderatorUser) {
//                $coreUser = $moderatorUser->getForumUser()->getUser();
//                $mods['users'][$coreUser['uid']] = $coreUser['uname'];
//            }
//        }
//        // get moderator groups
//        $qb = $this->entityManager->createQueryBuilder();
//        $qb->select('m')->from('Zikula\DizkusModule\Entity\ModeratorGroupEntity', 'm')->leftJoin('m.group', 'g');
//        if ($forum_id !== false) {
//            $qb->where('m.forum = :forum')->setParameter('forum', $forum_id);
//            // check parents also
//            if (!empty($parents)) {
//                $qb->orWhere('m.forum IN (:forums)')->setParameter('forums', $parents);
//            }
//        } else {
//            $qb->groupBy('m.group');
//        }
//        $moderatorGroupCollection = $qb->getQuery()->getResult();
//        if (is_array($moderatorGroupCollection) && !empty($moderatorGroupCollection)) {
//            foreach ($moderatorGroupCollection as $moderatorGroup) {
//                $mods['groups'][$moderatorGroup->getGroup()->getGid()] = $moderatorGroup->getGroup()->getName();
//            }
//        }
//
//        return $mods;

        /** @var $em Doctrine\ORM\EntityManager */
        $dql = "SELECT s.uid, u.uname
                FROM Zikula\UsersModule\Entity\UserSessionEntity s, Zikula\UsersModule\Entity\UserEntity u
                WHERE s.lastused > :activetime
                AND (s.uid >= 2
                AND s.uid = u.uid)
                OR s.uid = 0
                GROUP BY s.ipaddr, s.uid";
        $query = $this->container->get('doctrine.orm.entity_manager')->createQuery($dql);
        $activetime = new \DateTime(); // @todo maybe need to check TZ here
        $activetime->modify('-'.$this->getSystemSetting('secinactivemins').' minutes');
        $query->setParameter('activetime', $activetime);

        $onlineusers = $query->getArrayResult();

        $total = 0;
        if (is_array($onlineusers)) {
            $total = count($onlineusers);
            foreach ($onlineusers as $onlineuser) {
                if ($onlineuser['uid'] != 0) {
                    $params['user_id'] = $onlineuser['uid'];
                    $onlineuser['admin'] = (isset($moderators['users'][$onlineuser['uid']])
                    && $moderators['users'][$onlineuser['uid']] == $onlineuser['uname'])
                    || $this->container->get('zikula_dizkus_module.security')->canAdministrate($params);
                    $unames[$onlineuser['uid']] = $onlineuser;
                    $numusers++;
                } else {
                    $numguests++;
                }
            }
        }

        $users = [];
        if ($checkgroups == true) {
            foreach ($unames as $user) {
                if ($user['admin'] == false) {
                    // @todo use service when ready
                    $groups = ModUtil::apiFunc('Groups', 'user', 'getusergroups', ['uid' => $user['uid']]);

                    foreach ($groups as $group) {
                        if (isset($moderators['groups'][$group['gid']])) {
                            $user['admin'] = true;
                        } else {
                            $user['admin'] = false;
                        }
                    }
                }

                $users[$user['uid']] = [
                    'uid'   => $user['uid'],
                    'uname' => $user['uname'],
                    'admin' => $user['admin'], ];
            }
            $unames = $users;
        }
        usort($unames, [$this, 'cmp_userorder']);

        $dizkusonline['numguests'] = $numguests;

        $dizkusonline['numusers'] = $numusers;
        $dizkusonline['total'] = $total;
        $dizkusonline['unames'] = $unames;

        return $dizkusonline;
    }

    /**
     * Sorting user lists by ['uname']
     */
    private function cmp_userorder($a, $b)
    {
        return strcmp($a['uname'], $b['uname']);
    }
}
