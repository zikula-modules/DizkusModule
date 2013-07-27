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
    const ROOTNAME = 'ROOT243fs546g1565h88u9fdjkh3tnbti8f2eo78f';

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
    private $name = '';

    /**
     * description
     * 
     * @ORM\Column(type="text")
     */
    private $description = '';

    /**
     * topicCount
     *
     * @ORM\Column(type="integer")
     */
    private $topicCount = 0;

    /**
     * The number of posts of the forum
     *
     * @ORM\Column(type="integer")
     */
    private $postCount = 0;

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
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Forum", mappedBy="parent", cascade={"remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Post", cascade={"persist"})
     * @ORM\JoinColumn(name="last_post_id", referencedColumnName="post_id", nullable=true)
     */
    private $last_post;

    /**
     * pop3Connection
     *
     * @ORM\Column(type="object", nullable=true)
     */
    private $pop3Connection = null;

    /**
     * moduleref
     *
     * @ORM\Column(type="integer")
     */
    private $moduleref = 0;

    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_ForumUserFavorite", mappedBy="forum", cascade={"remove"})
     */
    private $favorites;

    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Topic", mappedBy="forum", cascade={"remove"})
     */
    private $topics;

    /**
     * Dizkus_Entity_Moderator_User collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Moderator_User", mappedBy="forum", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $moderatorUsers;

    /**
     * Dizkus_Entity_Moderator_Group collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Moderator_Group", mappedBy="forum", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $moderatorGroups;

    /**
     * Subscriptions
     * 
     * Dizkus_Entity_ForumSubscription collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_ForumSubscription", mappedBy="forum", cascade={"remove"})
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
        $this->children = new ArrayCollection();
    }

    public function getForum_id()
    {
        return $this->forum_id;
    }

    public function setForum_id($forum_id)
    {
        $this->forum_id = $forum_id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getTopicCount()
    {
        return $this->topicCount;
    }

    public function setTopicCount($topics)
    {
        $this->topicCount = $topics;
    }

    public function incrementTopicCount()
    {
        $this->topicCount++;
    }
    
    public function decrementTopicCount()
    {
        $this->topicCount--;
    }

    public function getPostCount()
    {
        return $this->postCount;
    }

    public function setPostCount($posts)
    {
        $this->postCount = $posts;
    }

    public function incrementPostCount()
    {
        $this->postCount++;
    }
    
    public function decrementPostCount()
    {
        $this->postCount--;
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
     * @return ArrayCollection Dizkus_Entity_Forum
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * remove all the child forums
     */
    public function removeChildren()
    {
        $this->children->clear();
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

    public function getPop3Connection()
    {
        return $this->pop3Connection;
    }

    public function setPop3Connection(Dizkus_Connection_Pop3 $connection)
    {
        $this->pop3Connection = $connection;
    }

    public function getModuleref()
    {
        return $this->moduleref;
    }

    public function setModuleref($moduleref)
    {
        $this->moduleref = $moduleref;
    }

    /**
     * get ForumUsers that have marked this forum as favorite
     * @return ArrayCollection Dizkus_Entity_ForumUserFavorites
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * get forum Topics
     * 
     * @return ArrayCollection Dizkus_Entity_Topic
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * get Moderators
     * 
     * @return ArrayCollection Dizkus_Entity_Moderator_User
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
     * @return ArrayCollection Dizkus_Entity_Moderator_Group
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
            $group = $em->find('Zikula\Module\GroupsModule\Entity\GroupEntity', $gid);
            $moderatorGroup->setGroup($group);
            $moderatorGroup->setForum($this);
            $this->moderatorGroups->add($moderatorGroup);
        }
    }

    /**
     * get Forum Subscriptions
     * @return ArrayCollection Dizkus_Entity_ForumSubscription
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

}