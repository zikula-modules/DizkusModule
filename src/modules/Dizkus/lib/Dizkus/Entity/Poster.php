<?php

use Doctrine\ORM\Mapping as ORM;


/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_users")
 */
class Dizkus_Entity_Poster extends Zikula_EntityAccess
{

    /**
     * The following are annotations which define the post_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=true)
     */
    private $user_id;

    public function getuser_id()
    {
        return $this->user_id;
    }


    public function setuser_id($id)
    {
        return $this->user_id = $id;
    }


    /**
     * The following are annotations which define the topic_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $user_posts = 0;

    public function getuser_posts()
    {
        return $this->user_posts;
    }

    public function setuser_posts($posts)
    {
        return $this->user_posts = $posts;
    }

    public function incrementUser_posts() {
        $this->user_posts++;
    }

    public function decrementUser_posts() {
        $this->user_posts--;
    }


    /**
     * The following are annotations which define the topic_id field.
     *
     * @ORM\Column(type="boolean")
     */
    private $user_autosubscribe = true;

    public function getuser_autosubscribe()
    {
        return $this->user_autosubscribe;
    }

    public function setuser_autosubscribe($autosubscribe)
    {
        return $this->user_autosubscribe = $autosubscribe;
    }


    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $user_level = 1;

    public function getuser_level()
    {
        return $this->user_level;
    }

    public function setuser_level($level)
    {
        return $this->user_level = $level;
    }


    /**
     * The following are annotations which define the post_time field.
     * 
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $user_lastvisit = null;

    public function getuser_lastvisit()
    {
        return $this->user_lastvisit;
    }

    public function setuser_lastvisit($lastvisit)
    {
        return $this->user_lastvisit = $lastvisit;
    }



    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Column(type="boolean")
     */
    private $user_favorites = false;

    public function getuser_favorites()
    {
        return $this->user_favorites;
    }

    public function setuser_favorites($favorites)
    {
        return $this->user_favorites = $favorites;
    }


    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Column(type="boolean")
     */
    private $user_post_order = false;

    public function getuser_post_order()
    {
        return $this->user_post_order;
    }

    public function setuser_post_order($post_order)
    {
        return $this->user_post_order = $post_order;
    }


    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Ranks", cascade={"persist"} )
     * @ORM\JoinColumn(name="user_rank", referencedColumnName="rank_id", nullable=true)
     */
    private $user_rank;

    public function getuser_rank()
    {
        return $this->user_rank;
    }

    public function setuser_rank($rank)
    {
        return $this->user_rank = $rank;
    }




}