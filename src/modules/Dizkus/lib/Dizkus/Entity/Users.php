<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class Dizkus_Entity_Users extends Zikula_EntityAccess
{
    /**
     * The following are annotations which define the uid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $uid;

    /**
     * The following are annotations which define the uname field.
     * 
     * @ORM\Column(type="string", length="25")
     */
    private $uname = '';

    /**
     * attributes
     * 
     * @var Dizkus_Entity_UsersRanks
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_UsersRanks", 
     *                mappedBy="object_id", cascade={"all"}, 
     *                orphanRemoval=true)
     */
    private $attributes;

    /**
     * Topics subscribed by the user.
     *
     * @ORM\ManyToMany(targetEntity="Dizkus_Entity_Topics", mappedBy="subscribers", fetch="LAZY")
     */
    private $topicSubscriptions;

    /**
     * Forums subscribed by the user.
     *
     * @ORM\ManyToMany(targetEntity="Dizkus_Entity_Forums", mappedBy="moderators", fetch="LAZY")
     */
    private $forumModerated;

    /**
     * Forums subscribed by the user.
     *
     * @ORM\ManyToMany(targetEntity="Dizkus_Entity_Forums", mappedBy="subscribers", fetch="LAZY")
     */
    private $forumSubscriptions;

    /**
     * Forums favorited by the user.
     *
     * @ORM\ManyToMany(targetEntity="Dizkus_Entity_Forums", mappedBy="favorited", fetch="LAZY")
     */
    private $forumFavorites;

    /**
     * Topics posted by the user.
     *
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Topics", mappedBy="poster", fetch="LAZY")
     */
    private $forumTopics;

    /**
     * Posts by the user.
     *
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_Posts", mappedBy="poster", fetch="LAZY")
     */
    private $forumPosts;



    public function getuid()
    {
        return $this->uid;
    }
    
    public function getuname()
    {
        return $this->uname;
    }

    public function getAttributes()
    {
        
        return $this->attributes;
    }

    /**
     * Construction function
     */
    public function __construct()
    {
        $this->attributes = new Doctrine\Common\Collections\ArrayCollection();
        return true;
    }
}
