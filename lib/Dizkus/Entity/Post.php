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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Post entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_posts")
 */
class Dizkus_Entity_Post extends Zikula_EntityAccess
{

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
     * post_msgid
     * 
     * @ORM\Column(type="string", length=100)
     */
    private $post_msgid = '';

    /**
     * post_text
     * 
     * @ORM\Column(type="text")
     */
    private $post_text = '';

    /**
     * post_attach_signature
     *
     * @ORM\Column(type="boolean")
     */
    private $post_attach_signature = false;

    /**
     * post_attach_signature
     *
     * @ORM\Column(type="boolean")
     */
    private $post_first = false;

    /**
     * post_title
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $post_title = '';

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_ForumUser", cascade={"persist"})
     * @ORM\JoinColumn(name="poster_id", referencedColumnName="user_id")
     */
    private $poster;

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Topic", inversedBy="posts")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id")
     * */
    private $topic;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (ModUtil::getVar('Dizkus', 'log_ip') == 'no') {
            // for privacy issues ip logging can be deactivated
            $this->poster_ip = '127.0.0.1';
        } else {
            // some enviroment for logging ;)
            if (System::serverGetVar('HTTP_X_FORWARDED_FOR')) {
                $this->poster_ip = System::serverGetVar('REMOTE_ADDR') . "/" . System::serverGetVar('HTTP_X_FORWARDED_FOR');
            } else {
                $this->poster_ip = System::serverGetVar('REMOTE_ADDR');
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

    public function getPost_attach_signature()
    {
        return $this->post_attach_signature;
    }

    public function setPost_attach_signature($attachSignature)
    {
        return $this->post_attach_signature = $attachSignature;
    }

    public function getPost_first()
    {
        return $this->post_first;
    }

    public function setPost_first($first)
    {
        return $this->post_first = $first;
    }

    /**
     * Is the post a first post in topic?
     * convenience naming
     * 
     * @return boolean
     */
    public function isFirst()
    {
        return $this->post_first;;
    }

    public function getPost_title()
    {
        return $this->post_title;
    }

    public function setPost_title($title)
    {
        return $this->post_title = $title;
    }

    public function getPost_time()
    {
        return $this->post_time;
    }
    
    public function setPost_time(DateTime $time)
    {
        $this->post_time = $time;
    }
    
    public function updatePost_time(DateTime $time=null)
    {
        if (!isset($time)) {
            $time = new DateTime();
        }
        $this->post_time = $time;
    }

    public function getPoster_ip()
    {
        return $this->poster_ip;
    }

    public function getPost_msgid()
    {
        return $this->post_msgid;
    }

    /**
     * Get User who made post
     * 
     * @return Dizkus_Entity_ForumUser
     */
    public function getPoster()
    {
        return $this->poster;
    }

    /**
     * set user who made the post
     * 
     * @param Dizkus_Entity_ForumUser $poster
     */
    public function setPoster(Dizkus_Entity_ForumUser $poster)
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
            'rank_image' => 'a',
            'rank' => 'a',
            'rank_link' => 'a',
            'rank_desc' => 'a',
            'moderate' => 'a',
            'edit' => 'a',
            'reply' => 'a',
            'user_posts' => 'a',
            'seeip' => 'a',
        );
    }

    /**
     * get Post topic
     * 
     * @return Dizkus_Entity_Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Set post Topic
     * 
     * @param Dizkus_Entity_Topic $topic
     */
    public function setTopic(Dizkus_Entity_Topic $topic)
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

}