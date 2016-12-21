<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Entity;

use ServiceUtil;
use ModUtil;
use DateTime;
use UserUtil;
use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Post entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_posts")
 * @ORM\Entity(repositoryClass="Zikula\DizkusModule\Entity\Repository\PostRepository")
 */
class PostEntity extends EntityAccess
{
    /**
     * Module name
     * @var string
     */
    const MODULENAME = 'ZikulaDizkusModule';

    /**
     * post_id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $post_id;

    /**
     * post_time
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $post_time;

    /**
     * poster_ip
     *
     * @ORM\Column(type="string", length=50)
     */
    private $poster_ip = '';

    /**
     * msgid
     *
     * @ORM\Column(type="string", length=100)
     */
    private $msgid = '';

    /**
     * post_text
     *
     * @ORM\Column(type="text")
     */
    private $post_text = '';

    /**
     * attachSignature
     *
     * @ORM\Column(type="boolean")
     */
    private $attachSignature = false;

    /**
     * isFirstPost
     *
     * @ORM\Column(type="boolean")
     */
    private $isFirstPost = false;

    /**
     * title
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title = '';

    /**
     * @ORM\ManyToOne(targetEntity="ForumUserEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="poster_id", referencedColumnName="user_id")
     */
    private $poster;

    /**
     * @ORM\ManyToOne(targetEntity="TopicEntity", inversedBy="posts")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id")
     * */
    private $topic;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!ModUtil::getVar(self::MODULENAME, 'log_ip')) {
            // for privacy issues ip logging can be deactivated
            $this->poster_ip = 'unrecorded';
        } else {
            $request = ServiceUtil::get('request');
            if ($request->server->get('HTTP_X_FORWARDED_FOR')) {
                $this->poster_ip = $request->server->get('REMOTE_ADDR') . '/' . $request->server->get('HTTP_X_FORWARDED_FOR');
            } else {
                $this->poster_ip = $request->server->get('REMOTE_ADDR');
            }
        }
    }

    public function getPost_id()
    {
        return $this->post_id;
    }

    public function getPost_text()
    {
        return $this->post_text;
    }

    public function setPost_text($text)
    {
        return $this->post_text = stripslashes($text);
    }

    public function getAttachSignature()
    {
        return $this->attachSignature;
    }

    public function setAttachSignature($attachSignature)
    {
        return $this->attachSignature = $attachSignature;
    }

    public function getIsFirstPost()
    {
        return $this->isFirstPost;
    }

    public function setIsFirstPost($first)
    {
        return $this->isFirstPost = $first;
    }

    /**
     * Is the post a first post in topic?
     * convenience naming
     *
     * @return boolean
     */
    public function isFirst()
    {
        return $this->isFirstPost;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        return $this->title = $title;
    }

    public function getPost_time()
    {
        return $this->post_time;
    }

    public function setPost_time(\DateTime $time)
    {
        $this->post_time = $time;
    }

    public function updatePost_time(\DateTime $time = null)
    {
        if (!isset($time)) {
            $time = new \DateTime();
        }
        $this->post_time = $time;
    }

    public function getPoster_ip()
    {
        return $this->poster_ip;
    }

    public function getMsgid()
    {
        return $this->msgid;
    }

    /**
     * Get User who made post
     *
     * @return ForumUserEntity
     */
    public function getPoster()
    {
        return $this->poster;
    }

    /**
     * set user who made the post
     *
     * @param ForumUserEntity $poster
     */
    public function setPoster(ForumUserEntity $poster)
    {
        $this->poster = $poster;
    }

    /**
     * convenience method to retrieve user id of poster
     *
     * @return integer
     */
    public function getPoster_id()
    {
        return $this->poster->getUser_id();
    }

    public function getPoster_data()
    {
        return array(
            'image' => 'a',
            'rank' => 'a',
            'rank_link' => 'a',
            'description' => 'a',
            'moderate' => 'a',
            'edit' => 'a',
            'reply' => 'a',
            'postCount' => 'a',
            'seeip' => 'a');
    }

    /**
     * get Post topic
     *
     * @return TopicEntity
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set post Topic
     *
     * @param TopicEntity $topic
     */
    public function setTopic(TopicEntity $topic)
    {
        $this->topic = $topic;
    }

    /**
     * convenience method to retreive topic ID
     *
     * @return integer
     */
    public function getTopic_id()
    {
        return $this->topic->getTopic_id();
    }

    /**
     * determine if a user is allowed to edit this post
     *
     * @param  integer $uid
     * @return boolean
     */
    public function getUserAllowedToEdit($uid = null)
    {
        if (!isset($this->post_time)) {
            return false;
        }
        // default to current user
        $uid = isset($uid) ? $uid : UserUtil::getVar('uid');
        $timeAllowedToEdit = ModUtil::getVar(self::MODULENAME, 'timespanforchanges');
        // in hours
        $postTime = clone $this->post_time;
        $canEditUtil = $postTime->modify("+{$timeAllowedToEdit} hours");
        $now = new \DateTime();
        if ($uid == $this->poster->getUser_id() && $now <= $canEditUtil) {
            return true;
        }

        return false;
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['userAllowedToEdit'] = $this->getUserAllowedToEdit();

        return $array;
    }
}
