<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Entity;

use Zikula\Core\Doctrine\EntityAccess;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use UserUtil;
use ZLanguage;

/**
 * ForumUser entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_users")
 */
class ForumUserEntity extends EntityAccess
{
    const USER_LEVEL_NORMAL = 1;
    const USER_LEVEL_DELETED = -1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $user_id;

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
    private $level = self::USER_LEVEL_NORMAL;

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
     * @ORM\ManyToOne(targetEntity="RankEntity", cascade={"persist"} )
     * @ORM\JoinColumn(name="rank", referencedColumnName="rank_id", nullable=true)
     */
    private $rank;

    /**
     * ForumUserFavoriteEntity collection
     * @ORM\OneToMany(targetEntity="ForumUserFavoriteEntity", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $favoriteForums;

    /**
     * TopicSubscriptionEntity collection
     * @ORM\OneToMany(targetEntity="TopicSubscriptionEntity", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $topicSubscriptions;

    /**
     * ForumSubscriptionEntity collection
     * @ORM\OneToMany(targetEntity="ForumSubscriptionEntity", mappedBy="forumUser", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $forumSubscriptions;

    /**
     * NON-PERSISTED property
     * array of core vars for user
     * @var array
     */
    private $user = [];

    /**
     * constructor
     * @param integer $uid the core user id
     */
    public function __construct($uid)
    {
        $this->user_id = $uid;
        $this->user = $this->getUser();
        $this->favoriteForums = new ArrayCollection();
        $this->topicSubscriptions = new ArrayCollection();
        $this->forumSubscriptions = new ArrayCollection();
    }

    /**
     * @param mixed $user_id
     */
    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getUser_id()
    {
        return $this->user_id;
    }

    /**
     * Core user vars
     * @return array|boolean (if core user doesn't exist, method returns false)
     */
    public function getUser()
    {
        if (empty($this->user)) {
            $this->user = UserUtil::getVars($this->user_id);
            if (empty($this->user['uname'])) {
                $dom = ZLanguage::getModuleDomain('ZikulaDizkusModule');
                $this->user['uname'] = __('Deleted user', $dom);
            }
        }

        return $this->user;
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
