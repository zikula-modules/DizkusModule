<?php

/**
 * Copyright Dizkus Team 2012
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Dizkus
 * @link https://github.com/zikula-modules/Dizkus
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
use Doctrine\ORM\Tools\Pagination\Paginator;

class Dizkus_Manager_Forum
{

    private $_forum;
    private $_itemsPerPage;
    private $_numberOfItems;
    protected $entityManager;
    protected $name;

    /**
     * construct
     */
    public function __construct($id = null)
    {
        $this->entityManager = ServiceUtil::getService('doctrine.entitymanager');
        $this->name = 'Dizkus';

        if ($id > 0) {
            $this->_forum = $this->entityManager->find('Dizkus_Entity_Forum', $id);
        } else {
            $this->_forum = new Dizkus_Entity_Forum();
        }
    }

    /**
     * Check if forum exists
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->_forum ? true : false;
    }

    /**
     * Check if forum is category (lvl ==0)
     *
     * @return boolean
     */
    public function isCategory()
    {
        return $this->_forum->getLvl() == 0 ? false : true;
    }

    /**
     * return page as array
     *
     * @return mixed array or false
     */
    public function toArray()
    {
        if (!$this->_forum) {
            return false;
        }

        return $this->_forum->toArray();
    }

    /**
     * return page as array
     *
     * @return array
     */
    public function getId()
    {
        return $this->_forum->getForum_id();
    }

    /**
     * return forum as doctrine2 object
     *
     * @return object
     */
    public function get()
    {
        return $this->_forum;
    }

    public function getPermissions()
    {
        return ModUtil::apiFunc($this->name, 'Permission', 'get', $this->_forum);
    }

    /**
     * get forum bread crumbs
     *
     * @param boolean $withoutCurrent Show tree without the current item.
     *
     * @return string
     */
    public function getBreadcrumbs($withoutCurrent = true)
    {
        if ($this->_forum->getLvl() == 0) {
            // already root
            return array();
        }

        $output = array();
        if (!$withoutCurrent) {
            $url = ModUtil::url($this->name, 'user', 'main', array('viewcat' => $this->_forum->getForum_id()));
            $output[] = array(
                'url' => $url,
                'title' => $this->_forum->getForum_name()
            );
        }

        $i = $this->_forum->getParent();
        while ($i->getLvl() != 0) {
            $url = ModUtil::url($this->name, 'user', 'viewforum', array('forum' => $i->getForum_id()));
            $output[] = array(
                'url' => $url,
                'title' => $i->getForum_name()
            );
            $i = $i->getParent();
        }
        // root
        $url = ModUtil::url($this->name, 'user', 'main', array('viewcat' => $i->getForum_id()));
        $output[] = array(
            'url' => $url,
            'title' => $i->getForum_name()
        );

        return array_reverse($output);
    }

    /**
     * return posts of a forum as doctrine2 object
     *
     * @return object
     */
    public function getTopics($startNumber = 1)
    {

        $this->_itemsPerPage = ModUtil::getVar($this->name, 'posts_per_page');

        $id = $this->_forum->getForum_id();


        $query = $this->entityManager
                ->createQueryBuilder()
                ->select('p')
                ->from('Dizkus_Entity_Topic', 'p')
                ->where('p.forum = :forumId')
                ->setParameter('forumId', $id)
                ->leftJoin('p.last_post', 'l')
                ->orderBy('p.sticky', 'DESC')
                ->addOrderBy('l.post_time', 'DESC')
                ->getQuery();

        $query->setFirstResult($startNumber - 1)->setMaxResults($this->_itemsPerPage);
        $paginator = new Paginator($query);
        $this->_numberOfItems = count($paginator);

        return $paginator;
    }

    /**
     * return page as array
     *
     * @return array
     */
    public function getPager()
    {
        return array(
            'itemsperpage' => $this->_itemsPerPage,
            'numitems' => $this->_numberOfItems
        );
    }

    /**
     * return page as array
     *
     * @return array
     */
    public function incrementReadCount()
    {
        $this->_forum->incrementCounter();
        $this->entityManager->flush();
        return true;
    }

    /**
     * return page as array
     */
    public function incrementPostCount()
    {
        $this->_forum->incrementForum_posts();
        $this->entityManager->flush();
    }

    /**
     * return page as array
     */
    public function incrementTopicCount()
    {
        $this->_forum->incrementForum_topics();
        $this->entityManager->flush();
    }

    public function setLastPost($post)
    {
        $this->_forum->setLast_post($post);
        $this->entityManager->flush();
    }

    /**
     * return page as array
     *
     * @param array $data Page data.
     *
     * @return boolean
     */
    public function store($data)
    {
        $this->_forum->merge($data);
        $this->entityManager->persist($this->_forum);
        $this->entityManager->flush();
    }

    /**
     * return page as array
     *
     * @return boolean
     */
    public function remove()
    {
        $this->entityManager->remove($this->_forum);
        $this->entityManager->flush();
        return true;
    }

}