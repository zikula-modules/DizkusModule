<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_topic_subscription", indexes={@ORM\Index(name="topic_idx", columns={"topic_id"}), @ORM\Index(name="user_idx", columns={"user_id"})})
 */
class Dizkus_Entity_TopicSubscriptions extends Zikula_EntityAccess
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
     * The following are annotations which define the topic_id field.
     * 
     * @ORM\Column(type="integer", unique=false)
     */
    private $topic_id = 0;

    /**
     * The following are annotations which define the user_id field.
     * 
     * @ORM\Column(type="integer", unique=false)
     */
    private $user_id = 0;


    public function getid()
    {
        return $this->id;
    }
    
    public function gettopic_id()
    {
        return $this->topic_id;
    }

    public function gettopic()
    {
        // FIXME relation getter here?
        return $this->topic_id;
    }
    
    public function getuser_id()
    {
        return $this->user_id;
    }

    public function setid($id)
    {
        $this->id = $id;
    }
    
    public function settopic_id($topic_id)
    {
        $this->topic_id = $topic_id;
    }

    public function setuser_id($user_id)
    {
        $this->user_id = $user_id;
    }
}
