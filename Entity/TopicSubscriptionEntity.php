<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Dizkus\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TopicSubscriptions entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_topic_subscription")
 */
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
     * @ORM\ManyToOne(targetEntity="Dizkus\Entity\TopicEntity")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id")
     */
    private $topic;

    /**
     * forumUser
     *
     * @ORM\ManyToOne(targetEntity="Dizkus\Entity\ForumUserEntity", inversedBy="user", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $forumUser;

    /**
     * constructor
     * @param ForumUserEntity $forumUser
     * @param TopicEntity     $topic
     */
    public function __construct(ForumUserEntity $forumUser, TopicEntity $topic)
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
     * @return TopicEntity
     */
    public function getTopic()
    {
        return $this->topic;
    }

    public function setTopic(TopicEntity $topic)
    {
        return $this->topic = $topic;
    }

    /**
     * get the forumUser
     * @return ForumUserEntity
     */
    public function getForumUser()
    {
        return $this->forumUser;
    }

    /**
     * set the forumUser
     * @param ForumUserEntity $forumUser
     */
    public function setUser(ForumUserEntity $forumUser)
    {
        $this->forumUser = $forumUser;
    }

}
