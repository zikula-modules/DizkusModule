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
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_topic_subscription")
 */
class Dizkus_Entity_TopicSubscriptions extends Zikula_EntityAccess
{

    /**
     * The following are annotations which define the id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The following are annotations which define the topic_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $topic_id;

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Topic", cascade={"persist"} )
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id")
     */
    private $topic;

    public function get()
    {
        return $this->topic;
    }

    public function settopic($topic)
    {
        return $this->topic = $topic;
    }

    /**
     * The following are annotations which define the user_id field.
     * 
     * @ORM\Column(type="integer")
     */
    private $user_id;

    public function getid()
    {
        return $this->id;
    }

    public function gettopic_id()
    {
        return $this->topic_id;
    }

    public function gettopic()
    {
        return $this->topic;
    }

    public function getuser_id()
    {
        return $this->user_id;
    }

    public function setid($id)
    {
        $this->id = $id;
    }

    public function setuser_id($user_id)
    {
        $this->user_id = $user_id;
    }

}