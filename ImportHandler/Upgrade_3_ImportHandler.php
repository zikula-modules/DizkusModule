<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\ImportHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Entity\PostEntity;
use Zikula\DizkusModule\Entity\TopicEntity;
use Zikula\DizkusModule\Entity\ForumUserEntity;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Entity\ForumSubscriptionEntity;
use Zikula\DizkusModule\Entity\ForumUserFavoriteEntity;
use Zikula\DizkusModule\Entity\TopicSubscriptionEntity;
use Zikula\DizkusModule\Entity\ModeratorUserEntity;
use Zikula\DizkusModule\Entity\ModeratorGroupEntity;

/**
 * 3.1.0 and 3.2.0 Import Handler
 *
 * @author Kaik
 */
class Upgrade_3_ImportHandler extends AbstractImportHandler
{
    private $prefix = '';

    public function getTitle()
    {
        return $this->translator->trans('Upgrade 3 tables handler', [], 'zikuladizkusmodule');
    }

    public function getDescription()
    {
        return $this->translator->trans('Tables need to be prefixed with version ie. 3_1_0', [], 'zikuladizkusmodule');
    }

    public function getStatus()
    {
        $status['tables'] = $this->getTablesForPrefix();

        return $status;
    }

    public function versionSupported()
    {
        $supported = [
            '3_1_0',
            '3_2_0'
        ];

        return in_array($this->prefix, $supported);
    }

    public function getRenderListView($prefix)
    {
        return $this->renderEngine->render('ZikulaDizkusModule:Import:list.upgrade_3.html.twig', [
            'importHandler' => $this->setPrefix($prefix),
            'data' => $this->getStatus()
        ]);
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /*
     * Ranks below
     *
     */
    public function getRanksStatus()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_ranks';
        $oldRanks = $connection->fetchAll($sql);
        $toImport = [];
        $found = [];

        foreach ($oldRanks as $oldRank) {
            $rank = $this->em->find('Zikula\DizkusModule\Entity\RankEntity', $oldRank['rank_id']);
            if ($rank) {
                $found[] = $rank;
            } else {
                $toImport[] = $oldRank;
            }
        }

        return ['found'=> $found, 'toImport' => $toImport];
    }

    public function importRanks($data)
    {
        $connection = $this->em->getConnection();
        $limit = $data['pageSize'];
        $offset = $data['page'];
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_ranks LIMIT :offset,:limit';
        $statement = $connection->prepare($sql);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, \PDO::PARAM_INT);
        $statement->execute();
        $currentPageItems = $statement->fetchAll();

        foreach ($currentPageItems as $rankArr) {
            $rankExists = $this->em->find('Zikula\DizkusModule\Entity\RankEntity', $rankArr['rank_id']);
            if ($rankExists) {
                continue;
            }
            $rank = new RankEntity();
            $rank->setRank_id($rankArr['rank_id']);
            $rank->setType($rankArr['rank_special']);
            $rank->setTitle($rankArr['rank_title']);
            $rank->setDescription($rankArr['rank_desc']);
            $rank->setImage($rankArr['rank_image']);
            $rank->setMinimumCount($rankArr['rank_min']);
            $rank->setMaximumCount($rankArr['rank_max']);
            //store object
            $metadata = $this->em->getClassMetadata(get_class($rank));
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $this->em->persist($rank);
            $this->em->flush();
        }

        return $currentPageItems;
    }

    /*
     * Users
     */
    public function getUsersStatus()
    {
        $usersCollection = new ArrayCollection();
        $currentUsers = $this->em->getRepository('Zikula\DizkusModule\Entity\ForumUserEntity')->findAll();
        foreach ($currentUsers as $cuser) {
            $usersCollection->add($cuser);
        }

        $old = [];
        $old['toImport'] = new ArrayCollection();
        $old['found'] = $this->getOldUsers();

        foreach ($old['found'] as $ouser) {
            $sameIdTest = function($key, $element) use ($ouser) {
                    return $element->getUserId() == $ouser['user_id'];
            };
            if (!$usersCollection->exists($sameIdTest)) {
                $u = $this->getForumUserFromTableRow($ouser);
                if ($u) {
                    $usersCollection->add($u);
                    $old['toImport']->add($u);
                }
            }
        }

//        //get users from posts
//        $posts = [];
//        $posts['toImport'] = new ArrayCollection();
//        $connection = $this->em->getConnection();
//        $sql = 'SELECT DISTINCT(poster_id) as poster_id FROM import_dizkus_posts';
//        $posts['found'] = $connection->fetchAll($sql);
//        foreach ($posts['found'] as $post) {
//            $sameIdTest = function($key, $element) use ($post) {
//                    return $element->getUserId() == $post['poster_id'];
//                };
//
//            if (!$usersCollection->exists($sameIdTest)) {
//                //$u = $this->getForumUserFromTableRow(['user_id' => $post['poster_id']]);
////                if($u){
////                    $usersCollection->add($u);
////                    $posts['toImport']->add($u);
////                }
//            }
//        }
//
//        //get users from posts
//        $posts = [];
//        $posts['toImport'] = new ArrayCollection();
//        $topicUsersCollection = new ArrayCollection();
//        $sql = 'SELECT DISTINCT(topic_poster) as topic_poster FROM import_dizkus_topics';
//        $topics = $connection->fetchAll($sql);
//        foreach ($topics as $topic) {
//            if (!$usersCollection->containsKey($topic['topic_poster'])) {
//                $u = new ForumUserEntity();
//                $u->setUserId($topic['topic_poster']);
//                //$u = $this->getForumUserFromTableRow(['user_id' => $topic['topic_poster']]);
//                $usersCollection->add($u);
//                $topicUsersCollection->add($u);
//            }
//        }

        return ['current' => $currentUsers,
                'old' => $old,
//                'posters' => $posts,
//                'topic' => $topicUsersCollection,
                'total' => $usersCollection
                ];
    }

    public function getOldUsers()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_users';
        $users = $connection->fetchAll($sql);

        return $users;
    }

    public function getUsersFromPosts()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT DISTINCT(poster_id) as poster_id FROM ' . $this->prefix . '_dizkus_posts';
        $postsUsers = $connection->fetchAll($sql);

        return $postsUsers;
    }

    public function getUsersFromTopics()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT DISTINCT(topic_poster) as topic_poster FROM ' . $this->prefix . '_dizkus_topics';
        $topicsUsers = $connection->fetchAll($sql);

        return $topicsUsers;
    }

    private function getForumUserFromTableRow($user)
    {
        if (!array_key_exists('user_id', $user)) {
            return false;
        }

        $newUser = new ForumUserEntity();
        $systemUser = $this->em->find('Zikula\UsersModule\Entity\UserEntity', $user['user_id']);
        if ($systemUser == null) {
            // user no longer exists in zikula
           return false;
        } else {
            $newUser->setLevel(1);
            $newUser->setUser($systemUser);
        }

        $newUser->setAutosubscribe(false);
        $newUser->setDisplayOnlyFavorites(array_key_exists('user_favorites', $user) ? $user['user_favorites'] : 0);
        $newUser->setLastvisit(array_key_exists('user_lastvisit', $user) ? new \DateTime($user['user_lastvisit']) : null);
        $newUser->setPostCount(array_key_exists('user_posts', $user) ? $user['user_posts'] : null);
        $newUser->setPostOrder(array_key_exists('user_post_order', $user) ? $user['user_post_order'] : 0);

        if (array_key_exists('user_rank', $user) && $user['user_rank'] != null) {
            $rank = $this->em->find('Zikula\DizkusModule\Entity\RankEntity', $user['user_rank']);
            if ($rank) {
                $newUser->setRank($rank);
            } else {
                $newUser->setRank(null);
            }
        } else {
            $newUser->setRank(null);
        }

        return $newUser;
    }

    public function importUsers($data)
    {
        $users = $this->getUsersStatus();
        $offset = $data['page'] + 1;
        $index_start = $offset * $data['pageSize'] - $data['pageSize'];
        $usersArr = is_object($users[$data['source']]['toImport']) ? $users[$data['source']]['toImport']->toArray() : $users[$data['source']]['toImport'];
        $elements = array_slice($usersArr, $index_start, $data['pageSize'], true);

        $done = [];
        foreach ($elements as $forumUser) {
            $forumUserObj = is_object($forumUser) ? $forumUser : $this->getForumUserFromTableRow($forumUser);
            if ($forumUserObj) {
                $metadata = $this->em->getClassMetadata(get_class($forumUserObj));
                $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                $this->em->persist($forumUserObj);
                $done[] = $forumUserObj->getUser();
            }
        }

        $this->em->flush();

//        $sql = 'SELECT * FROM import_dizkus_users LIMIT :offset,:limit';
//        $statement = $conn->prepare($sql);
//        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
//        $statement->bindValue('offset', $offset, \PDO::PARAM_INT);
//
//        $statement->execute();
//        $currentPageItems = $statement->fetchAll();
//
//        foreach ($currentPageItems as $userArr) {
//
//        //check if rank exists
//        $rankExists = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $userArr['user_id']);
//        //skip if exists
//        if ($rankExists) {
//            continue;
//        }
//
//        $user = $this->getForumUserFromTableRow($userArr);
//
//        $metadata = $this->em->getClassMetadata(get_class($user));
//        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
//        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
//        $this->em->persist($user);
//        $this->em->flush();
//
//        }

        return $done;
    }

    /*
     * Forums
     */
    public function getForumTree()
    {
        //now here it gets tricky because
        // our forum root have id = 1
        // we can have category with id 1
        // and we can have forum with id 1
        // apart from that categories can have same id's as forums
        // we want to merge that all into one forum tree
        // with root = 1 and possibly preserving forum id topic post and so on...
        $repo = $this->em->getRepository('Zikula\DizkusModule\Entity\ForumEntity');
        //$arrayTree = $repo->childrenHierarchy();
        $forumTree = $repo->getRootNodes();
        // forum tree should contain only root
        $forumRoot = $forumTree[0]; //$this->em->find('Zikula\DizkusModule\Entity\ForumEntity', 1);
        $categories = $this->getOldCategories();
        // because categories does not contain topics we will move them at the top
        // categories are lvl = 1 forums
        // in Dizkus 3.1.0 these does not contain topics forums only
        $catID = $this->getForumsMaxId() + 10;
        foreach ($categories as $category) {
            $category['newId'] = $catID . '_' . $category['cat_id'];
            $forum = $this->getForumFromCategoryTableRow($category);
            $forum->setParent($forumRoot);
            $forum->setLvl(1);
            $forumRoot->getChildren()->add($forum);
            $catID++;
        }

        return $forumTree;
    }

    public function getCurrentForumsCount()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT count(*) AS total FROM dizkus_forums';
        $statement = $connection->prepare($sql);
        $statement->execute();

        return $statement->fetchColumn();
    }

    public function getOldCategoriesCount()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_categories';
        $statement = $connection->prepare($sql);
        $statement->execute();

        return $statement->fetchColumn();
    }

    public function getOldForumsCount()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_forums';
        $statement = $connection->prepare($sql);
        $statement->execute();

        return $statement->fetchColumn();
    }

    public function getOldTopicsCount()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_topics';
        $statement = $connection->prepare($sql);
        $statement->execute();

        return $statement->fetchColumn();
    }

    public function getOldPostsCount()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_posts';
        $statement = $connection->prepare($sql);
        $statement->execute();

        return $statement->fetchColumn();
    }

    public function getOldCategories()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_categories ORDER BY cat_order ASC';
        $categories = $connection->fetchAll($sql);

        return $categories;
    }

    private function getForumFromCategoryTableRow($category)
    {
        $newCatForum = new ForumEntity();
        $newCatForum->setId($category['newId']);
        $newCatForum->setName($category['cat_title']);
        $newCatForum->setLft($category['cat_order']);
        $newCatForum->setStatus(1);
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_forums WHERE cat_id = ' . $category['cat_id'] . ' ORDER BY forum_order ASC';
        $forums = $connection->fetchAll($sql);

        foreach ($forums as $catForum) {
            $forum = $this->getForumObjectFromTableRow($catForum);
            $forum->setParent($newCatForum);
            $newCatForum->getChildren()->add($forum);
        }

        return $newCatForum;
    }

    private function getForumObjectFromTableRow($forum)
    {
        $newForum = new ForumEntity();
        $id = array_key_exists('forum_id', $forum) ? $forum['forum_id'] : null;
        if ($id === null && array_key_exists('id', $forum)) {
            $id = $forum['id'];
        }
        if ($id == 1) {
            $id = $this->getForumsMaxId() + 5 . '_' . 1;
        } elseif ($id === null) {

            return false;
        }

        $newForum->setId($id);
        $newForum->setName($forum['forum_name']);
        $newForum->setDescription($forum['forum_desc']);
        $newForum->setTopicCount($forum['forum_topics']);
        $newForum->setPostCount($forum['forum_posts']);
        $newForum->setModuleref($forum['forum_moduleref']);
        //old tables do not have lvl
        $newForum->setLvl(2);
        $newForum->setLft($forum['forum_order']);
        $root = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', 1);
        $newForum->setRoot($root);
        $newForum->setStatus(1);

////            $connectionData['coreUser'] = $this->entityManager->getReference('Zikula\\UsersModule\\Entity\\UserEntity', $connectionData['userid']);
////            $connection = new Pop3Connection($connectionData);
////            $forum = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $connectionData['id']);
////            $forum->setPop3Connection($connection);

        return $newForum;
    }


    private function explodeForumId($mixedId)
    {
        $result = ['current' => false, 'old'=> false];
        $ids = explode('_', $mixedId);
        if (count($ids) > 1) {
            $result['current'] = $ids[0];
            $result['old'] = $ids[1];
        } else {
            $result['current'] = $ids[0];
        }

        return $result;
    }

    private function getCurrentForumId($mixedId)
    {
        $res = $this->explodeForumId($mixedId);

        return $res['current'];
    }

    private function getOldForumId($mixedId)
    {
        $res = $this->explodeForumId($mixedId);

        return $res['old'];
    }

    private function getForumsMaxId()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT MAX(forum_id) as maxID FROM ' . $this->prefix . '_dizkus_forums';
        $forumLastId = $connection->fetchAssoc($sql);

        return $forumLastId['maxID'];
    }

    private function getForumsMinId()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT MIN(forum_id) as minID FROM ' . $this->prefix . '_dizkus_forums';
        $forumFirsId = $connection->fetchAssoc($sql);

        return $forumFirsId['minID'];
    }

    public function getAllForums()
    {
        $forumsCollection = new ArrayCollection();
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_forums ORDER BY forum_id ASC';
        $forums = $connection->fetchAll($sql);
        foreach ($forums as $forum) {
            $forumsCollection->add($this->getForumObjectFromTableRow($forum));
        }

        return $forumsCollection;
    }

    public function importCategory($data)
    {
        $forumObj = new ForumEntity();
        $forumObj->setId($this->getCurrentForumId($data['node']['id']));
        $forumObj->setName($data['node']['name']);
        $forumObj->setLvl(1);
        $forumObj->setLft($data['node']['lft']);
        $root = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', 1);
        $forumObj->setParent($root);
        $forumObj->setRoot(1);

        if ($forumObj) {
            $metadata = $this->em->getClassMetadata(get_class($forumObj));
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            //no bulk insert
            $this->em->persist($forumObj);
            $this->em->flush();
        }
        $data['log'][]  = 'Import cat ' . $data['node']['id'] . ' done';

        return $data;
    }

    public function importForum($data)
    {
        $forumObj = new ForumEntity();
        $forumIdMix = $data['node']['id'];
        $forumObj->setId($this->getCurrentForumId($forumIdMix));
        $forumObj->setName($data['node']['name']);
        $forumObj->setDescription($data['node']['description']);
        $forumObj->setPostCount($data['node']['postCount']);
        $forumObj->setPostCount($data['node']['topicCount']);
        $forumObj->setModuleref($data['node']['moduleref']);
        $parent = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $this->getCurrentForumId($data['node']['parentid']));
        $forumObj->setParent($parent);
        $forumObj->setLvl(2);
        $forumObj->setLft($data['node']['lft']);
        //$root = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', 1);
        $forumObj->setRoot(1);
        //save forum object
        if ($forumObj) {
            $metadata = $this->em->getClassMetadata(get_class($forumObj));
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            //no bulk insert
            $this->em->persist($forumObj);
            $this->em->flush();
        }

        $data['topics_total'] = $this->getOldForumId($forumIdMix) === false ? $this->getTopicsCount($forumObj->getId()) : $this->getTopicsCount($this->getOldForumId($forumIdMix));
        $data['topics_pages'] = ceil($data['topics_total'] / $data['topics_limit']);
        $data['log'][]  = 'Forum ' . $forumIdMix . ' topics to import ' . $data['topics_total'];
        $data['log'][]  = 'Import forum '. $forumIdMix .' done';

        return $data;
    }

    /*
     * Topics
     */
    public function importTopics($data)
    {
        $data['topics'] = $this->getTopics($data);
        foreach ($data['topics'] as $topic) {
            if ($topic) {
                $metadata = $this->em->getClassMetadata(get_class($topic));
                $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                //no bulk insert
                $this->em->persist($topic);
                $this->em->flush();
                $data['topics_imported']++;
            }
            $data['topic_index']++;
            //prepare topic posts for import
            $data['topic'] = $topic->getId();
            $data['posts_total'] = $topic->getReplyCount();
            $data['posts_pages'] = ceil($data['posts_total'] / $data['posts_limit']);
            //import posts will handle posts
            $data = $this->importPosts($data);
            if ($data['posts_pages'] > 1) {
                $data['node']['lvl'] = 3;

                return $data;
            } else {
                // full topic import done
            }
        }

        return $data;
    }

    public function getOldTopics()
    {
        $topicsCollection = new ArrayCollection();
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_topics ORDER BY topic_time ASC';
        $topics = $connection->fetchAll($sql);
        foreach ($topics as $topic) {
            $topicsCollection->add($topic);
        }

        return $topicsCollection;
    }

    public function getTopicsCount($forum_id)
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_topics WHERE forum_id = :forum_id';
        $statement = $connection->prepare($sql);
        $statement->bindValue('forum_id', $forum_id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchColumn(0);
    }

    public function getTopics($data)
    {
        if ($this->getOldForumId($data['node']['id'])) {
            $forum_id = (int) $this->getOldForumId($data['node']['id']);
        } else {
            $forum_id = (int) $this->getCurrentForumId($data['node']['id']);
        }

        $limit = $data['topics_limit'];
        $offset = $data['topics_page'] === 0 ? $data['topics_page'] : $data['topics_page'] * $limit;
        // offset on page where we stopped to import posts
        if ($data['topic_index'] !== null) {
            $offset = $data['topic_index'];
            //new limit
            $overPage = $data['topic_index'] % $limit;
            $limit = $limit - $overPage;
            if ($overPage == 0){
            }
        }

        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_topics WHERE forum_id = '. $forum_id .' ORDER BY topic_id ASC LIMIT '. $offset .','. $limit .'';
        $statement = $connection->prepare($sql);
        $statement->execute();
        $topicsCollection = new ArrayCollection();
        $topics = $statement->fetchAll();
        $forum = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $this->getCurrentForumId($data['node']['id']));
        foreach ($topics as $topic) {
            $topicObj = $this->getTopic($topic);
            $topicObj->setForum($forum);
            $topicsCollection->add($topicObj);
        }

        return $topicsCollection->toArray();
    }

    public function getTopic($topic)
    {
        $newTopic = new TopicEntity();
        $newTopic->setId($topic['topic_id']);
        $newTopic->setTitle($topic['topic_title']);
        $poster = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $topic['topic_poster']);
        if ($poster === null) {
            $poster = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', 1);
        }
        $newTopic->setPoster($poster);
        $postsCount = (int) $this->getPostsCount($topic['topic_id']);
        $newTopic->setReplyCount($postsCount);// -1
        $topicTime = new \DateTime($topic['topic_time']);
        $newTopic->setTopic_time($topicTime);
        $newTopic->setViewCount($topic['topic_views']);
        $newTopic->setSolved($topic['topic_status']);
        $newTopic->setSticky($topic['sticky']);
        //decode reference $this->decodeHooks();

        return $newTopic;
    }

    /*
     * Posts below
     *
     */
    public function importPosts($data)
    {
        $posts = $this->getPosts($data);
        foreach ($posts as $post) {
            if ($post) {
                $metadata = $this->em->getClassMetadata(get_class($post));
                $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
                //bulk insert
                $this->em->persist($post);
                $data['posts_imported']++;
            }
        }
        $this->em->flush();
        $data['log'][]  = 'Topic #'. $data['topic'] .' page '. $data['posts_page'] .' imported posts ' . count($posts) . ' ';

        return $data;
    }

    public function getAllPosts()
    {
        $postsCollection = new ArrayCollection();
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_posts ORDER BY post_time ASC';
        $posts = $connection->fetchAll($sql);
        foreach ($posts as $post) {
            $postsCollection->add($this->getPost($post));
        }

        return $postsCollection;
    }

    public function getPostsCount($topic_id)
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_posts WHERE topic_id = :topic_id';
        $statement = $connection->prepare($sql);
        $statement->bindValue('topic_id', $topic_id, \PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchColumn();
    }

    public function getPosts($data)
    {
        $topic = (int) $data['topic'];
        $limit = $data['posts_limit'];
        $offset = $data['posts_page'] == 0 ? $data['posts_page'] : $data['posts_page'] * $limit;
        $postsCollection = new ArrayCollection();
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_posts WHERE topic_id = '. $topic .' ORDER BY post_id ASC LIMIT '. $offset .','. $limit .'';
        $statement = $connection->prepare($sql);
        $statement->execute();

        $posts = $connection->fetchAll($sql);
        $topicObj = $this->em->find('Zikula\DizkusModule\Entity\TopicEntity', $topic);
        foreach ($posts as $post) {
            $postObj = $this->getPost($post);
            $postObj->setTopic($topicObj);
            $postsCollection->add($postObj);
        }

        return $postsCollection;
    }

    public function getPost($post)
    {
        $newPost = new PostEntity();
        $newPost->setId($post['post_id']);
        $newPost->setTitle($post['post_title']);
        $newPost->setPostText($post['post_text']);
        $newPost->setPost_time(new \DateTime($post['post_time']));
        $newPost->setPoster_ip($post['poster_ip']);
        $poster = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $post['poster_id']);
        if ($poster === null) {
            $poster = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', 1);
        }
        $newPost->setPoster($poster);

        return $newPost;
    }

    public function getPoster($poster_id)
    {
        $forumUser = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $poster_id);
        if ($forumUser == null) {
            $forumUser = new ForumUserEntity();
        }

        return $forumUser;
    }

    /*
     * Other below
     *
     */
    public function getFavoritesStatus()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_forum_favorites';
        $old = $connection->fetchAll($sql);
        $toImport = [];
        $found = [];
        foreach ($old as $itm) {
            //            $forum = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $itm['forum_id']);
//            $user = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $itm['user_id']);
//            if (!$forum || !$user) {
//                $found[] = $itm;
//            } else {
                $toImport[] = $itm;
//            }
        }

        return ['found'=> $found, 'toImport' => $toImport];
    }

    public function importFavorites($data)
    {
        $connection = $this->em->getConnection();
        if (!array_key_exists('total', $data)) {
            $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_forum_favorites';
            $statement = $connection->prepare($sql);
            $statement->execute();
            $data['total'] = $statement->fetchColumn();
            $data['pages'] = ceil($data['total'] / $data['pageSize']);
        }

        $limit = $data['pageSize'];
        $offset = $data['page'] == 0 ? $data['page'] : $data['page'] * $limit;

        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_forum_favorites LIMIT :offset,:limit';
        $statement = $connection->prepare($sql);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, \PDO::PARAM_INT);
        $statement->execute();
        $currentPageItems = $statement->fetchAll();
        $data['imported'] = 0;
        $data['rejected'] = 0;
        foreach ($currentPageItems as $itm) {
            $forum = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $itm['forum_id']);
            $user = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $itm['user_id']);
            if (!$forum || !$user) {
                $data['rejected']++;

                continue;
            }
            $itmObj = new ForumUserFavoriteEntity($user, $forum);

            $metadata = $this->em->getClassMetadata(get_class($itmObj));
            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $this->em->persist($itmObj);

            $data['imported']++;
        }
        $this->em->flush();

        return $data;
    }

    public function getModeratorsUsersStatus()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_forum_mods WHERE user_id < 1000000';
        $old = $connection->fetchAll($sql);
        $toImport = [];
        $found = [];
        foreach ($old as $itm) {
            //            $forum = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $itm['forum_id']);
//            $user = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $itm['user_id']);
//            if (!$forum || !$user) {
//                $found[] = $itm;
//            } else {
                $toImport[] = $itm;
//            }
        }

        return ['found'=> $found, 'toImport' => $toImport];
    }

    public function importModeratorsUsers($data)
    {
        $connection = $this->em->getConnection();
        if (!array_key_exists('total', $data)) {
            $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_forum_mods WHERE user_id < 1000000';
            $statement = $connection->prepare($sql);
            $statement->execute();
            $data['total'] = $statement->fetchColumn();
            $data['pages'] = ceil($data['total'] / $data['pageSize']);
        }

        $limit = $data['pageSize'];
        $offset = $data['page'] == 0 ? $data['page'] : $data['page'] * $limit;

        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_forum_mods WHERE user_id < 1000000 LIMIT :offset,:limit';
        $statement = $connection->prepare($sql);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, \PDO::PARAM_INT);
        $statement->execute();
        $currentPageItems = $statement->fetchAll();
        $data['imported'] = 0;
        $data['rejected'] = 0;
        foreach ($currentPageItems as $itm) {
            $forum = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $itm['forum_id']);
            $user = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $itm['user_id']);
            if (!$forum || !$user) {
                $data['rejected']++;

                continue;
            }
            $itmObj = new ModeratorUserEntity();
            $itmObj->setForum($forum);
            $itmObj->setForumUser($user);
            $this->em->persist($itmObj);

            $data['imported']++;
        }
        $this->em->flush();

        return $data;
    }

    public function getModeratorsGroupsStatus()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_forum_mods WHERE user_id > 1000000';
        $old = $connection->fetchAll($sql);
        $toImport = [];
        $found = [];
        foreach ($old as $itm) {
            //            $forum = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $itm['forum_id']);
//            $gid = $itm['user_id'] - 1000000;
//            $group = $this->em->find('Zikula\GroupsModule\Entity\GroupEntity', $gid);
//            if (!$forum || !$group) {
//                $found[] = $itm;
//            } else {
                $toImport[] = $itm;
//            }
        }

        return ['found'=> $found, 'toImport' => $toImport];
    }

    public function importModeratorsGroups($data)
    {
        $connection = $this->em->getConnection();
        if (!array_key_exists('total', $data)) {
            $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_forum_mods WHERE user_id > 1000000';
            $statement = $connection->prepare($sql);
            $statement->execute();
            $data['total'] = $statement->fetchColumn();
            $data['pages'] = ceil($data['total'] / $data['pageSize']);
        }

        $limit = $data['pageSize'];
        $offset = $data['page'] == 0 ? $data['page'] : $data['page'] * $limit;

        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_forum_mods WHERE user_id > 1000000 LIMIT :offset,:limit';
        $statement = $connection->prepare($sql);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, \PDO::PARAM_INT);
        $statement->execute();
        $currentPageItems = $statement->fetchAll();
        $data['imported'] = 0;
        $data['rejected'] = 0;
        foreach ($currentPageItems as $itm) {
            $forum = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $itm['forum_id']);
            $gid = $itm['user_id'] - 1000000;
            $group = $this->em->find('Zikula\GroupsModule\Entity\GroupEntity', $gid);
            if (!$forum || !$group) {
                $data['rejected']++;

                continue;
            }
            $itmObj = new ModeratorGroupEntity();
            $itmObj->setForum($forum);
            $itmObj->setGroup($group);

//            $metadata = $this->em->getClassMetadata(get_class($itmObj));
//            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
//            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $this->em->persist($itmObj);

            $data['imported']++;
        }
        $this->em->flush();

        return $data;
    }

    public function getFSStatus()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_subscription';
        $old = $connection->fetchAll($sql);
        $toImport = [];
        $found = [];
        foreach ($old as $itm) {
            //            $forum = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $itm['forum_id']);
//            $user = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $itm['user_id']);
//            if (!$forum || !$user) {
//                $found[] = $itm;
//            } else {
                $toImport[] = $itm;
//            }
        }

        return ['found'=> $found, 'toImport' => $toImport];
    }

    public function importFS($data)
    {
        $connection = $this->em->getConnection();
        if (!array_key_exists('total', $data)) {
            $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_subscription';
            $statement = $connection->prepare($sql);
            $statement->execute();
            $data['total'] = $statement->fetchColumn();
            $data['pages'] = ceil($data['total'] / $data['pageSize']);
        }

        $limit = $data['pageSize'];
        $offset = $data['page'] == 0 ? $data['page'] : $data['page'] * $limit;

        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_subscription LIMIT :offset,:limit';
        $statement = $connection->prepare($sql);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, \PDO::PARAM_INT);
        $statement->execute();
        $currentPageItems = $statement->fetchAll();
        $data['imported'] = 0;
        $data['rejected'] = 0;
        foreach ($currentPageItems as $itm) {
            $forum = $this->em->find('Zikula\DizkusModule\Entity\ForumEntity', $itm['forum_id']);
            $user = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $itm['user_id']);
            if (!$forum || !$user) {
                $data['rejected']++;

                continue;
            }
            $itmObj = new ForumSubscriptionEntity($user, $forum);

//            $metadata = $this->em->getClassMetadata(get_class($itmObj));
//            $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
//            $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $this->em->persist($itmObj);

            $data['imported']++;
        }
        $this->em->flush();

        return $data;
    }

    public function getTSStatus()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_topic_subscription';
        $old = $connection->fetchAll($sql);
        $toImport = [];
        $found = [];
        foreach ($old as $itm) {
            //            $topic = $this->em->find('Zikula\DizkusModule\Entity\TopicEntity', $itm['topic_id']);
//            $user = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $itm['user_id']);
//            if (!$topic || !$user) {
//                $found[] = $itm;
//            } else {
                $toImport[] = $itm;
//            }
        }

        return ['found'=> $found, 'toImport' => $toImport];
    }

    public function importTS($data)
    {
        $connection = $this->em->getConnection();
        if (!array_key_exists('total', $data)) {
            $sql = 'SELECT count(*) AS total FROM ' . $this->prefix . '_dizkus_topic_subscription';
            $statement = $connection->prepare($sql);
            $statement->execute();
            $data['total'] = $statement->fetchColumn();
            $data['pages'] = ceil($data['total'] / $data['pageSize']);
        }

        $limit = $data['pageSize'];
        $offset = $data['page'] == 0 ? $data['page'] : $data['page'] * $limit;

        $sql = 'SELECT * FROM ' . $this->prefix . '_dizkus_topic_subscription LIMIT :offset,:limit';
        $statement = $connection->prepare($sql);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->bindValue('offset', $offset, \PDO::PARAM_INT);
        $statement->execute();
        $currentPageItems = $statement->fetchAll();
        $data['imported'] = 0;
        $data['rejected'] = 0;
        foreach ($currentPageItems as $itm) {
            $topic = $this->em->find('Zikula\DizkusModule\Entity\TopicEntity', $itm['topic_id']);
            $user = $this->em->find('Zikula\DizkusModule\Entity\ForumUserEntity', $itm['user_id']);
            if (!$topic || !$user) {
                $data['rejected']++;

                continue;
            }

            $itmObj = new TopicSubscriptionEntity($user, $topic);
            $this->em->persist($itmObj);
            $data['imported']++;
        }
        $this->em->flush();

        return $data;
    }

    /*
     * DB check below
     *
     */
    public function getTablesForPrefix()
    {
        $connection = $this->em->getConnection();
        $importTables = [];
        foreach ($connection->getSchemaManager()->listTables() as $tableDetails) {
            if (strpos($tableDetails->getName(), $this->prefix) !== false) {
                $tablename = substr($tableDetails->getName(), strlen($this->prefix));
                $importTables[$tablename]['elements'] = $connection->fetchAll('SELECT * FROM ' . $tableDetails->getName());
                $importTables[$tablename]['status'] = $this->checkTableStatus($tableDetails, $this->prefix);
            }
        }

        return $importTables;
    }

    /**
     * Check table status
     */
    public function checkTableStatus($tableDetails, $prefix)
    {
        if (!in_array(str_replace($prefix, "", $tableDetails->getName()), $this->getSupportedTables())) {
            return false;
        }

        return $this->checkTableColumns($tableDetails, $prefix);
    }

    /**
     * Get supported tables
     */
    public function getSupportedTables()
    {
        return ['dizkus_categories',
            'dizkus_forum_mods',
            'dizkus_forums',
            'dizkus_posts',
            'dizkus_subscription',
            'dizkus_ranks',
            'dizkus_topics',
            'dizkus_topic_subscription',
            'dizkus_forum_favorites',
            'dizkus_users'
        ];
    }

    /**
     * Check table columns
     */
    public function checkTableColumns($tableDetails, $prefix)
    {
        if ($this->check310TableCompatibility($tableDetails, $prefix)) {
            return '3.1.0';
        }

        return false;
    }

    /**
     * Check 3 compat
     */
    public function check310TableCompatibility($tableDetails, $prefix)
    {
        $tablesWithColumns = [
            'dizkus_categories' => ['cat_id',
                'cat_title',
                'cat_order'
            ],
            'dizkus_forum_mods' => ['id',
                'forum_id',
                'user_id'
            ],
            'dizkus_forums' => ['forum_id',
                'forum_name',
                'forum_desc',
                'forum_topics',
                'forum_posts',
                'forum_last_post_id',
                'cat_id',
                'is_subforum',
                'forum_order',
                'forum_pop3_active',
                'forum_pop3_server',
                'forum_pop3_port',
                'forum_pop3_login',
                'forum_pop3_password',
                'forum_pop3_interval',
                'forum_pop3_lastconnect',
                'forum_pop3_pnuser',
                'forum_pop3_pnpassword',
                'forum_pop3_matchstring',
                'forum_moduleref',
                'forum_pntopic'
            ],
            'dizkus_posts' => ['post_id',
                'topic_id',
                'forum_id',
                'poster_id',
                'post_time',
                'poster_ip',
                'post_msgid',
                'post_text',
                'post_title'
            ],
            'dizkus_subscription' => ['msg_id',
                'forum_id',
                'user_id'
            ],
            'dizkus_ranks' => ['rank_id',
                'rank_title',
                'rank_min',
                'rank_max',
                'rank_special',
                'rank_image',
                'rank_desc'
            ],
            'dizkus_topics' => ['topic_id',
                'topic_title',
                'topic_poster',
                'topic_time',
                'topic_views',
                'topic_replies',
                'topic_last_post_id',
                'forum_id',
                'topic_status',
                'sticky',
                'topic_reference'
            ],
            'dizkus_topic_subscription' => ['id',
                'topic_id',
                'user_id'
            ],
            'dizkus_forum_favorites' => ['forum_id',
                'user_id'
            ],
            'dizkus_users' => ['user_id',
                'user_posts',
                'user_rank',
                'user_level',
                'user_lastvisit',
                'user_favorites',
                'user_post_order'
            ],
        ];

        if (array_key_exists(str_replace($prefix, "", $tableDetails->getName()), $tablesWithColumns)) {
            $importTableColumns = array_keys($tableDetails->getColumns());
            $supportedColumns = $tablesWithColumns[str_replace($prefix, "", $tableDetails->getName())];
            // exact fields order check
            return array_diff($importTableColumns, $supportedColumns) === array_diff($supportedColumns, $importTableColumns);
        }

        return false;
    }
}

////
////    /**
////     * migrate hooked topics data to maintain hook connection with original object.
////     *
////     * This routine will only attempt to migrate references where the topic_reference field
////     * looks like `moduleID-objectId` -> e.g. '14-57'. If the field contains any underscores
////     * the topic will be locked and the reference left unmigrated. This is mainly because
////     * modules that use that style of reference are not compatible with Core 1.4.0+
////     * anyway and so migrating their references would be pointless.
////     *
////     * Additionally, if the subscriber module has more than one subscriber area, then migration is
////     * also impossible (which to choose?) so the topic is locked and reference left
////     * unmigrated also.
////     *
////     * @param array $rows
////     */
////    private function upgrade_to_4_0_0_migrateHookedTopics($rows)
////    {
////        $count = 0;
////        foreach ($rows as $row) {
////            $topic = $this->entityManager->find('Zikula\DizkusModule\Entity\TopicEntity', $row['topic_id']);
////            if (isset($topic)) {
////                if (strpos($row['topic_reference'], '_') !== false) {
////                    // reference contains an unsupported underscore, lock the topic
////                    $topic->lock();
////                } else {
////                    list($moduleId, $objectId) = explode('-', $row['topic_reference']);
////                    //$moduleInfo = ModUtil::getInfo($moduleId);
////                    $module = $this->entityManager->getRepository('Zikula\ExtensionsModule\Entity\ExtensionEntity')->find($moduleId);
////                    if ($module) {
////                        $searchCritera = [
////                            'owner' => $module->getName(),
////                            'areatype' => 's',
////                            'category' => 'ui_hooks', ];
////                        $subscriberArea = $this->entityManager->getRepository('Zikula\\Component\\HookDispatcher\\Storage\\Doctrine\\Entity\\HookAreaEntity')->findBy($searchCritera);
////                        if (count($subscriberArea) != 1) {
////                            // found either too many areas or none. cannot migrate
////                            $topic->lock();
////                        } else {
////                            // finally set the information
////                            $topic->setHookedModule($module->getName());
////                            $topic->setHookedAreaId($subscriberArea->getId());
////                            $topic->setHookedObjectId($objectId);
////                        }
////                    }
////                }
////
////                ++$count;
////                if ($count > 20) {
////                    $this->entityManager->flush();
////                    $count = 0;
////                }
////            }
////        }
////        // flush remaining
////        $this->entityManager->flush();
////    }
