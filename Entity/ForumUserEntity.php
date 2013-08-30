<?php/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ForumUser entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_users")
 */

namespace Dizkus\Entity;

use ArrayCollection;
use Dizkus_Entity_ForumUserFavorite;
use Dizkus_Entity_TopicSubscription;
use Dizkus_Entity_ForumSubscription;

class ForumUserEntity extends \Zikula_EntityAccess
{
    /**
     * Core user entity
     * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/composite-primary-keys.html
     * @see /system/Zikula/Module/UsersModule/Entity/UserEntity.php
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Zikula\Module\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="uid")
     */
    private $user;
    /**
     * postCount
     *
     * @ORM\Column(type="integer")
     */
    private $postCount = 0;
    /**
     * autosubscribe preference
     *
     * @ORM\Column(type="boolean")
     */
    private $autosubscribe = true;
    /**
     * user level
     *
     * @ORM\Column(type="integer")
     */
    private $level = 1;
    /**
     * lastvisit
     *
     * @Gedmo\Timestampable
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastvisit = null;
    /**
     * user_favorites
     * user choice to display favorites only (true)
     *     or all forums (false)
     *
     * @ORM\Column(name="user_favorites", type="boolean")
     */
    private $displayOnlyFavorites = false;
    /**
     * postOrder
     *
     * @ORM\Column(type="boolean")
     */
    private $postOrder = false;
    // ASC (oldest to newest)
    /**
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Rank", cascade={"persist"} )
     * @ORM\JoinColumn(name="rank", referencedColumnName="rank_id", nullable=true)
     */
    private $rank;
    /**
     * Dizkus_Entity_ForumUserFavorite collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_ForumUserFavorite", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $favoriteForums;
    /**
     * Dizkus_Entity_TopicSubscription collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_TopicSubscription", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $topicSubscriptions;
    /**
     * Dizkus_Entity_ForumSubscription collection
     * @ORM\OneToMany(targetEntity="Dizkus_Entity_ForumSubscription", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $forumSubscriptions;
    /**
     * constructor
     */
    public function __construct()
    {
        $this->favoriteForums = new ArrayCollection();
        $this->topicSubscriptions = new ArrayCollection();
        $this->forumSubscriptions = new ArrayCollection();
    }

    public function getUser_id()
    {
        return $this->user->getUid();
    }

    /**
     * Core User Entity
     * @return Zikula\Module\UsersModule\Entity\UserEntity
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * set the user
     * @param Zikula\Module\UsersModule\Entity\UserEntity $user
     */
    public function setUser(Zikula\Module\UsersModule\Entity\UserEntity $user)
    {
        $this->user = $user;
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

    public function getAutosubscribe()
    {
        return $this->autosubscribe;
    }

    public function setAutosubscribe($autosubscribe)
    {
        $this->autosubscribe = $autosubscribe;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getLastvisit()
    {
        return $this->lastvisit;
    }

    public function setLastvisit($lastvisit)
    {
        $this->lastvisit = $lastvisit;
    }

    /**
     * get if user wants only to display favorite forums
     * @return boolean
     */
    public function getDisplayOnlyFavorites()
    {
        return $this->displayOnlyFavorites;
    }

    /**
     * set the displayOnlyFavorites value;
     * @param boolean $val
     */
    public function setDisplayOnlyFavorites($val)
    {
        if (is_bool($val)) {
            $this->displayOnlyFavorites = $val;
        }
    }

    /**
     * display favorite forums only
     */
    public function showFavoritesOnly()
    {
        $this->displayOnlyFavorites = true;
    }

    /**
     * display all forums (not just favorites)
     */
    public function showAllForums()
    {
        $this->displayOnlyFavorites = false;
    }

    public function getPostOrder()
    {
        return $this->postOrder;
    }

    public function setPostOrder($postOrder)
    {
        $this->postOrder = $postOrder;
    }

    /**
     * get User rank
     *
     * @return Dizkus_Entity_Rank
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * set the User rank
     * @param Dizkus_Entity_Rank $rank
     */
    public function setRank(Dizkus_Entity_Rank $rank)
    {
        $this->rank = $rank;
    }

    /**
     * clear the User rank value
     */
    public function clearRank()
    {
        $this->rank = null;
    }

    /**
     * get User favorite forums
     * @return Dizkus_Entity_ForumUserFavorite collection
     */
    public function getFavoriteForums()
    {
        return $this->favoriteForums;
    }

    /**
     * add a forum as favorite
     * @param Dizkus_Entity_Forum $forum
     */
    public function addFavoriteForum(Dizkus_Entity_Forum $forum)
    {
        $forumUserFavorite = new Dizkus_Entity_ForumUserFavorite($this, $forum);
        if (!$this->favoriteForums->contains($forumUserFavorite)) {
            $this->favoriteForums->add($forumUserFavorite);
        }
    }

    /**
     * remove a forum as favorite
     * @param Dizkus_Entity_ForumUserFavorite $forumUserFavorite
     */
    public function removeFavoriteForum(Dizkus_Entity_ForumUserFavorite $forumUserFavorite)
    {
        $this->favoriteForums->removeElement($forumUserFavorite);
    }

    /**
     * clear all forum favorites
     */
    public function clearForumFavorites()
    {
        $this->favoriteForums->clear();
    }

    /**
     * get User topic subscriptions
     * @return Dizkus_Entity_TopicSubscription collection
     */
    public function getTopicSubscriptions()
    {
        return $this->topicSubscriptions;
    }

    /**
     * add a topic subscription
     * @param Dizkus_Entity_Topic $topic
     */
    public function addTopicSubscription(Dizkus_Entity_Topic $topic)
    {
        $topicSubscription = new Dizkus_Entity_TopicSubscription($this, $topic);
        $this->topicSubscriptions->add($topicSubscription);
    }

    /**
     * remove a topic subscription
     * @param Dizkus_Entity_TopicSubscription $topicSubscription
     */
    public function removeTopicSubscription(Dizkus_Entity_TopicSubscription $topicSubscription)
    {
        $this->topicSubscriptions->removeElement($topicSubscription);
    }

    /**
     * clear all topic subscriptions
     */
    public function clearTopicSubscriptions()
    {
        $this->topicSubscriptions->clear();
    }

    /**
     * get User forum subscriptions
     * @return Dizkus_Entity_ForumSubscription collection
     */
    public function getForumSubscriptions()
    {
        return $this->forumSubscriptions;
    }

    /**
     * add a forum subscription
     * @param Dizkus_Entity_Forum $forum
     */
    public function addForumSubscription(Dizkus_Entity_Forum $forum)
    {
        $forumSubscription = new Dizkus_Entity_ForumSubscription($this, $forum);
        $this->forumSubscriptions->add($forumSubscription);
    }

    /**
     * remove a forum subscription
     * @param Dizkus_Entity_ForumSubscription $forumSubscription
     */
    public function removeForumSubscription(Dizkus_Entity_ForumSubscription $forumSubscription)
    {
        $this->forumSubscriptions->removeElement($forumSubscription);
    }

    /**
     * clear all forum subscriptions
     */
    public function clearForumSubscriptions()
    {
        $this->forumSubscriptions->clear();
    }

}
