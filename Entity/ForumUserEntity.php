<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Dizkus\Entity;

use Dizkus\Entity\ForumUserFavoriteEntity;
use Dizkus\Entity\TopicSubscriptionEntity;
use Dizkus\Entity\ForumSubscriptionEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ForumUser entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_users")
 */
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
     * @ORM\ManyToOne(targetEntity="Dizkus\Entity\RankEntity", cascade={"persist"} )
     * @ORM\JoinColumn(name="rank", referencedColumnName="rank_id", nullable=true)
     */
    private $rank;

    /**
     * Dizkus\Entity\ForumUserFavoriteEntity collection
     * @ORM\OneToMany(targetEntity="Dizkus\Entity\ForumUserFavoriteEntity", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $favoriteForums;

    /**
     * Dizkus\Entity\TopicSubscriptionEntity collection
     * @ORM\OneToMany(targetEntity="Dizkus\Entity\TopicSubscriptionEntity", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $topicSubscriptions;

    /**
     * Dizkus\Entity\ForumSubscriptionEntity collection
     * @ORM\OneToMany(targetEntity="Dizkus\Entity\ForumSubscriptionEntity", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
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
     * @return RankEntity
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * set the User rank
     * @param RankEntity $rank
     */
    public function setRank(RankEntity $rank)
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
     * @return ForumUserFavoriteEntity collection
     */
    public function getFavoriteForums()
    {
        return $this->favoriteForums;
    }

    /**
     * add a forum as favorite
     * @param ForumEntity $forum
     */
    public function addFavoriteForum(ForumEntity $forum)
    {
        $forumUserFavorite = new ForumUserFavoriteEntity($this, $forum);
        if (!$this->favoriteForums->contains($forumUserFavorite)) {
            $this->favoriteForums->add($forumUserFavorite);
        }
    }

    /**
     * remove a forum as favorite
     * @param ForumUserFavoriteEntity $forumUserFavorite
     */
    public function removeFavoriteForum(ForumUserFavoriteEntity $forumUserFavorite)
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
     * @return TopicSubscriptionEntity collection
     */
    public function getTopicSubscriptions()
    {
        return $this->topicSubscriptions;
    }

    /**
     * add a topic subscription
     * @param TopicEntity $topic
     */
    public function addTopicSubscription(TopicEntity $topic)
    {
        $topicSubscription = new TopicSubscriptionEntity($this, $topic);
        $this->topicSubscriptions->add($topicSubscription);
    }

    /**
     * remove a topic subscription
     * @param TopicSubscriptionEntity $topicSubscription
     */
    public function removeTopicSubscription(TopicSubscriptionEntity $topicSubscription)
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
     * @return ForumSubscriptionEntity collection
     */
    public function getForumSubscriptions()
    {
        return $this->forumSubscriptions;
    }

    /**
     * add a forum subscription
     * @param ForumEntity $forum
     */
    public function addForumSubscription(ForumEntity $forum)
    {
        $forumSubscription = new ForumSubscriptionEntity($this, $forum);
        $this->forumSubscriptions->add($forumSubscription);
    }

    /**
     * remove a forum subscription
     * @param ForumSubscriptionEntity $forumSubscription
     */
    public function removeForumSubscription(ForumSubscriptionEntity $forumSubscription)
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
