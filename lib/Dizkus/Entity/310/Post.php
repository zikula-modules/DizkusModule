<?php

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Old Post entity class.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_posts")
 */
class Dizkus_Entity_310_Post extends Zikula_EntityAccess
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
     * The following are annotations which define the topic_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $topic_id = 0;

    public function gettopic_id()
    {
        return $this->topic_id;
    }

    public function settopic_id($id)
    {
        return $this->topic_id = $id;
    }

    /**
     * The following are annotations which define the forum_id field.
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
     * @ORM\Column(type="integer"))
     */
    private $poster_id;

    public function getposter_id()
    {
        return $this->poster_id;
    }

}