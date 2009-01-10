<?php
/************************************************************************
 * Dizkus - The Zikula forum                                            *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the Dizkus Module Development Team        *
 * http://www.dizkus.com/                                               *
 ************************************************************************
 * Modified version of: *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License *
 ************************************************************************
 * This program is free software; you can redistribute it and/or modify *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation; either version 2 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * This program is distributed in the hope that it will be useful,      *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with this program; if not, write to the Free Software          *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 *
 * USA                                                                  *
 ************************************************************************
 *
 * search functions
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2004 by Frank Schummertz
 * @package Dizkus
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.dizkus.com
 *
 ***********************************************************************/

Loader::includeOnce("modules/Dizkus/common.php");

/**
 * Search plugin info
 **/
function Dizkus_searchapi_info()
{
    return array('title'     => 'Dizkus',
                 'functions' => array('Dizkus' => 'search'));
}

/**
 * Search form component
 **/
function Dizkus_searchapi_options($args)
{
    if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('active', (isset($args['active']) && isset($args['active']['Dizkus'])) || !isset($args['active']));
        $pnr->assign('forums', pnModAPIFunc('Dizkus', 'admin', 'readforums'));
        return $pnr->fetch('dizkus_search.html');
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
    $args['datarow']['url'] = pnModUrl('Dizkus', 'user', 'viewtopic', array('topic' => $args['datarow']['extra']));
    return true;
}


/**
 * Search form component
 **/
function Dizkus_searchapi_internalsearchoptions($args)
{
    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign('forums', pnModAPIFunc('Dizkus', 'admin', 'readforums'));
    return $pnr->fetch('dizkus_user_search.html');
}

/**
 * Search plugin main function
 *
 *@params q             string the text to search
 *@params searchtype    string 'AND', 'OR' or 'EXACT'
 *@params searchorder   string 'newest', 'oldest' or 'alphabetical' 
 *@params numlimit      int    limit for search, defaultsto 10
 *@params page          int    number of page t show
 *@params startnum      int    the first item to show
 *
 **/
function Dizkus_searchapi_search($args)
{
    if(!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
        return false;
    }

    $args['forums']       = FormUtil::getPassedValue('Dizkus_forum', null, 'GETPOST');
    $args['searchwhere']  = FormUtil::getPassedValue('Dizkus_searchwhere', 'post', 'GETPOST');

    $minlen = pnModGetVar('Dizkus', 'minsearchlength', 3);
    $maxlen = pnModGetVar('Dizkus', 'maxsearchlength', 30);
    if (strlen($args['q']) < $minlen || strlen($args['q']) > $maxlen) {
        return LogUtil::registerStatus(pnML('_DZK_SEARCHLENGTHHINT', array('minlen' => $minlen, 'maxlen' => $maxlen)));
    }
    if(!is_array($args['forums']) || count($args['forums'])== 0) {
        // set default
        $args['forums'][0] = -1;
    }
    
    if($args['searchwhere'] <> 'post' && $args['searchwhere'] <> 'author') {
        $args['searchwhere'] = 'post';
    }

    // check mod var for fulltext support
    $funcname = (pnModGetVar('Dizkus', 'fulltextindex', 'no')=='yes') ? 'fulltext' : 'nonfulltext';
    pnModAPIFunc('Dizkus', 'search', $funcname, $args);
    return true;
}

/**
 * nonfulltext
 * the function that will search the forum
 *
 * THIS FUNCTION SHOULD NOT BE USED DIRECTLY, CALL Dizkus_searchapi_search INSTEAD
 *
 *@private
 *
 *@params q             string the text to search
 *@params searchtype    string 'AND', 'OR' or 'EXACT'
 *@params searchorder   string 'newest', 'oldest' or 'alphabetical' 
 *@params numlimit      int    limit for search, defaultsto 10
 *@params page          int    number of page t show
 *@params startnum      int    the first item to show
 * from Dizkus:
 *@params searchwhere   string 'posts' or 'author'
 *@params forums        array of forums to dearch
 *@returns true or false
 */
function Dizkus_searchapi_nonfulltext($args)
{
    if(!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
        return false;
    }

    switch ($args['searchwhere']) {
        case 'author':
            // searchfor is empty, we search by author only (done later on)
            $searchauthor = pnUserGetIDFromName($args['q']);
            if ($searchauthor > 0){
                $wherematch = ' p.poster_id=' . DataUtil::formatForStore($searchauthor) . ' AND ';
            } else {
                return false;
            }
            break;
        case 'post':  
        default:
            $flag = false;
            $words = explode(' ', $args['q']);
            if(strtoupper($args['searchtype'])=='EXACT') {
                $q = DataUtil::formatForStore($args['q']);
                $wherematch .= "(pt.post_text LIKE '%$q%' OR t.topic_title LIKE '%$q%') \n";
            } else {
                $wherematch = '( ';
                foreach($words as $word) {
                    if($flag) {
                        switch(strtoupper($args['searchtype'])) {
                            case 'AND' :
                                $wherematch .= ' AND ';
                                break;
                            case 'OR' :
                            default :
                                $wherematch .= ' OR ';
                                break;
                        }
                    }
                    // get post_text and match up forums/topics/posts
                    $word = DataUtil::formatForStore($word);
                    $wherematch .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                    $flag = true;
                }
                $wherematch .= ' )';
            }
    }

    // get all forums the user is allowed to read
    $userforums = pnModAPIFunc('Dizkus', 'user', 'readuserforums');
    if(!is_array($userforums) || count($userforums)==0) {
        // error or user is not allowed to read any forum at all
        // return empty result set without even doing a db access
        return(array($searchresults, 0));
    }
    // now create a very simple array of forum_ids only. we do not need
    // all the other stuff in the $userforums array entries
    $allowedforums = array();
    for($i=0; $i<count($userforums); $i++) {
        array_push($allowedforums, $userforums[$i]['forum_id']);
    }

    if((!is_array($args['forums']) && $args['forums'] == -1) || $args['forums'][0]==-1) {
        // search in all forums we are allowed to see
        $whereforums = 'f.forum_id IN (' . DataUtil::formatForStore(implode($allowedforums, ',')) . ') ';
    } else {
        // filter out forums we are not allowed to read
        $forums2 = array();
        for($i=0;$i<count($args['forums']); $i++) {
            if(in_array($args['forums'][$i], $allowedforums)) {
                $forums2[] = $args['forums'][$i];
            }
        }
        if(count($forums2)==0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return(array($searchresults, 0));
        }
        $whereforums = 'f.forum_id IN(' . DataUtil::formatForStore(implode($forums2, ',')) . ') ';
    }

    // sorting not necessary, this is done when reading the serch_result table

    start_search($wherematch, $selectmatch, $whereforums, $args);
    return true;
}

/**
 * fulltext
 * the function that will search the forum using fulltext indices - does not work on
 * InnoDB databases!!!
 *
 * THIS FUNCTION SHOULD NOT BE USED DIRECTLY, CALL Dizkus_searchapi_search INSTEAD
 *
 *@private
 *
 *@params q             string the text to search
 *@params searchtype    string 'AND', 'OR' or 'EXACT'
 *@params searchorder   string 'newest', 'oldest' or 'alphabetical' 
 *@params numlimit      int    limit for search, defaultsto 10
 *@params page          int    number of page t show
 *@params startnum      int    the first item to show
 * from Dizkus:
 *@params searchwhere   string 'posts' or 'author'
 *@params forums        array of forums to dearch
 *@returns true or false
 */
function Dizkus_searchapi_fulltext($args)
{
    if(!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
        return false;
    }

    // partial sql stored in $wherematch
    $wherematch = '';
    // selectmatch contains almost the same as wherematch without the last AND and
    // will be used in the SELECT part like ... selectmatch as score
    // to enable ordering the results by score
    $selectmatch = '';
    switch ($args['searchwhere']) {
        case 'author':
            // we search by author only
            $searchauthor = pnUserGetIDFromName($args['q']);
            if ($searchauthor > 0){
                $wherematch = ' p.poster_id=' . DataUtil::formatForStore($searchauthor) . ' AND ';
            } else {
                return false;
            }
            break;
        case 'post':
        default:
            $searchtype = strtoupper($args['searchtype']);
            if ($searchtype == 'EXACT') {
                $q = DataUtil::formatForStore($args['q']);
                $wherematch = "(MATCH pt.post_text AGAINST ('%" . $q . "%') OR MATCH t.topic_title AGAINST ('%" . $q ."%')) \n";
                $selectmatch = ", MATCH pt.post_text AGAINST ('%" .$q . "%') as textscore, MATCH t.topic_title AGAINST ('%" . $q . "%') as subjectscore \n";
            } else {
                $flag = false;
                $words = explode(' ', $args['q']);
                $wherematch .= '( ';
                foreach($words as $word) {
                    if($flag) {
                        if ($searchtype == 'OR') {
                            $wherematch .= ' OR ';
                        } else {
                            $wherematch .= ' AND ';
                        }
                    }
                    $word = DataUtil::formatForStore($word);
                    // get post_text and match up forums/topics/posts
                    //$query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                    $wherematch .= "(MATCH pt.post_text AGAINST ('%$word%') OR MATCH t.topic_title AGAINST ('%$word%')) \n";
                    $selectmatch = ", MATCH pt.post_text AGAINST ('%$word%') as textscore, MATCH t.topic_title AGAINST ('%$word%') as subjectscore \n";
                    $flag = true;
                }
                $wherematch .= ' ) ';
            }
    }

    // check forums (multiple selection is possible!)
    // partial sql stored in $whereforums
    $whereforums = '';
    
    // get all forums the user is allowed to read
    $userforums = pnModAPIFunc('Dizkus', 'user', 'readuserforums');
    if(!is_array($userforums) || count($userforums)==0) {
        // error or user is not allowed to read any forum at all
        // return empty result set without even doing a db access
        return false;
    }
    // now create a very simple array of forum_ids only. we do not need
    // all the other stuff in the $userforums array entries
    $allowedforums = array();
    for($i=0; $i<count($userforums); $i++) {
        array_push($allowedforums, $userforums[$i]['forum_id']);
    }

    if((!is_array($args['forums']) && $args['forums'] == -1) || $args['forums'][0]==-1) {
        // search in all forums we are allowed to see
        $whereforums = 'f.forum_id IN (' . DataUtil::formatForStore(implode($allowedforums, ',')) . ') ';
    } else {
        // filter out forums we are not allowed to read
        $forums2 = array();
        for($i=0;$i<count($args['forums']); $i++) {
            if(in_array($args['forums'][$i], $allowedforums)) {
                $forums2[] = $args['forums'][$i];
            }
        }
        if(count($forums2)==0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return false;
        }
        $whereforums .= 'f.forum_id IN(' . DataUtil::formatForStore(implode($forums2, ',')) . ') ';
    }
    
    // sorting not necessary, this is done when reading the serch_result table

    start_search($wherematch, $selectmatch, $whereforums, $args);
    return true;
}

function start_search($wherematch='', $selectmatch='', $whereforums='', $args)
{
    pnModDBInfoLoad('Search');
    $pntable      = pnDBGetTables();
    $searchtable  = $pntable['search_result'];
    $searchcolumn = $pntable['search_result_column'];

    $query = "SELECT DISTINCT
              t.topic_id,
              t.topic_title,
              pt.post_text,
              p.post_time
              $selectmatch
              FROM ".$pntable['dizkus_posts']." AS p,
                   ".$pntable['dizkus_forums']." AS f,
                   ".$pntable['dizkus_posts_text']." AS pt,
                   ".$pntable['dizkus_topics']." AS t
              WHERE $wherematch
              AND p.post_id=pt.post_id
              AND p.topic_id=t.topic_id
              AND p.forum_id=f.forum_id
              AND $whereforums
              GROUP BY t.topic_id";

    $result = DBUtil::executeSQL($query);
    if (!$result) {
        return LogUtil::registerError (_GETFAILED);
    }

    $sessionId = session_id();

    $insertSql = 'INSERT INTO ' . $searchtable . '('
                . $searchcolumn['title'] . ','
                . $searchcolumn['text'] . ','
                . $searchcolumn['extra'] . ','
                . $searchcolumn['module'] . ','
                . $searchcolumn['created'] . ','
                . $searchcolumn['session']
                . ') VALUES ';

    // Process the result set and insert into search result table
    $showtextinsearchresults = pnModGetVar('Dizkus', 'showtextinsearchresults', 'no');
    for (; !$result->EOF; $result->MoveNext()) {
        $topic = $result->GetRowAssoc(2);
        $topictext = ($showtextinsearchresults == 'yes') ? DataUtil::formatForStore(str_replace('[addsig]', '', $topic['post_text'])) : '';
        $sql = $insertSql . '('
               . '\'' . DataUtil::formatForStore($topic['topic_title']) . '\', '
               . '\'' . $topictext . '\', '
               . '\'' . DataUtil::formatForStore($topic['topic_id']) . '\', '
               . '\'Dizkus\', '
               . '\'' . DataUtil::formatForStore($topic['post_time']) . '\', '
               . '\'' . DataUtil::formatForStore($sessionId) . '\')';
        $insertResult = DBUtil::executeSQL($sql);
        if (!$insertResult) {
            return LogUtil::registerError (_GETFAILED);
        }
    }
    return true;
}
