<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Entity\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ForumRepository extends NestedTreeRepository
{
    public function getRssForums()
    {
        $dql = 'SELECT f FROM Zikula\DizkusModule\Entity\ForumEntity f
                WHERE f.pop3Connection IS NOT NULL';
        $query = $this->_em->createQuery($dql);
        try {
            $result = $query->getResult();
        } catch (\Exception $e) {
            echo '<pre>';
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }

        return $result;
    }
    // hmm free topics?
    public function countForumTopics($forum = 0)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $query = $this->_em->createQuery('SELECT COUNT(t.topic_id) FROM Zikula\DizkusModule\Entity\TopicEntity t WHERE t.forum=:forum');
        $query->setParameter('forum', $forum);
        $count = $query->getSingleScalarResult();

        return $count;
    }

    /**
     * gets the last $maxforums forums.
     *
     * @param mixed[] $params {
     * @var int maxforums    number of forums to read, default = 5
     *                        }
     *
     * @return array $topForums
     *
     * @todo fix
     */
    public function getTopForums($params)
    {
        $forumMax = (!empty($params['maxforums'])) ? $params['maxforums'] : 5;

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('f')
            ->from('Zikula\DizkusModule\Entity\ForumEntity', 'f')
            ->orderBy('f.lvl', 'DESC')
            ->addOrderBy('f.postCount', 'DESC');
        $qb->setMaxResults($forumMax);
        $forums = $qb->getQuery()->getResult();

        $topForums = [];
        if (!empty($forums)) {
            foreach ($forums as $forum) {
                if ($this->permission->canRead($forum)) {
                    $topforum = $forum->toArray();
                    $topforum['name'] = $forum->getName();
                    $parent = $forum->getParent();
                    $parentName = isset($parent) ? $parent->getName() : $this->translator->__('Root');
                    $topforum['cat_title'] = $parentName;
                    array_push($topForums, $topforum);
                }
            }
        }

        return $topForums;
    }
}
