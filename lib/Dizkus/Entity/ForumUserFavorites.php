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
 * Favorites entity class.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forum_favorites", indexes={@ORM\Index(name="forum_idx", columns={"forum_id"}), @ORM\Index(name="user_idx", columns={"user_id"})})
 */
class Dizkus_Entity_ForumUserFavorites extends Zikula_EntityAccess
{

    /**
     * forumUser
     * 
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_ForumUser", inversedBy="user", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $forumUser;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forum", inversedBy="moderatorUsers")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     * */
    private $forum;

    /**
     * Constructor
     * @param Dizkus_Entity_ForumUser $forumUser
     * @param Dizkus_Entity_Forum $forum
     */
    function __construct(Dizkus_Entity_ForumUser $forumUser, Dizkus_Entity_Forum $forum)
    {
        $this->forumUser = $forumUser;
        $this->forum = $forum;
    }

    /**
     * get the forum
     * @return Dizkus_Entity_Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * get the forumUser
     * @return Dizkus_Entity_ForumUser
     */
    public function getForumUser()
    {
        return $this->forumUser;
    }

    /**
     * set the forum
     * @param Dizkus_Entity_Forum $forum
     */
    public function setForum(Dizkus_Entity_Forum $forum)
    {
        $this->forum = $forum;
    }

    /**
     * set the forumUser
     * @param Dizkus_Entity_ForumUser $forumUser
     */
    public function setUser(Dizkus_Entity_ForumUser $forumUser)
    {
        $this->forumUser = $forumUser;
    }

}