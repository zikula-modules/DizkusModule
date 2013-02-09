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
 * ForumSubscription entity class.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_subscription", indexes={@ORM\Index(name="forum_idx", columns={"forum_id"}), @ORM\Index(name="user_idx", columns={"user_id"})})
 */
class Dizkus_Entity_ForumSubscription extends Zikula_EntityAccess
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
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forum")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     */
    private $forum;

    /**
     * The following are annotations which define the user_id field.
     * 
     * @ORM\Column(type="integer")
     */
    private $user_id = 0;

    public function getMsg_id()
    {
        return $this->msg_id;
    }

    /**
     * get forum
     * 
     * @return Dizkus_Entity_Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * set forum
     * 
     * @param Dizkus_Entity_Forum $forum
     */
    public function setForum(Dizkus_Entity_Forum $forum)
    {
        $this->forum = $forum;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($id)
    {
        $this->user_id = $id;
    }

}