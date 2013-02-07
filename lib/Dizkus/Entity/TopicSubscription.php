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
 * TopicSubscriptions entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_topic_subscription")
 */
class Dizkus_Entity_TopicSubscription extends Zikula_EntityAccess
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
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Topic", cascade={"persist"} )
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id")
     */
    private $topic;

    /**
     * user_id
     * 
     * @ORM\Column(type="integer")
     */
    private $user_id;

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

    public function getId()
    {
        return $this->id;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;
    }

}