<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                            *
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
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

Loader::includeOnce("modules/pnForum/common.php");

/**
 * Search plugin info
 **/
function pnForum_searchapi_info()
{
    return array('title'     => 'pnForum',
                 'functions' => array('pnForum' => 'search'));
}

/**
 * Search form component
 **/
function pnForum_searchapi_options($args)
{
    if (SecurityUtil::checkPermission('pnforum::', '::', ACCESS_READ)) {
        $pnr = pnRender::getInstance('pnForum');
        $pnr->assign('active', (isset($args['active']) && isset($args['active']['pnForum'])) || !isset($args['active']));
        $pnr->assign('forums', pnModAPIFunc('pnForum', 'admin', 'readforums'));
        return $pnr->fetch('pnforum_search.html');
    }
    return '';
}

/**
 * Do last minute access checking and assign URL to items
 *
 * Access checking is ignored since access check has
 * already been done. But we do add a URL to the found user
 */
function pnForum_searchapi_search_check(&$args)
{
    $datarow = &$args['datarow'];
    $extra = unserialize($datarow['extra']);
    
    $datarow['url'] = pnModUrl('pnForum', 'user', 'viewtopic', array('topic' => $extra['topic_id']));
    return true;
}


/**
 * Search form component
 **/
function pnForum_searchapi_internalsearchoptions($args)
{
    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnr = pnRender::getInstance('pnForum', false, null, true);
    $pnr->assign('forums', pnModAPIFunc('pnForum', 'admin', 'readforums'));
    return $pnr->fetch('pnforum_user_search.html');
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
function pnForum_searchapi_search($args)
{
    if(!SecurityUtil::checkPermission('pnForum::', '::', ACCESS_READ)) {
        return false;
    }

    $args['forums']       = FormUtil::getPassedValue('pnForum_forum', null, 'POST');
    $args['searchwhere']  = FormUtil::getPassedValue('pnForum_searchwhere', 'post', 'POST');

    if(!is_array($args['forums']) || count($args['forums'])== 0) {
        // set default
        $args['forums'][0] = -1;
    }
    
    if($args['searchwhere'] <> 'post' && $args['searchwhere'] <> 'author') {
        $args['searchwhere'] = 'post';
    }

    // check mod var for fulltext support
    $funcname = (pnModGetVar('pnForum', 'fulltextindex', 0)==1) ? 'fulltext' : 'nonfulltext';
    pnModAPIFunc('pnForum', 'search', $funcname, $args);
    return true;
}

/**
 * nonfulltext
 * the function that will search the forum
 *
 * THIS FUNCTION SHOULD NOT BE USED DIRECTLY, CALL pnForum_searchapi_search INSTEAD
 *
 *@private
 *
 *@params q             string the text to search
 *@params searchtype    string 'AND', 'OR' or 'EXACT'
 *@params searchorder   string 'newest', 'oldest' or 'alphabetical' 
 *@params numlimit      int    limit for search, defaultsto 10
 *@params page          int    number of page t show
 *@params startnum      int    the first item to show
 * from pnForum:
 *@params searchwhere   string 'posts' or 'author'
 *@params forums        array of forums to dearch
 *@returns true or false
 */
function pnForum_searchapi_nonfulltext($args)
{
    if(!SecurityUtil::checkPermission('pnForum::', '::', ACCESS_READ)) {
        return false;
    }

    pnModDBInfoLoad('Search');
    $pntable      = pnDBGetTables();
    $searchtable  = $pntable['search_result'];
    $searchcolumn = $pntable['search_result_column'];

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
            $wherematch = '( ';
            foreach($words as $word) {
                if($flag) {
                    switch($bool) {
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
                $wherematch .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                $flag = true;
            }
            $wherematch .= ' ) AND ';
    }

    // get all forums the user is allowed to read
    $userforums = pnModAPIFunc('pnForum', 'user', 'readuserforums');
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
        $whereforums = ' AND f.forum_id IN (' . DataUtil::formatForStore(implode($allowedforums, ',')) . ') ';
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
        $whereforums = ' AND f.forum_id IN(' . DataUtil::formatForStore(implode($forums2, ',')) . ') ';
    }

    start_search($wherematch, $selectmatch, $whereforums, $args);
    return true;
}

/**
 * fulltext
 * the function that will search the forum using fulltext indices - does not work on
 * InnoDB databases!!!
 *
 * THIS FUNCTION SHOULD NOT BE USED DIRECTLY, CALL pnForum_searchapi_search INSTEAD
 *
 *@private
 *
 *@params q             string the text to search
 *@params searchtype    string 'AND', 'OR' or 'EXACT'
 *@params searchorder   string 'newest', 'oldest' or 'alphabetical' 
 *@params numlimit      int    limit for search, defaultsto 10
 *@params page          int    number of page t show
 *@params startnum      int    the first item to show
 * from pnForum:
 *@params searchwhere   string 'posts' or 'author'
 *@params forums        array of forums to dearch
 *@returns true or false
 */
function pnForum_searchapi_fulltext($args)
{
    if(!SecurityUtil::checkPermission('pnForum::', '::', ACCESS_READ)) {
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
            if($args['searchtype'] == 'AND') {
                // AND
                $wherematch = "(MATCH pt.post_text AGAINST ('%" . $args['q'] . "%') OR MATCH t.topic_title AGAINST ('%" . $args['q'] ."%')) \n";
                $selectmatch = ", MATCH pt.post_text AGAINST ('%" .$args['q'] . "%') as textscore, MATCH t.topic_title AGAINST ('%" . $args['q'] . "%') as subjectscore \n";
            } else {
                // OR
                $flag = false;
                $words = explode(' ', $args['q']);
                $wherematch .= '( ';
                foreach($words as $word) {
                    if($flag) {
                        $wherematch .= ' OR ';
                    }
                    $word = DataUtil::formatForStore($word);
                    // get post_text and match up forums/topics/posts
                    //$query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                    $wherematch .= "(MATCH pt.post_text AGAINST ('%$word%') OR MATCH t.topic_title AGAINST ('%$word%')) \n";
                    $flag = true;
                }
                $wherematch .= ' ) ';
            }
            $wherematch .= ' AND ';
            
            $flag = false;
            $words = explode(' ', $args['q']);
            $wherematch = '( ';
            foreach($words as $word) {
                if($flag==true) {
                    switch(strtolower($args['searchtype'])) {
                        case 'or':
                            $wherematch .= ' OR ';
                            break;
                        case 'and':
                        default:
                            $wherematch .= 'AND ';
                    }
                }
                $word = DataUtil::formatForStore($word);
                $wherematch .= "(MATCH pt.post_text AGAINST ('%$word%') OR MATCH t.topic_title AGAINST ('%$word%')) \n";
                $flag = true;
            }
            $wherematch .= ' ) AND ';
    }

    // check forums (multiple selection is possible!)
    // partial sql stored in $whereforums
    $whereforums = '';
    
    // get all forums the user is allowed to read
    $userforums = pnModAPIFunc('pnForum', 'user', 'readuserforums');
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
        $whereforums = ' AND f.forum_id IN (' . DataUtil::formatForStore(implode($allowedforums, ',')) . ') ';
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
        $whereforums .= ' AND f.forum_id IN(' . DataUtil::formatForStore(implode($forums2, ',')) . ') ';
    }
                 
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
              f.forum_id,
              f.forum_name,
              f.cat_id,
              c.cat_title,
              pt.post_text,
              pt.post_id,
              t.topic_id,
              t.topic_title,
              t.topic_poster,
              t.topic_replies,
              t.topic_views,
              t.topic_status,
              t.topic_last_post_id,
              p.poster_id,
              p.post_time
              $selectmatch
              FROM ".$pntable['pnforum_posts']." AS p,
                   ".$pntable['pnforum_forums']." AS f,
                   ".$pntable['pnforum_posts_text']." AS pt,
                   ".$pntable['pnforum_topics']." AS t,
                   ".$pntable['pnforum_categories']." AS c
              WHERE
              $wherematch
              p.post_id=pt.post_id
              AND p.topic_id=t.topic_id
              AND p.forum_id=f.forum_id
              AND c.cat_id=f.cat_id
              $whereforums";

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
    for (; !$result->EOF; $result->MoveNext()) {
        $topic = $result->GetRowAssoc(2);
        $sql = $insertSql . '('
               . '\'' . DataUtil::formatForStore($topic['topic_title']) . '\', '
               . '\'' . DataUtil::formatForStore(str_replace('[addsig]', '', $topic['post_text'])) . '\', '
               . '\'' . DataUtil::formatForStore(serialize(array('searchwhere' => $args['searchwhere'], 'searchfor' => $args['q'], 'topic_id' => $topic['topic_id']))) . '\', '
               . '\'' . 'pnForum' . '\', '
               . '\'' . DataUtil::formatForStore($topic['post_time']) . '\', '
               . '\'' . DataUtil::formatForStore($sessionId) . '\')';
        $insertResult = DBUtil::executeSQL($sql);
        if (!$insertResult) {
            return LogUtil::registerError (_GETFAILED);
        }
    }
    return true;
}
