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
     * get an array of users where uname matching text fragment(s).
     * @todo fix this
     * @param array $args['fragments']
     * @param int   $args['limit']
     *
     * @return array
     */
    public function getUsersByFragments($args)
    {
        $fragments = isset($args['fragments']) ? $args['fragments'] : null;
        $limit = isset($args['limit']) ? $args['limit'] : -1;
        if (empty($fragments)) {
            return [];
        }
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
        $users = $this->entityManager->createNativeQuery($sql, $rsm)->getResult();

        return $users;
    }

    /**
     * gets the top $maxposters users depending on their post count.
     *
     * @param mixed[] $params {
     *
     * @var int maxposters    number of users to read, default = 3
     * @var int months        number months back to search, default = 6
     *          }
     *
     * @return array $topPosters
     */
    public function getTopPosters($params)
    {
        $posterMax = (!empty($params['maxposters'])) ? $params['maxposters'] : 3;
        $months = (!empty($params['months'])) ? $params['months'] : 6;

        $qb = $this->entityManager->createQueryBuilder();
        $timePeriod = new \DateTime();
        $timePeriod->modify("-$months months");
        $qb->select('u')
            ->from('Zikula\DizkusModule\Entity\ForumUserEntity', 'u')
            ->where('u.user_id > 1')
            ->andWhere('u.lastvisit > :timeperiod')
            ->setParameter('timeperiod', $timePeriod)
            ->orderBy('u.postCount', 'DESC');
        $qb->setMaxResults($posterMax);
        $forumUsers = $qb->getQuery()->getResult();

        $topPosters = [];
        if (!empty($forumUsers)) {
            foreach ($forumUsers as $forumUser) {
                $coreUser = $forumUser->getUser();
                $topPosters[] = [
                    'user_name' => $coreUser['uname'],
                    // for BC reasons
                    'postCount' => $forumUser->getPostCount(),
                    'user_id'   => $forumUser->getUser_id(), ];
            }
        }

        return $topPosters;
    }
}
