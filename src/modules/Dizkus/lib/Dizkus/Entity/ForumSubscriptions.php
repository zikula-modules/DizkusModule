<?php

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_subscription", indexes={@ORM\Index(name="forum_idx", columns={"forum_id"}), @ORM\Index(name="user_idx", columns={"user_id"})})
 */
class Dizkus_Entity_ForumSubscriptions extends Zikula_EntityAccess
{
    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=false)
     */
    private $forum_id = 0;

    /**
     * The following are annotations which define the user_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", unique=false)
     */
    private $user_id = 0;

    /**
     * Related forum.
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forums")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     */
    private $forum;

    /**
     * Related user.
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Users")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="uid")
     */
    private $user_data;



    public function getmsg_id()
    {
        return $this->msg_id;
    }

    public function getforum_id()
    {
        return $this->forum_id;
    }

    public function getuser_id()
    {
        return $this->user_id;
    }

    public function setmsg_id($id)
    {
        $this->msg_id = $id;
    }

    public function setforum_id($id)
    {
        $this->forum_id = $id;
    }

    public function setuser_id($id)
    {
        $this->user_id = $id;
    }
}
