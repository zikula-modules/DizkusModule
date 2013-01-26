<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forum_mods_group")
 */
class Dizkus_Entity_Moderator_Group extends Zikula_EntityAccess
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
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="gid")
     */
    private $group;

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forum", inversedBy="moderatorGroups")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     * */
    private $forum;

    public function getForum()
    {
        return $this->forum;
    }

    public function setForum(Dizkus_Entity_Forum $forum)
    {
        $this->forum = $forum;
    }

}