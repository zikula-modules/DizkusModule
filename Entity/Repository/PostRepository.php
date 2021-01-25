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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PostRepository extends EntityRepository
{
    protected $postManagementService;

    /**
     * Delete a post via dql
     * avoids cascading deletion errors
     * but does not deleted associations
     *
     * @param integer $id
     */
    public function manualDelete($id)
    {
        $dql = 'DELETE Zikula\DizkusModule\Entity\PostEntity p
            WHERE p.post_id = :id';
        $this->_em->createQuery($dql)->setParameter('id', $id)->execute();
    }

    /**
     * Set post manager
     *
     * @param PostManager $postManagementService
     */
    public function setManager(\Zikula\DizkusModule\Manager\PostManager $postManagementService)
    {
        $this->postManagementService = $postManagementService;

        return $this;
    }

    /**
     * gets the last $maxPosts postings of forum $forum_id.
     *
     * @param int limit number of posts to read, default = 5
     * @param int user last postings of user
     * @param array forums limit to forums in array
     *
     * @return array $lastposts
     */
    public function getManagedPosts($since = null, $page = 1, $limit = 5, $user = null, $forums = null)
    {
        if (!$this->postManagementService instanceof \Zikula\DizkusModule\Manager\PostManager) {
            return [];
        }

        list($posts, $pager) = $this->getPosts($since, $page, $limit, $user, $forums);
        $managedPosts = new ArrayCollection();
        foreach ($posts->getIterator() as $key => $post) {
            $postManager = $this->postManagementService->getManager(null, $post);
            $managedPosts->set($key, $postManager);
        }

        return [$managedPosts, $pager];
    }

    /**
     * gets the last $maxPosts postings of forum $forum_id.
     *
     * @param int limit number of posts to read, default = 5
     * @param int user last postings of user
     * @param array forums limit to forums in array
     *
     * @return array $lastposts
     */
    public function getPosts($since = null, $page = 1, $limit = 5, $user = null, $forums = null)
    {
        // hard limit to 100 to be safe
        //$limit = ($limit > 100) ? 100 : $limit;

        $qb = $this->_em->createQueryBuilder();
        $qb->select(['p', 'fu', 't', 'f'])
            ->from('Zikula\DizkusModule\Entity\PostEntity', 'p')
            ->leftJoin('p.topic', 't')
            ->leftJoin('t.forum', 'f')
            ->innerJoin('p.poster', 'fu');

        // sql part per selected time frame
        switch ($since) {
            case null:
                break;
            case '24':
                // today
                $qb->where('p.post_time > :wheretime')->setParameter('wheretime', new \DateTime('today'));

                break;
            case '48':
                // since yesterday
                $qb->where('p.post_time > :wheretime')->setParameter('wheretime', new \DateTime('yesterday'));

                break;
            case '168':
                // lastweek
                $qb->where('p.post_time > :wheretime')->setParameter('wheretime', new \DateTime('-1 week'));

                break;
            default:
                // since
                $qb->where('p.post_time > :wheretime')->setParameter('wheretime', (new \DateTime())->modify('-' . $since . ' hours'));
        }

        if (!empty($forums)) {
            $qb->andWhere('t.forum IN (:forums)')
                ->setParameter('forums', $forums);
        }

        if (!empty($user)) {
            $qb->andWhere('fu.user = :id')
                ->setParameter('id', $user);
        }

        $qb->orderBy('p.post_time', 'DESC');

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)->setMaxResults($limit);
        $posts = new Paginator($qb);
        $pager = [
            'numitems'     => $posts->count(),
            'itemsperpage' => $limit, ];

        return [$posts, $pager];
    }
}
