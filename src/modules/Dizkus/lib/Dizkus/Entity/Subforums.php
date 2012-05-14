<?php

use Doctrine\ORM\Mapping as ORM;


/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forums")
 */
class Dizkus_Entity_Subforums extends Zikula_EntityAccess
{

    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $forum_id;

    /**
     * The following are annotations which define the forum_name field.
     * 
     * @ORM\Column(type="string", length="150")
     */
    private $forum_name;

    /**
     * The following are annotations which define the forum_desc field.
     * 
     * @ORM\Column(type="text")
     */
    private $forum_desc;

    /**
     * The following are annotations which define the forum_topics field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_topics = 0;

    /**
     * The following are annotations which define the forum_posts field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_posts = 0;

    /**
     * The following are annotations which define the forum_last_post_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_last_post_id = 0;

    /**
     * The following are annotations which define the cat_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $cat_id = 0;

    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $is_subforum = 0;

    /**
     * The following are annotations which define the forum_order field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_order = 0;

    /**
     * The following are annotations which define the forum_pop3_active field.
     *
     * @ORM\Column(type="boolean")
     */
    private $forum_pop3_active = false;

    /**
     * The following are annotations which define the forum_pop3_server field.
     * 
     * @ORM\Column(type="string", length="60")
     */
    private $forum_pop3_server = '';

    /**
     * The following are annotations which define the forum_pop3_port field.
     *
     * @ORM\Column(type="integer", length="5")
     */
    private $forum_pop3_port = 110;

    /**
     * The following are annotations which define the forum_pop3_login field.
     * 
     * @ORM\Column(type="string", length="60")
     */
    private $forum_pop3_login = '';
    
    /**
     * The following are annotations which define the forum_pop3_password field.
     * 
     * @ORM\Column(type="string", length="60")
     */
    private $forum_pop3_password = '';
    
    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="integer", length="4")
     */
    private $forum_pop3_interval = 0;

    /**
     * The following are annotations which define the forum_pop3_lastconnect field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_pop3_lastconnect = 0;

    /**
     * The following are annotations which define the forum_pop3_pnuser field.
     * 
     * @ORM\Column(type="string", length="60")
     */
    private $forum_pop3_pnuser = '';
    
    /**
     * The following are annotations which define the forum_pop3_pnpassword field.
     * 
     * @ORM\Column(type="string", length="40")
     */
    private $forum_pop3_pnpassword = '';
    
    /**
     * The following are annotations which define the forum_pop3_matchstring field.
     * 
     * @ORM\Column(type="string", length="255")
     */
    private $forum_pop3_matchstring = '';
    
    /**
     * The following are annotations which define the forum_moduleref field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_moduleref = 0;

    /**
     * The following are annotations which define the forum_pntopic field.
     *
     * @ORM\Column(type="integer", length="4")
     */
    private $forum_pntopic = 0;

    
    
    public function getforum_id()
    {
        return $this->forum_id;
    }
    
    public function getforum_name()
    {
        return $this->forum_name;
    }
    
    public function getforum_desc()
    {
        return $this->forum_desc;
    }
    
    public function getforum_topics()
    {
        return $this->forum_topics;
    }
    
    public function getforum_posts()
    {
        return $this->forum_posts;
    }
    
    public function getforum_last_post_id()
    {
        return $this->forum_last_post_id;
    }
    
    public function getcat_id()
    {
        return $this->cat_id;
    }
    
    public function getis_subforum()
    {
        return $this->is_subforum;
    }
    
    public function getforum_order()
    {
        return $this->forum_order;
    }
    
    public function getforum_pop3_active()
    {
        return $this->forum_pop3_active;
    }
    
    public function getforum_pop3_server()
    {
        return $this->forum_pop3_server;
    }
    
    public function getforum_pop3_port()
    {
        return $this->forum_pop3_port;
    }
    
    public function getforum_pop3_login()
    {
        return $this->forum_pop3_login;
    }
    
    public function getforum_pop3_password()
    {
        return $this->forum_pop3_password;
    }
    
    public function getforum_pop3_interval()
    {
        return $this->forum_pop3_interval;
    }
    
    public function getforum_pop3_lastconnect()
    {
        return $this->forum_pop3_lastconnect;
    }
    
    public function getforum_pop3_pnuser()
    {
        return $this->forum_pop3_pnuser;
    }
    
    public function getforum_pop3_pnpassword()
    {
        return $this->forum_pop3_pnpassword;
    }
    
    public function getforum_pop3_matchstring()
    {
        return $this->forum_pop3_matchstring;
    }
    
    public function getforum_moduleref()
    {
        return $this->forum_moduleref;
    }
    
    public function getforum_pntopic()
    {
        return $this->forum_pntopic;
    }

}