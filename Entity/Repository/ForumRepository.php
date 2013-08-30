<?php/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

namespace Dizkus\Entity\Repository;

class ForumRepository extends \NestedTreeRepository
{
    public function getRssForums()
    {
        $dql = 'SELECT f FROM Dizkus_Entity_Forum f
                WHERE f.pop3Connection IS NOT NULL';
        $query = $this->_em->createQuery($dql);
        try {
            $result = $query->getResult();
        } catch (Exception $e) {
            echo '<pre>';
            var_dump($e->getMessage());
            var_dump($query->getDQL());
            var_dump($query->getParameters());
            var_dump($query->getSQL());
            die;
        }

        return $result;
    }

}
