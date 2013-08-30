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
use ModUtil;
use SecurityUtil;
use LogUtil;
use UserUtil;
use DataUtil;
use Search_Api_User;
use DBUtil;

/**
 * This class provides the search api functions
 */
class SearchApi extends \Zikula_AbstractApi
{
    /**
     * Search plugin info
     *
     * @return array Info array
     */
    public function info()
    {
        return array('title' => $this->__('Dizkus'), 'functions' => array('Dizkus' => 'search'));
    }

    /**
     * Search form component
     *
     * @param array $args The arguments array.
     *        bool $args['active'] The active value.
     *
     * @return string
     */
    public function options($args)
    {
        if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            $view = Zikula_View::getInstance('Dizkus', false, null, true);
            $view->assign('active', isset($args['active']) && isset($args['active']['Dizkus']) || !isset($args['active']));
            $view->assign('forums', ModUtil::apiFunc($this->name, 'Forum', 'getParents', array('includeRoot' => false)));

            return $view->fetch('search/options.tpl');
        }

        return false;
    }

    /**
     * Do last minute access checking and assign URL to items
     *
     * Access checking is ignored since access check has
     * already been done. But we add a link to the topic found
     *
     * @param array $args The arguments array.
     *
     * @return string
     */
    public function search_check($args)
    {
        $args['datarow']['url'] = $args['datarow']['extra'];

        return true;
    }

    /**
     * Search plugin main function
     *
     * @param array $args The arguments array.
     *        string $args['q'] The text to search.
     *        string $args['searchtype'] The type of the search ('AND', 'OR' or 'EXACT').
     *        string $args['ssearchorder'] The search order ('newest', 'oldest' or 'alphabetical').
     *
     * @return string
     */
    public function search($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            return true;
        }
        if ($this->request->isMethod('GET')) {
            $args['forums'] = $this->request->query->get('Dizkus_forum', null);
            $args['searchwhere'] = $this->request->query->get('Dizkus_searchwhere', 'post');
        } else {
            $args['forums'] = $this->request->request->get('Dizkus_forum', null);
            $args['searchwhere'] = $this->request->request->get('Dizkus_searchwhere', 'post');
        }
        $minlen = $this->getVar('minsearchlength', 3);
        $maxlen = $this->getVar('maxsearchlength', 30);
        if (strlen($args['q']) < $minlen || strlen($args['q']) > $maxlen) {
            return LogUtil::registerStatus($this->__f('Error! For forum searches, the search string must be between %1$s and %2$s characters in length.', array($minlen, $maxlen)));
        }
        if (!is_array($args['forums']) || count($args['forums']) == 0) {
            // set default
            $args['forums'][0] = -1;
        }
        if ($args['searchwhere'] != 'post' && $args['searchwhere'] != 'author') {
            $args['searchwhere'] = 'post';
        }
        // ToDo: Reactivate fulltext support
        // check mod var for fulltext support
        //$funcname = ($this->getVar('fulltextindex', 0) == 1) ? 'fulltext' : 'nonfulltext';
        //$funcname = ($this->getVar('fulltextindex', 'no') == 'yes') ? 'fulltext' : 'nonfulltext';
        return $this->nonfulltext($args);
    }

    /**
     * nonfulltext
     *
     * the function that will search the forum
     *
     * @param array $args The arguments array.
     * q             string the text to search
     * searchtype    string 'AND', 'OR' or 'EXACT'
     * searchorder   string 'newest', 'oldest' or 'alphabetical'
     * numlimit      int    limit for search, defaultsto 10
     * page          int    number of page t show
     * startnum      int    the first item to show
     * from Dizkus:
     * searchwhere   string 'posts' or 'author'
     * forums        array of forums to dearch
     * @return boolean
     */
    private function nonfulltext($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            return true;
        }
        // get all forums the user is allowed to read
        $userforums = ModUtil::apiFunc('Dizkus', 'forum', 'getForumIdsByPermission');
        if (!is_array($userforums) || count($userforums) == 0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return true;
        }
        $where = '';
        switch ($args['searchwhere']) {
            case 'author':
                // searchfor is empty, we search by author only (done later on)
                $searchauthor = UserUtil::getIDFromName($args['q']);
                if ($searchauthor > 0) {
                    $where = 'p.poster = ' . DataUtil::formatForStore($searchauthor);
                } else {
                    return true;
                }
                break;
            case 'post':
            default:
                $where = Search_Api_User::construct_where($args, array('t.title', 'p.post_text'), null);
                if (!empty($where)) {
                    $where = trim(substr(trim($where), 1, -1));
                }
        }
        // check forums (multiple selection is possible!)
        if (!is_array($args['forums']) && $args['forums'] == -1 || $args['forums'][0] == -1) {
            // search in all forums we are allowed to see
            $whereforums = 't.forum IN (' . DataUtil::formatForStore(implode($userforums, ',')) . ') ';
        } else {
            // filter out forums we are not allowed to read
            $args['forums'] = array_intersect($userforums, (array) $args['forums']);
            if (count($args['forums']) == 0) {
                // error or user is not allowed to read any forum at all
                // return empty result set without even doing a db access
                return true;
            }
            $whereforums = 't.forum IN (' . DataUtil::formatForStore(implode($args['forums'], ',')) . ') ';
        }
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t')->from('Dizkus_Entity_Topic', 't')->leftJoin('t.posts', 'p')->where($where)->andWhere($whereforums);
        $topics = $qb->getQuery()->getResult();
        $sessionId = session_id();
        $showtextinsearchresults = $this->getVar('showtextinsearchresults', 'no');
        // Process the result set and insert into search result table
        foreach ($topics as $topic) {
            $posts = $topic->getPosts();
            $record = array('title' => $topic->getTitle(), 'text' => $showtextinsearchresults == 'yes' ? $posts[0]->getPost_text() : '', 'created' => $topic->getTopic_time()->format('Y-m-d H:i:s'), 'module' => 'Dizkus', 'session' => $sessionId, 'extra' => ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic->getTopic_id())));
            if (!DBUtil::insertObject($record, 'search_result')) {
                return LogUtil::registerError($this->__('Error! Could not save the search results.'));
            }
        }

        return true;
    }

    /**
     * fulltext
     *
     * the function that will search the forum using fulltext indices - does not work on
     * InnoDB databases!!!
     *
     * @params q             string the text to search
     * @params searchtype    string 'AND', 'OR' or 'EXACT'
     * @params searchorder   string 'newest', 'oldest' or 'alphabetical'
     * from Dizkus:
     * @params searchwhere   string 'posts' or 'author'
     * @params forums        array of forums to search
     *
     * @return boolean
     */
    private function fulltext($args)
    {
        return;
        // disabled until reimplemented
        /*
         * There are no simple solutions for Fulltext searching using Doctrine
         * http://stackoverflow.com/questions/7246008/doctrine2-use-fulltext-and-myisam
         *
         */
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            return true;
        }
        // get all forums the user is allowed to read
        $userforums = ModUtil::apiFunc('Dizkus', 'forum', 'getForumIdsByPermission');
        if (!is_array($userforums) || count($userforums) == 0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return true;
        }
        // partial sql stored in $wherematch
        $wherematch = '';
        switch ($args['searchwhere']) {
            case 'author':
                // we search by author only
                $searchauthor = UserUtil::getIDFromName($args['q']);
                if ($searchauthor > 0) {
                    $wherematch = ' p.poster_id=\'' . DataUtil::formatForStore($searchauthor) . '\'';
                } else {
                    return true;
                }
                break;
            case 'post':
            default:
                $searchtype = strtoupper($args['searchtype']);
                if ($searchtype == 'EXACT') {
                    $q = DataUtil::formatForStore($args['q']);
                    $wherematch = "(MATCH p.post_text AGAINST ('{$q}') OR MATCH t.title AGAINST ('{$q}')) \n";
                } else {
                    $plusminuscount = preg_match('/[\\+\\-]/', $args['q']);
                    if ($this->getVar('extendedsearch', 'no') == 'yes' && $plusminuscount > 0) {
                        // seems to be an extended search
                        $q = DataUtil::formatForStore($args['q']);
                        $wherematch = "(MATCH p.post_text AGAINST ('{$q}' IN BOOLEAN MODE) OR MATCH t.title AGAINST ('{$q}' IN BOOLEAN MODE)) \n";
                    } else {
                        $wherematcharray = array();
                        $words = explode(' ', $args['q']);
                        foreach ($words as $word) {
                            $word = DataUtil::formatForStore($word);
                            // get post_text and match up forums/topics/posts
                            //$query .= "(pt.post_text LIKE '%$word%' OR t.title LIKE '%$word%') \n";
                            $wherematcharray[] .= "(MATCH p.post_text AGAINST ('{$word}') OR MATCH t.title AGAINST ('{$word}')) \n";
                        }
                        $wherematch = implode($searchtype == 'OR' ? ' OR ' : ' AND ', $wherematcharray);
                    }
                }
        }
        // check forums (multiple selection is possible!)
        // partial sql stored in $whereforums
        $whereforums = '';
        if (!is_array($args['forums']) && $args['forums'] == -1 || $args['forums'][0] == -1) {
            // search in all forums we are allowed to see
            $whereforums = 'p.forum_id IN (' . DataUtil::formatForStore(implode($userforums, ',')) . ') ';
        } else {
            // filter out forums we are not allowed to read
            $args['forums'] = array_intersect($userforums, (array) $args['forums']);
            if (count($args['forums']) == 0) {
                // error or user is not allowed to read any forum at all
                // return empty result set without even doing a db access
                return true;
            }
            $whereforums .= 'p.forum_id IN (' . DataUtil::formatForStore(implode($args['forums'], ',')) . ') ';
        }
        $this->start_search($wherematch, $whereforums);

        return true;
    }

    /**
     * Start search
     *
     * @param string $wherematch  The where expression.
     * @param string $whereforums The text to search.
     *
     * @return boolean
     */
    public function start_search($wherematch, $whereforums)
    {
        ModUtil::dbInfoLoad('Search');
        $ztable = DBUtil::getTables();
        $topicurl = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => '%%%'));
        $sessionid = DataUtil::formatForStore(session_id());
        $now = time();
        $showtextinsearchresults = $this->getVar('showtextinsearchresults', 'no');
        $textsql = $showtextinsearchresults == 'yes' ? 'REPLACE(p.post_text, \'[addsig]\', \'\') as text' : '\'\'';
        $sql = 'INSERT INTO ' . $ztable['search_result'] . '
                (title, text, module, extra, created, found, sesid)
                SELECT
                  t.title,
                  ' . $textsql . ',
                  \'Dizkus\',
                  REPLACE (\'' . $topicurl . '\', \'%%%\', t.topic_id) as extra,
                  p.post_time,
                  NOW(),
                  \'' . $sessionid . '\'
                  FROM ' . $ztable['dizkus_posts'] . ' AS p,
                       ' . $ztable['dizkus_topics'] . ' AS t
                  WHERE ' . $wherematch . '
                  AND p.topic_id=t.topic_id
                  AND ' . $whereforums . '
                  GROUP BY p.topic_id';
        $res = DBUtil::executeSQL($sql);

        return true;
    }

}
