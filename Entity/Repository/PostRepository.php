<?php/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
use Doctrine\ORM\EntityRepository;

namespace Dizkus\Entity\Repository;

class PostRepository extends \EntityRepository
{
    /**
     * Delete a post via dql
     * avoids cascading deletion errors
     * but does not deleted associations
     *
     * @param integer $id
     */
    public function manualDelete($id)
    {
        $dql = 'DELETE Dizkus_Entity_Post p
            WHERE p.post_id = :id';
        $this->_em->createQuery($dql)->setParameter('id', $id)->execute();
    }

}
