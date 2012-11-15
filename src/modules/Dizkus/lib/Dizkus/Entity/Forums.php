<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forums", indexes={@ORM\Index(name="forum_last_post_idx", columns={"forum_last_post_id"}), @ORM\Index(name="forum_moduleref_idx", columns={"forum_moduleref"})})
 */
class Dizkus_Entity_Forums extends Zikula_EntityAccess
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
    private $forum_name = '';

    /**
     * The following are annotations which define the forum_desc field.
     * 
     * @ORM\Column(type="text")
     */
    private $forum_desc = '';

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
    private $parent_id = 0;

    /**
     * The following are annotations which define the forum topics field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_topics = 0;

    /**
     * The following are annotations which define the forum posts field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_posts = 0;

    /**
     * The following are annotations which define the forum_last_post_id field.
     *
     * @ORM\Column(type="integer", unique=false)
     */
    private $forum_last_post_id = 0;

    /**
     * The following are annotations which define the forum order field.
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
     * @ORM\Column(type="boolean")
     */
    private $forum_pop3_server = '';

    /**
     * The following are annotations which define the forum_pop3_port field.
     *
     * @ORM\Column(type="integer", length=5)
     */
    private $forum_pop3_port = 110;

    /**
     * The following are annotations which define the forum_pop3_login field.
     *
     * @ORM\Column(type="string", length=60)
     */
    private $forum_pop3_login = '';

    /**
     * The following are annotations which define the forum_pop3_password field.
     *
     * @ORM\Column(type="string", length=60)
     */
    private $forum_pop3_password = '';

    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="integer", length=4)
     */
    private $forum_pop3_interval = 0;

    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_pop3_lastconnect = 0;

    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="string", length=60)
     */
    private $forum_pop3_pnuser = '';

    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="string", length=40)
     */
    private $forum_pop3_pnpassword = '';

    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="string", length=255)
     */
    private $forum_pop3_matchstring = '';

    /**
     * The following are annotations which define the forum_moduleref field.
     *
     * @ORM\Column(type="integer", unique=false)
     */
    private $forum_moduleref = 0;

    /**
     * The following are annotations which define the forum_pntopic field.
     *
     * @ORM\Column(type="integer", length=4)
     */
    private $forum_pntopic = 0;

    /**
     * Category which the forum belongs to.
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Categories", inversedBy="forums")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="cat_id")
     */
    private $category;

    /**
     * Parent forum.
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forums")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="forum_id")
     */
    private $parent;

    /**
     * Last post.
     *
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Posts")
     * @ORM\JoinColumn(name="forum_last_post_id", referencedColumnName="post_id")
     */
    private $last_post;

    /**
     * Forum moderators.
     *
     * @ORM\ManyToMany(targetEntity="Dizkus_Entity_Users", inversedBy="forumModerated", fetch="LAZY")
     * @ORM\JoinTable(name="dizkus_forum_mods",
     *   joinColumns={@ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="uid")}
     * )
     */
    private $moderators;

    /**
     * Subscribed users.
     *
     * @ORM\ManyToMany(targetEntity="Dizkus_Entity_Users", inversedBy="forumSubscriptions", fetch="LAZY")
     * @ORM\JoinTable(name="dizkus_subscription",
     *   joinColumns={@ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="uid")}
     * )
     */
    private $subscribers;

    /**
     * Favorite users.
     *
     * @ORM\ManyToMany(targetEntity="Dizkus_Entity_Users", inversedBy="forumFavorites", fetch="LAZY")
     * @ORM\JoinTable(name="dizkus_forum_favorites",
     *   joinColumns={@ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id")},
     *   inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="uid")}
     * )
     */
    private $favorited;



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

    public function getcat_id()
    {
        return $this->cat_id;
    }

    public function getForum_topics()
    {
        return $this->forum_topics;
    }

    public function getForum_last_post_id()
    {
        return $this->forum_topics;
    }

    public function getforum_posts()
    {
        return $this->forum_posts;
    }

    public function getparent_id()
    {
        return $this->parent_id;
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

    public function getPnuser()
    {
        return $this->pnuser;
    }

    public function getPnpassword()
    {
        return $this->pnpassword;
    }

    public function getPop3_test()
    {
        return '';
    }

    public function getPop3_server()
    {
        return $this->forum_pop3_server;
    }

    public function getPop3_port()
    {
        return $this->forum_pop3_port;
    }

    public function getPop3_login()
    {
        return $this->forum_pop3_login;
    }

    public function getPop3_password()
    {
        return $this->forum_pop3_password;
    }

    public function getPop3_matchstring()
    {
        return $this->forum_pop3_matchstring;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getParent()
    {
        if ($this->parent_id == 0) {
            return 'c'.$this->cat_id;
        } else {
            return $this->parent_id;
        }
    }

    public function getLast_post()
    {
        return $this->last_post;
    }

    public function getModerators()
    {
        return $this->moderators;
    }

    public function getSubscribers()
    {
        return $this->subscribers;
    }

    public function getFavorited()
    {
        return $this->favorited;
    }

    public function setforum_id($forum_id)
    {
        $this->forum_id = $forum_id;
    }

    public function setforum_name($forum_name)
    {
        $this->forum_name = $forum_name;
    }

    public function setforum_desc($forum_name)
    {
        $this->forum_desc = $forum_name;
    }

    public function setparent_id($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function setcat_id($cat_id)
    {
        $this->cat_id = $cat_id;
    }

    public function setextsource($extsource)
    {
        if ($extsource == 'mail2forum') {
            $this->forum_pop3_active = true;
        } else {
            $this->forum_pop3_active = false;
        }
    }

    public function setpnuser($pnuser)
    {
        $this->pnuser = $pnuser;
    }

    public function setpop3_test($pop3_test)
    {
        $this->pop3_test = $pop3_test;
    }

    public function setpop3_server($pop3_server)
    {
        $this->pop3_server = $pop3_server;
    }

    public function setPop3_port($pop3_port)
    {
        $this->pop3_port = $pop3_port;
    }

    public function setpop3_login($pop3_login)
    {
        $this->pop3_login = $pop3_login;
    }

    public function setPop3_matchstring($pop3_matchstring)
    {
        $this->pop3_matchstring = $pop3_matchstring;
    }

    public function setpnpassword($pnpassword)
    {
        $this->pnpassword = $pnpassword;
    }

    public function setpop3_password($pop3_password)
    {
        $this->pop3_password = $pop3_password;
    }

    public function setparent($parent)
    {
        // category parent
        if (substr($parent, 0 , 1) == 'c') {
            $parent = substr($parent, 1);
            if ($parent != $this->cat_id) {
                // change category
                $this->cat_id = (int)$parent;
            }
            $this->parent_id = 0;
            return;
        }

        if ($parent != $this->parent_id) {
            // change forum
            $this->parent_id = $parent;
            $this->cat_id = 0;
            $this->forum_order = ModUtil::apiFunc('Dizkus', 'Forum', 'getHighestOrder', $parent);
        }
    }

    public function setforum_order($forum_order)
    {
        $this->forum_order = $forum_order;
    }
}
