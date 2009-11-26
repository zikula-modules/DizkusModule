<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

Loader::includeOnce('modules/Dizkus/common.php');

/**
 * readcatgories
 * read the categories from database, if cat_id is set, only this one will be read
 *
 * @params $args['cat_id'] int the category id to read (optional)
 * @returns array of category information
 *
 */
function Dizkus_adminapi_readcategories($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntables = pnDBGetTables();
    $catcolumn = $pntables['dizkus_categories_column'];

    $where = '';
    if (isset($args['cat_id'])) {
        $where .= "WHERE $catcolumn[cat_id]=" . DataUtil::formatForStore($args['cat_id']) . " ";
    }
    $orderby = 'cat_order ASC';

    $categories = DBUtil::selectObjectArray('dizkus_categories', $where, $orderby);
    if (isset($args['cat_id'])) {
        return $categories[0];
    }

    // we now check the cat_order field in each category entry. Each
    // cat_order may only appear once there. If we find it more than once, we will adjust
    // all following cat_orders by incrementing them by 1
    // the fact that is array is sorted by cat_order simplifies this :-)
    $last_cat_order = 0;   // for comparison
    $cat_order_adjust = 0; // holds the number of shifts we have to do
    $shifted = false; // trigger, if true we have to update the db
    for ($i = 0; $i < count($categories); $i++) {
        // we leave cat_order = 0 untouched!
        if ($cat_order_adjust > 0) {
            // we have done at least one change before which means that all foloowing categories
            // have to be changed too.
            $categories[$i]['cat_order'] = $categories[$i]['cat_order'] + $cat_order_adjust;
            $shifted = true;
        } else if ($categories[$i]['cat_order'] == $last_cat_order ) {
            $cat_order_adjust++;
            $categories[$i]['cat_order'] = $categories[$i]['cat_order'] + $cat_order_adjust;
            $shifted = true;
        }
        $last_cat_order = $categories[$i]['cat_order'];
    }
    if ($shifted == true) {
        DBUtil::updateObjectArray($categories, 'dizkus_categories', 'cat_id');
    }

    return $categories;
}

/**
 * updatecategory
 * update a category in database, either cat_title or cat_order

 * @params $args['cat_title'] string category title
 * @params $args['cat_id'] int category id
 */
function Dizkus_adminapi_updatecategory($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return showforumerror(__('Sorry! You do not have authorisation to perform this action', $dom), __FILE__, __LINE__);
    }
    
    // copy all entries from $args to $obj that are found in the categories table
    // this prevents possible SQL errors if non existing keys are passed to this function
    $pntables = pnDBGetTables();
    $obj = array();
    foreach ($args as $key => $arg) {
        if (array_key_exists($key, $pntables['dizkus_categories_column'])) {
            $obj[$key] = $arg;
        }
    }

    if (isset($obj['cat_id'])) {
        $obj = DBUtil::updateObject($obj, 'dizkus_categories', null, 'cat_id');
        return true;
    }
    return false;
}

/**
 * addcategory
 * adds a new category
 *
 * @params $args['cat_title'] string the categories title
 */
function Dizkus_adminapi_addcategory($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return showforumerror(__('Sorry! You do not have authorisation to perform this action.', $dom), __FILE__, __LINE__);
    }

    if (isset($args['cat_title'])) {
        $args['cat_order'] = DBUtil::selectObjectCount('dizkus_categories') + 1;
        $obj = DBUtil::insertObject($args, 'dizkus_categories', 'cat_id');
        return $obj['cat_id'];
    }
    return false;
}

/**
 * delete a category
 * deletes a category from db including all forums and posts!
 *
 * @params $args['cat_id'] int the id of the category to delete
 *
 */
function Dizkus_adminapi_deletecategory($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return showforumerror(__('Sorry! You do not have authorisation to perform this action.', $dom), __FILE__, __LINE__);
    }

    if (isset($args['cat_id'])) {
        // read all the forums in this category
        $forums = Dizkus_adminapi_readforums(array('cat_id' => $args['cat_id']));
        if (is_array($forums) && count($forums)>0) {
            foreach ($forums as $forum) {
                // remove all forums in this category
                pnModAPIFunc('Dizkus', 'admin', 'deleteforum',
                             array('forum_id' => $forum['forum_id'],
                                   'ok'       => 1));
            }  //foreach forum
        }
        // now we can delete the category
        $res = DBUtil::deleteObject($args, 'dizkus_categories', null, 'cat_id');
        return true;
    }
    return showforumerror(__('Error! The action you wanted to perform was not successful for some reason, maybe because of a problem with your input. Please check and try again.', $dom), __FILE__, __LINE__);
}

/**
 * readforums
 * read the forums list and performs  permission for each depending on the permcheck parameter
 * default is ACCESS_READ. "nocheck" means, return the forums no matter if the user has sufficient
 * rights or not, in this case the calling function has to take care of it!!
 *
 * @params $args['forum_id'] int only read this forum (optional)
 * @params $args['cat_id'] int read the forums in this category only (optional)
 * @params $args['permcheck'] string either "nocheck", "see", "read", "write", "moderate" or "admin", default is "read" (optional)
 * @returns array of forums or
 *         one forum in case of forum_id set
 */
function Dizkus_adminapi_readforums($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = &pnDBGetTables();
    $forumcolumn = $pntable['dizkus_forums_column'];
    $catcolumn   = $pntable['dizkus_categories_column'];

    $permcheck = (isset($args['permcheck'])) ? strtoupper($args['permcheck']): ACCESS_READ;
    if (!empty($permcheck) &&
        ($permcheck <> ACCESS_OVERVIEW) &&
        ($permcheck <> ACCESS_READ) &&
        ($permcheck <> ACCESS_COMMENT) &&
        ($permcheck <> ACCESS_MODERATE) &&
        ($permcheck <> ACCESS_ADMIN) &&
        ($permcheck <> 'NOCHECK')  ) {
        return showforumerror(__('Error! The action you wanted to perform was not successful for some reason, maybe because of a problem with your input. Please check and try again.', $dom), __FILE__, __LINE__);
    }

    $where = '';
    if (isset($args['forum_id'])) {
        $where = "WHERE tbl.forum_id='". DataUtil::formatForStore($args['forum_id']) ."' ";
    } elseif (isset($args['cat_id'])) {
        $where = "WHERE tbl.cat_id='". DataUtil::formatForStore($args['cat_id']) ."' ";
    }
    
    if ($permcheck <> 'NOCHECK') {
        $permfilter[] = array('realm' => 0,
                              'component_left'   =>  'Dizkus',
                              'component_middle' =>  '',
                              'component_right'  =>  '',
                              'instance_left'    =>  'cat_id',
                              'instance_middle'  =>  'forum_id',
                              'instance_right'   =>  '',
                              'level'            =>  $permcheck);
    } else {
        $permfilter = null;
    }

    $joininfo[] = array ('join_table'         =>  'dizkus_categories',
                         'join_field'         =>  array('cat_title', 'cat_id'),
                         'object_field_name'  =>  array('cat_title', 'cat_id'),
                         'compare_field_table'=>  'cat_id',
                         'compare_field_join' =>  'cat_id');
    $orderby = 'a.cat_order, tbl.forum_order, tbl.forum_name';

    $forums = DBUtil::selectExpandedObjectArray('dizkus_forums', $joininfo, $where, $orderby, -1, -1, '', $permfilter);

    for ($i = 0; $i < count($forums); $i++) {
        // rename some fields for BC compatibility
        $forums[$i]['pop3_active']      = $forums[$i]['forum_pop3_active'];
        $forums[$i]['pop3_server']      = $forums[$i]['forum_pop3_server'];
        $forums[$i]['pop3_port']        = $forums[$i]['forum_pop3_port'];
        $forums[$i]['pop3_login']       = $forums[$i]['forum_pop3_login'];
        $forums[$i]['pop3_password']    = $forums[$i]['forum_pop3_password'];
        $forums[$i]['pop3_interval']    = $forums[$i]['forum_pop3_interval'];
        $forums[$i]['pop3_lastconnect'] = $forums[$i]['forum_pop3_lastconnect'];
        $forums[$i]['pop3_matchstring'] = $forums[$i]['forum_pop3_matchstring'];
        $forums[$i]['pop3_pnuser']      = $forums[$i]['forum_pop3_pnuser'];
        $forums[$i]['pop3_pnpassword']  = $forums[$i]['forum_pop3_pnpassword'];

        // we re-use the pop3_active field to distinguish between
        // 0 - no external source
        // 1 - mail
        // 2 - rss
        // now
        // to do: rename the db fields:   
        $forums[$i]['externalsource']     = $forums[$i]['forum_pop3_active'];
        $forums[$i]['externalsourceurl']  = $forums[$i]['forum_pop3_server'];
        $forums[$i]['externalsourceport'] = $forums[$i]['forum_pop3_port'];
        $forums[$i]['pnuser']             = $forums[$i]['forum_pop3_pnuser'];
        $forums[$i]['pnpassword']         = $forums[$i]['forum_pop3_pnpassword'];
    }

    if (count($forums) > 0) {
        if (isset($args['forum_id'])) {
            return $forums[0];
        }
    }
    return $forums;
}

/**
 * readmoderators
 * $forum_id
 */
function Dizkus_adminapi_readmoderators($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    $sql = "SELECT u.pn_uname, u.pn_uid
            FROM ".$pntable['users']." u, ".$pntable['dizkus_forum_mods']." f
            WHERE f.forum_id = '".DataUtil::formatForStore($forum_id)."' AND u.pn_uid = f.user_id
            AND f.user_id<1000000";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $mods = array();
    if ($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            $mod = array();
            list( $mod['uname'],
                  $mod['uid'] ) = $result->fields;
            array_push($mods, $mod);
        }
    }
    dzkCloseDB($result);

    $sql = "SELECT g.pn_name, g.pn_gid
            FROM ".$pntable['groups']." g, ".$pntable['dizkus_forum_mods']." f
            WHERE f.forum_id = '".DataUtil::formatForStore($forum_id)."' AND g.pn_gid = f.user_id-1000000
            AND f.user_id>1000000";

    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    if ($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            $mod = array();
            list( $mod['uname'],
                  $mod['uid'] ) = $result->fields;
            $mod['uid'] = $mod['uid'] + 1000000;
            array_unshift($mods, $mod);
        }
    }
    dzkCloseDB($result);

    return $mods;
}

/**
 * readusers
 *
 */
function Dizkus_adminapi_readusers($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    $sql = "SELECT n.pn_uid, n.pn_uname
            FROM ".$pntable['users']." AS n
            left JOIN ".$pntable['dizkus_users']." AS u
            ON u.user_id=n.pn_uid
            WHERE n.pn_uid != 1 ";

    foreach($moderators as $mod) {
        if ($mod['uid']<=1000000) {
            // mod uids > 1000000 are groups
            $sql .= "AND n.pn_uid != '".DataUtil::formatForStore($mod['uid'])."'";
        }
    }
    $sql .= "ORDER BY pn_uname";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $users = array();
    if ($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $user = array();
            list( $user['uid'],
                  $user['uname'] ) = $result->fields;
            array_push( $users, $user );
        }
    }
    dzkCloseDB($result);
    return $users;
}

/**
 * readgroups
 *
 */
function Dizkus_adminapi_readgroups($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($args);
    unset($args);

    list($dbconn, $pntable) = dzkOpenDB();

    // read groups
    $sql = "SELECT g.pn_gid, g.pn_name
            FROM ".$pntable['groups']." AS g ";

    $where_flag = false;
    $group_flag = false;
    foreach($moderators as $mod) {
        if ($mod['uid']>1000000) {
            // mod uids > 1000000 are groups
            if (!$where_flag) {
                $sql .= 'WHERE ';
                $where_flag = true;
            }
            if ($group_flag) {
                $sql .= ' AND ';
            }
            $sql .= "g.pn_gid != '".DataUtil::formatForStore((int)$mod['uid']-1000000)."' ";
            $group_flag = true;
        }
    }
    $sql .= "ORDER BY g.pn_name";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    $groups = array();
    if ($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext())
        {
            $group = array();
            list( $group['gid'],
                  $group['name'] ) = $result->fields;
            $group['gid'] = $group['gid'] + 1000000;
            array_push( $groups, $group );
        }
    }
    dzkCloseDB($result);

    return $groups;

}

/**
 * readranks
 * @params ranktype   
 *
 */
function Dizkus_adminapi_readranks($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    // read images
    $path     = pnModGetVar('Dizkus', 'url_ranks_images');
    $handle   = opendir($path);
    $filelist = array();
    while ($file = readdir($handle)) {
        if (dzk_isimagefile($path.'/'.$file)) {
            $filelist[] = $file;
        }
    }
    asort($filelist);

    $pntables = pnDBGetTables();
    $rcol = $pntables['dizkus_ranks_column'];

    if ($args['ranktype']==0) {
        $orderby = 'ORDER BY ' . $rcol['rank_min'];
    } else {
        $orderby = 'ORDER BY ' . $rcol['rank_title'];
    }
    $ranks = DBUtil::selectObjectArray('dizkus_ranks', 'WHERE ' . $rcol['rank_special'] . '=' . DataUtil::formatForStore($args['ranktype']), $orderby);

    if (is_array($ranks)) {
        foreach($ranks as $cnt => $rank) {
        	$ranks[$cnt]['users'] = pnModAPIFunc('Dizkus', 'admin', 'readrankusers',
                                          array('rank_id' => $ranks[$cnt]['rank_id']));
        }
    }
/*
    // add a dummy rank on top for new ranks
    array_unshift($ranks, array('rank_id'      => -1,
                                'rank_title'   => '',
                                'rank_min'     => 0,
                                'rank_max'     => 0,
                                'rank_special' => 0,
                                'rank_image'   => 'onestar.gif',
                                'users'        => array()));
*/
    return array($filelist, $ranks);
}

/**
 * saverank
 * @params rank_special, rank_id, rank_min, rank_max, rank_image, rank_id
 *
 */
function Dizkus_adminapi_saverank($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

	if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return showforumerror(__('Sorry! You do not have authorisation to perform this action.', $dom), __FILE__, __LINE__);
    }

    foreach($args['ranks'] as $rankid => $rank) {
        if ($rankid == '-1') {
            $obj = DBUtil::insertObject($rank, 'dizkus_ranks', 'rank_id');
        } else {
            $rank['rank_id'] = $rankid;
            if ($rank['rank_delete'] == '1') {
                $res = DBUtil::deleteObject($rank, 'dizkus_ranks', null, 'rank_id');
            } else {
                $res = DBUtil::updateObject($rank, 'dizkus_ranks', null, 'rank_id');
            }
        }
    }
    return;
}

/**
 * readrankusers
 * rank_id
 */
function Dizkus_adminapi_readrankusers($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $pntable = pnDBGetTables();

    $sql = 'SELECT  u.user_id
            FROM ' . $pntable['dizkus_ranks'] . ' as r,
                 ' . $pntable['dizkus_users'].' as u
            WHERE r.rank_id=' . DataUtil::formatForStore($args['rank_id']) . '
              AND u.user_rank=r.rank_id
              AND r.rank_special=1
              AND u.user_id <>""';

    $res = DBUtil::executeSQL($sql);
    $objarray = DBUtil::marshallObjects($res, array('user_id'));
    $users = array_map('_get_rank_users', $objarray);

    return $users;
}

/**
 * helper function
 */
function _get_rank_users($u)
{
    return $u['user_id'];
}

/**
 * assignranksave
 * setrank array(uid) = rank_id
 */
function Dizkus_adminapi_assignranksave($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return showforumerror(__('Sorry! You do not have authorisation to perform this action.', $dom), __FILE__, __LINE__);
    }

    if (is_array($args['setrank'])) {
        $ranksavearray = array();
        foreach($args['setrank'] as $user_id => $rank_id) {
            $ranksavearray[] = array('user_id' => $user_id, 'user_rank' => $rank_id);
        }
        DBUtil::updateObjectArray($ranksavearray, 'dizkus_users', 'user_id');
    }
    return;
}

/**
 * This function should receive $id, $type
 * synchronizes forums/topics/users
 *
 * $id, $type
 */
function Dizkus_adminapi_sync($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    list($dbconn, $pntable) = dzkOpenDB();

    switch ($args['type'])
    {
        case 'forum':
            $sql = "SELECT max(post_id) AS last_post
                    FROM ".$pntable['dizkus_posts']."
                    WHERE forum_id = '".(int)DataUtil::formatForStore($args['id'])."'";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            $result_lastpost = $dbconn->Affected_Rows();

            if ($result_lastpost != 0) {
                list($last_post) = $result->FetchRow();
            } else {
                $last_post = 0;
            }
            dzkCloseDB($result);

            $sql = "SELECT count(post_id) AS total
                    FROM ".$pntable['dizkus_posts']."
                    WHERE forum_id = '".(int)DataUtil::formatForStore($args['id'])."'";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            $row = $result->GetRowAssoc(false);
            dzkCloseDB($result);
            $total_posts = $row['total'];

            $sql = "SELECT count(topic_id) AS total
                    FROM ".$pntable['dizkus_topics']."
                    WHERE forum_id = ".(int)DataUtil::formatForStore($args['id'])."";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            $row = $result->GetRowAssoc(false);
            dzkCloseDB($result);
            $total_topics = $row['total'];

            $sql = "UPDATE ".$pntable['dizkus_forums']."
                    SET forum_last_post_id = '".(int)DataUtil::formatForStore($last_post)."',
                        forum_posts = '".(int)DataUtil::formatForStore($total_posts)."',
                        forum_topics = '".DataUtil::formatForStore($total_topics)."'
                    WHERE forum_id = '".(int)DataUtil::formatForStore($args['id'])."'";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            dzkCloseDB($result);
            break;

        case 'topic':
            $sql = "SELECT max(post_id) AS last_post
                    FROM ".$pntable['dizkus_posts']."
                    WHERE topic_id = '".(int)DataUtil::formatForStore($args['id'])."'";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            $result_lastpost = $dbconn->Affected_Rows();

            if ($result_lastpost != 0) {
                list($last_post) = $result->FetchRow();
            } else {
                $last_post = 0;
            }
            dzkCloseDB($result);

            $sql = "SELECT count(post_id) AS total
                    FROM ".$pntable['dizkus_posts']."
                    WHERE topic_id = '".(int)DataUtil::formatForStore($args['id'])."'";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            //$row = $result->GetRowAssoc(false);
            list($total_posts) = $result->FetchRow(); // $row['total'];
            dzkCloseDB($result);

            $total_posts -= 1;
            $sql = "UPDATE ".$pntable['dizkus_topics']."
                    SET topic_replies = '".(int)DataUtil::formatForStore($total_posts)."', topic_last_post_id = '".(int)DataUtil::formatForStore($last_post)."'
                    WHERE topic_id = '".(int)DataUtil::formatForStore($args['id'])."'";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            dzkCloseDB($result);
            break;

        case 'all forums':
            $forums = Dizkus_adminapi_readforums();
            foreach ($forums as $forum) {
                Dizkus_adminapi_sync(array('id' => $forum['forum_id'], 'type' => 'forum'));
            }
            break;

        case 'all topics':
            $sql = "SELECT topic_id
                    FROM ".$pntable['dizkus_topics'];
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

            if ($result->RecordCount() > 0) {
                for (; !$result->EOF; $result->MoveNext()) {
                    list($topic_id) = $result->fields;
                    Dizkus_adminapi_sync(array('id' => $topic_id, 'type' => 'topic'));
                }
            }
            dzkCloseDB($result);
            break;

        case 'all posts':
            $sql = "SELECT poster_id,
                           count(poster_id) as total_posts
                    FROM ".$pntable['dizkus_posts']."
                    GROUP BY poster_id";
            $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

            if ($result->RecordCount() > 0) {
                for (; !$result->EOF; $result->MoveNext()) {
                    list($poster_id, 
                         $total_posts) = $result->fields;

                    $sub_sql = "UPDATE ".$pntable['dizkus_users']."
                                SET user_posts = '".(int)DataUtil::formatForStore($total_posts)."'
                                WHERE user_id = '".(int)DataUtil::formatForStore($poster_id)."'";
                    $result2 = dzkExecuteSQL($dbconn, $sub_sql, __FILE__, __LINE__);
                    dzkCloseDB($result2);
                }
            }
            dzkCloseDB($result);
            break;

        case 'all users':
        case 'user': 
            // copy new users to dizkus_users table
            $sql = 'INSERT INTO ' . $pntable['dizkus_users'] . ' (user_id)
                    SELECT pn_uid
                    FROM ' . $pntable['users'] . '
                    LEFT JOIN ' . $pntable['dizkus_users'] . '
                    ON user_id = pn_uid
                    WHERE user_id IS NULL';
            $res = DBUtil::executeSQL($sql);

            break;
        default:
            return showforumerror('Error! Bad parameter in synchronisation:', __FILE__, __LINE__);
    }

    return true;
}

/**
 * addforum
 * Adds a new forum
 *
 * @params $args['forum_name'] string the forums name
 * @params $args['desc'] string the forum description
 * @params $args['cat_id'] int the category where the forum shall be added
 * @params $args['mods'] array of moderators
 * @params $args['forum_order'] int the forums order, optional
 * @params $args['pop3_active'] int pop3 active?
 * @params $args['pop3_server'] string server name
 * @params $args['pop3_port'] int pop3 port
 * @params $args['pop3_login'] string login
 * @params $args['pop3_password'] string password
 * @params $args['pop3_interval'] int poll interval
 * @params $args['pop3_matchstring'] string  reg exp
 * @params $args['pop3_pnuser'] string Zikula username
 * @params $args['pop3_pnpassword'] string Zikula password
 * @params $args['moduleref'] string reference module
 * @params $args['pntopic']   int PN topic id
 * @returns int the new forums id
 *
 */
function Dizkus_adminapi_addforum($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($args);
    unset($args);

    if ( !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN) &&
        !SecurityUtil::checkPermission('Dizkus::CreateForum', $cat_id . "::", ACCESS_EDIT) ) {
        return showforumerror(__('Sorry! You do not have authorisation to perform this action.', $dom), __FILE__, __LINE__);
    }

    list($dbconn, $pntable) = dzkOpenDB();
    $forumtable  = $pntable['dizkus_forums'];
    $forumcolumn = $pntable['dizkus_forums_column'];

    $forum_name = strip_tags($forum_name);
    if (empty($forum_name)) {
        return showforumerror(__('Error! You did not enter all the information required in the form. Did you assign at least one moderator? Please go back and try again.', $dom), __FILE__, __LINE__);
    }
    if (!$desc) {
        $desc = '';
    }
    $desc = nl2br($desc); // to be fixed ASAP
    //$desc = DataUtil::formatForStore($desc);
    //$forum_name = DataUtil::formatForStore($forum_name);
    //$cat_id = DataUtil::formatForStore($cat_id);
    $sql = "SELECT max(forum_order) AS highest
            FROM " . $forumtable . "
            WHERE " . $forumcolumn['cat_id'] ."= '" . DataUtil::formatForStore($cat_id) . "'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    list($highest) = $result->fields;
    dzkCloseDB($result);
    $highest++;
    $forum_id = $dbconn->GenID($pntable['dizkus_forums']);
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
            VALUES ('".DataUtil::formatForStore($forum_id)."',
                    '".DataUtil::formatForStore($forum_name)."',
                    '".DataUtil::formatForStore($desc)."',
                    '".DataUtil::formatForStore($cat_id)."',
                    '".DataUtil::formatForStore($highest)."',
                    '".(int)DataUtil::formatForStore($pop3_active)."',
                    '".DataUtil::formatForStore($pop3_server)."',
                    '".(int)DataUtil::formatForStore($pop3_port)."',
                    '".DataUtil::formatForStore($pop3_login)."',
                    '".DataUtil::formatForStore($pop3_password)."',
                    '".(int)DataUtil::formatForStore($pop3_interval)."',
                    '".DataUtil::formatForStore($pop3_matchstring)."',
                    '".DataUtil::formatForStore($pop3_pnuser)."',
                    '".DataUtil::formatForStore($pop3_pnpassword)."',
                    '".DataUtil::formatForStore($moduleref)."',
                    '".DataUtil::formatForStore($pntopic)."')";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
//dzk_ajaxerror($dbconn->ErrorMsg());
    dzkCloseDB($result);
    $newforumid = $dbconn->PO_Insert_ID($pntable['dizkus_forums'], 'forum_id');
    $count = 0;
    if (is_array($mods) && count($mods)>0) {
        while(list($mod_number, $mod) = each($mods)) {
            $mod_query = "INSERT INTO ".$pntable['dizkus_forum_mods']."
                                (forum_id,
                                user_id)
                            VALUES ('".DataUtil::formatForStore($newforumid)."',
                                    '".DataUtil::formatForStore($mod)."')";
            $mod_res = dzkExecuteSQL($dbconn, $mod_query, __FILE__, __LINE__);
            dzkCloseDB($mod_res);
        }
    }
    if (isset($forum_order) && is_numeric($forum_order)) {
        pnModAPIFunc('Dizkus', 'admin', 'reorderforumssave',
                array('cat_id'      => $cat_id,
                    'forum_id'    => $newforumid,
                    'neworder'    => $forum_order,
                    'oldorder'    => $highest));
    }

    // Let any hooks know that we have created a new item.
    pnModCallHooks('item', 'create', $newforumid, array('module' => 'Dizkus',
                                                        'forum_id' => $newforumid));

    return $newforumid;
}

/**
 * editforum
 */
// $forum_id, $forum_name, $desc, $cat_id, $mods, $rem_mods)
function Dizkus_adminapi_editforum($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($args);
    unset($args);

    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return showforumerror(__('Sorry! You do not have authorisation to perform this action.', $dom), __FILE__, __LINE__);
    }

    list($dbconn, $pntable) = dzkOpenDB();

    $pop3passwordupdate = "";
    if (!empty($pop3_password)) {
        // pop3_password is not empty - save it
        $pop3passwordupdate = "forum_pop3_password    ='".DataUtil::formatForStore($pop3_password)."',";
    }
    $pnpasswordupdate = "";
    if (!empty($pop3_pnpassword)) {
        // pop3_pnpassword is not empty - save it
        $pnpasswordupdate = "forum_pop3_pnpassword    ='".DataUtil::formatForStore($pop3_pnpassword)."',";
    }

    $sql = "UPDATE ".$pntable['dizkus_forums']."
            SET forum_name='".DataUtil::formatForStore(strip_tags($forum_name))."',
            forum_desc='".DataUtil::formatForStore($desc)."',
            cat_id=" . (int)DataUtil::formatForStore($cat_id) . ",
            forum_pop3_active      =".(int)DataUtil::formatForStore($pop3_active).",
            forum_pop3_server      ='".DataUtil::formatForStore($pop3_server)."',
            forum_pop3_port        =".(int)DataUtil::formatForStore($pop3_port).",
            forum_pop3_login       ='".DataUtil::formatForStore($pop3_login)."',
            $pop3passwordupdate
            forum_pop3_interval    =".(int)DataUtil::formatForStore($pop3_interval).",
            forum_pop3_pnuser      ='".DataUtil::formatForStore($pop3_pnuser)."',
            $pnpasswordupdate
            forum_pop3_matchstring ='".DataUtil::formatForStore($pop3_matchstring)."',
            forum_moduleref        =".DataUtil::formatForStore($moduleref).",
            forum_pntopic          =".DataUtil::formatForStore($pntopic)."
            WHERE forum_id=".DataUtil::formatForStore($forum_id)."";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    dzkCloseDB($result);

    if (isset($mods) && !empty($mods)) {
        $recentmods = pnModAPIFunc('Dizkus', 'admin', 'readmoderators',
                                   array('forum_id' => $forum_id));
        foreach ($mods as $mod) {
            $mod_query = "INSERT INTO ".$pntable['dizkus_forum_mods']." (forum_id, user_id) VALUES ('".DataUtil::formatForStore($forum_id)."', '".DataUtil::formatForStore($mod)."')";
            $mods = dzkExecuteSQL($dbconn, $mod_query, __FILE__, __LINE__);
            dzkCloseDB($mods);
        }
    }
    if (isset($rem_mods) && !empty($rem_mods)) {
        foreach ($rem_mods as $mod) {
            $rem_query = "DELETE FROM ".$pntable['dizkus_forum_mods']."
                        WHERE forum_id = '".DataUtil::formatForStore($forum_id)."' AND user_id = '".DataUtil::formatForStore($mod)."'";
            $rem = dzkExecuteSQL($dbconn, $rem_query, __FILE__, __LINE__);
            dzkCloseDB($rem);
        }
    }

    return;
}

/**
 * delete forum
 *
 * @params $args['forum_id']
 *
 */
function Dizkus_adminapi_deleteforum($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return showforumerror(__('Sorry! You do not have authorisation to administer this module.', $dom), __FILE__, __LINE__);
    }

    $whereforumid = 'WHERE forum_id=' . DataUtil::formatForStore($args['forum_id']);
    // delete forum
    $res = DBUtil::deleteObject($args, 'dizkus_forums', null, 'forum_id');
    
    // delete mods
    $res = DBUtil::deleteWhere('dizkus_forum_mods', $whereforumid);

    // delete forum subscription
    $res = DBUtil::deleteWhere('dizkus_subscription', $whereforumid);

    // topics
    $topics = DBUtil::selectObjectArray('dizkus_topics', $whereforumid);
    if (is_array($topics) && count($topics) > 0) {        
        foreach($topics as $topic) {
            $res = DBUtil::deleteWhere('dizkus_topic_subscription', 'WHERE topic_id=' . DataUtil::formatForStore($topic['topic_id']));
        }
    }
    $res = DBUtil::deleteWhere('dizkus_topics', $whereforumid);
/*
    // posts
    $posts = DBUtil::selectObjectArray('dizkus_posts', $whereforumid);
    if (is_array($posts) && count($posts) > 0) {
        foreach($posts as $post) {
//          $res = DBUtil::deleteWhere('dizkus_posts_text', 'WHERE post_id=' . DataUtil::formatForStore($post['post_id']));
        }
    }
*/
    $res = DBUtil::deleteWhere('dizkus_posts', $whereforumid);
    return;
}

/**
 * get_pntopics
 *
 */
function Dizkus_adminapi_get_pntopics()
{
    return false;
}

/**
 * store new forum order
 *
 * @params $args['forum_id'] int the forum id
 * @params $args['cat_id']   int the forums category id
 * @params $args['order']    int the forum order number in this category
 *
 */
function Dizkus_adminapi_storenewforumorder($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    extract($args);
    unset($args);

    if ( !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
        return showforumerror(__('Sorry! You do not have authorisation to perform this action.', $dom), __FILE__, __LINE__);
    }

    if (!isset($forum_id) || empty($forum_id) || !is_numeric($forum_id)) {
        dzk_ajaxerror(_MODARGSERROR . ' (Dizkus_adminapi_storenewforumorder(), forumid=' . $forum_id);
    }
    if (!isset($cat_id) || empty($cat_id) || !is_numeric($cat_id)) {
        dzk_ajaxerror(_MODARGSERROR . ' (Dizkus_adminapi_storenewforumorder(), cat_id=' . $cat_id);
    }
    if (!isset($order) || empty($order) || !is_numeric($order) || ($order<1)) {
        dzk_ajaxerror(_MODARGSERROR . ' (Dizkus_adminapi_storenewforumorder(), order=' . $order);
    }

    list($dbconn, $pntable) = dzkOpenDB();

    $forumtable   = $pntable['dizkus_forums'];
    $forumcolumn  = &$pntable['dizkus_forums_column'];

    $sql = "UPDATE " . $forumtable. "
            SET " . $forumcolumn['forum_order'] ."='" . (int)DataUtil::formatForStore($order) . "',
                " . $forumcolumn['cat_id'] . "='" . (int)DataUtil::formatForStore($cat_id) . "'
            WHERE " . $forumcolumn['forum_id'] . "='" . (int)DataUtil::formatForStore($forum_id) . "'";
    $result = dzkExecuteSQL($dbconn, $sql, __FILE__, __LINE__, false, false);
    if (is_bool($result) && $result==false) {
        return false;
    }
    dzkCloseDB($result);
    return true;

}

/**
 * get available admin panel links
 *
 * @author Mark West
 * @return array array of admin links
 */
function Dizkus_adminapi_getlinks()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    $links = array();
    if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => pnModURL('Dizkus', 'admin', ''), 'text' => __('Start', $dom), 'title' => __('Index page', $dom));
        $links[] = array('url' => pnModURL('Dizkus', 'admin', 'reordertree'), 'text' => __('Edit forum tree', $dom), 'title' => __('Create, edit and delete forum categories and forums, and arrange the tree structure of forums and categories', $dom));
        $links[] = array('url' => pnModURL('Dizkus', 'admin', 'ranks', array('ranktype' => 0)), 'text' => __('Edit user ranks', $dom), 'title' => __('Create, edit and delete user ranks', $dom));
        $links[] = array('url' => pnModURL('Dizkus', 'admin', 'ranks', array('ranktype' => 1)), 'text' => __('Edit honorary ranks', $dom), 'title' => __('Create, edit and delete honorary ranks', $dom));
        $links[] = array('url' => pnModURL('Dizkus', 'admin', 'assignranks'), 'text' => __('Assign honorary rank', $dom), 'title' => __('Assign honorary ranks to users', $dom));
        $links[] = array('url' => pnModURL('Dizkus', 'admin', 'managesubscriptions'), 'text' => __('Manage subscriptions', $dom), 'title' => __('Find users\' topic and forum subscriptions, and delete them', $dom));
        $links[] = array('url' => pnModURL('Dizkus', 'admin', 'syncforums'), 'text' => __('Synchronise data', $dom), 'title' => __('Synchronise Zikula and Dizkus user information, forum index, topics and posts counter', $dom));
        $links[] = array('url' => pnModURL('Dizkus', 'admin', 'preferences'), 'text' => __('Settings', $dom), 'title' => __('Settings to configure various forum-wide options', $dom));
    }
    return $links;
}
