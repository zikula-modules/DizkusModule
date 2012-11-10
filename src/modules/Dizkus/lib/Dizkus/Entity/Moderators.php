<?php

use Doctrine\ORM\Mapping as ORM;


/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forum_mods")
 */
class Dizkus_Entity_Moderators extends Zikula_EntityAccess
{
    /**
     * The following are annotations which define the id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    

    /**
     * The following are annotations which define the forum id field.
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Topics", inversedBy="forum_mods")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     */
    private $forum_id;
    
    /**
     * The following are annotations which define the user id field.
     *
     * @ORM\Column(type="integer")
     */
    private $user_id;
    
    
    /**
     * user data
     * 
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Users")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="uid")
     */
    private $user_data;
    
    
    /**
     * item forum
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Forums")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     */
    private $forum;
    
    
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