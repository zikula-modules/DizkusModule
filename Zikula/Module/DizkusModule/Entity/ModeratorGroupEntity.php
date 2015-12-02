<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Zikula\Module\DizkusModule\Entity\ForumEntity;
use Zikula\GroupsModule\Entity\GroupEntity as ZikulaGroup;

/**
 * ModeratorGroup entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forum_mods_group")
 */
class ModeratorGroupEntity extends EntityAccess
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
     * Zikula Core Group Entity
     * @ORM\ManyToOne(targetEntity="Zikula\GroupsModule\Entity\GroupEntity")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="gid")
     */
    private $group;

    /**
     * @ORM\ManyToOne(targetEntity="ForumEntity", inversedBy="moderatorGroups")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     * */
    private $forum;

    /**
     * get Core Group
     * @return \Zikula\GroupsModule\Entity\GroupEntity
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * set group
     * @param \Zikula\GroupsModule\Entity\GroupEntity $group
     */
    public function setGroup(ZikulaGroup $group)
    {
        $this->group = $group;
    }

    /**
     * get Forum
     * @return ForumEntity
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * set Forum
     * @param ForumEntity $forum
     */
    public function setForum(ForumEntity $forum)
    {
        $this->forum = $forum;
    }

}
