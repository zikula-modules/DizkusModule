<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forum_favorites", indexes={@ORM\Index(name="forum_idx", columns={"forum_id"}), @ORM\Index(name="user_idx", columns={"user_id"})})
 */
class Dizkus_Entity_Favorites extends Zikula_EntityAccess
{
    /**
     * The following are annotations which define the fid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=false)
     */
    private $forum_id = 0;
    
    /**
     * The following are annotations which define the category field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=false)
     */
    private $user_id = 0;



    public function getForum_id()
    {
        return $this->forum_id;
    }
    
    public function getUser_id()
    {
        return $this->user_id;
    }
    
    public function setForum_id($forum_id)
    {
        $this->forum_id = $forum_id;
    }
    
    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;
    }
}
