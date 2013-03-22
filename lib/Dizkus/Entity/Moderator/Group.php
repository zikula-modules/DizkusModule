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
 * Moderator_Group entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forum_mods_group")
 */
class Dizkus_Entity_Moderator_Group extends Zikula_EntityAccess
{

    /**
     * id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Zikula\Module\GroupsModule\Entity\GroupEntity", inversedBy="gid")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="gid")
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forum", inversedBy="moderatorGroups")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     * */
    private $forum;

    /**
     * get Core Group
     * @return Zikula\Module\GroupsModule\Entity\GroupEntity
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * set group
     * @param Zikula\Module\GroupsModule\Entity\GroupEntity $group
     */
    public function setGroup(Zikula\Module\GroupsModule\Entity\GroupEntity $group)
    {
        $this->group = $group;
    }

    /**
     * get Forum
     * @return Dizkus_Entity_Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * set Forum
     * @param Dizkus_Entity_Forum $forum
     */
    public function setForum(Dizkus_Entity_Forum $forum)
    {
        $this->forum = $forum;
    }

}