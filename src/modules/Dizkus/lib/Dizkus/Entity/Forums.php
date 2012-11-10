<?php

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


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
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_ForumSubscriptions", mappedBy="forum_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $forum_id;

    /**
     * The following are annotations which define the forum_name field.
     * 
     * @ORM\Column(type="string", length=150)
     */
    private $forum_name = '';


    /**
     * The following are annotations which define the forum_desc field.
     * 
     * @ORM\Column(type="text")
     */
    private $forum_desc = '';
    
    
    /**
     * The following are annotations which define the forum_topics field.
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
     * The following are annotations which define the forum_last_post_id field.
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $forum_last_post_id = null;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;


    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Forums", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="forum_id")
     */
    private $parent;



    /**
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Forums", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;


    /**
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Posts")
     * @ORM\JoinColumn(name="forum_last_post_id", referencedColumnName="post_id")
     */
    private $last_post;



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
     * @ORM\Column(type="integer")
     */
    private $forum_moduleref = 0;



    /**
     * The following are annotations which define the forum_pntopic field.
     *
     * @ORM\Column(type="integer", length=4)
     */
    private $forum_pntopic = 0;








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
     * The following are annotations which define the forum order field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_order = 0;




    /**
     * @ORM\OneToOne(targetEntity="Dizkus_Entity_Favorites",cascade={"persist"})
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

    public function getlast_post()
    {
        return $this->last_post;
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











    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getLft()
    {
        return $this->lft;
    }


    public function getLvl()
    {
        return $this->lvl;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getRgt()
    {
        return $this->rgt;
    }




    public function setParent(Dizkus_Entity_Forums $parent = null)
    {
        $this->parent = $parent;
    }





    public function getCat_id()
    {
        return $this->cat_id;
    }

    public function getParent_id()
    {
        return $this->parent_id;
    }


    public function getForum_order()
    {
        return $this->forum_order;
    }


    public function getTopics()
    {
        return $this->topics;
    }

    public function getNumberOfTopics()
    {
        return count($this->topics);
    }


}