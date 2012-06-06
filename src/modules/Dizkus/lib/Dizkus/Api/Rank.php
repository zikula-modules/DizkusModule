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
 * This class provides the rank api functions
 */
class Dizkus_Api_Rank extends Zikula_AbstractApi {


    /**
     * getById
     *
     * Get a rank by its id.
     *
     * @param int $id The rank id.
     *
     * @return array The rank array
     */
    public function getById($id) {
        return $this->entityManager->find('Dizkus_Entity_Ranks', $id)->toArray();
    }

}