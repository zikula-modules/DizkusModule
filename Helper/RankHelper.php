<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Helper;

use Doctrine\ORM\EntityManager;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Security\Permission;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * CronHelper.
 *
 * @author Kaik
 */
class RankHelper
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Permission
     */
    private $permission;

    /**
     * @var VariableApi
     */
    private $variableApi;

    private $_userRanks = [];

    private $imagesPath;

    public function __construct(
            EntityManager $entityManager,
            Permission $permission,
            VariableApi $variableApi
         ) {
        $this->name = 'ZikulaDizkusModule';
        $this->entityManager = $entityManager;
        $this->permission = $permission;
        $this->variableApi = $variableApi;

        $this->imagesPath = $this->variableApi->get($this->name, 'url_ranks_images');
    }

    /**
     * Get all ranks.
     *
     * @param array ranktype
     *
     * @return array
     */
    public function getAll($ranktype)
    {
        if (RankEntity::TYPE_POSTCOUNT == $ranktype) {
            $orderby = 'minimumCount';
        } else {
            $orderby = 'title';
        }
        $ranks = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\RankEntity')
            ->findBy(['type' => $ranktype], [$orderby => 'ASC']);

        return $ranks;
    }

    /**
     * Get all ranks.
     *
     * @param array ranktype
     *
     * @return array
     */
    public function getAllRankImages()
    {
        // read images
        $handle = opendir(\Zikula\DizkusModule\DizkusModuleInstaller::generateRelativePath().'/Resources/public/images/'.$this->imagesPath);
        $filelist = [];
        while ($file = readdir($handle)) {
            if ($this->isImageFile($this->imagesPath.'/'.$file)) {
                $filelist[] = $file;
            }
        }
        asort($filelist);

        return $filelist;
    }

    /**
     * Modify a rank.
     *
     * @param array $args Argument array
     *
     * @return bool
     */
    public function save($args)
    {
        if (!$this->permission->canAdministrate()) {
            throw new AccessDeniedException();
        }
        //title, description, minimumCount, maximumCount, type, image
        foreach ($args['ranks'] as $rankid => $rank) {
            if ('-1' == $rankid) {
                $r = new RankEntity();
                $r->merge($rank);
                $this->entityManager->persist($r);
            } else {
                $r = $this->entityManager->find('Zikula\DizkusModule\Entity\RankEntity', $rankid);
                if ((isset($rank['rank_delete'])) && ('1' == $rank['rank_delete'])) {
                    $this->entityManager->remove($r);
                    // update users that are assigned the rank to null
                    $users = $this->entityManager->getRepository('ZikulaDizkusModule:RankEntity')->findBy(['rank' => $rankid]);
                    foreach ($users as $user) {
                        $user->clearRank();
                    }
                } else {
                    $r->merge($rank);
                    $this->entityManager->persist($r);
                }
            }
        }
        $this->entityManager->flush();

        return true;
    }

    public function getImageLink($image)
    {
        return $this->imagesPath . '/' . $image;
    }

    /**
     * Get user rank data.
     *
     * @param array $args arguments array
     *
     * @return array The rank data of the poster
     */
    public function getData($args)
    {
        $data = [];
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
            ->from('Zikula\DizkusModule\Entity\RankEntity', 'r')
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
        $data['rank_link'] = 'http://' == substr($data['description'], 0, 7) ? $data['description'] : '';
        if (!empty($data['image'])) {
            $data['image'] = $this->getVar('url_ranks_images').'/'.$data['image'];
            $data['image_attr'] = function_exists('getimagesize') ? @getimagesize($data['image']) : null;
        }

        return $data;
    }

    /**
     * dzk is an image
     * check if a filename is an image or not.
     */
    private function isImageFile($filepath)
    {
        if (function_exists('getimagesize') && false != @getimagesize($filepath)) {
            return true;
        }
        if (preg_match('/^(.*)\\.(gif|jpg|jpeg|png)/si', $filepath)) {
            return true;
        }

        return false;
    }
}
