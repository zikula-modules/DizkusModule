<?php
/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\DizkusModule\Connection\Pop3Connection;

/**
 * Forum entity class.
 *
 * @ORM\Entity
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="dizkus_forums")
 * @ORM\Entity(repositoryClass="Zikula\DizkusModule\Entity\Repository\ForumRepository")
 */
class ForumEntity extends EntityAccess
{
    const ROOTNAME = 'ROOT243fs546g1565h88u9fdjkh3tnbti8f2eo78f';

    const STATUS_LOCKED = true;

    const STATUS_UNLOCKED = false;

    /**
     * forum_id.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $forum_id;

    /**
     * forum_name.
     *
     * @ORM\Column(type="string", length=150)
     */
    private $name = '';

    /**
     * description.
     *
     * @ORM\Column(type="text")
     */
    private $description = '';

    /**
     * topicCount.
     *
     * @ORM\Column(type="integer")
     */
    private $topicCount = 0;

    /**
     * The number of posts of the forum.
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
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="cat_id", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ForumEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="forum_id")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="ForumEntity", mappedBy="parent", cascade={"remove"})
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @ORM\OneToOne(targetEntity="PostEntity", cascade={"persist"})
     * @ORM\JoinColumn(name="last_post_id", referencedColumnName="post_id", nullable=true, onDelete="SET NULL")
     */
    private $last_post;

    /**
     * pop3Connection.
     *
     * @ORM\Column(type="object", nullable=true)
     */
    private $pop3Connection = null;

    /**
     * moduleref.
     *
     * @ORM\Column(type="integer")
     */
    private $moduleref = 0;

    /**
     * @ORM\OneToMany(targetEntity="ForumUserFavoriteEntity", mappedBy="forum", cascade={"remove"})
     */
    private $favorites;

    /**
     * @ORM\OneToMany(targetEntity="TopicEntity", mappedBy="forum", cascade={"remove"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"sticky" = "DESC", "time" = "ASC"})
     */
    private $topics;

    /**
     * ModeratorUserEntity collection.
     *
     * @ORM\OneToMany(targetEntity="ModeratorUserEntity", mappedBy="forum", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $moderatorUsers;

    /**
     * ModeratorGroupEntity collection.
     *
     * @ORM\OneToMany(targetEntity="ModeratorGroupEntity", mappedBy="forum", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $moderatorGroups;

    /**
     * Subscriptions.
     *
     * ForumSubscriptionEntity collection
     *
     * @ORM\OneToMany(targetEntity="ForumSubscriptionEntity", mappedBy="forum", cascade={"remove"})
     */
    private $subscriptions;

    /**
     * Forum status locked (1)/unlocked (0)
     * locking a forum prevents new TOPICS from being created within.
     *
     * @ORM\Column(type="boolean")
     */
    private $status = self::STATUS_UNLOCKED;

    /**
     * Constructor.
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
        return $this->name == self::ROOTNAME ? 'Forum Index' : $this->name;
    }

    public function setName($name)
    {
        // dont' allow user to set another forum to rootname
        if ($name == self::ROOTNAME && $this->lvl != 0) {
            return;
        }
        // once root forum is set do not allow to change name
        if ($this->name != self::ROOTNAME) {
            $this->name = $name;
        }
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

    public function getParents()
    {
        $parents = [];
        $parent = $this->getParent();
        while ($parent != null) {
            $parents[$parent->getForum_id()] = $parent;
            $parent = $parent->getParent();
        }
        ksort($parents);

        return $parents;
    }

    /**
     * get Forum parent.
     *
     * @return ForumEntity
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(ForumEntity $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * get Children.
     *
     * @return ArrayCollection ForumEntity
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * remove all the child forums.
     */
    public function removeChildren()
    {
        $this->children->clear();
    }

    /**
     * get last post in Forum.
     *
     * @return PostEntity
     */
    public function getLast_post()
    {
        return $this->last_post;
    }

    public function setLast_post(PostEntity $post = null)
    {
        return $this->last_post = $post;
    }

    public function getPop3Connection()
    {
        return $this->pop3Connection;
    }

    public function setPop3Connection($connection)
    {
        if (is_null($connection) || $connection instanceof Pop3Connection) {
            $this->pop3Connection = $connection;
        }
    }

    public function removePop3Connection()
    {
        $this->pop3Connection = null;
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
     * get ForumUsers that have marked this forum as favorite.
     *
     * @return ArrayCollection ForumUserFavoritesEntity
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * get forum Topics.
     *
     * @return ArrayCollection TopicEntity
     */
    public function getTopics()
    {
        $criteria = Criteria::create()
            ->orderBy(["sticky" => Criteria::DESC])
            ->orderBy(["topic_time" => Criteria::ASC])
        ;
        $topics = $this->topics->matching($criteria);
        dump($topics);
        return $topics;
    }

    /**
     * get Moderators.
     *
     * @return ArrayCollection ModeratorUserEntity
     */
    public function getModeratorUsers()
    {
        return $this->moderatorUsers;
    }

    /**
     * Get all the moderator uids for current forum or full tree.
     *
     * @param bool $includeParents include entire parent tree? (recursive)
     *
     * @return array
     */
    public function getNodeModeratorUsers()
    {
        $nodeModeratorsCollection = new ArrayCollection();
        $thisForum = $this;
        while (isset($thisForum) && $thisForum->getForum_id() > 1) {
            foreach ($thisForum->getModeratorUsers() as $moderatorUser) {
                $nodeModeratorsCollection->add($moderatorUser->getForumUser());
            }
            // set thisForum to null to stop
            $thisForum = $thisForum->getParent();
        }

        return $nodeModeratorsCollection;
    }

    public function setModeratorUsers($moderators)
    {
        $this->moderatorUsers->clear();
        foreach ($moderators as $moderator) {
            $moderator->setForum($this);
            $this->moderatorUsers->add($moderator);
        }
    }

    /**
     * get forum moderator groups.
     *
     * @return ArrayCollection ModeratorGroupEntity
     */
    public function getModeratorGroups()
    {
        return $this->moderatorGroups;
    }

    /**
     * Get all the moderator group ids for current forum or full tree.
     *
     * @param bool $includeParents include entire parent tree? (recursive)
     *
     * @return array
     */
    public function getNodeModeratorGroups()
    {
        $nodeModeratorGroupsCollection = new ArrayCollection();
        $thisForum = $this;
        while (isset($thisForum) && $thisForum->getForum_id() > 1) {
            foreach ($thisForum->getModeratorGroups() as $moderatorGroup) {
                $nodeModeratorGroupsCollection->add($moderatorGroup->getGroup());
            }
            $thisForum = $thisForum->getParent();
        }

        return $nodeModeratorGroupsCollection;
    }

    public function setModeratorGroups($moderatorGroups)
    {
        $this->moderatorGroups->clear();
        foreach ($moderatorGroups as $moderatorGroup) {
            $moderatorGroup->setForum($this);
            $this->moderatorGroups->add($moderatorGroup);
        }
    }

    /**
     * get Forum Subscriptions.
     *
     * @return ArrayCollection ForumSubscriptionEntity
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * get forum status.
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * set forum status.
     *
     * @param bool $status
     */
    public function setStatus($status)
    {
        if (is_bool($status)) {
            $this->status = $status;
        }
    }

    /**
     * is forum locked?
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->status;
    }

    /**
     * lock the forum.
     */
    public function lock()
    {
        $this->status = self::STATUS_LOCKED;
    }

    /**
     * unlock the forum.
     */
    public function unlock()
    {
        $this->status = self::STATUS_UNLOCKED;
    }
}
