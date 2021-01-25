<?php

declare(strict_types=1);

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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * ForumUser entity class.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_users")
 * @ORM\Entity(repositoryClass="Zikula\DizkusModule\Entity\Repository\ForumUserRepository")
 */
class ForumUserEntity extends EntityAccess
{
    const USER_LEVEL_NORMAL = 1;

    const USER_LEVEL_DELETED = -1;

    /**
     * Zikula user
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Zikula\UsersModule\Entity\UserEntity")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="uid", nullable=true)
     */
    private $user;

    /**
     * postCount.
     *
     * @ORM\Column(type="integer")
     */
    private $postCount = 0;

    /**
     * autosubscribe preference.
     *
     * @ORM\Column(type="boolean")
     */
    private $autosubscribe = true;

    /**
     * user level.
     *
     * @ORM\Column(type="integer")
     */
    private $level = self::USER_LEVEL_NORMAL;

    /**
     * lastvisit.
     *
     * @Gedmo\Timestampable
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastvisit;

    /**
     * user_favorites
     * user choice to display favorites only (true)
     *     or all forums (false).
     *
     * @ORM\Column(name="user_favorites", type="boolean")
     */
    private $displayOnlyFavorites = false;

    /**
     * postOrder.
     * ASC (oldest to newest)
     *
     * @ORM\Column(type="boolean")
     */
    private $postOrder = false;

    /**
     * @ORM\ManyToOne(targetEntity="RankEntity", cascade={"persist"} )
     * @ORM\JoinColumn(name="rank", referencedColumnName="rank_id", nullable=true)
     */
    private $rank;

    /**
     * ForumUserFavoriteEntity collection.
     *
     * @ORM\OneToMany(targetEntity="ForumUserFavoriteEntity", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $favoriteForums;

    /**
     * TopicSubscriptionEntity collection.
     *
     * @ORM\OneToMany(targetEntity="TopicSubscriptionEntity", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $topicSubscriptions;

    /**
     * ForumSubscriptionEntity collection.
     *
     * @ORM\OneToMany(targetEntity="ForumSubscriptionEntity", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $forumSubscriptions;

    /**
     * constructor.
     *
     * @param int $zuser the core user object
     */
    public function __construct()
    {
        $this->favoriteForums = new ArrayCollection();
        $this->topicSubscriptions = new ArrayCollection();
        $this->forumSubscriptions = new ArrayCollection();
    }

    /**
     * @param obj $zuser
     */
    public function setUser(UserEntity $zuser = null)
    {
        $this->user = $zuser;
    }

    /**
     * for import purposes
     * @param obj
     */
    public function setUserId($zuser = null)
    {
        $this->user = $zuser;
    }

    /**
     * Get user.
     *
     * @return
     */
    public function getUser()
    {
        // null look for user level
        return empty($this->user) ? null : $this->user;
    }

    public function getUserId()
    {
        // null look for user level 1 guest -1 deleted
        return empty($this->user) ? -1 : $this->user->getUid();
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getEmail()
    {
        return empty($this->user) ? null : $this->user->getEmail();
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
     * get if user wants only to display favorite forums.
     *
     * @return bool
     */
    public function getDisplayOnlyFavorites()
    {
        return $this->displayOnlyFavorites;
    }

    /**
     * set the displayOnlyFavorites value;.
     *
     * @param bool $val
     */
    public function setDisplayOnlyFavorites($val)
    {
        if (is_bool($val)) {
            $this->displayOnlyFavorites = $val;
        }
    }

    /**
     * display favorite forums only.
     */
    public function showFavoritesOnly()
    {
        $this->displayOnlyFavorites = true;
    }

    /**
     * display all forums (not just favorites).
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
     * get User rank.
     *
     * @return RankEntity
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * set the User rank.
     *
     * @param RankEntity $rank
     */
    public function setRank(RankEntity $rank = null)
    {
        $this->rank = $rank;
    }

    /**
     * clear the User rank value.
     */
    public function clearRank()
    {
        $this->rank = null;
    }

    /**
     * get User favorite forums.
     *
     * @return ForumUserFavoriteEntity collection
     */
    public function getFavoriteForums()
    {
        return $this->favoriteForums;
    }

    /**
     * add a forum as favorite.
     */
    public function addFavoriteForum(ForumEntity $forum)
    {
        $forumUserFavorite = new ForumUserFavoriteEntity($this, $forum);
        if (!$this->favoriteForums->contains($forumUserFavorite)) {
            $this->favoriteForums->add($forumUserFavorite);
        }
    }

    /**
     * remove a forum as favorite.
     */
    public function removeFavoriteForum(ForumUserFavoriteEntity $forumUserFavorite)
    {
        $this->favoriteForums->removeElement($forumUserFavorite);
    }

    /**
     * clear all forum favorites.
     */
    public function clearForumFavorites()
    {
        $this->favoriteForums->clear();
    }

    /**
     * get User topic subscriptions.
     *
     * @return TopicSubscriptionEntity collection
     */
    public function getTopicSubscriptions()
    {
        return $this->topicSubscriptions;
    }

    /**
     * add a topic subscription.
     */
    public function addTopicSubscription(TopicEntity $topic)
    {
        $topicSubscription = new TopicSubscriptionEntity($this, $topic);
        $this->topicSubscriptions->add($topicSubscription);
    }

    /**
     * remove a topic subscription.
     */
    public function removeTopicSubscription(TopicSubscriptionEntity $topicSubscription)
    {
        $this->topicSubscriptions->removeElement($topicSubscription);
    }

    /**
     * clear all topic subscriptions.
     */
    public function clearTopicSubscriptions()
    {
        $this->topicSubscriptions->clear();
    }

    /**
     * get User forum subscriptions.
     *
     * @return ForumSubscriptionEntity collection
     */
    public function getForumSubscriptions()
    {
        return $this->forumSubscriptions;
    }

    /**
     * add a forum subscription.
     */
    public function addForumSubscription(ForumEntity $forum)
    {
        $forumSubscription = new ForumSubscriptionEntity($this, $forum);
        $this->forumSubscriptions->add($forumSubscription);
    }

    /**
     * remove a forum subscription.
     */
    public function removeForumSubscription(ForumSubscriptionEntity $forumSubscription)
    {
        $this->forumSubscriptions->removeElement($forumSubscription);
    }

    /**
     * clear all forum subscriptions.
     */
    public function clearForumSubscriptions()
    {
        $this->forumSubscriptions->clear();
    }

    public function toArray()
    {
        return [
            'id' => $this->getUserId(),
            'postOrder' => $this->getPostOrder(),
            'displayOnlyFavorites' => $this->getDisplayOnlyFavorites(),
            'autosubscribe' => $this->getAutosubscribe()
        ];
    }
}
