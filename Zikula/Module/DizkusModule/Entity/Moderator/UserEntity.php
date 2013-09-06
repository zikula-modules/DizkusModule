<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Entity\Moderator;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Module\DizkusModule\Entity\ForumEntity;
use Zikula\Module\DizkusModule\Entity\ForumUserEntity;

/**
 * Moderator_User entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forum_mods")
 */
class UserEntity extends \Zikula_EntityAccess
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
     * forumUser
     *
     * @ORM\ManyToOne(targetEntity="Zikula\Module\DizkusModule\Entity\ForumUserEntity", inversedBy="user", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $forumUser;

    /**
     * @ORM\ManyToOne(targetEntity="Zikula\Module\DizkusModule\Entity\ForumEntity", inversedBy="moderatorUsers")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     * */
    private $forum;

    /**
     * get ForumUser
     *
     * @return ForumUserEntity
     */
    public function getForumUser()
    {
        return $this->forumUser;
    }

    /**
     * set ForumUser
     *
     * @param ForumUserEntity $user
     */
    public function setForumUser(ForumUserEntity $user)
    {
        $this->forumUser = $user;
    }

    /**
     * get Forum
     *
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
