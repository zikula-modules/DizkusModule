<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                               *
 ************************************************************************
 * Modified version of:                                                 *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License                                                              *
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
 * admin api functions
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
 * readcatgories
 * read the categories from database, if cat_id is set, only this one will be read
 *
 *@params $args['cat_id'] int the category id to read (optional)
 *@returns array of category information
 *
 */
function pnForum_adminapi_readcategories($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $categories = array();

    $cattable = $pntable['pnforum_categories'];
    $catcolumn = $pntable['pnforum_categories_column'];
    $where = "";
    if(isset($cat_id)) {
        $where .= "WHERE $catcolumn[cat_id]=$cat_id";
    }
    
    $sql = "SELECT $catcolumn[cat_id], $catcolumn[cat_title], $catcolumn[cat_order] 
            FROM $cattable
            $where 
            ORDER BY $catcolumn[cat_order]";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $category = array();
            list( $category['cat_id'],
                  $category['cat_title'],
                  $category['cat_order'] ) = $result->fields;
            array_push( $categories, $category );
        }
        usort($categories, 'cmp_catorder');
    }
    pnfCloseDB($result);
    if(isset($cat_id)) {
        return $categories[0];
    }
    return $categories;
}

/**
 * updatecategory
 * update a category in database
 
 *@params $args['cat_title'] string category title
 *@params $args['cat_id'] int category id
 */
function pnForum_adminapi_updatecategory($args)
{
    extract($args);
    unset($args);

    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__); 
    }
    
    if(isset($cat_title) && isset($cat_id)) {
        list($dbconn, $pntable) = pnfOpenDB();

        $cattable = $pntable['pnforum_categories'];
        $catcolumn = $pntable['pnforum_categories_column'];

        // prepare for db
        $cat_title = pnVarPrepForStore($cat_title);
        $cat_id = pnVarPrepForStore($cat_id);
        
        $sql = "UPDATE $cattable 
                SET $catcolumn[cat_title]= '$cat_title' 
                WHERE $catcolumn[cat_id] = '$cat_id'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);
        return true;
    }
    return false;
}

/** 
 * addcategory
 * adds a new category
 *
 *@params $args['cat_title'] string the categories title
 */
function pnForum_adminapi_addcategory($args)
{
    extract($args);
    unset($args);

    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__); 
    }
    
    if(isset($cat_title)) {
        list($dbconn, $pntable) = pnfOpenDB();

        $cattable = $pntable['pnforum_categories'];
        $catcolumn = $pntable['pnforum_categories_column'];
        
        $cat_title = pnVarPrepForStore($cat_title);
        $sql = "SELECT $catcolumn[cat_id] 
                FROM $cattable";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

        $numcats = $result->PO_RecordCount();
        pnfCloseDB($result);
        $neworder = $numcats + 1;
        $cat_id = $dbconn->GenID($cattable);
        $sql = "INSERT INTO $cattable ($catcolumn[cat_id], $catcolumn[cat_title], $catcolumn[cat_order]) 
                VALUES (" . pnVarPrepForStore($cat_id) . ", '$cat_title', " . pnVarPrepForStore($neworder) . ")";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);
        return true;
    }
    return false;
}

/**
 * delete a category
 * deletes a category from db including all forums and posts!
 *
 *@params $args['cat_id'] int the id of the category to delete
 *
 */
function pnForum_adminapi_deletecategory($args) 
{
    extract($args);
    unset($args);
    
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__); 
    }
    
    if(isset($cat_id)) {
        if(!pnModAPILoad('pnForum', 'user')) {
            return showforumerror("loading userapi failed", __FILE__, __LINE__);
        } 

        list($dbconn, $pntable) = pnfOpenDB();
    
        $cattable = $pntable['pnforum_categories'];
        $catcolumn = $pntable['pnforum_categories_column'];

        // read all the forums in this category
        $forums = pnForum_adminapi_readforums(array('cat_id' => $cat_id));
        if(is_array($forums) && count($forums)>0) { 
            foreach($forums as $forum) {
                // remove all forums in this category
                pnModAPIFunc('pnForum', 'user', 'deleteforum',
                             array('forum_id' => $forum['forum_id'],
                                   'ok'       => 1));
            }  //foreach forum
        }
        // now we can delete the category
        $sql = "DELETE FROM $cattable 
                WHERE $catcolumn[cat_id] = $cat_id";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);
        return true;
    }
    return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
}

/**
 * readforums
 * read the forums list and performs  permission for each depending on the permcheck parameter
 * default is ACCESS_READ. "nocheck" means, return the forums no matter if the user has sufficient
 * rights or not, in this ase the caling function has to take care of it!!
 *
 *@params $args['forum_id'] int only read this forum
 *@params $args['cat_id'] int read the forums in this category only
 *@params $args['permcheck'] string either "nocheck", "see", "read", "write", "moderate" or "admin", default is "read"
 *@returns array of forums or
 *         one forum in case of forum_id set
 */
function pnForum_adminapi_readforums($args)
{
    extract($args);
    unset($args);
    
    list($dbconn, $pntable) = pnfOpenDB();

    $permcheck = (isset($permcheck)) ? strtolower($permcheck): "read";
    if( !empty($permcheck) && 
        ($permcheck <> "see") &&
        ($permcheck <> "read") &&
        ($permcheck <> "write") &&
        ($permcheck <> "moderate") &&
        ($permcheck <> "admin") &&
        ($permcheck <> "nocheck")  ) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__); 
    }
    $where = "";
    if(isset($forum_id)) {
        $where = "WHERE f.forum_id=$forum_id ";
    } elseif (isset($cat_id)) {
        $where = "WHERE c.cat_id=$cat_id ";
    }
    $sql = "SELECT f.forum_name,
                   f.forum_id,
                   f.forum_desc,
                   f.forum_access,
                   f.forum_type,
                   f.forum_order,
                   f.forum_topics,
                   f.forum_posts,
                   c.cat_title, 
                   c.cat_id
            FROM ".$pntable['pnforum_forums']." AS f
            LEFT JOIN ".$pntable['pnforum_categories']." AS c
            ON c.cat_id=f.cat_id
            $where                  
            ORDER BY c.cat_order, f.forum_order, f.forum_name";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $forums = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $forum = array();
            list( $forum['forum_name'],
                  $forum['forum_id'],
                  $forum['forum_desc'],
                  $forum['forum_access'],
                  $forum['forum_type'],
                  $forum['forum_order'],
                  $forum['forum_topics'],
                  $forum['forum_posts'],
                  $forum['cat_title'],
                  $forum['cat_id'] ) = $result->fields;
            if( ( ($permcheck=="see") && allowedtoseecategoryandforum($forum['cat_id'], $forum['forum_id']) ) 
              ||( ($permcheck=="read") && allowedtoreadcategoryandforum($forum['cat_id'], $forum['forum_id']) ) 
              ||( ($permcheck=="write") && allowedtowritetocategoryandforum($forum['cat_id'], $forum['forum_id']) ) 
              ||( ($permcheck=="moderate") && allowedtomoderatecategoryandforum($forum['cat_id'], $forum['forum_id']) ) 
              ||( ($permcheck=="admin") && allowedtoadmincategoryandforum($forum['cat_id'], $forum['forum_id']) )
              ||  ($permcheck=="nocheck") ) {
                array_push( $forums, $forum );
            }
        }
    }
    pnfCloseDB($result);
    if(count($forums)>0) {
        if(isset($forum_id)) {
            return $forums[0];
        }
    }
    return $forums;
}

/** 
 * readmoderators
 *
 */
function pnForum_adminapi_readmoderators($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT u.pn_uname, u.pn_uid 
            FROM ".$pntable['users']." u, ".$pntable['pnforum_forum_mods']." f 
            WHERE f.forum_id = '".pnVarPrepForStore($forum_id)."' AND u.pn_uid = f.user_id";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $mods = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $mod = array();
            list( $mod['uname'],
                  $mod['uid'] ) = $result->fields;
            array_push( $mods, $mod );
        }
    }
    pnfCloseDB($result);
    return $mods;
}

/** 
 * readusers
 *
 */
function pnForum_adminapi_readusers($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT n.pn_uid, n.pn_uname 
            FROM ".$pntable['users']." AS n 
            left JOIN ".$pntable['pnforum_users']." AS u 
            ON u.user_id=n.pn_uid 
            WHERE n.pn_uid != 1 ";
            
    foreach($moderators as $mod) {
        $sql .= "AND n.pn_uid != '".pnVarPrepForStore($mod['uid'])."'";
    }
    $sql .= "ORDER BY pn_uname";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $users = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $user = array();
            list( $user['uid'],
                  $user['uname'] ) = $result->fields;
            array_push( $users, $user );
        }
    }
    pnfCloseDB($result);
    return $users;

}

/**
 * readranks
 *
 */
function pnForum_adminapi_readranks($args)
{
    extract($args);
    unset($args);

    // read images
    $handle = opendir(pnModGetVar('pnForum', 'url_ranks_images'));
    $filelist = array();
    while ($file = readdir($handle)) {
        if ($file != "." && $file != ".." && $file != "CVS") {
            $filelist[] = $file;
        }
    }
    asort($filelist);

    list($dbconn, $pntable) = pnfOpenDB();
    
    $rtable = $pntable['pnforum_ranks'];
    $rcol = $pntable['pnforum_ranks_column'];

    $sql = "SELECT $rcol[rank_id],
                   $rcol[rank_title],
                   $rcol[rank_min],
                   $rcol[rank_max],
                   $rcol[rank_special],
                   $rcol[rank_image],
                   $rcol[rank_style]
            FROM $rtable 
            WHERE $rcol[rank_special] = $ranktype ";
    if($ranktype==0) {
        $ql .= "ORDER BY $rcol[rank_min]";
    } else {
        $sql .="ORDER BY $rcol[rank_title]";
    }
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $ranks = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $rank = array();
            list( $rank['rank_id'],
                  $rank['rank_title'],
                  $rank['rank_min'],
                  $rank['rank_max'],
                  $rank['rank_special'],
                  $rank['rank_image'],
                  $rank['rank_style'] ) = $result->fields;
            array_push( $ranks, $rank );
        }
    }
    // add a dummy rank on top for new ranks
    $dummy = array();
    $dummy['rank_id']      = -1;
    $dummy['rank_title']   = "";
    $dummy['rank_min']     = 0;
    $dummy['rank_max']     = 0;
    $dummy['rank_special'] = 0;
    $dummy['rank_image']   = "onestar.gif";
    $dummy['rank_style']   = "";
    array_unshift($ranks, $dummy);
    
    pnfCloseDB($result);
    return array($filelist, $ranks);
}

/**
 * saverank
 *
 */
function pnForum_adminapi_saverank($args)
{
    extract($args);
    unset($args);
    
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__); 
    }
    
    list($dbconn, $pntable) = pnfOpenDB();

    // Prep for DB
    $title = pnVarPrepForStore($title);
    $min_posts = pnVarPrepForStore($min_posts);
    $max_posts = pnVarPrepForStore($max_posts);
    $image = pnVarPrepForStore($image);
    $rank_id = pnVarPrepForStore($rank_id);

    switch($actiontype) {
        case 'Add':
            $rank_id = $dbconn->GenID($pntable['pnforum_ranks']);
            $sql = "INSERT INTO ".$pntable['pnforum_ranks']." (rank_id, rank_title, rank_min, rank_max, rank_special, rank_image) VALUES ("
                   . pnVarPrepForStore($rank_id) . ", '$title', '$min_posts', '$max_posts', '$ranktype', '$image')";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
            break;
      case 'Update':
            if($ranktype==1)
            {
                $sql = "UPDATE ".$pntable['pnforum_ranks']." SET rank_title = '$title', rank_image = '$image' WHERE rank_id = '$rank_id'";
            }else{
                $sql = "UPDATE ".$pntable['pnforum_ranks']." SET rank_title = '$title', rank_max = '$max_posts', rank_min = '$min_posts', rank_image = '$image' WHERE rank_id = '$rank_id'";
            }
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
            break;
        
      case 'Delete':
            $sql = "DELETE FROM ".$pntable['pnforum_ranks']." WHERE rank_id = '$rank_id'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
    }
    return;
}

/**
 * readrankusers
 *
 */
function pnForum_adminapi_readrankusers($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();
    
    $sql = "SELECT  u.user_id, 
                    p.pn_uname, 
                    r.rank_id, 
                    r.rank_title, 
                    r.rank_image, 
                    r.rank_style 
              FROM ".$pntable['pnforum_ranks']." as r 
              LEFT JOIN ".$pntable['pnforum_users']." as u 
              ON r.rank_id=u.user_rank
              LEFT JOIN ".$pntable['users']." as p 
              ON p.pn_uid=u.user_id
              WHERE (r.rank_special=1) AND (u.user_id <>'') AND (p.pn_uname<>'')
              ORDER BY p.pn_uname";         
    
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $users = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $user = array();
            list( $user['user_id'],
                  $user['pn_uname'],
                  $user['rank_id'],
                  $user['rank_title'],
                  $user['rank_image'],
                  $user['rank_style'] ) = $result->fields;
            array_push( $users, $user );
        }
    }
    pnfCloseDB($result);
    return $users;
}

/**
 * readnorankusers
 */
function pnForum_adminapi_readnorankusers()
{
    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT u.user_id, p.pn_uname 
              FROM ".$pntable['pnforum_users']." as u 
              LEFT JOIN ".$pntable['users']." as p 
              ON p.pn_uid=u.user_id
              WHERE (u.user_rank=0) and (p.pn_uid<>1) and (u.user_id <> '')
              ORDER BY p.pn_uname";         
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    $users = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $user = array();
            list( $user['user_id'],
                  $user['pn_uname'] ) = $result->fields;
            array_push( $users, $user );
        }
    }
    pnfCloseDB($result);
    return $users;
}

/** 
 * assignranksave
 *
 */
function pnForum_adminapi_assignranksave($args)
{
    extract($args);
    unset($args);
    
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__); 
    }
    
    if(!is_numeric($rank_id) && !is_numeric($user_id) ) {
        return false;
    }
    
    list($dbconn, $pntable) = pnfOpenDB();

    $rank_id = (int)$rank_id;
    $user_id = (int)$user_id;
    
    switch($actiontype) 
    {
        // no difference between Add and Update here, redundant code removed
        case 'Add':
        case 'Update':
            $sql = "UPDATE ".$pntable['pnforum_users']."
                    SET user_rank='$rank_id' 
                    WHERE user_id = '$user_id'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
            break;
        case 'Delete':
            $sql = "UPDATE ".$pntable['pnforum_users']."
                    SET user_rank='0' 
                    WHERE user_id = '$user_id'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
            break;
    }
    return;
}

/**
 * reorder categories
 */
function pnForum_adminapi_reordercategoriessave($args)
{
    extract($args);
    unset($args);
    
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__); 
    }
    
    list($dbconn, $pntable) = pnfOpenDB();

    $cat_id    = (int)$cat_id;
    $cat_order = (int)$cat_order;

    if ($direction=='up') {
        if ($cat_order>1) {
            $order = $cat_order - 1;
            $sql = "UPDATE ".$pntable['pnforum_categories']." 
                    SET cat_order = '".pnVarPrepForStore($order)."' 
                    WHERE cat_id = '".pnVarPrepForStore($cat_id)."'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
            $sql = "UPDATE ".$pntable['pnforum_categories']." 
                    SET cat_order = '".pnVarPrepForStore($cat_order)."' 
                    WHERE cat_order = '".pnVarPrepForStore($order)."' 
                    AND cat_id <> '".pnVarPrepForStore($cat_id)."'";                        
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
        }
    } else {
        $sql = "SELECT cat_id 
                FROM ".$pntable['pnforum_categories']."";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        $numcategories = $result->PO_RecordCount();
        pnfCloseDB($result);
        if ($cat_order < $numcategories) {
            $newno = $cat_order + 1;
            $sql = "UPDATE ".$pntable['pnforum_categories']." 
                    SET cat_order = '".pnVarPrepForStore($newno)."' 
                    WHERE cat_id = '".pnVarPrepForStore($cat_id)."'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
            $sql = "UPDATE ".$pntable['pnforum_categories']." 
                    SET cat_order = '".pnVarPrepForStore($cat_order)."' 
                    WHERE cat_order = '".pnVarPrepForStore($newno)."' 
                    AND cat_id <> '".pnVarPrepForStore($cat_id)."'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
        }
    }
    return;
}

/**
 * reorder forums save
 *@params $args['neworder'] int the new order number
 *@params $args['oldorder'] int the old order number
 *@params $args['forum_id'] int the forum id
 *@params $args['cat_id'] int the category id
 */
function pnForum_adminapi_reorderforumssave($args)
{
    extract($args);
    unset($args);

    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__); 
    }

    if(!isset($neworder) || !isset($oldorder)){
        pnSessionSetVar('errormsg', _MODARGSERROR);
        return false;
    }

    list($dbconn, $pntable) = pnfOpenDB();

    $forumtable   = $pntable['pnforum_forums'];
    $forumcolumn  = &$pntable['pnforum_forums_column']; 

    $cat_id      = (int)$cat_id;
    $forum_id    = (int)$forum_id;
    $neworder    = (int)$neworder;
    $oldorder    = (int)$oldorder;

    if ((int)$oldorder > (int)$neworder) {
        if ($neworder < 0) {
            $neworder = 0;
        }
        if ($neworder == 0) {
            $sql = "SELECT $forumcolumn[forum_id],
                $forumcolumn[forum_order]
                    FROM $forumtable
                    WHERE $forumcolumn[forum_order] >= '" . (int)$oldorder . "'
                    AND $forumcolumn[cat_id]='" . (int)pnVarPrepForStore($cat_id) . "'
                    ORDER BY $forumcolumn[forum_order] DESC";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            while(list($forumid, $currentOrder) = $result->fields) {
                $sql2 = "UPDATE $forumtable
                    SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($currentOrder-1) . "'
                    WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forumid) . "'";
                $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                pnfCloseDB($result2);
                $result->MoveNext();
            }
            pnfCloseDB($result);
            $sql2 = "UPDATE $forumtable
                SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($neworder) . "'
                WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
            $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
            pnfCloseDB($result2);
        } else {
            $sql = "SELECT $forumcolumn[forum_id],
                $forumcolumn[forum_order]
                    FROM $forumtable
                    WHERE $forumcolumn[forum_order] >= '" . (int)$neworder . "'
                    AND $forumcolumn[forum_order] <= '" . (int)$oldorder . "'
                    AND $forumcolumn[cat_id]='" . (int)pnVarPrepForStore($cat_id) . "'
                    AND $forumcolumn[forum_order] != '0'
                    ORDER BY $forumcolumn[forum_order] DESC";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            if ($result->EOF) {
                $sql2 = "UPDATE $forumtable
                    SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($neworder) . "'
                    WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
                $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                pnfCloseDB($result2);
            } else {
                while(list($forum_id, $currentOrder) = $result->fields) {
                    if ($currentOrder == $oldorder) {
                        // we are dealing with the old value so make it the new value
                        $currentOrder = $neworder;
                    } else {
                        $currentOrder++;
                    }
                    $sql2 = "UPDATE $forumtable
                        SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($currentOrder) . "'
                        WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
                    $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                    pnfCloseDB($result2);
                    $result->MoveNext();
                }
            }
            pnfCloseDB($result);
        }
    } else {
        // new order > old order

        $sql = "SELECT max(forum_order) AS maxorder 
            FROM $forumtable 
            WHERE $forumcolumn[cat_id] = '" . (int)pnVarPrepForStore($cat_id) . "'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        list($maxorder) = $result->fields;
        pnfCloseDB($result);

        // The new sequence is lower in the list
        //if the new requested sequence is bigger than
        //the maximum sequence number then set it to
        //the maximum number.  We don't want any spaces
        //in the sequence.
        if ($neworder > ($maxorder+1)) {
            $neworder = ((int)$maxorder)+1;
        }

        if ($oldorder == 0) {
            $sql = "SELECT $forumcolumn[forum_id],
                $forumcolumn[forum_order]
                    FROM $forumtable
                    WHERE $forumcolumn[forum_order] >= '" . (int)$neworder . "'
                    AND $forumcolumn[cat_id]='" . (int)pnVarPrepForStore($cat_id) . "'
                    ORDER BY $forumcolumn[forum_order] DESC";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            while(list($forumid, $currentOrder) = $result->fields) {
                $sql2 = "UPDATE $forumtable
                    SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($currentOrder+1) . "'
                    WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forumid) . "'";
                $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                pnfCloseDB($result2);
                $result->MoveNext();
            }
            pnfCloseDB($result);
            $sql2 = "UPDATE $forumtable
                SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($neworder) . "'
                WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
            $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
            pnfCloseDB($result2);
        } else {
            $sql = "SELECT $forumcolumn[forum_id],
                $forumcolumn[forum_order]
                    FROM $forumtable
                    WHERE $forumcolumn[forum_order] >= '" . (int)$oldorder . "'
                    AND $forumcolumn[forum_order] <= '" . (int)$neworder . "'
                    AND $forumcolumn[cat_id]='" . (int)pnVarPrepForStore($cat_id) . "'
                    AND $forumcolumn[forum_order] != '0'
                    ORDER BY $forumcolumn[forum_order] ASC";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            if ($result->EOF) {
                $sql2 = "UPDATE $forumtable
                    SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($neworder) . "'
                    WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
                $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                pnfCloseDB($result2);
            } else {
                while(list($forum_id, $currentOrder) = $result->fields) {
                    if ($currentOrder == $oldorder) {
                        // we are dealing with the old value so make it the new value
                        $currentOrder = $neworder;
                    } else {
                        $currentOrder--;
                    }
                    $sql2 = "UPDATE $forumtable
                        SET $forumcolumn[forum_order] = '" . (int)pnVarPrepForStore($currentOrder) . "'
                        WHERE $forumcolumn[forum_id] = '" . (int)pnVarPrepForStore($forum_id) . "'";
                    $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                    pnfCloseDB($result2);
                    $result->MoveNext();
                }
            }
            pnfCloseDB($result);
        }

    }
    return;
}

/**
 * This function should receive $id, $type
 * synchronizes forums/topics/users
 */
function pnForum_adminapi_sync($args)
{
//$id, $type)
    extract($args);
    unset($args);
    
    list($dbconn, $pntable) = pnfOpenDB();

    switch($type) {
        case 'forum':
            $sql = "SELECT max(post_id) AS last_post 
                    FROM ".$pntable['pnforum_posts']." 
                    WHERE forum_id = ".(int)pnVarPrepForStore($id)."";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            $result_lastpost = $dbconn->Affected_Rows();
            pnfCloseDB($result);
            if ($result_lastpost != 0) {
                list($last_post) = $result->FetchRow();
            } else {
                $last_post = 0;
            }

            $sql = "SELECT count(post_id) AS total 
                    FROM ".$pntable['pnforum_posts']." 
                    WHERE forum_id = ".(int)pnVarPrepForStore($id)."";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            $row = $result->GetRowAssoc(false);
            pnfCloseDB($result);
            $total_posts = $row['total'];

            $sql = "SELECT count(topic_id) AS total 
                    FROM ".$pntable['pnforum_topics']." 
                    WHERE forum_id = ".(int)pnVarPrepForStore($id)."";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            $row = $result->GetRowAssoc(false);
            pnfCloseDB($result);
            $total_topics = $row["total"];
            $sql = "UPDATE ".$pntable['pnforum_forums']." 
                    SET forum_last_post_id = '".(int)pnVarPrepForStore($last_post)."', forum_posts = '".(int)pnVarPrepForStore($total_posts)."', forum_topics = '".pnVarPrepForStore($total_topics)."' 
                    WHERE forum_id = '".(int)pnVarPrepForStore($id)."'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
            break;

        case 'topic':
            $sql = "SELECT max(post_id) AS last_post 
                    FROM ".$pntable['pnforum_posts']." 
                    WHERE topic_id = '".(int)pnVarPrepForStore($id)."'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            $result_lastpost = $dbconn->Affected_Rows();
            pnfCloseDB($result);
            if ($result_lastpost != 0) {
                list($last_post) = $result->FetchRow();
            } else {
                $last_post = 0;
            }

            $sql = "SELECT count(post_id) AS total 
                    FROM ".$pntable['pnforum_posts']." 
                    WHERE topic_id = '".(int)pnVarPrepForStore($id)."'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            //$row = $result->GetRowAssoc(false);
            list($total_posts) = $result->FetchRow(); // $row["total"];
            pnfCloseDB($result);

            $total_posts -= 1;
            $sql = "UPDATE ".$pntable['pnforum_topics']." 
                    SET topic_replies = '".(int)pnVarPrepForStore($total_posts)."', topic_last_post_id = '".(int)pnVarPrepForStore($last_post)."' 
                    WHERE topic_id = '".(int)pnVarPrepForStore($id)."'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
            break;

    case 'all forums':
            $forums = pnForum_adminapi_readforums();
            foreach($forums as $forum) {
                pnForum_adminapi_sync(array('id' =>$forum['forum_id'], 'type' => "forum"));
            }
            break;
    case 'all topics':
            $sql = "SELECT topic_id 
                    FROM ".$pntable['pnforum_topics']."";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            if($result->RecordCount()>0) {
                for (; !$result->EOF; $result->MoveNext())
                {
                    list($topic_id) = $result->fields;
                    pnForum_adminapi_sync(array('id' =>$topic_id, 'type' => "topic"));
                }
            }
            pnfCloseDB($result);
            break;
    case 'all posts':
            $sql = "SELECT poster_id, count(poster_id) as total_posts 
                    FROM ".$pntable['pnforum_posts']."
                    GROUP BY poster_id";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            if($result->RecordCount()>0) {
                for (; !$result->EOF; $result->MoveNext()) {
                    list($poster_id,
                         $total_posts) = $result->fields;
                    $sub_sql = "UPDATE ".$pntable['pnforum_users']." 
                                SET user_posts = '".(int)pnVarPrepForStore($total_posts)."' 
                                WHERE user_id = '".(int)pnVarPrepForStore($poster_id)."'";
                    $result2 = pnfExecuteSQL($dbconn, $sub_sql, __FILE__, __LINE__);
                }
            }
            pnfCloseDB($result);
            break;
    case 'users':
            $sql = "SELECT n.pn_uid, 
                           b.* 
                    FROM ".$pntable['users']." AS n 
                    LEFT JOIN ".$pntable['pnforum_users']." AS b 
                    ON b.user_id=n.pn_uid 
                    WHERE b.user_id is NULL";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            if($result->RecordCount()>0) {

                for (; !$result->EOF; $result->MoveNext()) {
                    list($pn_uid) = $result->fields;
                    $sql2 = "INSERT into ".$pntable['pnforum_users']." (user_id) 
                             VALUES ('".(int)pnVarPrepForStore($pn_uid)."')";
                    $result2 = pnfExecuteSQL($dbconn, $sql2, __FILE__, __LINE__);
                } //$result->MoveNext();
            }
            pnfCloseDB($result);
            break;
    default:
            return showforumerror("wrong parameter in sync", __FILE__, __LINE__);
    }
    return true;
}

/**
 * addforum
 * Adds a new forum 
 *
 *@params $args['forum_name'] string the forums name 
 *@params $args['desc'] string the forum description 
 *@params $args['cat_id'] int the category where the forum shall be added 
 *@params $args['mods'] array of moderators
 *@params $args['forum_order'] int the forums order, optional  
 *@returns int the new forums id 
 *
 */
function pnForum_adminapi_addforum($args)
{
    extract($args);
    unset($args);
    if( !pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN) && 
        !pnSecAuthAction(0, 'pnForum::CreateForum', $cat_id . "::", ACCESS_EDIT) ) { 
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__); 
    }

    list($dbconn, $pntable) = pnfOpenDB();

    if(empty($forum_name)) {
        return showforumerror(_PNFORUM_CREATEFORUM_INCOMPLETE, __FILE__, __LINE__);
    }
    if (!$desc) {
        $desc = '';
    }
    $desc = nl2br($desc); // to be fixed ASAP
    $desc = pnVarPrepForStore($desc);
    $forum_name = pnVarPrepForStore($forum_name);
    $cat_id = pnVarPrepForStore($cat_id);
    $sql = "SELECT max(forum_order) AS highest 
            FROM ".$pntable['pnforum_forums']." 
            WHERE cat_id = '$cat_id'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    list($highest) = $result->fields;
    pnfCloseDB($result);
    $highest++;
    $forum_id = $dbconn->GenID($pntable['pnforum_forums']);
    $sql = "INSERT INTO ".$pntable['pnforum_forums']." 
                (forum_id,
                forum_name, 
                forum_desc, 
                cat_id, 
                forum_order) 
            VALUES ('".pnVarPrepForStore($forum_id)."',
                    '".$forum_name."', 
                    '".$desc."', 
                    '".$cat_id."', 
                    '".pnVarPrepForStore($highest)."')";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);
    $forum = $dbconn->PO_Insert_ID($pntable['pnforum_forums'], 'forum_id');
    $count = 0;
    if(is_array($mods) && count($mods)>0) {
        while(list($mod_number, $mod) = each($mods)) {
            $mod_query = "INSERT INTO ".$pntable['pnforum_forum_mods']." 
                                (forum_id, 
                                user_id) 
                            VALUES ('".pnVarPrepForStore($forum)."', 
                                    '".pnVarPrepForStore($mod)."')";
            $mod_res = pnfExecuteSQL($dbconn, $mod_query, __FILE__, __LINE__);
            pnfCloseDB($mod_res);
        }
    }
    if (isset($forum_order) && is_numeric($forum_order)) {
        pnModAPIFunc('pnForum', 'admin', 'reorderforumssave',
                array('cat_id'      => $cat_id,
                    'forum_id'    => $forum,
                    'neworder'    => $forum_order,
                    'oldorder'    => $highest));
    }
    return $forum;
}

/**
 * editforum
 */
function pnForum_adminapi_editforum($args)
{
// $forum_id, $forum_name, $desc, $cat_id, $mods, $rem_mods)
    extract($args);
    unset($args);
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__); 
    }

    list($dbconn, $pntable) = pnfOpenDB();

    // prepare for store
    $desc = pnVarPrepForStore($desc);
    $forum_name = pnVarPrepForStore($forum_name);
    $cat_id = pnVarPrepForStore($cat_id);
    $sql = "UPDATE ".$pntable['pnforum_forums']." 
            SET forum_name='".$forum_name."', 
            forum_desc='".$desc."', 
            cat_id='".$cat_id."' 
            WHERE forum_id=".$forum_id."";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);
    if(isset($mods) && !empty($mods)) {
        foreach ($mods as $mod) {
            $mod_query = "INSERT INTO ".$pntable['pnforum_forum_mods']." (forum_id, user_id) VALUES ('".pnVarPrepForStore($forum_id)."', '".pnVarPrepForStore($mod)."')";
            $mods = pnfExecuteSQL($dbconn, $mod_query, __FILE__, __LINE__);
            pnfCloseDB($mods);
        }
    }
    if(isset($rem_mods) && !empty($rem_mods)) {
        foreach ($rem_mods as $mod) {
            $rem_query = "DELETE FROM ".$pntable['pnforum_forum_mods']." 
                        WHERE forum_id = '".pnVarPrepForStore($forum_id)."' AND user_id = '".pnVarPrepForStore($mod)."'";
            $rem = pnfExecuteSQL($dbconn, $rem_query, __FILE__, __LINE__);
            pnfCloseDB($rem);
        }
    }
    return;
}

/**
 * delete forum
 *
 *@params $args['forum_id']
 *@params $args['ok']
 *
 */
function pnForum_adminapi_deleteforum($args)
{
// $forum_id, $ok
    extract($args);
    unset($args);

    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) { 
        return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__); 
    }

    list($dbconn, $pntable) = pnfOpenDB();
    
    if($ok==1) {
        // delet forum
        $sql = "DELETE FROM ".$pntable['pnforum_forums']." 
                WHERE forum_id = '".pnVarPrepForStore($forum_id)."'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);
        // delete mods
        $sql = "DELETE FROM ".$pntable['pnforum_forum_mods']." 
                WHERE forum_id = '".pnVarPrepForStore($forum_id)."'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);
        // delete forum subscription
        $sql = "DELETE FROM ".$pntable['pnforum_subscription']." 
                WHERE forum_id = '".pnVarPrepForStore($forum_id)."'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);

        // topics
        $sql = "SELECT topic_id 
                FROM ".$pntable['pnforum_topics']." 
                WHERE forum_id = '".pnVarPrepForStore($forum_id)."'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        if($result->RecordCount()>0) {
            for (; !$result->EOF; $result->MoveNext()) {
                list($topic_id) = $result->fields;
                $sql = "DELETE FROM ".$pntable['pnforum_topic_subscription']." 
                        WHERE topic_id = '".pnVarPrepForStore($topic_id)."'";
                $del = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
                pnfCloseDB($del);
                $sql = "DELETE FROM ".$pntable['pnforum_topics']." 
                        WHERE topic_id = '".pnVarPrepForStore($topic_id)."'";
                $del = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
                pnfCloseDB($del);
            }
        }
        pnfCloseDB($result);

        // posts
        $sql = "SELECT post_id 
                FROM ".$pntable['pnforum_posts']." 
                WHERE forum_id = '".pnVarPrepForStore($forum_id)."'";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        if($result->RecordCount()>0) {
            for (; !$result->EOF; $result->MoveNext()) {
                list($post_id) = $result->fields;
                $sql = "DELETE FROM ".$pntable['pnforum_posts_text']." 
                        WHERE post_id = '".pnVarPrepForStore($post_id)."'";
                $del = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
                pnfCloseDB($del);
                $sql = "DELETE FROM ".$pntable['pnforum_posts']." 
                        WHERE post_id = '".pnVarPrepForStore($post_id)."'";
                $del = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
                pnfCloseDB($del);
            }
        }
        pnfCloseDB($result);
    }
    return;
}
?>
