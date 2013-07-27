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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Topics entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_topics")
 * @ORM\Entity(repositoryClass="Dizkus_Entity_Repository_TopicRepository")
 */
class Dizkus_Entity_Topic extends Zikula_EntityAccess
{

    /**
     * topic_id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $topic_id;

    /**
     * poster
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_ForumUser", cascade={"persist"})
     * @ORM\JoinColumn(name="poster", referencedColumnName="user_id")
     */
    private $poster;

    /**
     * topic_title
     *
     * @ORM\Column(type="string", length=255)
     */
    private $topic_title = '';

    /**
     * topic_time
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $topic_time;

    /**
     * topic_status
     *
     * @ORM\Column(type="integer")
     */
    private $topic_status = 0;

    /**
     * topic_views
     *
     * @ORM\Column(type="integer")
     */
    private $topic_views = 0;

    /**
     * topic_replies
     *
     * @ORM\Column(type="integer", length=10)
     */
    private $topic_replies = 0;

    /**
     * sticky
     *
     * @ORM\Column(type="boolean")
     */
    private $sticky = false;

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forum", inversedBy="topics")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     * */
    private $forum;

    /**
     * topic_reference
     *
     * @ORM\Column(type="string", length=60)
     */
    private $topic_reference = '';

    /**
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Post", cascade={"persist"})
     * @ORM\JoinColumn(name="topic_last_post_id", referencedColumnName="post_id", nullable=true)
     */
    private $last_post;

    /**
     * solved
     *
     * @ORM\Column(type="boolean")
     */
    private $solved = false;

    /**
     * posts
     * 
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Post", mappedBy="topic", cascade={"remove"})
     * @ORM\OrderBy({"post_time" = "ASC"})
     */
    private $posts;

    /**
     * Subscriptions
     * 
     * Dizkus_Entity_TopicSubscription collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_TopicSubscription", mappedBy="topic", cascade={"remove"})
     */
    private $subscriptions;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }

    public function getTopic_id()
    {
        return $this->topic_id;
    }

    public function setTopic_id($id)
    {
        $this->topic_id = $id;
    }

    public function getTopic_replies()
    {
        return $this->topic_replies;
    }

    public function setTopic_replies($replies)
    {
        $this->topic_replies = $replies;
    }

    public function incrementTopic_replies()
    {
        $this->topic_replies++;
    }

    public function decrementTopic_replies()
    {
        $this->topic_replies--;
    }

    /**
     * get Forum
     * @return Dizkus_Entity_Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    public function setForum(Dizkus_Entity_Forum $forum)
    {
        $this->forum = $forum;
    }

    public function getLast_post()
    {
        return $this->last_post;
    }

    public function setLast_post(Dizkus_Entity_Post $post)
    {
        return $this->last_post = $post;
    }

    /**
     * get the topic poster
     * 
     * @return Dizkus_Entity_ForumUser
     */
    public function getPoster()
    {
        return $this->poster;
    }

    public function getTopic_title()
    {
        return $this->topic_title;
    }

    public function getTopic_status()
    {
        return $this->topic_status;
    }

    public function getTopic_time()
    {
        return $this->topic_time;
    }
    
    public function setTopic_time(DateTime $time)
    {
        $this->topic_time = $time;
    }

    public function getTopic_views()
    {
        return $this->topic_views;
    }

    public function getSticky()
    {
        return $this->sticky;
    }

    public function getTopic_reference()
    {
        return $this->topic_reference;
    }

    public function getSolved()
    {
        return $this->solved;
    }

    public function lock()
    {
        $this->topic_status = 1;
    }

    public function unlock()
    {
        $this->topic_status = 0;
    }

    public function sticky()
    {
        $this->sticky = true;
    }

    public function unsticky()
    {
        $this->sticky = false;
    }

    public function incrementTopic_views()
    {
        $this->topic_views++;
    }

    public function setTopic_title($title)
    {
        $this->topic_title = $title;
    }

    /**
     * set the Topic poster
     * 
     * @param Dizkus_Entity_ForumUser $poster
     */
    public function setPoster(Dizkus_Entity_ForumUser $poster)
    {
        $this->poster = $poster;
    }

    public function setSolved($solved)
    {
        $this->solved = $solved;
    }

    public function getPosts()
    {
        return $this->posts;
    }
    
    /**
     * remove all posts
     */
    public function unsetPosts() {
        $this->posts = null;
    }
    
    public function addPost(Dizkus_Entity_Post $post) {
        $this->posts[] = $post;
    }
    
    public function getTotal_posts()
    {
        return count($this->posts);
    }
    
    public function getHot_topic()
    {
        $hotThreshold = ModUtil::getVar('Dizkus', 'hot_threshold');
        $totalPosts = $this->getTotal_posts();
        return ($totalPosts >= $hotThreshold);
    }

    /**
     * get Topic Subscriptions
     * @return Dizkus_Entity_TopicSubscription collection
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

}