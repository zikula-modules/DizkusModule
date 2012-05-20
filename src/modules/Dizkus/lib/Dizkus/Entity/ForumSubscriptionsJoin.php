<?php

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_subscription")
 */
class Dizkus_Entity_ForumSubscriptionsJoin extends Zikula_EntityAccess
{
    
    /**
     * The following are annotations which define the msg_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $msg_id;
    
    
    /**
     * The following are annotations which define the forum_id field.
     * 
     * @ORM\Column(type="integer")
     */
    private $forum_id = 0;
    
    /**
     * The following are annotations which define the user_id field.
     * 
     * @ORM\Column(type="integer")
     */
    private $user_id = 0;
    
    
    /**
     * item forum
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Forums")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     */
    private $forum;

    
    public function getmsg_id()
    {
        return $this->msg_id;
    }
    
    public function getforum_id()
    {
        return $this->forum_id;
    }
    
    public function getforum()
    {
        return $this->forum;
    }
    
    public function getuser_id()
    {
        return $this->user_id;
    }
    
}