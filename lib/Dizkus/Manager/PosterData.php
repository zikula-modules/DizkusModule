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
class Dizkus_Manager_PosterData
{

    /**
     * managed poster
     * @var Dizkus_Entity_ForumUser
     */
    private $_poster;
    protected $entityManager;
    protected $name;

    /**
     * construct
     */
    public function __construct($uid = null)
    {
        $this->entityManager = ServiceUtil::getService('doctrine.entitymanager');
        $this->name = 'Dizkus';

        if (empty($uid)) {
            $uid = UserUtil::getVar('uid');
        }
        $this->_poster = $this->entityManager->find('Dizkus_Entity_ForumUser', $uid);

        if (!$this->_poster) {
            $this->_poster = new Dizkus_Entity_ForumUser();
            $this->_poster->setUser_id($uid);
        }
    }

    /**
     * post_order
     *
     * @return string
     */
    public function getPostOrder()
    {
        return $this->_poster->getUser_post_order() ? 'ASC' : 'DESC';
    }

    /**
     * set post_order
     *
     * @return string
     */
    public function setPostOrder($sort)
    {
        if ($sort == 'asc') {
            $order = false;
        } else {
            $order = true;
        }
        $this->_poster->setUser_post_order($order);
        $this->entityManager->flush();
    }

    /**
     * return topic as doctrine2 object
     *
     * @return Dizkus_Entity_ForumUser
     */
    public function get()
    {
        return $this->_poster;
    }

    /**
     * return topic as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_poster->toArray();
    }

    /**
     * persist and flush
     *
     * @return void
     */
    public function store($data)
    {
        $this->_poster->merge($data);
        $this->entityManager->persist($this->_poster);
        $this->entityManager->flush();
    }

}