<?php/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
use Doctrine\ORM\Mapping as ORM;

/**
 * Moderator_User entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forum_mods")
 */

namespace Dizkus\Entity\Moderator;


class User extends \Zikula_EntityAccess
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
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_ForumUser", inversedBy="user", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $forumUser;
    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forum", inversedBy="moderatorUsers")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     * */
    private $forum;
    /**
     * get ForumUser
     * 
     * @return Dizkus_Entity_ForumUser
     */
    public function getForumUser()
    {
        return $this->forumUser;
    }
    
    /**
     * set ForumUser
     * 
     * @param Dizkus_Entity_ForumUser $user
     */
    public function setForumUser(Dizkus_Entity_ForumUser $user)
    {
        $this->forumUser = $user;
    }
    
    /**
     * get Forum
     * 
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