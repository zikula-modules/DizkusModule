<?php

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_posts")
 */
class Dizkus_Entity_Post extends Zikula_EntityAccess
{

    /**
     * The following are annotations which define the post_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $post_id;

    public function getpost_id()
    {
        return $this->post_id;
    }

    /**
     * forum id
     * this should probably be changed to `forum` and be Dizkus_Entity_Forum obj
     * one to one?
     *
     * @ORM\Column(type="integer")
     */
    private $forum_id = 0;

    public function getforum_id()
    {
        return $this->forum_id;
    }

    public function setforum_id($forumId)
    {
        return $this->forum_id = $forumId;
    }

    /**
     * The following are annotations which define the post_time field.
     * 
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $post_time;

    /**
     * The following are annotations which define the poster_ip field.
     * 
     * @ORM\Column(type="string", length=50)
     */
    private $poster_ip = '';

    /**
     * The following are annotations which define the post_msgid field.
     * 
     * @ORM\Column(type="string", length=100)
     */
    private $post_msgid = '';

    /**
     * The following are annotations which define the post_text field.
     * 
     * @ORM\Column(type="text")
     */
    private $post_text = '';

    public function getpost_text()
    {
        return $this->post_text;
    }

    public function setpost_text($text)
    {
        return $this->post_text = stripslashes($text);
    }

    /**
     * The following are annotations which define the post_attach_signature field.
     *
     * @ORM\Column(type="boolean")
     */
    private $post_attach_signature = false;

    public function getpost_attach_signature()
    {
        return $this->post_attach_signature;
    }

    public function setpost_attach_signature($attachSignature)
    {
        return $this->post_attach_signature = $attachSignature;
    }

    /**
     * The following are annotations which define the post_attach_signature field.
     *
     * @ORM\Column(type="boolean")
     */
    private $post_first = false;

    public function getpost_first()
    {
        return $this->post_first;
    }

    public function setpost_first($first)
    {
        return $this->post_first = $first;
    }

    /**
     * The following are annotations which define the post_title field.
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $post_title = '';

    public function getpost_title()
    {
        return $this->post_title;
    }

    public function setpost_title($title)
    {
        return $this->post_title = $title;
    }

    public function getpost_time()
    {
        return $this->post_time;
    }

    public function getposter_ip()
    {
        return $this->poster_ip;
    }

    public function getpost_msgid()
    {
        return $this->post_msgid;
    }

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Poster", cascade={"persist"} )
     * @ORM\JoinColumn(name="poster_id", referencedColumnName="user_id")
     */
    private $poster;

    /**
     * Get User who made post
     * 
     * @return Dizkus_Entity_Poster
     */
    public function getposter()
    {
        return $this->poster;
    }

    public function setposter(Dizkus_Entity_Poster $poster)
    {
        return $this->poster = $poster;
    }
    
    /**
     * convenience method
     * 
     * @return integer
     */
    public function getPoster_id()
    {
        return $this->poster->getuser_id();
    }

    public function getposter_data()
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

    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Topic", inversedBy="posts")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id")
     * */
    private $topic;

    /**
     * get Post topic
     * 
     * @return Dizkus_Entity_Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    public function setTopic(Dizkus_Entity_Topic $topic)
    {
        $this->topic = $topic;
    }
    
    /**
     * convenience method
     * 
     * @return integer
     */
    public function getTopic_id()
    {
        return $this->topic->gettopic_id();
    }

}