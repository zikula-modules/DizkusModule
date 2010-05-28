<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

Loader::includeOnce('modules/Dizkus/common.php');

/**
 * Search plugin info
 */
function Dizkus_searchapi_info()
{
    return array('title'     => 'Dizkus',
                 'functions' => array('Dizkus' => 'search'));
}

/**
 * Search form component
 */
function Dizkus_searchapi_options($args)
{
    if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
        $render = pnRender::getInstance('Dizkus', false, null, true);
        $render->assign('active', (isset($args['active']) && isset($args['active']['Dizkus'])) || !isset($args['active']));
        $render->assign('forums', pnModAPIFunc('Dizkus', 'admin', 'readforums'));
        return $render->fetch('dizkus_search.html');
    }
    return '';
}

/**
 * Do last minute access checking and assign URL to items
 *
 * Access checking is ignored since access check has
 * already been done. But we do add a URL to the found user
 */
function Dizkus_searchapi_search_check(&$args)
{
    $extra = unserialize($args['datarow']['extra']);
    $args['datarow']['url'] = pnModUrl('Dizkus', 'user', 'viewtopic', array('topic' => $extra['topic_id']));
    return true;
}

/**
 * Search form component
 */
function Dizkus_searchapi_internalsearchoptions($args)
{
    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $render = pnRender::getInstance('Dizkus', false, null, true);

    $render->assign('forums', pnModAPIFunc('Dizkus', 'admin', 'readforums'));

    return $render->fetch('dizkus_user_search.html');
}

/**
 * Search plugin main function
 *
 * @params q             string the text to search
 * @params searchtype    string 'AND', 'OR' or 'EXACT'
 * @params searchorder   string 'newest', 'oldest' or 'alphabetical' 
 * @params numlimit      int    limit for search, defaultsto 10
 * @params page          int    number of page t show
 * @params startnum      int    the first item to show
 */
function Dizkus_searchapi_search($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
        return false;
    }

    $args['forums']      = FormUtil::getPassedValue('Dizkus_forum', null, 'GETPOST');
    $args['searchwhere'] = FormUtil::getPassedValue('Dizkus_searchwhere', 'post', 'GETPOST');

    $minlen = pnModGetVar('Dizkus', 'minsearchlength', 3);
    $maxlen = pnModGetVar('Dizkus', 'maxsearchlength', 30);
    if (strlen($args['q']) < $minlen || strlen($args['q']) > $maxlen) {
        return LogUtil::registerStatus(__f('Error! For forum searches, the search string must be between %1$s and %2$s characters in length.', array($minlen, $maxlen), $dom));
    }
    if (!is_array($args['forums']) || count($args['forums']) == 0) {
        // set default
        $args['forums'][0] = -1;
    }

    if ($args['searchwhere'] <> 'post' && $args['searchwhere'] <> 'author') {
        $args['searchwhere'] = 'post';
    }

    // check mod var for fulltext support
    //$funcname = (pnModGetVar('Dizkus', 'fulltextindex', 0) == 1) ? 'fulltext' : 'nonfulltext';
    $funcname = (pnModGetVar('Dizkus', 'fulltextindex', 'no') == 'yes') ? 'fulltext' : 'nonfulltext';

    return pnModAPIFunc('Dizkus', 'search', $funcname, $args);
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
function Dizkus_searchapi_nonfulltext($args)
{
    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
        return false;
    }

    // get all forums the user is allowed to read
    $userforums = pnModAPIFunc('Dizkus', 'user', 'readuserforums');
    if (!is_array($userforums) || count($userforums) == 0) {
        // error or user is not allowed to read any forum at all
        // return empty result set without even doing a db access
        return false;
    }

    switch ($args['searchwhere'])
    {
        case 'author':
            // searchfor is empty, we search by author only (done later on)
            $searchauthor = pnUserGetIDFromName($args['q']);
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
                $wherematch = array();
                $words = array_filter(explode(' ', $args['q']));
                foreach ($words as $word)
                {
                    $word = DataUtil::formatForStore($word);
                    // get post_text and match up forums/topics/posts
                    //$query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                    $wherematch[] = "(p.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                }
                $wherematch = implode(($searchtype == 'OR' ? ' OR ' : ' AND '), $wherematch);
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

    // sorting not necessary, this is done when reading the serch_result table
    return start_search($wherematch, '', $whereforums, '', $args);
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
 * @params numlimit      int    limit for search, defaultsto 10
 * @params page          int    number of page t show
 * @params startnum      int    the first item to show
 * from Dizkus:
 * @params searchwhere   string 'posts' or 'author'
 * @params forums        array of forums to dearch
 * @returns true or false
 */
function Dizkus_searchapi_fulltext($args)
{
    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
        return false;
    }

    // get all forums the user is allowed to read
    $userforums = pnModAPIFunc('Dizkus', 'user', 'readuserforums');
    if (!is_array($userforums) || count($userforums) == 0) {
        // error or user is not allowed to read any forum at all
        // return empty result set without even doing a db access
        return false;
    }

    // partial sql stored in $wherematch
    $wherematch = '';
    // selectmatch contains almost the same as wherematch without the last AND and
    // will be used in the SELECT part like ... selectmatch as score
    // to enable ordering the results by score
    $selectmatch = '';
    $havingmatch = '';
    switch ($args['searchwhere'])
    {
        case 'author':
            // we search by author only
            $searchauthor = pnUserGetIDFromName($args['q']);
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
                $selectmatch = ", MATCH p.post_text AGAINST ('$q') as textscore, MATCH t.topic_title AGAINST ('$q') as subjectscore \n";
            } else {
                $selectmatch = array();
                $wherematch  = array();
                $havingmatch = array();
                $words = array_filter(explode(' ', $args['q']));
                $q = array();
                foreach ($words as $word)
                {
                    $word = trim(DataUtil::formatForStore($word));
                    // get post_text and match up topics/posts
                    $q[] = $searchtype == 'OR' ? "($word)" : $word;
                }
                $q = implode(' ', $q);
                $selectmatch = ", MATCH p.post_text AGAINST ('$q') as textscore, MATCH t.topic_title AGAINST ('$q') as subjectscore \n";
                $wherematch  = "(MATCH p.post_text AGAINST ('$q') OR MATCH t.topic_title AGAINST ('$q')) \n";
                $havingmatch = "(textscore > 0.2 OR subjectscore > 0.2)";
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

    // sorting not necessary, this is done when reading the serch_result table
    return start_search($wherematch, $selectmatch, $whereforums, $havingmatch, $args);
}

function start_search($wherematch='', $selectmatch='', $whereforums='', $havingmatch='', $args=array())
{
    pnModDBInfoLoad('Search');
    $pntable = pnDBGetTables();

    if (!empty($havingmatch)) {
        $havingmatch = "HAVING $havingmatch";
    }

    $sql = 'SELECT DISTINCT
              t.topic_title,
              t.topic_id,
              p.post_text,
              p.post_time
              '.$selectmatch.'
              FROM '.$pntable['dizkus_posts'].' AS p,
                   '.$pntable['dizkus_topics'].' AS t
              WHERE '.$wherematch.'
              AND p.topic_id=t.topic_id
              AND '.$whereforums.'
              GROUP BY t.topic_id
              '.$havingmatch;

    $res = DBUtil::executeSQL($sql);
    $colarray = array('topic_title', 'topic_id', 'post_text', 'post_time');
    $result    = DBUtil::marshallObjects($res, $colarray);

    if (is_array($result) && !empty($result)) {
        // Process the result set and insert into search result table
        $showtextinsearchresults = pnModGetVar('Dizkus', 'showtextinsearchresults', 'no');
        $sessionid = session_id();
        
        foreach($result as $resline) {
            $topictext = ($showtextinsearchresults == 'yes') ? DataUtil::formatForStore(str_replace('[addsig]', '', $resline['post_text'])) : '';
            $searchresult[] = array('title'   => $resline['topic_title'],
                                    'text'    => $topictext,
                                    'extra'   => serialize(array('searchwhere' => $args['searchwhere'], 'searchfor' => $args['q'], 'topic_id' => $resline['topic_id'])),
                                    'module'  => 'Dizkus',
                                    'created' => $resline['post_time'],
                                    'session' => $sessionid);
        }
        DBUtil::insertObjectArray($searchresult, 'search_result');
    }
            
    return true;
}
