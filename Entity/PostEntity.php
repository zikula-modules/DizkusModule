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
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * Post entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_posts")
 * @ORM\Entity(repositoryClass="Zikula\DizkusModule\Entity\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks
 */
class PostEntity extends EntityAccess
{
    /**
     * Module name
     * @var string
     */
    const MODULENAME = 'ZikulaDizkusModule';

    /**
     * id
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="post_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @Assert\Length(
     *      min = 1,
     *      max = 65535,
     *      minMessage = "Your post must be at least {{ limit }} characters long",
     *      maxMessage = "Your post cannot be longer than {{ limit }} characters"
     * )
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
     * @ORM\ManyToOne(targetEntity="ForumUserEntity")
     * @ORM\JoinColumn(name="poster_id", referencedColumnName="user_id")
     */
    private $poster;

    /**
     * @ORM\ManyToOne(targetEntity="TopicEntity", inversedBy="posts")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id", onDelete="CASCADE")
     * */
    private $topic;

    private $subscribe = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->poster_ip = 'unrecorded';
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setPoster_ip($poster_ip)
    {
        $this->poster_ip = $poster_ip;
    }

    public function setMsgid($msgid)
    {
        $this->msgid = $msgid;
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
        $this->setPostText($text);

        return $this;
    }

    public function setPostText($text)
    {
        $this->post_text = stripslashes($text);

        return $this;
    }

    public function getAttachSignature()
    {
        return $this->attachSignature;
    }

    public function setAttachSignature($attachSignature)
    {
        $this->attachSignature = $attachSignature;

        return $this;
    }

    public function getIsFirstPost()
    {
        return $this->isFirstPost;
    }

    public function setIsFirstPost($first = true)
    {
        $this->isFirstPost = $first;

        return $this;
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
        $this->title = $title;

        return $this;
    }

    public function getPost_time()
    {
        return $this->post_time;
    }

    public function setPost_time(\DateTime $time)
    {
        $this->post_time = $time;

        return $this;
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

        return $this;
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

        return $this;
    }

    /**
     * convenience method to retreive topic ID
     *
     * @return integer
     */
    public function getTopicId()
    {
        return $this->topic->getId();
    }

    public function toArray()
    {
        $array = parent::toArray();

        return $array;
    }

    /**
     * convenience method to retreive topic ID
     *
     * @return integer
     */
    public function getSubscribe()
    {
        return $this->topic->getSubscribe();
    }

    /**
     * convenience method to retreive topic ID
     *
     * @return integer
     */
    public function setSubscribe($subscribe)
    {
        $this->subscribe = $subscribe;

        return $this;
    }
}
