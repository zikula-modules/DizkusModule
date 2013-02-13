<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * ForumUser entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_users")
 */
class Dizkus_Entity_ForumUser extends Zikula_EntityAccess
{

    /**
     * Core user entity
     * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/composite-primary-keys.html
     * @see /system/Users/Entity/UserEntity.php
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Users\Entity\UserEntity")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="uid")
     */
    private $user;

    /**
     * user_posts
     *
     * @ORM\Column(type="integer")
     */
    private $user_posts = 0;

    /**
     * user_autosubscribe
     *
     * @ORM\Column(type="boolean")
     */
    private $user_autosubscribe = true;

    /**
     * user_level
     *
     * @ORM\Column(type="integer")
     */
    private $user_level = 1;

    /**
     * user_lastvisit
     * 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $user_lastvisit = null;

    /**
     * user_favorites
     *
     * @ORM\Column(type="boolean")
     */
    private $user_favorites = false;

    /**
     * user_post_order
     *
     * @ORM\Column(type="boolean")
     */
    private $user_post_order = false;

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Rank", cascade={"persist"} )
     * @ORM\JoinColumn(name="user_rank", referencedColumnName="rank_id", nullable=true)
     */
    private $user_rank;

    public function getUser_id()
    {
        return $this->user->getUid();
    }

    /**
     * Core User Entity
     * @return Users\Entity\UserEntity
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * set the user
     * @param Users\Entity\UserEntity $user
     */
    public function setUser(Users\Entity\UserEntity $user)
    {
        $this->user = $user;
    }

    public function getUser_posts()
    {
        return $this->user_posts;
    }

    public function setUser_posts($posts)
    {
        return $this->user_posts = $posts;
    }

    public function incrementUser_posts()
    {
        $this->user_posts++;
    }

    public function decrementUser_posts()
    {
        $this->user_posts--;
    }

    public function getUser_autosubscribe()
    {
        return $this->user_autosubscribe;
    }

    public function setUser_autosubscribe($autosubscribe)
    {
        return $this->user_autosubscribe = $autosubscribe;
    }

    public function getUser_level()
    {
        return $this->user_level;
    }

    public function setUser_level($level)
    {
        return $this->user_level = $level;
    }

    public function getUser_lastvisit()
    {
        return $this->user_lastvisit;
    }

    public function setUser_lastvisit($lastvisit)
    {
        return $this->user_lastvisit = $lastvisit;
    }

    public function getUser_favorites()
    {
        return $this->user_favorites;
    }

    public function setUser_favorites($favorites)
    {
        return $this->user_favorites = $favorites;
    }

    public function getUser_post_order()
    {
        return $this->user_post_order;
    }

    public function setUser_post_order($post_order)
    {
        return $this->user_post_order = $post_order;
    }

    /**
     * User rank
     * 
     * @return Dizkus_Entity_Rank
     */
    public function getUser_rank()
    {
        return $this->user_rank;
    }

    public function setUser_rank(Dizkus_Entity_Rank $rank)
    {
        return $this->user_rank = $rank;
    }

}