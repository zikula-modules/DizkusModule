<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Api;

use LogUtil;
use SecurityUtil;
use Zikula\Module\DizkusModule\Entity\RankEntity;
use Zikula\Module\DizkusModule\Manager\ForumUserManager;

/**
 * This class provides the rank api functions
 */
class RankApi extends \Zikula_AbstractApi
{

    private $_userRanks = array();

    /**
     * Get all ranks
     *
     * @param array $args Arguments array.
     *
     * @return array
     *
     */
    public function getAll($args)
    {
        // read images
        $path = $this->getVar('url_ranks_images');
        $handle = opendir($path);
        $filelist = array();
        while ($file = readdir($handle)) {
            if ($this->dzk_isimagefile($path . '/' . $file)) {
                $filelist[] = $file;
            }
        }
        asort($filelist);
        if ($args['ranktype'] == RankEntity::TYPE_POSTCOUNT) {
            $orderby = 'minimumCount';
        } else {
            $orderby = 'title';
        }
        $ranks = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\RankEntity')
            ->findBy(array('type' => $args['ranktype']), array($orderby => 'ASC'));

        return array($filelist, $ranks);
    }

    /**
     * Modify a rank
     *
     * @param array $args Argument array
     *
     * @return boolean
     */
    public function save($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        //title, description, minimumCount, maximumCount, type, image
        foreach ($args['ranks'] as $rankid => $rank) {
            if ($rankid == '-1') {
                $r = new RankEntity();
                $r->merge($rank);
                $this->entityManager->persist($r);
            } else {
                $r = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\RankEntity', $rankid);
                if ((isset($rank['rank_delete'])) && ($rank['rank_delete'] == '1')) {
                    $this->entityManager->remove($r);
                } else {
                    $r->merge($rank);
                    $this->entityManager->persist($r);
                }
            }
        }
        $this->entityManager->flush();

        return true;
    }

    /**
     * assignranksave
     *
     * setrank array(uid) = rank_id
     */
    public function assign($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        if (is_array($args['setrank'])) {
            foreach ($args['setrank'] as $userId => $rankId) {
                $rankId = $rankId == 0 ? null : $rankId;
                $managedForumUser = new ForumUserManager($userId);
                if (isset($rankId)) {
                    $rank = $this->entityManager->getReference('Zikula\Module\DizkusModule\Entity\RankEntity', $rankId);
                    $managedForumUser->get()->setRank($rank);
                } else {
                    $managedForumUser->get()->clearRank();
                }
            }
            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * Get user rank data
     *
     * @param array $args Arguments array.
     *
     * @return array The rank data of the poster
     */
    public function getData($args)
    {
        $data = array();
        if (!isset($args['poster'])) {
            return $data;
        }
        // user has assigned rank
        $userRank = $args['poster']->getRank();
        if (isset($userRank)) {
            $data = $userRank->toArray();

            return $data = $this->addImageAndLink($data);
        }
        // check if rank by number of posts is cached
        $uid = $args['poster']->getUser_id();
        if (array_key_exists($uid, $this->_userRanks)) {
            return $this->_userRanks[$uid];
        }
        // get rank by number of post
        $userRank = $this->entityManager
            ->createQueryBuilder()
            ->select('r')
            ->from('Zikula\Module\DizkusModule\Entity\RankEntity', 'r')
            ->where('r.minimumCount <= :posts and r.maximumCount >= :posts')
            ->setParameter('posts', $args['poster']->getPostCount())
            ->getQuery()
            ->setMaxResults(1)
            ->getArrayResult();
        if (isset($userRank[0])) {
            $data = $this->addImageAndLink($userRank[0]);
        }
        // cache rank by number of posts
        $this->_userRanks[$uid] = $data;

        return $data;
    }

    private function addImageAndLink($data)
    {
        $data['rank_link'] = substr($data['description'], 0, 7) == 'http://' ? $data['description'] : '';
        if (!empty($data['image'])) {
            $data['image'] = $this->getVar('url_ranks_images') . '/' . $data['image'];
            $data['image_attr'] = function_exists('getimagesize') ? @getimagesize($data['image']) : null;
        }

        return $data;
    }

    /**
     * dzk is an image
     * check if a filename is an image or not
     */
    private function dzk_isimagefile($filepath)
    {
        if (function_exists('getimagesize') && @getimagesize($filepath) != false) {
            return true;
        }
        if (preg_match('/^(.*)\\.(gif|jpg|jpeg|png)/si', $filepath)) {
            return true;
        }

        return false;
    }

}
