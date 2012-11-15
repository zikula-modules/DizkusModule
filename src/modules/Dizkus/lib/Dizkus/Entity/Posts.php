<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_posts", indexes={@ORM\Index(name="topic_idx", columns={"topic_id"}), @ORM\Index(name="forum_idx", columns={"forum_id"}), @ORM\Index(name="poster_idx", columns={"poster_id"}), @ORM\Index(name="post_msg_idx", columns={"post_msgid"})})
 */
class Dizkus_Entity_Posts extends Zikula_EntityAccess
{
    /**
     * The following are annotations which define the post_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $post_id;

    /**
     * The following are annotations which define the topic_id field.
     *
     * @ORM\Column(type="integer", unique=false)
     */
    private $topic_id = 0;

    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Column(type="integer", unique=false)
     */
    private $forum_id = 0;

    /**
     * The following are annotations which define the poster_id field.
     *
     * @ORM\Column(type="integer", unique=false)
     */
    private $poster_id = 1;

    /**
     * The following are annotations which define the post_time field.
     * 
     * @ORM\Column(type="datetime")
     */
    private $post_time = '';

    /**
     * The following are annotations which define the poster_ip field.
     * 
     * @ORM\Column(type="string", length="50")
     */
    private $poster_ip = '';

    /**
     * The following are annotations which define the post_msgid field.
     * 
     * @ORM\Column(type="string", length="100", unique=false)
     */
    private $post_msgid = '';

    /**
     * The following are annotations which define the post_text field.
     * 
     * @ORM\Column(type="text")
     */
    private $post_text = '';
    
    /**
     * The following are annotations which define the post_title field.
     * 
     * @ORM\Column(type="string", length="255")
     */
    private $post_title = '';

    /**
     * Forum of the post.
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forums")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")
     */
    private $forum;

    /**
     * Topic of the post.
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Topics")
     * @ORM\JoinColumn(name="topic_id", referencedColumnName="topic_id")
     */
    private $topic;

    /**
     * Poster.
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Users", inversedBy="forumPosts")
     * @ORM\JoinColumn(name="poster_id", referencedColumnName="uid")
     */
    private $poster;



    public function getpost_id()
    {
        return $this->post_id;
    }
    
    public function gettopic_id()
    {
        return $this->topic_id;
    }
    
    public function getforum_id()
    {
        return $this->forum_id;
    }
    
    public function getposter_id()
    {
        return $this->poster_id;
    }
    
    public function getpost_time()
    {
        $time = new DateTime($this->post_time);
        return $time->format('Y-m-d H:i:s');
    }
    
    public function getposter_ip()
    {
        return $this->poster_ip;
    }
    
    public function getpost_msgid()
    {
        return $this->post_msgid;
    }
    
    public function getpost_text()
    {
        return $this->post_text;
    }
    
    public function getpost_title()
    {
        return $this->post_title;
    }
}
