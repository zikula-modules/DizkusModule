<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Dizkus\Api;

use Zikula_View;
use UserUtil;
use ModUtil;
use LogUtil;
use System;
use CookieUtil;
use Dizkus_Manager_Post;
use DateTime;
use Dizkus_Manager_Topic;
use DataUtil;
use Doctrine;

class UserApi extends \Zikula_AbstractApi
{

    /**
     * Instance of Zikula_View.
     *
     * @var Zikula_View
     */
    protected $view;

    /**
     * Initialize.
     *
     * @return void
     */
    protected function initialize()
    {
        $this->setView();
    }

    /**
     * Set view property.
     *
     * @param Zikula_View $view Default null means new Render instance for this module name.
     *
     * @return Zikula_AbstractController
     */
    protected function setView(Zikula_View $view = null)
    {
        if (is_null($view)) {
            $view = Zikula_View::getInstance($this->getName());
        }
        $this->view = $view;

        return $this;
    }

    /**
     * Counts posts in forums, topics
     * or counts forum users
     *
     * @params $args['id'] int the id, depends on 'type' parameter
     * @params $args['type'] string, defines the id parameter
     * @params $args['force'] boolean, default false, if true, do not use cached
     * @returns int (depending on type and id)
     */
    public function countstats($args)
    {
        $id = isset($args['id']) ? $args['id'] : null;
        $type = isset($args['type']) ? $args['type'] : null;
        $force = isset($args['force']) ? (bool) $args['force'] : false;
        static $cache = array();
        switch ($type) {
            case 'all':
            case 'allposts':
                if (!isset($cache[$type])) {
                    $cache[$type] = $this->countEntity('Post');
                }

                return $cache[$type];
                break;
            case 'forum':
                if (!isset($cache[$type])) {
                    $cache[$type] = $this->countEntity('Forum');
                }

                return $cache[$type];
                break;
            case 'topic':
                if (!isset($cache[$type][$id])) {
                    $cache[$type][$id] = $this->countEntity('Post', 'topic', $id);
                }

                return $cache[$type][$id];
                break;
            case 'forumposts':
                if ($force || !isset($cache[$type][$id])) {
                    $dql = 'SELECT count(p)
                        FROM Dizkus\Entity\PostEntity p
                        WHERE p.topic IN (
                            SELECT t.topic_id
                            FROM Dizkus\Entity\TopicEntity t
                            WHERE t.forum = :forum)';
                    $query = $this->entityManager->createQuery($dql)->setParameter('forum', $id);
                    $cache[$type][$id] = $query->getSingleScalarResult();
                }

                return $cache[$type][$id];
                break;
            case 'forumtopics':
                if ($force || !isset($cache[$type][$id])) {
                    $cache[$type][$id] = $this->countEntity('Topic', 'forum', $id);
                }

                return $cache[$type][$id];
                break;
            case 'alltopics':
                if (!isset($cache[$type])) {
                    $cache[$type] = $this->countEntity('Topic');
                }

                return $cache[$type];
                break;
            case 'allmembers':
                if (!isset($cache[$type])) {
                    $cache[$type] = count(UserUtil::getUsers());
                }

                return $cache[$type];
                break;
            case 'lastmember':
            case 'lastuser':
                if (!isset($cache[$type])) {
                    $qb = $this->entityManager->createQueryBuilder();
                    $qb->select('u')->from('Dizkus\Entity\ForumUserEntity', 'u')->orderBy('u.user', 'DESC')->setMaxResults(1);
                    $user = $qb->getQuery()->getSingleResult();
                    $cache[$type] = $user->getUser()->getUname();
                }

                return $cache[$type];
                break;
            default:
                return LogUtil::registerError($this->__('Error! Wrong parameters in countstats().'), null, ModUtil::url('Dizkus', 'user', 'index'));
        }
    }

    private function countEntity($entityname, $where = null, $parameter = null)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(a)')->from('Dizkus_Entity_' . $entityname, 'a');
        if (isset($where) && isset($parameter)) {
            $qb->andWhere('a.' . $where . ' = :parameter')->setParameter('parameter', $parameter);
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * setcookies
     *
     * reads the cookie, updates it and returns the last visit date in unix timestamp
     *
     * @params none
     * @returns unix timestamp last visit date
     *
     */
    public function setcookies()
    {
        /**
         * set last visit cookies and get last visit time
         * set LastVisit cookie, which always gets the current time and lasts one year
         */
        $path = System::getBaseUri();
        if (empty($path)) {
            $path = '/';
        } elseif (substr($path, -1, 1) != '/') {
            $path .= '/';
        }
        $time = time();
        CookieUtil::setCookie('DizkusLastVisit', "{$time}", $time + 31536000, $path, null, null, false);
        $lastVisitTemp = CookieUtil::getCookie('DizkusLastVisitTemp', false, null);
        $temptime = empty($lastVisitTemp) ? $time : $lastVisitTemp;
        // set LastVisitTemp cookie, which only gets the time from the LastVisit and lasts for 30 min
        CookieUtil::setCookie('DizkusLastVisitTemp', "{$temptime}", time() + 1800, $path, null, null, false);

        return $temptime;
    }

    /**
     * get_viewip_data
     *
     * @param array $args The argument array.
     *        int $args['post_id] The postings id.
     *
     * @return array with informstion.
     */
    public function get_viewip_data($args)
    {
        $managedPost = new Dizkus_Manager_Post($args['post_id']);
        $pip = $managedPost->get()->getPoster_ip();
        $viewip = array(
            'poster_ip' => $pip,
            'poster_host' => gethostbyaddr($pip));
        $dql = 'SELECT p, fu, u
            FROM Dizkus\Entity\PostEntity p
            JOIN p.poster fu
            JOIN fu.user u
            WHERE p.poster_ip = :pip
            GROUP BY p.poster';
        $query = $this->entityManager->createQuery($dql)->setParameter('pip', $pip);
        $posts = $query->getResult();
        foreach ($posts as $post) {
            /* @var $post Dizkus\Entity\PostEntity */
            $viewip['users'][] = array(
                'uid' => $post->getPoster()->getUser_id(),
                'uname' => $post->getPoster()->getUser()->getUname(),
                'postcount' => $post->getPoster()->getPostCount());
        }

        return $viewip;
    }

    /**
     * getTopicPage
     * Uses the number of replyCount and the posts_per_page settings to determine the page
     * number of the last post in the thread. This is needed for easier navigation.
     *
     * @params $args['replyCount'] int number of topic replies
     * @return int page number of last posting in the thread
     */
    public function getTopicPage($args)
    {
        if (!isset($args['replyCount']) || !is_numeric($args['replyCount']) || $args['replyCount'] < 0) {
            return LogUtil::registerArgsError();
        }
        // get some enviroment
        $posts_per_page = ModUtil::getVar('Dizkus', 'posts_per_page');
        $post_sort_order = ModUtil::getVar('Dizkus', 'post_sort_order');
        $last_page = 0;
        if ($post_sort_order == 'ASC') {
            // +1 for the initial posting
            $last_page = floor($args['replyCount'] / $posts_per_page) * $posts_per_page + 1;
        }
        // if not ASC then DESC which means latest topic is on top anyway...
        return $last_page;
    }

    /**
     * insert rss
     * @see rss2dizkus.php - only used there
     *
     * @params $args['forum']    array with forum data
     * @params $args['items']    array with feed data as returned from Feeds module
     * @return boolean true or false
     */
    public function insertrss($args)
    {
        if (!$args['forum'] || !$args['items']) {
            return false;
        }
        foreach ($args['items'] as $item) {
            // create the reference
            $dateTimestamp = $item->get_date('Y-m-d H:i:s');
            if (empty($dateTimestamp)) {
                $reference = md5($item->get_link());
                $dateTimestamp = date('Y-m-d H:i:s', time());
            } else {
                $reference = md5($item->get_link() . '-' . $dateTimestamp);
            }
            $topicTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimestamp);
            // Checking if the forum already has that news.
            $topic = $this->entityManager->getRepository('Dizkus\Entity\TopicEntity')->findOneBy(array('reference' => $reference));
            if (!isset($topic)) {
                // Not found, add the feed item
                $subject = $item->get_title();
                // create message
                $message = '<strong>' . $this->__('Summary') . ' :</strong>\\n\\n' . $item->get_description() . '\\n\\n<a href="' . $item->get_link() . '">' . $item->get_title() . '</a>\\n\\n';
                // store message
                $newManagedTopic = new Dizkus_Manager_Topic();
                $data = array(
                    'title' => $subject,
                    'message' => $message,
                    'topic_time' => $topicTime,
                    'forum_id' => $args['forum']['forum_id'],
                    'attachSignature' => false,
                    'subscribe_topic' => false,
                    'reference' => $reference);
                $newManagedTopic->prepare($data);
                $topicId = $newManagedTopic->create();
                if (!$topicId) {
                    // An error occured
                    return false;
                }
            }
        }

        return true;
    }

    public function isSpam($message)
    {
        // Akismet
        if (ModUtil::available('Akismet') && $this->getVar('spam_protector') == 'Akismet') {
            if (ModUtil::apiFunc('Akismet', 'user', 'isspam', array('content' => $message))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the useragent is a bot (blacklisted)
     *
     * @return boolean
     */
    public function useragentIsBot()
    {
        // check the user agent - if it is a bot, return immediately
        $robotslist = array(
            'ia_archiver',
            'googlebot',
            'mediapartners-google',
            'yahoo!',
            'msnbot',
            'jeeves',
            'lycos');
        $useragent = System::serverGetVar('HTTP_USER_AGENT');
        for ($cnt = 0; $cnt < count($robotslist); $cnt++) {
            if (strpos(strtolower($useragent), $robotslist[$cnt]) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * dzkVarPrepHTMLDisplay
     * removes the  [code]...[/code] before really calling DataUtil::formatForDisplayHTML()
     */
    public function dzkVarPrepHTMLDisplay($text)
    {
        // remove code tags
        $codecount1 = preg_match_all('/\\[code(.*)\\](.*)\\[\\/code\\]/si', $text, $codes1);
        for ($i = 0; $i < $codecount1; $i++) {
            $text = preg_replace('/(' . preg_quote($codes1[0][$i], '/') . ')/', " DIZKUSCODEREPLACEMENT{$i} ", $text, 1);
        }
        // the real work
        $text = nl2br(DataUtil::formatForDisplayHTML($text));
        // re-insert code tags
        for ($i = 0; $i < $codecount1; $i++) {
            // @todo should use htmlentities here???? dzkstriptags too vvv
            $text = preg_replace("/ DIZKUSCODEREPLACEMENT{$i} /", $codes1[0][$i], $text, 1);
        }

        return $text;
    }

    /**
     * dzkstriptags
     * strip all html tags outside of [code][/code]
     *
     * @params  $text     string the text
     * @returns string    the sanitized text
     */
    public function dzkstriptags($text = '')
    {
        if (!empty($text) && ModUtil::getVar('Dizkus', 'striptags') == 'yes') {
            // save code tags
            $codecount = preg_match_all('/\\[code(.*)\\](.*)\\[\\/code\\]/siU', $text, $codes);
            for ($i = 0; $i < $codecount; $i++) {
                $text = preg_replace('/(' . preg_quote($codes[0][$i], '/') . ')/', " DZKSTREPLACEMENT{$i} ", $text, 1);
            }
            // strip all html
            $text = strip_tags($text);
            // replace code tags saved before
            for ($i = 0; $i < $codecount; $i++) {
                // @todo should use htmlentities here???? dzkstriptagst too ^^^
                $text = preg_replace("/ DZKSTREPLACEMENT{$i} /", $codes[0][$i], $text, 1);
            }
        }

        return $text;
    }

    /**
     * get an array of users where uname matching text fragment(s)
     *
     * @param  array   $args['fragments']
     * @param  integer $args['limit']
     * @return array
     */
    public function getUsersByFragments($args)
    {
        $fragments = isset($args['fragments']) ? $args['fragments'] : null;
        $limit = isset($args['limit']) ? $args['limit'] : -1;
        if (empty($fragments)) {
            return array();
        }
        $rsm = new Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('Zikula\\Module\\UsersModule\\Entity\\UserEntity', 'u');
        $rsm->addFieldResult('u', 'uname', 'uname');
        $rsm->addFieldResult('u', 'uid', 'uid');
        $sql = 'SELECT u.uid, u.uname FROM users u WHERE ';
        $subSql = array();
        foreach ($fragments as $fragment) {
            $subSql[] = 'u.uname REGEXP \'(' . DataUtil::formatForStore($fragment) . ')\'';
        }
        $sql .= implode(' OR ', $subSql);
        $sql .= ' ORDER BY u.uname ASC';
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        $users = $this->entityManager->createNativeQuery($sql, $rsm)->getResult();

        return $users;
    }

    /**
     * Truncate text to desired length to nearest word
     * @see http://stackoverflow.com/a/9219884/2600812
     * @param  string  $text
     * @param  integer $chars
     * @return string
     */
    public static function truncate($text, $chars = 25)
    {
        $originalText = $text;
        $text = $text . ' ';
        $text = substr($text, 0, $chars);
        $text = substr($text, 0, strrpos($text, ' '));
        $text = strlen($originalText) == strlen($text) ? $text : $text . '...';

        return $text;
    }

}
