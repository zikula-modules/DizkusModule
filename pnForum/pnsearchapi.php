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

include_once("modules/pnForum/common.php");

/**
 * Search plugin info
 **/
function pnForum_searchapi_info()
{
    $search_modules = array('title' => 'pnForum', 'type' => 'API');
    return $search_modules;
}

/**
 * Search form component
 **/
function pnForum_searchapi_options($args)
{
    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnr = new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('forums', pnModAPIFunc('pnForum', 'admin', 'readforums'));
    return $pnr->fetch('pnforum_search.html');
}

/**
 * Search form component
 **/
function pnForum_searchapi_internalsearchoptions($args)
{
    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('forums', pnModAPIFunc('pnForum', 'admin', 'readforums'));
    return $pnr->fetch('pnforum_user_search.html');
}

/**
 * Search plugin main function
 **/
function pnForum_searchapi_search($args)
{
    list($active_pnForum,
         $vars['searchfor'],
         $vars['bool'],
         $vars['forums'],
         $vars['author'],
         $vars['order'],
         $vars['limit'],
         $vars['startnum'],
         $internalsearch) = pnVarCleanFromInput('active_pnForum',
                                                'q',
                                                'bool',
                                                'pnForum_forum',
                                                'pnForum_author',
                                                'pnForum_order',
                                                'pnForum_limit',
                                                'pnForum_startnum',
                                                'internalsearch');
    if(empty($active_pnForum)) {
        return;
    }
    // check for valid input
    if(empty($vars['limit']) || ($vars['limit']<0) || ($vars['limit']>50)) {
        $vars['limit'] = 10;
    }
    if($vars['bool']<>'AND' && $vars['bool']<>'OR') {
        $vars['bool'] = 'AND';
    }
    if(!is_array($vars['forums']) || count($vars['forums'])== 0) {
        // set default
        $vars['forums'][0] = '';
    }

    if(empty($vars['order']) || ($vars['order']<>0 && $vars['order']<>1) ) {
        // set default
        $vars['order'] = 1;
    }

    if(empty($vars['startnum'])) {
        $vars['startnum'] = 0;
    }

    // check mod var for fulltext support
    $funcname = (pnModGetVar('pnForum', 'fulltextindex')==1) ? 'fulltext' : 'nonfulltext';
    list($searchresults,
         $total_hits) =  pnModAPIFunc('pnForum', 'search', $funcname, $vars);

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('total_hits', $total_hits);
    $pnr->assign('searchresults', $searchresults);
    $pnr->assign('searchfor',    $vars['searchfor']);
    $pnr->assign('searchbool',   $vars['bool']);
    $pnr->assign('searchauthor', $vars['author']);
    $pnr->assign('searchforums', $vars['forums']);
    $pnr->assign('searchorder',  $vars['order']);
    $pnr->assign('searchlimit',  $vars['limit']);
    $pnr->assign('searchstart',  $vars['startnum']);
    $template = (!empty($internalsearch)) ? 'pnforum_user_searchresults.html' : 'pnforum_searchresults.html';
    return $pnr->fetch($template);
}

/**
 * nonfulltext
 * the function that will search the forum
 *
 * THIS FUNCTION SHOULD NOT BE USED DIRECTLY, CALL pnForum_searchapi_search INSTEAD
 *
 *@private
 *
 *@params $args['searchfor']  string the search term
 *@params $args['bool']       string 'AND' or 'OR'
 *@params $args['forums']     array array of forum ids to search in
 *@params $args['author']     string search for postings of this author only
 *@params $args['order']      array array of order to display results
 *@params $args['startnum']   int number of entry to start showing when on page > 1
 *@params $args['limit']      int number of hits to show per page > 1
 *@returns array with search results
 */
function pnForum_searchapi_nonfulltext($args)
{
    extract($args);
    unset($args);

    if( empty($searchfor) && empty($author) ) {
        return showforumerror(_PNFORUM_SEARCHINCLUDE_MISSINGPARAMETERS, __FILE__, __LINE__);
    }

    if(!isset($limit) || empty($limit)) {
        $limit = 10;
    }

    list($dbconn, $pntable) = pnfOpenDB();

    // prepare searchresults array
    $searchresults = array();

    $query = "SELECT DISTINCT
              f.forum_id,
              f.forum_name,
              f.cat_id,
              c.cat_title,
              pt.post_text,
              pt.post_id,
              t.topic_id,
              t.topic_title,
              t.topic_replies,
              t.topic_views,
              p.poster_id,
              p.post_time
              FROM ".$pntable['pnforum_posts']." AS p,
                   ".$pntable['pnforum_forums']." AS f,
                   ".$pntable['pnforum_posts_text']." AS pt,
                   ".$pntable['pnforum_topics']." AS t,
                   ".$pntable['pnforum_categories']." AS c
              WHERE ";

    $searchfor = pnVarPrepForStore(trim($searchfor));
    if(!empty($searchfor)) {
        $flag = false;
        $words = explode(' ', $searchfor);
        $query .= '( ';
        foreach($words as $word) {
            if($flag) {
                switch($bool) {
                    case 'AND' :
                        $query .= ' AND ';
                        break;
                    case 'OR' :
                    default :
                        $query .= ' OR ';
                        break;
                }
            }
            // get post_text and match up forums/topics/posts
            $query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
            $flag = true;
        }
        $query .= ' ) AND ';
    } else {
        // searchfor is empty, we search by author only
    }
    $query .= "p.post_id=pt.post_id \n";
    $query .= "AND p.topic_id=t.topic_id \n";
    $query .= "AND p.forum_id=f.forum_id\n";
    $query .= "AND c.cat_id=f.cat_id\n";


    // get all forums the user is allowed to read
    $userforums = pnModAPIFunc('pnForum', 'user', 'readuserforums');
    if(!is_array($userforums) || count($userforums)==0) {
        // error or user is not allowed to read any forum at all
        // return empty result set without even doing a db access
        return(array($searchresults, 0));
    }
    // now create a very simle array of forum_ids only. we do not need
    // all the other stuff in the $userforums array entries
    $allowedforums = array();
    for($i=0; $i<count($userforums); $i++) {
        array_push($allowedforums, $userforums[$i]['forum_id']);
    }

    if($forums[0]== -1) {
        // search in all forums we are allowed to see
        $query .= ' AND f.forum_id IN (' . pnVarPrepForStore(implode($allowedforums, ',')) . ') ';
    } else {
        // filter out forums we are not allowed to read
        $forums2 = array();
        for($i=0;$i<count($forums); $i++) {
            if(in_array($forums[$i], $allowedforums)) {
                $forums2[] = $forums[$i];
            }
        }
        if(count($forums2)==0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return(array($searchresults, 0));
        }
        $query .= ' AND f.forum_id IN(' . pnVarPrepForStore(implode($forums2, ',')) . ') ';
    }

    // authors
    if($author) {
        $searchauthor = pnUserGetIDFromName($author);
        if ($searchauthor > 0){
            $query .= ' AND p.poster_id=' . pnVarPrepForStore($searchauthor);
        }
    }

    // Not sure this is needed and is not cross DB compat
    //$query .= ' GROUP BY pt.post_id ';

    switch($order) {
        case 2:
            $query .= ' ORDER BY t.topic_title';
            break;
        case 3:
            $query .= ' ORDER BY f.forum_name';
            break;
        case 1:
        default:
            $query .= ' ORDER BY pt.post_id DESC';
    }

    $result = pnfExecuteSQL($dbconn, $query, __FILE__, __LINE__);

    $total_hits = 0;
    $skip_hits = 0;
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            if( ($startnum > 0) && ($skip_hits < $startnum-1) ) {
                $skip_hits++;
            } else {
                $sresult = array();
                list($sresult['forum_id'],
                     $sresult['forum_name'],
                     $sresult['cat_id'],
                     $sresult['cat_title'],
                     $sresult['post_text'],
                     $sresult['post_id'],
                     $sresult['topic_id'],
                     $sresult['topic_title'],
                     $sresult['topic_replies'],
                     $sresult['topic_views'],
                     $sresult['poster_id'],
                     $sresult['post_time']) = $result->fields;
                // no auth check for forum and category needed here
                // timezone
                $sresult['posted_unixtime'] = strtotime ($sresult['post_time']);
                $sresult['posted_time'] = ml_ftime(_DATETIMEBRIEF, GetUserTime($sresult['posted_unixtime']));
                $sresult['topic_title'] = stripslashes($sresult['topic_title']);
                
                //without signature
                $sresult['post_text'] = eregi_replace("\[addsig]$", '', $sresult['post_text']);
                
                //strip_tags is needed here 'cause maybe we cut within a html-tag...
                $sresult['post_text'] = strip_tags($sresult['post_text']);
                
                // username
                $sresult['poster_name'] = pnUserGetVar('uname', $sresult['poster_id']);
                
                // check if we have to skip the first $startnum entries or not
                // check if we have a limit and wether we have reached it or not
                if( ( ($limit > 0) && (count($searchresults) < $limit) ) || ($limit==0) ) {
                    array_push($searchresults, $sresult);
                }
            }
            $total_hits++;
        }
    }

    pnfCloseDB($result);
    return array($searchresults, $total_hits);
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
 *@params $args['searchfor']  string the search term
 *@params $args['bool']       string 'AND' or 'OR'
 *@params $args['forums']     array array of forum ids to search in
 *@params $args['author']     string searhc for postings of this author only
 *@params $args['order']      int order to display results
 *@params $args['startnum']   int number of entry to start showing when on page > 1
 *@params $args['limit']      int number of hits to show per page > 1
 *@returns array with search results
 */
function pnForum_searchapi_fulltext($args)
{
    extract($args);
    unset($args);

    if( empty($searchfor) && empty($author) ) {
        return showforumerror(_PNFORUM_SEARCHINCLUDE_MISSINGPARAMETERS, __FILE__, __LINE__);
    }

    if(!isset($limit) || empty($limit)) {
        $limit = 10;
    }

    list($dbconn, $pntable) = pnfOpenDB();

    // prepare array for search results
    $searchresults = array();

    $searchfor = pnVarPrepForStore(trim($searchfor));
    // partial sql stored in $wherematch
    $wherematch = '';
    // selectmatch contains almost the same as wherematch without the last AND and
    // will be used in the SELECT part like ... selectmatch as score
    // to enable ordering the results by score
    $selectmatch = '';
    if(!empty($searchfor)) {

        if($bool == 'AND') {
            // AND
            $wherematch = "(MATCH pt.post_text AGAINST ('%$searchfor%') OR MATCH t.topic_title AGAINST ('%$searchfor%')) \n";
            $selectmatch = ", MATCH pt.post_text AGAINST ('%$searchfor%') as textscore, MATCH t.topic_title AGAINST ('%$searchfor%') as subjectscore \n";
        } else {
            // OR
            $flag = false;
            $words = explode(' ', $searchfor);
            $wherematch .= '( ';
            foreach($words as $word) {
                if($flag) {
                    $wherematch .= ' OR ';
                }
                $word = pnVarPrepForStore($word);
                // get post_text and match up forums/topics/posts
                //$query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
                $wherematch .= "(MATCH pt.post_text AGAINST ('%$word%') OR MATCH t.topic_title AGAINST ('%$word%')) \n";
                $flag = true;
            }
            $wherematch .= ' ) ';
        }
        $wherematch .= ' AND ';

        $flag = false;
        $words = explode(' ', $searchfor);
        $wherematch = '( ';
        foreach($words as $word) {
            if($flag==true) {
                switch(strtolower($bool)) {
                    case 'or':
                        $wherematch .= ' OR ';
                        break;
                    case 'and':
                    default:
                        $wherematch .= 'AND ';
                }
            }
            $word = pnVarPrepForStore($word);
            // get post_text and match up forums/topics/posts
            //$query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
            $wherematch .= "(MATCH pt.post_text AGAINST ('%$word%') OR MATCH t.topic_title AGAINST ('%$word%')) \n";
            $flag = true;
        }
        $wherematch .= ' ) AND ';

        $flag = false;
        $words = explode(' ', $searchfor);
        $query .= '( ';
        foreach($words as $word) {
            if($flag==true) {
                switch(strtolower($bool)) {
                    case 'or':
                        $query .= ' OR ';
                        break;
                    case 'and':
                    default:
                        $query .= 'AND ';
                }
            }
            $word = pnVarPrepForStore($word);
            // get post_text and match up forums/topics/posts
            //$query .= "(pt.post_text LIKE '%$word%' OR t.topic_title LIKE '%$word%') \n";
            $query .= "(MATCH pt.post_text AGAINST ('%$word%') OR MATCH t.topic_title AGAINST ('%$word%')) \n";
            $flag = true;
        }
        $query .= ' ) ';
        $query .= ' AND ';

    } else {
        // searchfor is empty, we search by author only
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
    // now create a very simle array of forum_ids only. we do not need
    // all the other stuff in the $userforums array entries
    $allowedforums = array();
    for($i=0; $i<count($userforums); $i++) {
        array_push($allowedforums, $userforums[$i]['forum_id']);
    }

    if($forums[0]== -1) {
        // search in all forums we are allowed to see
        $whereforums = ' AND f.forum_id IN (' . pnVarPrepForStore(implode($allowedforums, ',')) . ') ';
    } else {
        // filter out forums we are not allowed to read
        $forums2 = array();
        for($i=0;$i<count($forums); $i++) {
            if(in_array($forums[$i], $allowedforums)) {
                $forums2[] = $forums[$i];
            }
        }
        if(count($forums2)==0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return(array($searchresults, 0));
        }
        $whereforums .= ' AND f.forum_id IN(' . pnVarPrepForStore(implode($forums2, ',')) . ') ';
    }

    // authors with adodb
    // partial sql stored in $whereauthor
    $whereauthor = '';
    if($author) {
        $searchuid = pnUserGetIDFromName($author);
        if(is_numeric($searchuid)) {
            $whereauthor = " AND p.poster_id=$searchuid \n";
        }
    }

    // Not sure this is needed and is not cross DB compat
    //$query .= " GROUP BY pt.post_id ";

    switch($order) {
        case 2:
            $searchordersql = ' ORDER BY t.topic_title ';
            break;
        case 3:
            $searchordersql = ' ORDER BY f.forum_name ';
            break;
        case 4:
            if($selectmatch) {
                $searchordersql = ' ORDER BY textscore DESC, subjectscore DESC ';
                break;
            } // no selectmatch, we slip through to default
        case 1:
        default:
            $searchordersql = ' ORDER BY pt.post_id DESC ';
            break;
    }
    
    $query = "SELECT DISTINCT
              f.forum_id,
              f.forum_name,
              f.cat_id,
              c.cat_title,
              pt.post_text,
              pt.post_id,
              t.topic_id,
              t.topic_title,
              t.topic_replies,
              t.topic_views,
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
              $whereforums
              $whereauthor
              $searchordersql";
pnfdebug('start', $startnum);
pnfdebug('limit', $limit);
//    $result = pnfSelectLimit($dbconn, $query, $limit, $startnum, __FILE__, __LINE__, $debug=false);

    $result = pnfExecuteSQL($dbconn, $query, __FILE__, __LINE__);

    $total_hits = $result->RecordCount();
    $skip_hits = 0;
    if($total_hits > 0) {
        for (; !$result->EOF; $result->MoveNext()) {
            if( ($startnum > 0) && ($skip_hits < $startnum-1) ) {
                $skip_hits++;
            } else {
                $sresult = array();
                list($sresult['forum_id'],
                     $sresult['forum_name'],
                     $sresult['cat_id'],
                     $sresult['cat_title'],
                     $sresult['post_text'],
                     $sresult['post_id'],
                     $sresult['topic_id'],
                     $sresult['topic_title'],
                     $sresult['topic_replies'],
                     $sresult['topic_views'],
                     $sresult['poster_id'],
                     $sresult['post_time']) = $result->fields;
                // check if we have to skip the first $startnum entries or not
                // no further auth check needed, we are only searching forums we
                // are allowed to read
                // timezone
                $sresult['posted_unixtime'] = strtotime ($sresult['post_time']);
                $sresult['posted_time'] = ml_ftime(_DATETIMEBRIEF, GetUserTime($sresult['posted_unixtime']));
                $sresult['topic_title'] = stripslashes($sresult['topic_title']);
    
                //without signature
                $sresult['post_text'] = eregi_replace("\[addsig]$", '', $sresult['post_text']);
    
                //strip_tags is needed here 'cause maybe we cut within a html-tag...
                $sresult['post_text'] = strip_tags($sresult['post_text']);
    
                // username
                $sresult['poster_name'] = pnUserGetVar('uname', $sresult['poster_id']);
    
                // check if we have a limit and if we have reached it or not
                if( ( ($limit > 0) && (count($searchresults) < $limit) ) || ($limit==0) ) {
                    array_push($searchresults, $sresult);
                }
            }
        }
    }

    pnfCloseDB($result);
    return array($searchresults, $total_hits);
}


?>