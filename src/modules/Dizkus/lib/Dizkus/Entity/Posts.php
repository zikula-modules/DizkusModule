<?php

use Doctrine\ORM\Mapping as ORM;


/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_posts")
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
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $topic_id = 0;
    
    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $forum_id = 0;

    /**
     * The following are annotations which define the poster_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $poster_id = 0;

    /**
     * The following are annotations which define the post_time field.
     * 
     * @ORM\Column(type="string", length="20")
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
     * @ORM\Column(type="string", length="100")
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
    
    public function getpost_text()
    {
        return $this->post_text;
    }
    
    public function getpost_title()
    {
        return $this->post_title;
    }
    

}