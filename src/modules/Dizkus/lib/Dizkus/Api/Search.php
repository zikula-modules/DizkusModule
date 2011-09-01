<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Search extends Zikula_AbstractApi {
    
    /**
     * Search plugin info
     */
    public function info()
    {
        return array('title'     => 'Dizkus',
                     'functions' => array('Dizkus' => 'search'));
    }
    
    /**
     * Search form component
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
     */
    public function search_check($args)
    {
        $args['datarow']['url'] = $args['datarow']['extra'];
        return true;
    }
    
    /**
     * Search plugin main function
     *
     * @params q             string the text to search
     * @params searchtype    string 'AND', 'OR' or 'EXACT'
     * @params searchorder   string 'newest', 'oldest' or 'alphabetical' 
     */
    public function search($args)
    {
        
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            return false;
        }
    
        $args['forums']      = FormUtil::getPassedValue('Dizkus_forum', null, 'GETPOST');
        $args['searchwhere'] = FormUtil::getPassedValue('Dizkus_searchwhere', 'post', 'GETPOST');
    
        $minlen = ModUtil::getVar('Dizkus', 'minsearchlength', 3);
        $maxlen = ModUtil::getVar('Dizkus', 'maxsearchlength', 30);
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
        //$funcname = (ModUtil::getVar('Dizkus', 'fulltextindex', 0) == 1) ? 'fulltext' : 'nonfulltext';
        $funcname = (ModUtil::getVar('Dizkus', 'fulltextindex', 'no') == 'yes') ? 'fulltext' : 'nonfulltext';
        
        return ModUtil::apiFunc('Dizkus', 'search', $funcname, $args);
    }
    
    /**
     * nonfulltext
     * the function that will search the forum
     *
     * THIS FUNCTION SHOULD NOT BE USED DIRECTLY, CALL Dizkus_searchapi_search INSTEAD
     *
     * @private
     *
     * @params q             string the text to search
     * @params searchtype    string 'AND', 'OR' or 'EXACT'
     * @params searchorder   string 'newest', 'oldest' or 'alphabetical' 
     * @params numlimit      int    limit for search, defaultsto 10
     * @params page          int    number of page t show
     * @params startnum      int    the first item to show
     * from Dizkus:
     * @params searchwhere   string 'posts' or 'author'
     * @params forums        array of forums to dearch
     * @returns true or false
     */
    public function nonfulltext($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            return false;
        }
    
        // get all forums the user is allowed to read
        $userforums = ModUtil::apiFunc('Dizkus', 'user', 'readuserforums');
        if (!is_array($userforums) || count($userforums) == 0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return false;
        }
    
        switch ($args['searchwhere'])
        {
            case 'author':
                // searchfor is empty, we search by author only (done later on)
                $searchauthor = UserUtil::getIDFromName($args['q']);
                if ($searchauthor > 0) {
                    $wherematch = " p.poster_id='" . DataUtil::formatForStore($searchauthor) . "'";
                } else {
                    return false;
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
                return false;
            }
            $whereforums = 'p.forum_id IN(' . DataUtil::formatForStore(implode($args['forums'], ',')) . ') ';
        }
    
        $this->start_search($wherematch, $whereforums);
        return true;
    }
    
    /**
     * fulltext
     * the function that will search the forum using fulltext indices - does not work on
     * InnoDB databases!!!
     *
     * THIS FUNCTION SHOULD NOT BE USED DIRECTLY, CALL Dizkus_searchapi_search INSTEAD
     *
     * @private
     *
     * @params q             string the text to search
     * @params searchtype    string 'AND', 'OR' or 'EXACT'
     * @params searchorder   string 'newest', 'oldest' or 'alphabetical' 
     * from Dizkus:
     * @params searchwhere   string 'posts' or 'author'
     * @params forums        array of forums to search
     * @returns true or false
     */
    public function fulltext($args)
    {
        
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            return false;
        }
    
        // get all forums the user is allowed to read
        $userforums = ModUtil::apiFunc('Dizkus', 'user', 'readuserforums');
        if (!is_array($userforums) || count($userforums) == 0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return false;
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
                    return false;
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
                    if ((ModUtil::getVar('Dizkus', 'extendedsearch', 'no') == 'yes') && ($plusminuscount > 0)) {
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
                return false;
            }
            $whereforums .= 'p.forum_id IN (' . DataUtil::formatForStore(implode($args['forums'], ',')) . ') ';
        }
    
        $this->start_search($wherematch, $whereforums);
        return true;
    }
    
    function start_search($wherematch, $whereforums)
    {
        ModUtil::dbInfoLoad('Search');
        $ztable = DBUtil::getTables();
    
        $topicurl = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => '%%%'));
        $sessionid = DataUtil::formatForStore(session_id());
        $now = time();
        $showtextinsearchresults = ModUtil::getVar('Dizkus', 'showtextinsearchresults', 'no');
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
