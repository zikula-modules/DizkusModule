<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * This class provides the search api functions
 */
class Dizkus_Api_Search extends Zikula_AbstractApi {
    
    /**
     * Search plugin info
     *
     * @return array Info array
     */
    public function info()
    {
        return array('title'     => 'Dizkus',
                     'functions' => array('Dizkus' => 'search'));
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
            $render = Zikula_View::getInstance('Dizkus', false, null, true);
            $render->assign('active', (isset($args['active']) && isset($args['active']['Dizkus'])) || !isset($args['active']));
            $render->assign('forums', ModUtil::apiFunc('Dizkus', 'admin', 'readforums'));
            return $render->fetch('dizkus_search.tpl');
        }
        return '';
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
        if ($this->request->isGet()) {
            $args['forums']      = $this->request->query->get('Dizkus_forum', null);
            $args['searchwhere'] = $this->request->query->get('Dizkus_searchwhere', 'post');
        } else {
            $args['forums']      = $this->request->request->get('Dizkus_forum', null);
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
    
        if ($args['searchwhere'] <> 'post' && $args['searchwhere'] <> 'author') {
            $args['searchwhere'] = 'post';
        }
    
        // check mod var for fulltext support
        //$funcname = ($this->getVar('fulltextindex', 0) == 1) ? 'fulltext' : 'nonfulltext';
        $funcname = ($this->getVar('fulltextindex', 'no') == 'yes') ? 'fulltext' : 'nonfulltext';

        return $this->$funcname($args);
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
        $userforums = ModUtil::apiFunc('Dizkus', 'user', 'readuserforums');
        if (!is_array($userforums) || count($userforums) == 0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return true;
        }
    
        switch ($args['searchwhere'])
        {
            case 'author':
                // searchfor is empty, we search by author only (done later on)
                $searchauthor = UserUtil::getIDFromName($args['q']);
                if ($searchauthor > 0) {
                    $wherematch = " p.poster_id='" . DataUtil::formatForStore($searchauthor) . "'";
                } else {
                    return true;
                }
                break;
    
            case 'post':
            default:
                $searchtype = strtoupper($args['searchtype']);
                if ($searchtype == 'EXACT') {
                    $q = DataUtil::formatForStore($args['q']);
                    $wherematch .= "(p.post_text LIKE '%$q%' OR t.topic_title LIKE '%$q%') \n";
                } else {
                    $wherematcharray = array();
                    $words = explode(' ', $args['q']);
                    foreach ($words as $word) {
                        $word = DataUtil::formatForStore($word);
                        // get post_text and match up forums/topics/posts
                        //$query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                        $wherematcharray[] = "(p.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                    }
                    $wherematch = implode(($searchtype == 'OR' ? ' OR ' : ' AND '), $wherematcharray);
                }
        }
    
        // now create a very simple array of forum_ids only. we do not need
        // all the other stuff in the $userforums array entries
        $allowedforums = array_keys($userforums);
    
        if ((!is_array($args['forums']) && $args['forums'] == -1) || $args['forums'][0] == -1) {
            // search in all forums we are allowed to see
            $whereforums = 'p.forum_id IN (' . DataUtil::formatForStore(implode($allowedforums, ',')) . ') ';
        } else {
            // filter out forums we are not allowed to read
            $args['forums'] = array_intersect($allowedforums, (array)$args['forums']);
    
            if (count($args['forums']) == 0) {
                // error or user is not allowed to read any forum at all
                // return empty result set without even doing a db access
                return true;
            }
            $whereforums = 'p.forum_id IN(' . DataUtil::formatForStore(implode($args['forums'], ',')) . ') ';
        }
    
        $this->start_search($wherematch, $whereforums);
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
        
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            return true;
        }
    
        // get all forums the user is allowed to read
        $userforums = ModUtil::apiFunc('Dizkus', 'user', 'readuserforums');
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
                    $wherematch = " p.poster_id='" . DataUtil::formatForStore($searchauthor) . "'";
                } else {
                    return true;
                }
                break;
    
            case 'post':
            default:
                $searchtype = strtoupper($args['searchtype']);
                if ($searchtype == 'EXACT') {
                    $q = DataUtil::formatForStore($args['q']);
                    $wherematch  =  "(MATCH p.post_text AGAINST ('$q') OR MATCH t.topic_title AGAINST ('$q')) \n";
                } else {
                    $plusminuscount = preg_match('/[\+\-]/', $args['q']);
                    if (($this->getVar('extendedsearch', 'no') == 'yes') && ($plusminuscount > 0)) {
                        // seems to be an extended search
                        $q = DataUtil::formatForStore($args['q']);
                        $wherematch  = "(MATCH p.post_text AGAINST ('$q' IN BOOLEAN MODE) OR MATCH t.topic_title AGAINST ('$q' IN BOOLEAN MODE)) \n";
                    } else {
                        $wherematcharray = array();
                        $words = explode(' ', $args['q']);
                        foreach ($words as $word) {
                            $word = DataUtil::formatForStore($word);
                            // get post_text and match up forums/topics/posts
                            //$query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                            $wherematcharray[] .= "(MATCH p.post_text AGAINST ('$word') OR MATCH t.topic_title AGAINST ('$word')) \n";
                            //$wherematcharray[] = "(p.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                        }
                        $wherematch = implode(($searchtype == 'OR' ? ' OR ' : ' AND '), $wherematcharray);
                    }
                }
        }
    
        // check forums (multiple selection is possible!)
        // partial sql stored in $whereforums
        $whereforums = '';
    
        // now create a very simple array of forum_ids only. we do not need
        // all the other stuff in the $userforums array entries
        $allowedforums = array_keys($userforums);
    
        if ((!is_array($args['forums']) && $args['forums'] == -1) || $args['forums'][0] == -1) {
            // search in all forums we are allowed to see
            $whereforums = 'p.forum_id IN (' . DataUtil::formatForStore(implode($allowedforums, ',')) . ') ';
        } else {
            // filter out forums we are not allowed to read
            $args['forums'] = array_intersect($allowedforums, (array)$args['forums']);
    
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
    function start_search($wherematch, $whereforums)
    {
        ModUtil::dbInfoLoad('Search');
        $ztable = DBUtil::getTables();
    
        $topicurl = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => '%%%'));
        $sessionid = DataUtil::formatForStore(session_id());
        $now = time();
        $showtextinsearchresults = $this->getVar('showtextinsearchresults', 'no');
        $textsql = ($showtextinsearchresults == 'yes') ? 'REPLACE(p.post_text, \'[addsig]\', \'\') as text' : '\'\'';
        
        $sql = 'INSERT INTO ' . $ztable['search_result'] . '
                (title, text, module, extra, created, found, sesid)
                SELECT
                  t.topic_title,
                  '.$textsql.',
                  \'Dizkus\',
                  REPLACE (\''.$topicurl.'\', \'%%%\', t.topic_id) as extra,
                  p.post_time,
                  NOW(),
                  \''.$sessionid.'\'
                  FROM '.$ztable['dizkus_posts'].' AS p,
                       '.$ztable['dizkus_topics'].' AS t
                  WHERE '.$wherematch.'
                  AND p.topic_id=t.topic_id
                  AND '.$whereforums.'
                  GROUP BY p.topic_id';
    
        $res = DBUtil::executeSQL($sql);
        
        return true;
    }

}
