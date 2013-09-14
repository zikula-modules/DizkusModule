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

namespace Zikula\Module\DizkusModule\Manager;

use ServiceUtil;
use UserUtil;
use Zikula\Module\DizkusModule\Entity\ForumUserEntity;

class ForumUserManager
{

    /**
     * managed forum user
     * @var ForumUserEntity
     */
    private $_forumUser;
    protected $entityManager;
    protected $name;

    /**
     * construct
     * @param integer $uid user id (optional: defaults to current user)
     */
    public function __construct($uid = null)
    {
        $this->entityManager = ServiceUtil::get('doctrine.entitymanager');
        $this->name = 'ZikulaDizkusModule';
        if (empty($uid)) {
            $uid = UserUtil::getVar('uid');
        }
        $this->_forumUser = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\ForumUserEntity', $uid);
        if (!$this->_forumUser) {
            $this->_forumUser = new ForumUserEntity();
            $coreUser = $this->entityManager->find('Zikula\\Module\\UsersModule\\Entity\\UserEntity', $uid);
            $this->_forumUser->setUser($coreUser);
            $this->entityManager->persist($this->_forumUser);
            $this->entityManager->flush();
        }
    }

    /**
     * postOrder
     *
     * @return string
     */
    public function getPostOrder()
    {
        return $this->_forumUser ? 'ASC' : 'DESC';
    }

    /**
     * set postOrder
     *
     * @return string
     */
    public function setPostOrder($sort)
    {
        if (strtolower($sort) == 'asc') {
            $order = true;
        } else {
            $order = false;
        }
        $this->_forumUser->setPostOrder($order);
        $this->entityManager->flush();
    }

    /**
     * return topic as doctrine2 object
     *
     * @return ForumUserEntity
     */
    public function get()
    {
        return $this->_forumUser;
    }

    /**
     * return topic as array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_forumUser->toArray();
    }

    /**
     * persist and flush
     *
     * @return void
     */
    public function store($data)
    {
        $this->_forumUser->merge($data);
        $this->entityManager->persist($this->_forumUser);
        $this->entityManager->flush();
    }

    /**
     * Change the value of Favorite Forum display
     * @param boolean $value
     */
    public function displayFavoriteForumsOnly($value)
    {
        $this->_forumUser->setDisplayOnlyFavorites($value);
        $this->entityManager->flush();
    }

}
