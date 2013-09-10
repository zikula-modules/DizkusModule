<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Entity;

use ModUtil;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Zikula\Core\ModUrl;

/**
 * Topic entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_topics")
 * @ORM\Entity(repositoryClass="Zikula\Module\DizkusModule\Entity\Repository\TopicRepository")
 */
class TopicEntity extends \Zikula_EntityAccess
{
    /**
     * Module name
     * @var string
     */
    const MODULENAME = 'ZikulaDizkusModule';

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
     * @ORM\ManyToOne(targetEntity="ForumUserEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="poster", referencedColumnName="user_id")
     */
    private $poster;

    /**
     * title
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title = '';

    /**
     * topic_time
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $topic_time;

    /**
     * status
     *
     * @ORM\Column(type="integer")
     */
    private $status = 0;

    /**
     * viewCount
     *
     * @ORM\Column(type="integer")
     */
    private $viewCount = 0;

    /**
     * replyCount
     *
     * @ORM\Column(type="integer", length=10)
     */
    private $replyCount = 0;

    /**
     * sticky
     *
     * @ORM\Column(type="boolean")
     */
    private $sticky = false;

    /**
     * @ORM\ManyToOne(targetEntity="ForumEntity", inversedBy="topics")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     * */
    private $forum;

    /**
     * @ORM\OneToOne(targetEntity="PostEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="last_post_id", referencedColumnName="post_id", nullable=true)
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
     * @ORM\OneToMany(targetEntity="PostEntity", mappedBy="topic", cascade={"remove"})
     * @ORM\OrderBy({"post_time" = "ASC"})
     */
    private $posts;

    /**
     * Subscriptions
     *
     * TopicSubscriptionEntity collection
     * @ORM\OneToMany(targetEntity="TopicSubscriptionEntity", mappedBy="topic", cascade={"remove"})
     */
    private $subscriptions;

    /**
     * module field (hooked module name)
     *
     * @ORM\Column(length=50, nullable=true)
     */
    private $hookedModule;

    /**
     * areaId field (hooked area id)
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $hookedAreaId;

    /**
     * objectId field (object id)
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $hookedObjectId;

    /**
     * url object
     * @var ModUrl
     *
     * @ORM\Column(type="object", nullable=true)
     */
    private $hookedUrlObject = null;

    /**
     * Constructor
     */
    public function __construct()
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

    public function getReplyCount()
    {
        return $this->replyCount;
    }

    public function setReplyCount($replies)
    {
        $this->replyCount = $replies;
    }

    public function incrementReplyCount()
    {
        $this->replyCount++;
    }

    public function decrementReplyCount()
    {
        $this->replyCount--;
    }

    /**
     * get Forum
     * @return ForumEntity
     */
    public function getForum()
    {
        return $this->forum;
    }

    public function setForum(ForumEntity $forum)
    {
        $this->forum = $forum;
    }

    public function getLast_post()
    {
        return $this->last_post;
    }

    public function setLast_post(PostEntity $post)
    {
        return $this->last_post = $post;
    }

    /**
     * get the topic poster
     *
     * @return ForumUserEntity
     */
    public function getPoster()
    {
        return $this->poster;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getTopic_time()
    {
        return $this->topic_time;
    }

    public function setTopic_time(\DateTime $time)
    {
        $this->topic_time = $time;
    }

    public function getViewCount()
    {
        return $this->viewCount;
    }

    public function getSticky()
    {
        return $this->sticky;
    }

    public function getSolved()
    {
        return $this->solved;
    }

    public function lock()
    {
        $this->status = 1;
    }

    public function unlock()
    {
        $this->status = 0;
    }

    public function sticky()
    {
        $this->sticky = true;
    }

    public function unsticky()
    {
        $this->sticky = false;
    }

    public function incrementViewCount()
    {
        $this->viewCount++;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * set the Topic poster
     *
     * @param ForumUserEntity $poster
     */
    public function setPoster(ForumUserEntity $poster)
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
    public function unsetPosts()
    {
        $this->posts = null;
    }

    public function addPost(PostEntity $post)
    {
        $this->posts[] = $post;
    }

    public function getTotal_posts()
    {
        return count($this->posts);
    }

    public function getHot_topic()
    {
        $hotThreshold = ModUtil::getVar(self::MODULENAME, 'hot_threshold');
        $totalPosts = $this->getTotal_posts();

        return $totalPosts >= $hotThreshold;
    }

    /**
     * get Topic Subscriptions
     * @return TopicSubscriptionEntity collection
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    public function getHookedModule()
    {
        return $this->hookedModule;
    }

    public function setHookedModule($hookedModule)
    {
        $this->hookedModule = $hookedModule;
    }

    public function getHookedAreaId()
    {
        return $this->hookedAreaId;
    }

    public function setHookedAreaId($hookedAreaId)
    {
        $this->hookedAreaId = $hookedAreaId;
    }

    public function getHookedObjectId()
    {
        return $this->hookedObjectId;
    }

    public function setHookedObjectId($hookedObjectId)
    {
        $this->hookedObjectId = $hookedObjectId;
    }

    public function getHookedUrlObject()
    {
        return $this->hookedUrlObject;
    }

    public function setHookedUrlObject(ModUrl $hookedUrlObject)
    {
        $this->hookedUrlObject = $hookedUrlObject;
    }

    public function userAllowedToEdit($uid = null)
    {
        return $this->posts->first()->getUserAllowedToEdit($uid);
    }

}
