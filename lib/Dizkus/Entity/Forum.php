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
 * Forums entity class
 *
 * @ORM\Entity
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="dizkus_forums")
 * @ORM\Entity(repositoryClass="Dizkus_Entity_Repository_ForumRepository")
 */
class Dizkus_Entity_Forum extends Zikula_EntityAccess
{

    /**
     * forum_id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $forum_id;

    /**
     * forum_name
     * 
     * @ORM\Column(type="string", length=150)
     */
    private $forum_name = '';

    /**
     * forum_desc
     * 
     * @ORM\Column(type="text")
     */
    private $forum_desc = '';

    /**
     * forum_topics
     *
     * @ORM\Column(type="integer")
     */
    private $forum_topics = 0;

    /**
     * The number of posts of the forum
     *
     * @ORM\Column(type="integer")
     */
    private $forum_posts = 0;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="forum_order", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl = 1;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt = 3;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="cat_id", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forum", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="forum_id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Forum", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Post", cascade={"persist"})
     * @ORM\JoinColumn(name="forum_last_post_id", referencedColumnName="post_id", nullable=true)
     */
    private $last_post;

    /**
     * forum_pop3_active
     *
     * @ORM\Column(type="boolean")
     */
    private $forum_pop3_active = false;

    /**
     * forum_pop3_server
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
     * forum_pop3_login
     *
     * @ORM\Column(type="string", length=60)
     */
    private $forum_pop3_login = '';

    /**
     * forum_pop3_password
     *
     * @ORM\Column(type="string", length=60)
     */
    private $forum_pop3_password = '';

    /**
     * forum_pop3_interval
     *
     * @ORM\Column(type="integer", length=4)
     */
    private $forum_pop3_interval = 0;

    /**
     * forum_pop3_interval
     *
     * @ORM\Column(type="integer")
     */
    private $forum_pop3_lastconnect = 0;

    /**
     * forum_pop3_interval
     *
     * @ORM\Column(type="string", length=60)
     */
    private $forum_pop3_pnuser = '';

    /**
     * forum_pop3_interval
     *
     * @ORM\Column(type="string", length=40)
     */
    private $forum_pop3_pnpassword = '';

    /**
     * forum_pop3_interval
     *
     * @ORM\Column(type="string", length=255)
     */
    private $forum_pop3_matchstring = '';

    /**
     * forum_moduleref
     *
     * @ORM\Column(type="integer")
     */
    private $forum_moduleref = 0;

    /**
     * forum_pntopic
     *
     * @ORM\Column(type="integer", length=4)
     */
    private $forum_pntopic = 0;

    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_ForumUserFavorite", mappedBy="forum")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id", nullable=true)
     */
    private $favorites;

    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Topic", mappedBy="forum")
     */
    private $topics;

    /**
     * Dizkus_Entity_Moderator_User collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Moderator_User", mappedBy="forum", cascade={"persist"}, orphanRemoval=true)
     */
    private $moderatorUsers;

    /**
     * Dizkus_Entity_Moderator_Group collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Moderator_Group", mappedBy="forum", cascade={"persist"}, orphanRemoval=true)
     */
    private $moderatorGroups;

    /**
     * Subscriptions
     * 
     * Dizkus_Entity_ForumSubscription collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_ForumSubscription", mappedBy="forum")
     */
    private $subscriptions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->favorites = new ArrayCollection();
        $this->topics = new ArrayCollection();
        $this->moderatorUsers = new ArrayCollection();
        $this->moderatorGroups = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }

    public function getForum_id()
    {
        return $this->forum_id;
    }

    public function setForum_id($forum_id)
    {
        $this->forum_id = $forum_id;
    }

    public function getForum_name()
    {
        return $this->forum_name;
    }

    public function setForum_name($forum_name)
    {
        $this->forum_name = $forum_name;
    }

    public function getForum_desc()
    {
        return $this->forum_desc;
    }

    public function setForum_desc($forum_name)
    {
        $this->forum_desc = $forum_name;
    }

    public function getForum_topics()
    {
        return $this->forum_topics;
    }

    public function setForum_topics($topics)
    {
        $this->forum_topics = $topics;
    }

    public function incrementForum_topics()
    {
        $this->forum_topics++;
    }
    
    public function decrementForum_topics()
    {
        $this->forum_topics--;
    }

    public function getForum_posts()
    {
        return $this->forum_posts;
    }

    public function setForum_posts($posts)
    {
        $this->forum_posts = $posts;
    }

    public function incrementForum_posts()
    {
        $this->forum_posts++;
    }
    
    public function decrementForum_posts()
    {
        $this->forum_posts--;
    }

    public function getLft()
    {
        return $this->lft;
    }

    public function getLvl()
    {
        return $this->lvl;
    }

    public function getRgt()
    {
        return $this->rgt;
    }

    public function getRoot()
    {
        return $this->root;
    }

    /**
     * get Forum parent
     * 
     * @return Dizkus_Entity_Forum
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Dizkus_Entity_Forum $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * get Children
     * 
     * @return Dizkus_Entity_Forum
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * get last post in Forum
     * 
     * @return Dizkus_Entity_Post
     */
    public function getLast_post()
    {
        return $this->last_post;
    }

    public function setLast_post(Dizkus_Entity_Post $post)
    {
        return $this->last_post = $post;
    }

    public function getForum_pop3_active()
    {
        return $this->forum_pop3_active;
    }

    public function setForum_pop3_active($pop3_active)
    {
        $this->forum_pop3_active = $pop3_active;
    }

    public function setExtsource($extsource)
    {
        if ($extsource == 'mail2forum') {
            $this->forum_pop3_active = true;
        } else {
            $this->forum_pop3_active = false;
        }
    }

    public function getForum_pop3_server()
    {
        return $this->forum_pop3_server;
    }

    public function setForum_pop3_server($pop3_server)
    {
        $this->forum_pop3_server = $pop3_server;
    }

    public function getForum_pop3_port()
    {
        return $this->forum_pop3_port;
    }

    public function setForum_pop3_port($pop3_port)
    {
        $this->forum_pop3_port = $pop3_port;
    }

    public function getForum_pop3_login()
    {
        return $this->forum_pop3_login;
    }

    public function setForum_pop3_login($pop3_login)
    {
        $this->forum_pop3_login = $pop3_login;
    }

    public function getForum_pop3_password()
    {
        return $this->forum_pop3_password;
    }

    public function setForum_pop3_password($pop3_password)
    {
        $this->forum_pop3_password = $pop3_password;
    }

    public function getForum_pop3_interval()
    {
        return $this->forum_pop3_interval;
    }

    public function setForum_pop3_interval($pop3_interval)
    {
        $this->forum_pop3_interval = $pop3_interval;
    }

    public function getForum_pop3_lastconnect()
    {
        return $this->forum_pop3_lastconnect;
    }

    public function setForum_pop3_lastconnect($pop3_lastconnection)
    {
        $this->forum_pop3_lastconnect = $pop3_lastconnection;
    }

    public function getForum_pop3_pnuser()
    {
        return $this->forum_pop3_pnuser;
    }

    public function setForum_pop3_pnuser($pop3_pnuser)
    {
        $this->forum_pop3_pnuser = $pop3_pnuser;
    }

    public function getForum_pop3_pnpassword()
    {
        return $this->forum_pop3_pnpassword;
    }

    public function setForum_pop3_pnpassword($pop3_pnpassword)
    {
        $this->forum_pop3_pnpassword = $pop3_pnpassword;
    }

    public function getForum_pop3_matchstring()
    {
        return $this->forum_pop3_matchstring;
    }

    public function setForum_pop3_matchstring($pop3_matchstring)
    {
        $this->forum_pop3_matchstring = $pop3_matchstring;
    }

    public function getForum_moduleref()
    {
        return $this->forum_moduleref;
    }

    public function setForum_moduleref($moduleref)
    {
        $this->forum_moduleref = $moduleref;
    }

    public function getForum_pntopic()
    {
        return $this->forum_pntopic;
    }

    public function setForum_pntopic($pntopic)
    {
        $this->forum_pntopic = $pntopic;
    }

    /**
     * get ForumUsers that have marked this forum as favorite
     * @return Dizkus_Entity_ForumUserFavorites collection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * get forum Topics
     * 
     * @return Dizkus_Entity_Topic
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * get Moderators
     * 
     * @return Dizkus_Entity_Moderator_User collection
     */
    public function getModeratorUsers()
    {
        return $this->moderatorUsers;
    }

    public function getModeratorUsersAsIdArray()
    {
        $output = array();
        foreach ($this->moderatorUsers as $moderatorUser) {
            $output[] = $moderatorUser->getForumUser()->getUser_id();
        }

        return $output;
    }

    public function setModeratorUsers($users)
    {
        // clear the associated users
        $this->moderatorUsers->clear();
        
        // add users
        foreach ($users as $uid) {
            $moderator = new Dizkus_Entity_Moderator_User();
            $managedForumUser = new Dizkus_Manager_ForumUser($uid);
            $moderator->setForumUser($managedForumUser->get());
            $moderator->setForum($this);
            $this->moderatorUsers->add($moderator);
        }
    }

    /**
     * get forum moderator groups
     * 
     * @return Dizkus_Entity_Moderator_Group collection
     */
    public function getModeratorGroups()
    {
        return $this->moderatorGroups;
    }

    public function getModeratorGroupsAsIdArray()
    {
        $output = array();
        foreach ($this->moderatorGroups as $moderatorGroup) {
            $output[] = $moderatorGroup->getGroup()->getGid();
        }

        return $output;
    }

    public function setModeratorGroups($gids)
    {
        // remove the associated moderators
        $this->moderatorGroups->clear();
        
        // add moderators
        foreach ($gids as $gid) {
            $moderatorGroup = new Dizkus_Entity_Moderator_Group();
            $em = ServiceUtil::getService('doctrine.entitymanager');
            $group = $em->find('Groups\Entity\GroupEntity', $gid);
            $moderatorGroup->setGroup($group);
            $moderatorGroup->setForum($this);
            $this->moderatorGroups->add($moderatorGroup);
        }
    }

    /**
     * get Forum Subscriptions
     * @return Dizkus_Entity_ForumSubscription collection
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

}