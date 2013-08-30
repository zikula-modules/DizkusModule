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
 * TopicSubscriptions entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_topic_subscription")
 */

namespace Dizkus\Entity;

class TopicSubscriptionEntity extends \Zikula_EntityAccess
{
    /**
     * table id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * topic
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Topic")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id")
     */
    private $topic;
    /**
     * forumUser
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_ForumUser", inversedBy="user", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $forumUser;
    /**
     * constructor
     * @param Dizkus_Entity_ForumUser $forumUser
     * @param Dizkus_Entity_Topic     $topic
     */
    public function __construct(Dizkus_Entity_ForumUser $forumUser, Dizkus_Entity_Topic $topic)
    {
        $this->forumUser = $forumUser;
        $this->topic = $topic;
    }

    /**
     * get the table id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * get topic
     *
     * @return Dizkus_Entity_Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    public function setTopic(Dizkus_Entity_Topic $topic)
    {
        return $this->topic = $topic;
    }

    /**
     * get the forumUser
     * @return Dizkus_Entity_ForumUser
     */
    public function getForumUser()
    {
        return $this->forumUser;
    }

    /**
     * set the forumUser
     * @param Dizkus_Entity_ForumUser $forumUser
     */
    public function setUser(Dizkus_Entity_ForumUser $forumUser)
    {
        $this->forumUser = $forumUser;
    }

}
