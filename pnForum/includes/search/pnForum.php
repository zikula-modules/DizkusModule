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
 * search include
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2004 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html> 
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

include_once("modules/pnForum/common.php");

$search_modules[] = array(
    'title' => 'pnForum',
    'func_search' => 'search_pnForum',
    'func_opt' => 'search_pnForum_opt'
);

function search_pnForum_opt($vars) 
{
    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 
    $forums = pnModAPIFunc('pnForum', 'admin', 'readforums');

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->assign('forums', $forums);
    return $pnr->fetch('pnforum_search.html'); 
}


function search_pnForum($vars) 
{
    if(!isset($vars['active_pnForum'])) {
        return;
    }
    
    // just forloading the language defines, this makes a separate lang file for the 
    // search include obsolete
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
    } 

    pnModDBInfoLoad('pnForum');
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

    $w = search_split_query($vars['q']);
    $flag = false;
        
    $query = "SELECT
              f.forum_id,
              f.cat_id,
              p.forum_id,
              pt.post_text,
              pt.post_id,
              t.forum_id,
              t.topic_id,
              t.topic_title,
              t.topic_replies,
              t.topic_views,
              c.cat_title,
              f.forum_name,
              p.poster_id,
              p.post_time 
              FROM ".$pntable['pnforum_posts']." AS p, 
                   ".$pntable['pnforum_forums']." AS f,
                   ".$pntable['pnforum_posts_text']." AS pt, 
                   ".$pntable['pnforum_topics']." AS t,
                   ".$pntable['pnforum_categories']." AS c
              WHERE ";
            
    // words
    foreach($w as $word) {
        if($flag) {
            switch($vars['bool']) {
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
        //$query .= '(';
        $query .= "(pt.post_text LIKE '$word' OR t.topic_title LIKE '$word') \n";
        $query .= "AND p.post_id=pt.post_id \n";
        $query .= "AND p.topic_id=t.topic_id \n";
        $query .= "AND p.forum_id=f.forum_id\n";
        $query .= "AND c.cat_id=f.cat_id\n";
        //$query .= ')';
        $flag = true;
        
        //check forums (multiple selection is possible!)
        if($vars['pnForum_forum'][0]) {
            $query .= " AND (";
            $flag = false;
            foreach($vars['pnForum_forum'] as $w) {
                if($flag) {
                    $query .= " OR ";
                }
                $query .= "f.forum_id=$w";
                $flag = true;
            }
            $query .= ") ";
        }
    
        // authors with adodb
        if($vars['pnForum_author']) {
            $search_username = addslashes($vars['pnForum_author']);
            $result= $dbconn->SelectLimit("SELECT pn_uid FROM $pntable[users] WHERE pn_uname = '$search_username'", 1);
            $row = $result->GetRowAssoc(false);
            $author = $row['pn_uid'];
            if ($author > 0){
                $query .= " AND p.poster_id=$author \n";
            } else {
                $query .= " AND p.poster_id=0 \n";
            }
        }
    }

    // Not sure this is needed and is not cross DB compat
    //$query .= " GROUP BY pt.post_id ";
    
    $order = $vars['pnForum_order']['0'];

    if ($order == 1){
        $query .= " ORDER BY pt.post_id DESC";
    }
    if ($order == 2){
        $query .= " ORDER BY t.topic_title";
    }
    if ($order == 3){
        $query .= " ORDER BY f.forum_name";
    }
    $result = $dbconn->Execute($query);

    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), __FILE__, __LINE__);
    }

    $total_hits = $result->PO_RecordCount();
    $searchresults = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            $sresult = array();
            list($sresult['forum_id'],
                 $sresult['cat_id'],
                 $sresult['forum_id'],
                 $sresult['post_text'],
                 $sresult['post_id'],
                 $sresult['forum_id'],
                 $sresult['topic_id'],
                 $sresult['topic_title'],
                 $sresult['topic_replies'],
                 $sresult['topic_views'],
                 $sresult['cat_title'],
                 $sresult['forum_name'],
                 $sresult['poster_id'],
                 $sresult['post_time']) = $result->fields;
            if (pnSecAuthAction(0, 'pnForum::Forum', $sresult['forum_name']."::", ACCESS_READ) && pnSecAuthAction(0, 'pnForum::Category', $sresult['cat_title']."::", ACCESS_READ))     {
                //auth check for forum an category before displaying search result
    
                // timezone
                $sresult['posted_unixtime'] = strtotime ($sresult['post_time']);
                $sresult['posted_time'] = ml_ftime(_DATETIMEBRIEF, GetUserTime($sresult['posted_unixtime']));
                $sresult['topic_title'] = stripslashes($sresult['topic_title']);
                
                //without signature
                $sresult['post_text'] = eregi_replace("\[addsig]$", "", $sresult['post_text']);
                
                //strip_tags is needed here 'cause maybe we cut within a html-tag...
                $sresult['post_text'] = strip_tags($sresult['post_text']);

                // username
                $sresult['poster_name'] = pnUserGetVar('uname', $sresult['poster_id']);
                
                array_push($searchresults, $sresult);
            }
        }
    }

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->assign('total_hits', $total_hits);
    $pnr->assign('searchresults', $searchresults);
    return $pnr->fetch('pnforum_searchresults.html');
}
?>
