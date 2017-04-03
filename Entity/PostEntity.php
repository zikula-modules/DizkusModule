<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Entity;

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
        $this->poster_ip = 'unrecorded';
//        if (!ModUtil::getVar(self::MODULENAME, 'log_ip')) {
//            // for privacy issues ip logging can be deactivated
//            $this->poster_ip = 'unrecorded';
//        } else {
//            $request = ServiceUtil::get('request');
//            if ($request->server->get('HTTP_X_FORWARDED_FOR')) {
//                $this->poster_ip = $request->server->get('REMOTE_ADDR') . '/' . $request->server->get('HTTP_X_FORWARDED_FOR');
//            } else {
//                $this->poster_ip = $request->server->get('REMOTE_ADDR');
//            }
//        }
    }

    public function getPost_id()
    {
        return $this->post_id;
    }

    public function getId()
    {
        return $this->post_id;
    }

    public function getPost_text()
    {
        return $this->post_text;
    }

    public function getPostText()
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
    public function getPosterId()
    {
        return $this->poster->getUserId();
    }

    public function getPoster_data()
    {
        return [
            'image' => 'a',
            'rank' => 'a',
            'rank_link' => 'a',
            'description' => 'a',
            'moderate' => 'a',
            'edit' => 'a',
            'reply' => 'a',
            'postCount' => 'a',
            'seeip' => 'a'];
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
    public function getTopicId()
    {
        return $this->topic->getTopic_id();
    }

    public function toArray()
    {
        $array = parent::toArray();

        return $array;
    }
}
