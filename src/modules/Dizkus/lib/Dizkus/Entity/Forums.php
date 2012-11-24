<?php

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * Forums entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="dizkus_forums")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
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

    public function getforum_id()
    {
        return $this->forum_id;
    }

    public function setforum_id($forum_id)
    {
        $this->forum_id = $forum_id;
    }



    /**
     * The following are annotations which define the forum_name field.
     * 
     * @ORM\Column(type="string", length=150)
     */
    private $forum_name = '';

    public function getforum_name()
    {
        return $this->forum_name;
    }

    public function setforum_name($forum_name)
    {
        $this->forum_name = $forum_name;
    }




    /**
     * The following are annotations which define the forum_desc field.
     * 
     * @ORM\Column(type="text")
     */
    private $forum_desc = '';


    public function getforum_desc()
    {
        return $this->forum_desc;
    }


    public function setforum_desc($forum_name)
    {
        $this->forum_desc = $forum_name;
    }





    /**
     * The following are annotations which define the forum_topics field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_topics = 0;

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

    /**
     * The number of posts of the forum
     *
     * @ORM\Column(type="integer")
     */
    private $forum_posts = 0;

    public function getforum_posts()
    {
        return $this->forum_posts;
    }

    public function setforum_posts($posts)
    {
        $this->forum_posts = $posts;
    }

    public function incrementForum_posts()
    {
        $this->forum_posts++;
    }



    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="forum_order", type="integer")
     */
    private $lft;


    public function getLft()
    {
        return $this->lft;
    }





    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl = 1;

    public function getLvl()
    {
        return $this->lvl;
    }



    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt = 3;

    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="cat_id", type="integer", nullable=true)
     */
    private $root;

    public function getRoot()
    {
        return $this->root;
    }



    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forums", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="forum_id")
     */
    private $parent;

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Dizkus_Entity_Forums $parent = null)
    {
        $this->parent = $parent;
    }


    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Forums", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    public function getChildren()
    {
        return $this->children;
    }


    /**
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Post", cascade={"persist"})
     * @ORM\JoinColumn(name="forum_last_post_id", referencedColumnName="post_id", nullable=true)
     */
    private $last_post;

    public function getlast_post()
    {
        return $this->last_post;
    }


    public function setlast_post($post)
    {
        return $this->last_post = $post;
    }



    /**
     * The following are annotations which define the forum_pop3_active field.
     *
     * @ORM\Column(type="boolean")
     */
    private $forum_pop3_active = false;

    public function getforum_pop3_active()
    {
        return $this->forum_pop3_active;
    }


    public function setforum_pop3_active($pop3_active)
    {
        $this->forum_pop3_active = $pop3_active;
    }


    public function setextsource($extsource)
    {
        if ($extsource == 'mail2forum') {
            $this->forum_pop3_active = true;
        } else {
            $this->forum_pop3_active = false;
        }
    }



    /**
     * The following are annotations which define the forum_pop3_server field.
     *
     * @ORM\Column(type="boolean")
     */
    private $forum_pop3_server = '';


    public function getforum_pop3_server()
    {
        return $this->forum_pop3_server;
    }


    public function setforum_pop3_server($pop3_server)
    {
        $this->forum_pop3_server = $pop3_server;
    }




    /**
     * The following are annotations which define the forum_pop3_port field.
     *
     * @ORM\Column(type="integer", length=5)
     */
    private $forum_pop3_port = 110;


    public function getforum_pop3_port()
    {
        return $this->forum_pop3_port;
    }

    public function setforum_pop3_port($pop3_port)
    {
        $this->forum_pop3_port = $pop3_port;
    }



    /**
     * The following are annotations which define the forum_pop3_login field.
     *
     * @ORM\Column(type="string", length=60)
     */
    private $forum_pop3_login = '';


    public function getforum_pop3_login()
    {
        return $this->forum_pop3_login;
    }

    public function setforum_pop3_login($pop3_login)
    {
        $this->forum_pop3_login = $pop3_login ;
    }



    /**
     * The following are annotations which define the forum_pop3_password field.
     *
     * @ORM\Column(type="string", length=60)
     */
    private $forum_pop3_password = '';


    public function getforum_pop3_password()
    {
        return $this->forum_pop3_password;
    }


    public function setforum_pop3_password($pop3_password)
    {
        $this->forum_pop3_password = $pop3_password;
    }



    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="integer", length=4)
     */
    private $forum_pop3_interval = 0;

    public function getforum_pop3_interval()
    {
        return $this->forum_pop3_interval;
    }

    public function setforum_pop3_interval($pop3_interval)
    {
        $this->forum_pop3_interval = $pop3_interval;
    }



    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_pop3_lastconnect = 0;


    public function getforum_pop3_lastconnect()
    {
        return $this->forum_pop3_lastconnect;
    }

    public function setforum_pop3_lastconnect($pop3_lastconnection)
    {
        $this->forum_pop3_lastconnect = $pop3_lastconnection;
    }



    /**
     * forum moderators
     *
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Moderators",
     *                mappedBy="forum_id", cascade={"all"},
     *                orphanRemoval=false)
     */
    private $forum_mods;





    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="string", length=60)
     */
    private $forum_pop3_pnuser = '';


    public function getforum_pop3_pnuser()
    {
        return $this->forum_pop3_pnuser;
    }

    public function setforum_pop3_pnuser($pop3_pnuser)
    {
        $this->forum_pop3_pnuser = $pop3_pnuser;
    }




    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="string", length=40)
     */
    private $forum_pop3_pnpassword = '';


    public function getforum_pop3_pnpassword()
    {
        return $this->forum_pop3_pnpassword;
    }

    public function setforum_pop3_pnpassword($pop3_pnpassword)
    {
        $this->forum_pop3_pnpassword = $pop3_pnpassword;
    }


    /**
     * The following are annotations which define the forum_pop3_interval field.
     *
     * @ORM\Column(type="string", length=255)
     */
    private $forum_pop3_matchstring = '';


    public function getforum_pop3_matchstring()
    {
        return $this->forum_pop3_matchstring;
    }


    public function setforum_pop3_matchstring($pop3_matchstring)
    {
        $this->forum_pop3_matchstring = $pop3_matchstring;
    }





    /**
     * The following are annotations which define the forum_moduleref field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_moduleref = 0;


    public function getforum_moduleref()
    {
        return $this->forum_moduleref;
    }


    public function setforum_moduleref($moduleref)
    {
        $this->forum_moduleref = $moduleref;
    }




    /**
     * The following are annotations which define the forum_pntopic field.
     *
     * @ORM\Column(type="integer", length=4)
     */
    private $forum_pntopic = 0;


    public function getforum_pntopic()
    {
        return $this->forum_pntopic;
    }


    public function setforum_pntopic($pntopic)
    {
        $this->forum_pntopic = $pntopic;
    }








    /**
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Favorites", cascade={"persist"})
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="forum_id", nullable=true)
     */
    private $favorites;


    public function getfavorites()
    {
        return $this->favorites;
    }


    public function getforum_mods()
    {
        return $this->forum_mods;
    }



    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Topic", mappedBy="forum")
     */
    private $topics;

    public function getTopics()
    {
        return $this->topics;
    }



    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Moderator_User", mappedBy="forum", cascade={"persist"}, orphanRemoval=true)
     */
    private $moderatorUsers;

    public function getModeratorUsers()
    {
        return $this->moderatorUsers;
    }

    public function getModeratorUsersAsArray()
    {
        $output = array();
        foreach($this->moderatorUsers as $user) {
            $output[] = $user->getUser_id();
        }

        return $output;
    }


    public function setModeratorUsers($users)
    {
        // remove moderators
        foreach($this->moderatorUsers as $key => $moderator) {
            $i = array_search($moderator->getUser_id(), $users);
            if (!$i) {
                $this->moderatorUsers->remove($key);
            } else {
                unset($users[$i]);
            }
        }
        // add moderators
        foreach($users as $uid) {
            $newModerator = new Dizkus_Entity_Moderator_User();
            $newModerator->setUser_id($uid);
            $newModerator->setForum($this);
            $this->moderatorUsers[] = $newModerator;
        }
    }




    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Moderator_Group", mappedBy="forum", cascade={"persist"}, orphanRemoval=true)
     */
    private $moderatorGroups;

    public function getModeratorGroups()
    {
        return $this->moderatorGroups;
    }

    public function getModeratorGroupsAsArray()
    {
        $output = array();
        foreach($this->moderatorGroups as $moderatorGroup) {
            $output[] = $moderatorGroup->getGroup()->getGid();
        }

        return $output;
    }


    public function setModeratorGroups($moderatorGroups)
    {
        // remove moderators
        foreach($this->moderatorGroups as $key => $moderatorGroup) {
            $i = array_search($moderatorGroup->getGroup()->getGid(), $moderatorGroups);
            if (!$i) {
                $this->moderatorGroups->remove($key);
            } else {
                unset($moderatorGroups[$i]);
            }
        }
        // add moderators
        foreach($moderatorGroups as $gid) {
            $em = ServiceUtil::getService('doctrine.entitymanager');
            $group = $this->_topic = $em->find('Dizkus_Entity_Group', $gid);
            $newModerator = new Dizkus_Entity_Moderator_Group();
            $newModerator->setGroup($group);
            $newModerator->setForum($this);
            $this->moderatorGroups[] = $newModerator;
        }
    }




    public function __construct() {
        $this->topics = new \Doctrine\Common\Collections\ArrayCollection();
        $this->moderatorUsers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->moderatorGroups = new \Doctrine\Common\Collections\ArrayCollection();
    }





}