<?php

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
 * TopicSubscription entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_topic_subscription")
 */
class TopicSubscriptionEntity extends EntityAccess
{
    /**
     * Table id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Topic
     *
     * @ORM\ManyToOne(targetEntity="TopicEntity", inversedBy="subscriptions")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id")
     */
    private $topic;

    /**
     * ForumUser
     *
     * @ORM\ManyToOne(targetEntity="ForumUserEntity", inversedBy="topicSubscriptions", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $forumUser;

    /**
     * Constructor
     * 
     * @param ForumUserEntity $forumUser
     * @param TopicEntity     $topic
     */
    public function __construct(ForumUserEntity $forumUser, TopicEntity $topic)
    {
        $this->forumUser = $forumUser;
        $this->topic = $topic;
    }

    /**
     * Get the subscription id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get topic
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
     * Get the ForumUser
     *
     * @return ForumUserEntity
     */
    public function getForumUser()
    {
        return $this->forumUser;
    }

    /**
     * Set the ForumUser
     *
     * @param ForumUserEntity $forumUser
     */
    public function setUser(ForumUserEntity $forumUser)
    {
        $this->forumUser = $forumUser;
    }
}
