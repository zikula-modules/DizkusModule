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
use Doctrine\Common\Collections\ArrayCollection;

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
     * user choice to display favorites only (true)
     *     or all forums (false)
     *
     * @ORM\Column(name="user_favorites", type="boolean")
     */
    private $displayOnlyFavorites = false;

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
    private $rank;

    /**
     * Dizkus_Entity_Forum collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Favorites", mappedBy="user_id", indexBy="forum", cascade={"persist"}, orphanRemoval=true)
     */
    private $favoriteForums;
    
    /**
     * constructor
     */
    function __construct()
    {
        $this->favoriteForums = new ArrayCollection();
    }

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
        $this->user_posts = $posts;
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
        $this->user_autosubscribe = $autosubscribe;
    }

    public function getUser_level()
    {
        return $this->user_level;
    }

    public function setUser_level($level)
    {
        $this->user_level = $level;
    }

    public function getUser_lastvisit()
    {
        return $this->user_lastvisit;
    }

    public function setUser_lastvisit($lastvisit)
    {
        $this->user_lastvisit = $lastvisit;
    }

    /**
     * get if user wants only to display favorite forums
     * @return boolean
     */
    public function getDisplayOnlyFavorites()
    {
        return $this->displayOnlyFavorites;
    }

    /**
     * display favorite forums only
     */
    public function showFavoritesOnly()
    {
        $this->displayOnlyFavorites = true;
    }

    /**
     * display all forums (not just favorites)
     */
    public function showAllForums()
    {
        $this->displayOnlyFavorites = false;
    }

    public function getUser_post_order()
    {
        return $this->user_post_order;
    }

    public function setUser_post_order($post_order)
    {
        $this->user_post_order = $post_order;
    }

    /**
     * get User rank
     * 
     * @return Dizkus_Entity_Rank
     */
    public function getRank()
    {
        return $this->rank;
    }
    
    /**
     * set the User rank
     * @param Dizkus_Entity_Rank $rank
     */
    public function setRank(Dizkus_Entity_Rank $rank)
    {
        $this->rank = $rank;
    }
    
    /**
     * clear the User rank value
     */
    public function clearRank()
    {
        $this->rank = null;
    }

    /**
     * get User favorite forums
     * @return Dizkus_Entity_Forum collection
     */
    public function getFavoriteForums()
    {
        return $this->favoriteForums;
    }

    /**
     * add a forum as favorite
     * @param Dizkus_Entity_Forum $forum
     */
    public function addFavoriteForum(Dizkus_Entity_Forum $forum)
    {
        $this->favoriteForums->add($forum);
    }

    /**
     * remove a forum as favorite
     * @param Dizkus_Entity_Forum $forum
     */
    public function removeFavoriteForum(Dizkus_Entity_Forum $forum)
    {
        $this->favoriteForums->removeElement($forum);
    }

    /**
     * clear all forum favorites
     */
    public function clearForumFavorites()
    {
        $this->favoriteForums->clear();
    }

    /**
     * Is this a favorite forum?
     * @param Dizkus_Entity_Forum $forum
     * @return boolean
     */
    public function isFavoriteForum(Dizkus_Entity_Forum $forum)
    {
        return $this->favoriteForums->contains($forum);
    }

}