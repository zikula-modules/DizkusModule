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


    /**
     * addToUserData
     *
     * Add rank infos to the userdata.
     *
     * @param array $userdata The userdata.
     *
     * @return array The userdata with the rank information
     */
    public function addToUserData($userdata) {
        $ztable = DBUtil::getTables();

        $rank = null;
        if ($userdata['user_rank'] != 0) {
            $rank = ModUtil::apiFunc($this->name, 'Rank', 'getById', $userdata['user_rank']);

        } elseif ($userdata['user_posts'] != 0) {
            $where =        $ztable['dizkus_ranks_column']['rank_min'].' <= '.(int)DataUtil::formatForStore($userdata['user_posts']).'
                      AND '.$ztable['dizkus_ranks_column']['rank_max'].' >= '.(int)DataUtil::formatForStore($userdata['user_posts']);

            $rank = DBUtil::selectObject('dizkus_ranks', $where);
        }

        if (is_array($rank)) {
            $userdata = array_merge($userdata, $rank);
            $userdata['rank'] = $userdata['rank_title']; // backwards compatibility
            $userdata['rank_link'] = (substr($userdata['rank_desc'], 0, 7) == 'http://') ? $userdata['rank_desc'] : '';
            if ($userdata['rank_image']) {
                $userdata['rank_image']      = ModUtil::getVar('Dizkus', 'url_ranks_images') . '/' . $userdata['rank_image'];
                $userdata['rank_image_attr'] = function_exists('getimagesize') ? @getimagesize($userdata['rank_image']) : null;
            }
        }
        return $userdata;
    }

}