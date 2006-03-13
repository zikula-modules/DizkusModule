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
        $where .= "WHERE $catcolumn[cat_id]=" . pnVarPrepForStore($cat_id) . " ";
    }

    $sql = "SELECT $catcolumn[cat_id], $catcolumn[cat_title], $catcolumn[cat_order]
            FROM $cattable
            $where
            ORDER BY $catcolumn[cat_order]";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            $category = array();
            list( $category['cat_id'],
                  $category['cat_title'],
                  $category['cat_order'] ) = $result->fields;
            if(isset($cat_id)) {
                return $category;
            }
            array_push( $categories, $category );
        }
        usort($categories, 'cmp_catorder');
    }
    pnfCloseDB($result);
    if(isset($cat_id)) {
        return $categories[0];
    }

    // we now check the cat_order field in each category entry. Each
    // cat_order may only appear once there. If we find it more than once, we will adjust
    // all following cat_orders by incrementing them by 1
    // the fact that is array is sorted by cat_order simplifies this :-)
    $last_cat_order = 0;   // for comparison
    $cat_order_adjust = 0; // holds the number of shifts we have to do
    for($i=0; $i<count($categories); $i++) {
        // we leave cat_order = 0 untouched!
        if($cat_order_adjust>0) {
            // we have done at least one change before which means that all foloowing categories
            // have to be changed too.
            $categories[$i]['cat_order'] = $categories[$i]['cat_order'] + $cat_order_adjust;
            // update db immediately
            $sql = "UPDATE $cattable
                    SET $catcolumn[cat_order]= '" . pnVarPrepForStore($categories[$i]['cat_order']) . "'
                    WHERE $catcolumn[cat_id] = '" . pnVarPrepForStore($categories[$i]['cat_id']) . "'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
        } else if($categories[$i]['cat_order'] == $last_cat_order ) {
            $cat_order_adjust++;
            $categories[$i]['cat_order'] = $categories[$i]['cat_order'] + $cat_order_adjust;
            $sql = "UPDATE $cattable
                    SET $catcolumn[cat_order]= '" . pnVarPrepForStore($categories[$i]['cat_order']) . "'
                    WHERE $catcolumn[cat_id] = '" . pnVarPrepForStore($categories[$i]['cat_id']) . "'";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
        }
        $last_cat_order = $categories[$i]['cat_order'];
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
        $new_cat_id = $dbconn->PO_Insert_ID($cattable, 'cat_id');
        pnfCloseDB($result);
        return $new_cat_id;   // true;
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
        list($dbconn, $pntable) = pnfOpenDB();

        $cattable = $pntable['pnforum_categories'];
        $catcolumn = $pntable['pnforum_categories_column'];

        // read all the forums in this category
        $forums = pnForum_adminapi_readforums(array('cat_id' => $cat_id));
        if(is_array($forums) && count($forums)>0) {
            foreach($forums as $forum) {
                // remove all forums in this category
                pnModAPIFunc('pnForum', 'admin', 'deleteforum',
                             array('forum_id' => $forum['forum_id'],
                                   'ok'       => 1));
            }  //foreach forum
        }
        // now we can delete the category
        $sql = "DELETE FROM $cattable
                WHERE $catcolumn[cat_id] = " . pnVarPrepForStore($cat_id) . " ";
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
 * rights or not, in this case the calling function has to take care of it!!
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

    $permcheck = (isset($permcheck)) ? strtolower($permcheck): 'read';
    if( !empty($permcheck) &&
        ($permcheck <> 'see') &&
        ($permcheck <> 'read') &&
        ($permcheck <> 'write') &&
        ($permcheck <> 'moderate') &&
        ($permcheck <> 'admin') &&
        ($permcheck <> 'nocheck')  ) {
        return showforumerror(_MODARGSERROR, __FILE__, __LINE__);
    }
    $where = "";
    if(isset($forum_id)) {
        $where = "WHERE f.forum_id=" . pnVarPrepForStore($forum_id) ." ";
    } elseif (isset($cat_id)) {
        $where = "WHERE c.cat_id=" . pnVarPrepForStore($cat_id) . " ";
    }
    $sql = "SELECT f.forum_name,
                   f.forum_id,
                   f.forum_desc,
                   f.forum_access,
                   f.forum_type,
                   f.forum_order,
                   f.forum_topics,
                   f.forum_posts,
                   f.forum_pop3_active,
                   f.forum_pop3_server,
                   f.forum_pop3_port,
                   f.forum_pop3_login,
                   f.forum_pop3_password,
                   f.forum_pop3_interval,
                   f.forum_pop3_lastconnect,
                   f.forum_pop3_matchstring,
                   f.forum_pop3_pnuser,
                   f.forum_pop3_pnpassword,
                   f.forum_moduleref,
                   f.forum_pntopic,
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
                  $forum['pop3_active'],
                  $forum['pop3_server'],
                  $forum['pop3_port'],
                  $forum['pop3_login'],
                  $forum['pop3_password'],
                  $forum['pop3_interval'],
                  $forum['pop3_lastconnect'],
                  $forum['pop3_matchstring'],
                  $forum['pop3_pnuser'],
                  $forum['pop3_pnpassword'],
                  $forum['forum_moduleref'],
                  $forum['forum_pntopic'],
                  $forum['cat_title'],
                  $forum['cat_id'] ) = $result->fields;
            // we re-use the pop3_active field to distinguish between
            // 0 - no external source
            // 1 - mail
            // 2 - rss
            // now
            // to do: rename the db fields:
            $forum['externalsource']     = $forum['pop3_active'];
            $forum['externalsourceurl']  = $forum['pop3_server'];
            $forum['externalsourceport'] = $forum['pop3_port'];
            $forum['pnuser']             = $forum['pop3_pnuser'];
            $forum['pnpassword']         = $forum['pop3_pnpassword'];
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
 * $forum_id
 */
function pnForum_adminapi_readmoderators($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    $sql = "SELECT u.pn_uname, u.pn_uid
            FROM ".$pntable['users']." u, ".$pntable['pnforum_forum_mods']." f
            WHERE f.forum_id = '".pnVarPrepForStore($forum_id)."' AND u.pn_uid = f.user_id
            AND f.user_id<1000000";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $mods = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $mod = array();
            list( $mod['uname'],
                  $mod['uid'] ) = $result->fields;
            array_push($mods, $mod);
        }
    }
    pnfCloseDB($result);

    $sql = "SELECT g.pn_name, g.pn_gid
            FROM ".$pntable['groups']." g, ".$pntable['pnforum_forum_mods']." f
            WHERE f.forum_id = '".pnVarPrepForStore($forum_id)."' AND g.pn_gid = f.user_id-1000000
            AND f.user_id>1000000";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $mod = array();
            list( $mod['uname'],
                  $mod['uid'] ) = $result->fields;
            $mod['uid'] = $mod['uid'] + 1000000;
            array_unshift($mods, $mod);
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
        if($mod['uid']<=1000000) {
            // mod uids > 1000000 are groups
            $sql .= "AND n.pn_uid != '".pnVarPrepForStore($mod['uid'])."'";
        }
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
 * readgroups
 *
 */
function pnForum_adminapi_readgroups($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();

    // read groups
    $sql = "SELECT g.pn_gid, g.pn_name
            FROM ".$pntable['groups']." AS g ";

    $where_flag = false;
    $group_flag = false;
    foreach($moderators as $mod) {
        if($mod['uid']>1000000) {
            // mod uids > 1000000 are groups
            if(!$where_flag) {
                $sql .= 'WHERE ';
                $where_flag = true;
            }
            if($group_flag) {
                $sql .= ' AND ';
            }
            $sql .= "g.pn_gid != '".pnVarPrepForStore((int)$mod['uid']-1000000)."' ";
            $group_flag = true;
        }
    }
    $sql .= "ORDER BY g.pn_name";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $groups = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $group = array();
            list( $group['gid'],
                  $group['name'] ) = $result->fields;
            $group['gid'] = $group['gid'] + 1000000;
            array_push( $groups, $group );
        }
    }
    pnfCloseDB($result);

    return $groups;

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
            WHERE $rcol[rank_special] = " . pnVarPrepForStore($ranktype) . " ";
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

    $rank_id = (int)pnVarPrepForStore($rank_id);
    $user_id = (int)pnVarPrepForStore($user_id);

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

    // read all categories
    $categories = pnForum_adminapi_readcategories();
    if(!is_array($categories) || count($categories)==0) {
        return showforumerror(_PNFORUM_NOCATEGORIES, __FILE__, __LINE__);
    }

    $cat_id    = (int)$cat_id;
    $cat_order = (int)$cat_order;

    list($dbconn, $pntable) = pnfOpenDB();

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
        // shift down
        $sql = "SELECT COUNT(1)
                FROM ".$pntable['pnforum_categories']."";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        list($numcategories) = $result->fields;
        $numcategories = (int)$numcategories;
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
 *@params $args['pop3_active'] int pop3 active?
 *@params $args['pop3_server'] string server name
 *@params $args['pop3_port'] int pop3 port
 *@params $args['pop3_login'] string login
 *@params $args['pop3_password'] string password
 *@params $args['pop3_interval'] int poll interval
 *@params $args['pop3_matchstring'] string  reg exp
 *@params $args['pop3_pnuser'] string postnuke username
 *@params $args['pop3_pnpassword'] string postnuke password
 *@params $args['moduleref'] string reference module
 *@params $args['pntopic']   int PN topic id
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
    $forumtable  = $pntable['pnforum_forums'];
    $forumcolumn = $pntable['pnforum_forums_column'];

    $forum_name = strip_tags($forum_name);
    if(empty($forum_name)) {
        return showforumerror(_PNFORUM_CREATEFORUM_INCOMPLETE, __FILE__, __LINE__);
    }
    if (!$desc) {
        $desc = '';
    }
    $desc = nl2br($desc); // to be fixed ASAP
    //$desc = pnVarPrepForStore($desc);
    //$forum_name = pnVarPrepForStore($forum_name);
    //$cat_id = pnVarPrepForStore($cat_id);
    $sql = "SELECT max(forum_order) AS highest
            FROM " . $forumtable . "
            WHERE " . $forumcolumn['cat_id'] ."= '" . pnVarPrepForStore($cat_id) . "'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    list($highest) = $result->fields;
    pnfCloseDB($result);
    $highest++;
    $forum_id = $dbconn->GenID($pntable['pnforum_forums']);
    $sql = "INSERT INTO " . $forumtable . "
                (" . $forumcolumn['forum_id'] . ",
                 " . $forumcolumn['forum_name'] . ",
                 " . $forumcolumn['forum_desc'] . ",
                 " . $forumcolumn['cat_id'] . ",
                 " . $forumcolumn['forum_order'] . ",
                 " . $forumcolumn['forum_pop3_active'] . ",
                 " . $forumcolumn['forum_pop3_server'] . ",
                 " . $forumcolumn['forum_pop3_port'] . ",
                 " . $forumcolumn['forum_pop3_login'] . ",
                 " . $forumcolumn['forum_pop3_password'] . ",
                 " . $forumcolumn['forum_pop3_interval'] . ",
                 " . $forumcolumn['forum_pop3_matchstring'] . ",
                 " . $forumcolumn['forum_pop3_pnuser'] . ",
                 " . $forumcolumn['forum_pop3_pnpassword'] . ",
                 " . $forumcolumn['forum_moduleref'] . ",
                 " . $forumcolumn['forum_pntopic'] . ")
            VALUES ('".pnVarPrepForStore($forum_id)."',
                    '".pnVarPrepForStore($forum_name)."',
                    '".pnVarPrepForStore($desc)."',
                    '".pnVarPrepForStore($cat_id)."',
                    '".pnVarPrepForStore($highest)."',
                    '".(int)pnVarPrepForStore($pop3_active)."',
                    '".pnVarPrepForStore($pop3_server)."',
                    '".(int)pnVarPrepForStore($pop3_port)."',
                    '".pnVarPrepForStore($pop3_login)."',
                    '".pnVarPrepForStore($pop3_password)."',
                    '".(int)pnVarPrepForStore($pop3_interval)."',
                    '".pnVarPrepForStore($pop3_matchstring)."',
                    '".pnVarPrepForStore($pop3_pnuser)."',
                    '".pnVarPrepForStore($pop3_pnpassword)."',
                    '".pnVarPrepForStore($moduleref)."',
                    '".pnVarPrepForStore($pntopic)."')";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
pnf_ajaxerror($dbconn->ErrorMsg());
    pnfCloseDB($result);
    $newforumid = $dbconn->PO_Insert_ID($pntable['pnforum_forums'], 'forum_id');
    $count = 0;
    if(is_array($mods) && count($mods)>0) {
        while(list($mod_number, $mod) = each($mods)) {
            $mod_query = "INSERT INTO ".$pntable['pnforum_forum_mods']."
                                (forum_id,
                                user_id)
                            VALUES ('".pnVarPrepForStore($newforumid)."',
                                    '".pnVarPrepForStore($mod)."')";
            $mod_res = pnfExecuteSQL($dbconn, $mod_query, __FILE__, __LINE__);
            pnfCloseDB($mod_res);
        }
    }
    if (isset($forum_order) && is_numeric($forum_order)) {
        pnModAPIFunc('pnForum', 'admin', 'reorderforumssave',
                array('cat_id'      => $cat_id,
                    'forum_id'    => $newforumid,
                    'neworder'    => $forum_order,
                    'oldorder'    => $highest));
    }

    // Let any hooks know that we have created a new item.
    pnModCallHooks('item', 'create', $newforumid, array('module' => 'pnForum',
                                                        'forum_id' => $newforumid));

    return $newforumid;
}

/**
 * editforum
 */
// $forum_id, $forum_name, $desc, $cat_id, $mods, $rem_mods)
function pnForum_adminapi_editforum($args)
{
    extract($args);
    unset($args);

    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__);
    }

    list($dbconn, $pntable) = pnfOpenDB();

    $pop3passwordupdate = "";
    if(!empty($pop3_password)) {
        // pop3_password is not empty - save it
        $pop3passwordupdate = "forum_pop3_password    ='".pnVarPrepForStore($pop3_password)."',";
    }
    $pnpasswordupdate = "";
    if(!empty($pop3_pnpassword)) {
        // pop3_pnpassword is not empty - save it
        $pnpasswordupdate = "forum_pop3_pnpassword    ='".pnVarPrepForStore($pop3_pnpassword)."',";
    }

    $sql = "UPDATE ".$pntable['pnforum_forums']."
            SET forum_name='".pnVarPrepForStore(strip_tags($forum_name))."',
            forum_desc='".pnVarPrepForStore($desc)."',
            cat_id=" . (int)pnVarPrepForStore($cat_id) . ",
            forum_pop3_active      =".(int)pnVarPrepForStore($pop3_active).",
            forum_pop3_server      ='".pnVarPrepForStore($pop3_server)."',
            forum_pop3_port        =".(int)pnVarPrepForStore($pop3_port).",
            forum_pop3_login       ='".pnVarPrepForStore($pop3_login)."',
            $pop3passwordupdate
            forum_pop3_interval    =".(int)pnVarPrepForStore($pop3_interval).",
            forum_pop3_pnuser      ='".pnVarPrepForStore($pop3_pnuser)."',
            $pnpasswordupdate
            forum_pop3_matchstring ='".pnVarPrepForStore($pop3_matchstring)."',
            forum_moduleref        =".pnVarPrepForStore($moduleref).",
            forum_pntopic          =".pnVarPrepForStore($pntopic)."
            WHERE forum_id=".pnVarPrepForStore($forum_id)."";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    if(isset($mods) && !empty($mods)) {
        $recentmods = pnModAPIFunc('pnForum', 'admin', 'readmoderators',
                                   array('forum_id' => $forum_id));
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

/**
 * get_pntopics
 *
 */
function pnForum_adminapi_get_pntopics()
{
    pnModDBInfoLoad('Topics');
    list($dbconn, $pntable) = pnfOpenDB();

    $pntopicstable  = $pntable['topics'];
    $pntopicscolumn = $pntable['topics_column'];

    $sql = "SELECT $pntopicscolumn[topicid],
                   $pntopicscolumn[topicname],
                   $pntopicscolumn[topicimage],
                   $pntopicscolumn[topictext]
            FROM $pntopicstable
            ORDER BY $pntopicscolumn[topicname]";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $topics = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $topic = array();
            list( $topic['topicid'],
                  $topic['topicname'],
                  $topic['topicimage'],
                  $topic['topictext'] ) = $result->fields;
            array_push($topics, $topic);
        }
    }
    return $topics;
}

/**
 * store new forum order
 *
 *@params $args['forum_id'] int the forum id 
 *@params $args['cat_id']   int the forums category id 
 *@params $args['order']    int the forum order number in this category
 *
 */
function pnForum_adminapi_storenewforumorder($args)
{
    extract($args);
    unset($args);

    if( !pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
        return showforumerror(_PNFORUM_NOAUTH, __FILE__, __LINE__);
    }
    
    if(!isset($forum_id) || empty($forum_id) || !is_numeric($forum_id)) {
        pnf_ajaxerror(_MODARGSERROR . ' (pnForum_adminapi_storenewforumorder(), forumid=' . $forum_id);
    }    
    if(!isset($cat_id) || empty($cat_id) || !is_numeric($cat_id)) {
        pnf_ajaxerror(_MODARGSERROR . ' (pnForum_adminapi_storenewcategoryorder(), cat_id=' . $cat_id);
    }    
    if(!isset($order) || empty($order) || !is_numeric($order) || ($order<1)) {
        pnf_ajaxerror(_MODARGSERROR . ' (pnForum_adminapi_storenewforumorder(), order=' . $order);
    }    

    list($dbconn, $pntable) = pnfOpenDB();

    $forumtable   = $pntable['pnforum_forums'];
    $forumcolumn  = &$pntable['pnforum_forums_column'];

    $sql = "UPDATE " . $forumtable. "
            SET " . $forumcolumn['forum_order'] ."='" . (int)pnVarPrepForStore($order) . "',
                " . $forumcolumn['cat_id'] . "='" . (int)pnVarPrepForStore($cat_id) . "'
            WHERE " . $forumcolumn['forum_id'] . "='" . (int)pnVarPrepForStore($forum_id) . "'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__, false, false);
    if(is_bool($result) && $result==false) {
        return false;
    }
    pnfCloseDB($result);
    return true;
    
}

/**
 * store new category order
 *
 *@params $args['cat_id'] int the category id 
 *@params $args['order']  int the category order number
 *
 */
function pnForum_adminapi_storenewcategoryorder($args)
{
    extract($args);
    unset($args);

    if( !pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
        pnf_ajaxerror(_PNFORUM_NOAUTH);
    }
    
    if(!isset($cat_id) || empty($cat_id) || !is_numeric($cat_id)) {
        pnf_ajaxerror(_MODARGSERROR . ' (pnForum_adminapi_storenewcategoryorder(), cat_id=' . $cat_id);
    }    
    if(!isset($order) || empty($order) || !is_numeric($order) || ($order<1)) {
        pnf_ajaxerror(_MODARGSERROR . ' (pnForum_adminapi_storenewcategoryorder(), order=' . $order);
    }    

    list($dbconn, $pntable) = pnfOpenDB();

    $cattable   = $pntable['pnforum_categories'];
    $catcolumn  = &$pntable['pnforum_categories_column'];

    $sql = "UPDATE $cattable
            SET $catcolumn[cat_order] = '" . (int)pnVarPrepForStore($order) . "'
            WHERE $catcolumn[cat_id] = '" . (int)pnVarPrepForStore($cat_id) . "'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__, false, false);
    if(is_bool($result) && $result==false) {
        return false;
    }
    pnfCloseDB($result);
    return true;
    
}
 
?>