<?php
/**
 * Copyright Pages Team 2012
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Pages
 * @link https://github.com/zikula-modules/Pages
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

class Dizkus_EntityAccess_PosterData
{

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
        $this->_poster = $this->entityManager->find('Dizkus_Entity_Poster', $uid);

        if (!$this->_poster ) {
            $this->_poster = new Dizkus_Entity_Poster();
            $this->_poster->setuser_id($uid);
        }
    }



    /**
     * return topic title
     *
     * @return string
     */
    public function getPostOrder()
    {
        return $this->_poster->getuser_post_order() ? 'ASC' : 'DESC';
    }

    /**
     * return topic title
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
        $this->_poster->setuser_post_order($order);
        $this->entityManager->flush();
    }




    /**
     * return topic as doctrine2 object
     *
     * @return object
     */
    public function get()
    {
        return $this->_poster;
    }


    /**
     * return topic as doctrine2 object
     *
     * @return object
     */
    public function toArray()
    {
        return $this->_poster->toArray();
    }


    /**
     * return page as array
     *
     * @return boolean
     */
    public function store($data)
    {
        $this->_poster->merge($data);
        $this->entityManager->persist($this->_poster);
        $this->entityManager->flush();
    }



}