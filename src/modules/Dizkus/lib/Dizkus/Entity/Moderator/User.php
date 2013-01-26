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
class Dizkus_Entity_Moderator_User extends Zikula_EntityAccess
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
     * The following are annotations which define the user id field.
     *
     * @ORM\Column(type="integer")
     */
    private $user_id;

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forums", inversedBy="moderatorUsers")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     * */
    private $forum;

    public function getForum()
    {
        return $this->forum;
    }

    public function setForum(Dizkus_Entity_Forums $forum)
    {
        $this->forum = $forum;
    }

}