<?php

declare(strict_types=1);

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * ModeratorUser entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forum_mods")
 */
class ModeratorUserEntity extends EntityAccess
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
     * @ORM\ManyToOne(targetEntity="ForumUserEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $forumUser;

    /**
     * @ORM\ManyToOne(targetEntity="ForumEntity", inversedBy="moderatorUsers")
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
     */
    public function setForum(ForumEntity $forum)
    {
        $this->forum = $forum;
    }
}
