<?php

declare(strict_types=1);

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
        $timePeriod->modify("-${months} months");
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
     * This function returns an array
     * numguests : number of guests online
     * numusers: number of users online
     * total: numguests + numusers
     *
     * @param int $secinactivemins Time interval
     * @param bool $moderatorCheck Moderator check setting
     *
     * @author       Frank Chestnut
     *
     * @since        10/10/2005
     *
     * @return array
     */
    public function getOnlineUsers($secinactivemins = 20, $moderatorCheck = false)
    {
        // set some defaults
        $numguests = 0;
        $numusers = 0;
        $unames = [];

        if ($moderatorCheck) {
            $moderators = ['users' => [], 'groups' => []];
            // get moderator users
            $qb = $this->_em->createQueryBuilder();
            $qb->select('m')->from('Zikula\DizkusModule\Entity\ModeratorUserEntity', 'm')->leftJoin('m.forumUser', 'u');
            $qb->groupBy('m.forumUser');
            $moderatorUserCollection = $qb->getQuery()->getResult();
            if (is_array($moderatorUserCollection) && !empty($moderatorUserCollection)) {
                foreach ($moderatorUserCollection as $moderatorUser) {
                    $coreUser = $moderatorUser->getForumUser()->getUser();
                    $moderators['users'][$coreUser['uid']] = $coreUser['uname'];
                }
            }
            // get moderator groups
            $qb = $this->_em->createQueryBuilder();
            $qb->select('m')->from('Zikula\DizkusModule\Entity\ModeratorGroupEntity', 'm')->leftJoin('m.group', 'g');
            $qb->groupBy('m.group');
            $moderatorGroupCollection = $qb->getQuery()->getResult();
            if (is_array($moderatorGroupCollection) && !empty($moderatorGroupCollection)) {
                foreach ($moderatorGroupCollection as $moderatorGroup) {
                    $moderators['groups'][$moderatorGroup->getGroup()->getGid()] = $moderatorGroup->getGroup()->getName();
                }
            }
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->select(['s.uid', 'u', 'g'])
            ->from('Zikula\UsersModule\Entity\UserSessionEntity', 's')
            ->leftJoin(
            'Zikula\UsersModule\Entity\UserEntity',
            'u',
            \Doctrine\ORM\Query\Expr\Join::WITH,
            's.uid = u.uid')
            ->leftJoin('u.groups', 'g')
            ->where('s.lastused > :activetime')
            ->andWhere('s.uid >= 2')
            ->orWhere('s.uid = 0')
            ->groupBy('s.ipaddr, s.uid')
            ;

        $activetime = new \DateTime(); // @todo maybe need to check TZ here
        $activetime->modify('-' . $secinactivemins . ' minutes');
        $qb->setParameter('activetime', $activetime);

        $onlineusers = $qb->getQuery()->getResult();

        $total = 0;
        if (is_array($onlineusers)) {
            $total = count($onlineusers);
            foreach ($onlineusers as $onlineuser) {
                if (0 !== $onlineuser['uid']) {
                    $unames[$onlineuser['uid']]['user'] = $onlineuser[0];
                    $unames[$onlineuser['uid']]['isModerator'] = false;
                    if ($moderatorCheck) {
                        if (array_key_exists($onlineuser['uid'], $moderators['users'])) {
                            $unames[$onlineuser['uid']]['isModerator'] = true;
                        }
                        if (count($moderators['groups']) > 0) {
                            foreach ($moderators['groups'] as $gKey => $gName) {
                                $unames[$onlineuser['uid']]['isModerator'] = $onlineuser[0]->getGroups()->offsetExists($gKey);
                            }
                        }
                    }
                    $numusers++;
                } else {
                    $numguests++;
                }
            }
        }

        usort($unames, [$this, 'cmp_userorder']);

        $dizkusonline['numguests'] = $numguests;

        $dizkusonline['numusers'] = $numusers;
        $dizkusonline['total'] = $total;
        $dizkusonline['users'] = $unames;

        return $dizkusonline;
    }

    /**
     * Sorting user lists by ['uname']
     */
    private function cmp_userorder($a, $b)
    {
        return strcmp($b['user']->getUname(), $a['user']->getUname());
    }
}
