<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Admin extends Zikula_AbstractApi {
    
    /**
     * readcatgories
     * read the categories from database, if cat_id is set, only this one will be read
     *
     * @params $args['cat_id'] int the category id to read (optional)
     * @returns array of category information
     *
     */
    public function readcategories($args)
    {
        $ztables = DBUtil::getTables();
        $catcolumn = $ztables['dizkus_categories_column'];
    
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
    public function updatecategory($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        // copy all entries from $args to $obj that are found in the categories table
        // this prevents possible SQL errors if non existing keys are passed to this function
        $ztables = DBUtil::getTables();
        $obj = array();
        foreach ($args as $key => $arg) {
            if (array_key_exists($key, $ztables['dizkus_categories_column'])) {
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
    public function addcategory($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
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
    public function deletecategory($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        if (isset($args['cat_id'])) {
            // read all the forums in this category
            $forums = $this->readforums(array('cat_id' => $args['cat_id']));
            if (is_array($forums) && count($forums)>0) {
                foreach ($forums as $forum) {
                    // remove all forums in this category
                    ModUtil::apiFunc('Dizkus', 'admin', 'deleteforum',
                                 array('forum_id' => $forum['forum_id'],
                                       'ok'       => 1));
                }  //foreach forum
            }
            // now we can delete the category
            return DBUtil::deleteObject($args, 'dizkus_categories', null, 'cat_id');
        }
    
        return LogUtil::registerError($this->__('Error! The action you wanted to perform was not successful for some reason, maybe because of a problem with what you input. Please check and try again.'));
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
    public function readforums($args=array())
    {
        $permcheck = (isset($args['permcheck'])) ? strtoupper($args['permcheck']): ACCESS_READ;
        if (!empty($permcheck) &&
            ($permcheck <> ACCESS_OVERVIEW) &&
            ($permcheck <> ACCESS_READ) &&
            ($permcheck <> ACCESS_COMMENT) &&
            ($permcheck <> ACCESS_MODERATE) &&
            ($permcheck <> ACCESS_ADMIN) &&
            ($permcheck <> 'NOCHECK')  ) {
            return LogUtil::registerError($this->__('Error! The action you wanted to perform was not successful for some reason, maybe because of a problem with what you input. Please check and try again.'));
        }
    
        $where = '';
        if (isset($args['forum_id'])) {
            $where = "WHERE tbl.forum_id='". (int)DataUtil::formatForStore($args['forum_id']) ."' ";
        } elseif (isset($args['cat_id'])) {
            $where = "WHERE tbl.cat_id='". (int)DataUtil::formatForStore($args['cat_id']) ."' ";
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
    public function readmoderators($args)
    {
        $ztable = DBUtil::getTables();
    
        $sql = 'SELECT u.uname, u.uid
                FROM '.$ztable['users'].' u, '.$ztable['dizkus_forum_mods'].' f
                WHERE f.forum_id = '.DataUtil::formatForStore($args['forum_id']).' AND u.uid = f.user_id
                AND f.user_id<1000000';
    
        $res = DBUtil::executeSQL($sql);
        $colarray = array('uname', 'uid');
        $result1    = DBUtil::marshallObjects($res, $colarray);
    
        $sql = 'SELECT g.name, g.gid
                FROM '.$ztable['groups'].' g, '.$ztable['dizkus_forum_mods'].' f
                WHERE f.forum_id = '.DataUtil::formatForStore($args['forum_id']).' AND g.gid = f.user_id-1000000
                AND f.user_id>1000000';
    
        $res = DBUtil::executeSQL($sql);
        $colarray = array('uname', 'uid');
        $result2  = DBUtil::marshallObjects($res, $colarray);
    
        $mods = array_merge($result1, $result2);
        return $mods;
    }
    
    /**
     * readusers
     *$moderators
     */
    public function readusers($args)
    {
        $ztable = DBUtil::getTables();
        $userscolumn = $ztable['users_column'];
    
        $sql = "SELECT ".$userscolumn['uid'].", ".$userscolumn['uname']."
                FROM ".$ztable['users']."
                WHERE ".$userscolumn['uid']." != 1 ";
    
        foreach($args['moderators'] as $mod) {
            if ($mod['uid']<=1000000) {
                // mod uids > 1000000 are groups
                $sql .= "AND ".$userscolumn['uid']." != '".DataUtil::formatForStore($mod['uid'])."' ";
            }
        }
        $sql .= "ORDER BY ".$userscolumn['uname'];
    
        $res = DBUtil::executeSQL($sql);
        $colarray = array('uid', 'uname');
        $users    = DBUtil::marshallObjects($res, $colarray);
    
        return $users;
    }
    
    /**
     * readgroups
     *$moderators
     */
    public function readgroups($args)
    {
        $ztable = DBUtil::getTables();
        
        // read groups
        $sql = "SELECT g.gid+1000000, g.name
                FROM ".$ztable['groups']." AS g ";
    
        $where_flag = false;
        $group_flag = false;
        foreach ($args['moderators'] as $mod) {
            if ($mod['uid'] > 1000000) {
                // mod uids > 1000000 are groups
                if (!$where_flag) {
                    $sql .= 'WHERE ';
                    $where_flag = true;
                }
                if ($group_flag) {
                    $sql .= ' AND ';
                }
                $sql .= "g.gid != '".DataUtil::formatForStore((int)$mod['uid']-1000000)."' ";
                $group_flag = true;
            }
        }
        $sql .= "ORDER BY g.name";
    
        $res = DBUtil::executeSQL($sql);
        $colarray = array('gid', 'name');
        $groups    = DBUtil::marshallObjects($res, $colarray);
    
        return $groups;
    
    }
    
    /**
     * readranks
     * @params ranktype   
     *
     */
    public function readranks($args)
    {
        // read images
        $path     = ModUtil::getVar('Dizkus', 'url_ranks_images');
        $handle   = opendir($path);
        $filelist = array();
        while ($file = readdir($handle)) {
            if (dzk_isimagefile($path.'/'.$file)) {
                $filelist[] = $file;
            }
        }
        asort($filelist);
    
        $ztables = DBUtil::getTables();
        $rcol = $ztables['dizkus_ranks_column'];
    
        if ($args['ranktype']==0) {
            $orderby = 'ORDER BY ' . $rcol['rank_min'];
        } else {
            $orderby = 'ORDER BY ' . $rcol['rank_title'];
        }
        $ranks = DBUtil::selectObjectArray('dizkus_ranks', 'WHERE ' . $rcol['rank_special'] . '=' . DataUtil::formatForStore($args['ranktype']), $orderby);
    
        if (is_array($ranks)) {
            foreach ($ranks as $cnt => $rank) {
            	$ranks[$cnt]['users'] = ModUtil::apiFunc('Dizkus', 'admin', 'readrankusers',
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
     */
    public function saverank($args)
    {
    	if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        foreach ($args['ranks'] as $rankid => $rank)
        {
            if ($rankid == '-1') {
                $res = DBUtil::insertObject($rank, 'dizkus_ranks', 'rank_id');
            } else {
                $rank['rank_id'] = $rankid;
                if ($rank['rank_delete'] == '1') {
                    $res = DBUtil::deleteObject($rank, 'dizkus_ranks', null, 'rank_id');
                } else {
                    $res = DBUtil::updateObject($rank, 'dizkus_ranks', null, 'rank_id');
                }
            }
        }
    
        return $res;
    }
    
    /**
     * readrankusers
     * read all users that have a certain rank_id
     */
    public function readrankusers($args)
    {
        ModUtil::dbInfoLoad('Settings');
        $ztable = DBUtil::getTables();
        $objcol = $ztable['objectdata_attributes_column'];
        
        $where = $objcol['attribute_name'] . "='dizkus_user_rank' AND " .$objcol['value']."=" . DataUtil::formatForStore($args['rank_id']);
        $users = DBUtil::selectObjectArray('objectdata_attributes', $where, '', -1, -1, 'object_id');

        return array_keys($users);
    }
    
    /**
     * assignranksave
     * setrank array(uid) = rank_id
     */
    public function assignranksave($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        if (is_array($args['setrank'])) {
            $ranksavearray = array();
            foreach($args['setrank'] as $user_id => $rank_id) {
                UserUtil::setVar('dizkus_user_rank', $rank_id, $user_id);
            }
        }
    
        return true;
    }
    
    /**
     * This function should receive $id, $type
     * synchronizes forums/topics/users
     *
     * $id, $type
     */
    public function sync($args)
    {
        $ztable = DBUtil::getTables();
        switch ($args['type'])
        {
            case 'forum':
                $f['forum_id']           = $args['id'];
                $f['forum_last_post_id'] = DBUtil::selectFieldMax('dizkus_posts', 'post_id', 'MAX', 'forum_id='.DataUtil::formatForStore($args['id']));
                $f['forum_posts']        = DBUtil::selectObjectCount('dizkus_posts', 'forum_id='.DataUtil::formatForStore($args['id']));
                $f['forum_topics']       = DBUtil::selectObjectCount('dizkus_topics', 'forum_id='.DataUtil::formatForStore($args['id']));
    
                DBUtil::updateObject($f, 'dizkus_forums', null, 'forum_id');
                break;
            case 'topic':
                $t['topic_id']           = $args['id'];
                $t['topic_last_post_id'] = DBUtil::selectFieldMax('dizkus_posts', 'post_id', 'MAX', 'topic_id='.DataUtil::formatForStore($args['id']));
                $t['topic_replies']      = DBUtil::selectObjectCount('dizkus_posts', 'topic_id='.DataUtil::formatForStore($args['id'])) -1;
    
                DBUtil::updateObject($t, 'dizkus_topics', null, 'topic_id');
                break;
            case 'all forums':
                $forums = $this->readforums();
                foreach ($forums as $forum) {
                    $this->sync(array('id' => $forum['forum_id'], 'type' => 'forum'));
                }
                break;
    
            case 'all topics':
                $topics = DBUtil::selectObjectArray('dizkus_topics');
                foreach ($topics as $topic) {
                    $this->sync(array('id' => $topic['topic_id'], 'type' => 'topic'));
                }
                break;
    
            case 'all posts':
                ModUtil::dbInfoLoad('Settings');
                $tables = DBUtil::getTables();
                
                $objtable    = $tables['objectdata_attributes'];
                $objcolumn   = $tables['objectdata_attributes_column'];
                $poststable  = $tables['dizkus_posts'];
                $postscolumn = $tables['dizkus_posts_column'];
                
                // drop all attributes 'dizkus_user_posts'
                DBUtil::deleteWhere('objectdata_attributes', $objcolumn['attribute_name'] . "='dizkus_user_posts'");
            
                // re-insert from scratch
                $timestring = DataUtil::formatForStore(date('Y-m-d H:i:s'));
                $this_uid   = DataUtil::formatForStore(UserUtil::getVar('uid'));
                $sql = "INSERT INTO " . $objtable . " (" . $objcolumn['attribute_name'] . ",
                                                       " . $objcolumn['object_type'] . ",
                                                       " . $objcolumn['object_id'] . ",
                                                       " . $objcolumn['value'] . ",
                                                       " . $objcolumn['cr_date'] . ",
                                                       " . $objcolumn['cr_uid'] . ",
                                                       " . $objcolumn['lu_date'] . ",
                                                       " . $objcolumn['lu_uid'] . ")
                        SELECT 'dizkus_users_posts',
                               'users',
                               " . $postscolumn['poster_id'] . ",
                               COUNT(" . $postscolumn['poster_id'] . ") as total_posts,
                               '" . $timestring . "',
                               " . $this_uid . ",
                               '" . $timestring . "',
                               " . $this_uid . "
                        FROM " . $poststable . "
                        GROUP BY " . $postscolumn['poster_id'];
                DBUtil::executeSQL($sql);
                break;
            default:
                return LogUtil::registerError('Error! Bad parameter in synchronisation:', null, ModUtil::url('Dizkus', 'admin', 'main'));
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
    public function addforum($args)
    {
        $mods = $args['mods'];
        unset($args['mods']);
    
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN) &&
            !SecurityUtil::checkPermission('Dizkus::CreateForum', $args['cat_id'] . "::", ACCESS_EDIT) ) {
            return LogUtil::registerPermissionError();
        }
    
        $ztable = DBUtil::getTables();
    
        $args['forum_name'] = strip_tags($args['forum_name']);
        if (empty($args['forum_name'])) {
            return LogUtil::registerError($this->__('Error! You did not enter all the required information in the form. Did you assign at least one moderator? Please correct your entries and try again.'));
        }
    
        if (!$args['forum_desc']) {
            $args['forum_desc'] = '';
        }
        $args['forum_desc'] = nl2br($args['forum_desc']); // to be fixed ASAP
        //$desc = DataUtil::formatForStore($desc);
        //$forum_name = DataUtil::formatForStore($forum_name);
        //$cat_id = DataUtil::formatForStore($cat_id);
    
        $highest = DBUtil::selectFieldMax('dizkus_forums', 'forum_order', 'MAX', 'cat_id='.DataUtil::formatForStore($args['cat_id']));
        $highest++;
        
        $newforum = DBUtil::insertObject($args, 'dizkus_forums', 'forum_id');
    
        $count = 0;
        if (!is_null($mods) && is_array($mods) && count($mods)>0) {
        	foreach($mods as $singlemod) {
                $newmod = array('forum_id' => $newforum['forum_id'],
                                'user_id'  => $singlemod); // [0]??
                $newmod = DBUtil::insertObject($newmod, 'dizkus_forum_mods'); // todo: add index field to forum_mods table!!!!
            }
        }
    
        if (isset($args['forum_order']) && is_numeric($args['forum_order'])) {
            ModUtil::apiFunc('Dizkus', 'admin', 'reorderforumssave',
                         array('cat_id'    => $args['cat_id'],
                               'forum_id'  => $newforum['forum_id'],
                               'neworder'  => $args['forum_order'],
                               'oldorder'  => $highest));
        }
    
        // Let any hooks know that we have created a new item.
        //ModUtil::callHooks('item', 'create', $newforum['forum_id'], array('module' => 'Dizkus',
        //                                                              'forum_id' => $newforum['forum_id']));
    
        return $newforum['forum_id'];
    }
    
    /**
     * editforum
     */
    // $forum_id, $forum_name, $desc, $cat_id, $mods, $rem_mods)
    public function editforum($args)
    {
        $mods = $args['mods'];
        $rem_mods = $args['rem_mods'];
        unset($args['mods']);
        unset($args['rem_mods']);
    
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        if (empty($args['forum_pop3_password'])) {
            // pop3_password is empty - do not save it
            unset($args['forum_pop3_password']);
        }
    
        if (empty($args['forum_pop3_pnpassword'])) {
            // pop3_pnpassword is empty - do not save it
            unset($args['forum_pop3_pnpassword']);
        }
    
        DBUtil::updateObject($args, 'dizkus_forums', null, 'forum_id');
        
        if (isset($mods) && !empty($mods)) {
            $recentmods = ModUtil::apiFunc('Dizkus', 'admin', 'readmoderators',
                                       array('forum_id' => $args['forum_id']));
            foreach ($mods as $mod) {
                $newmod = array('forum_id' => $args['forum_id'],
                                'user_id'  => $mod);
                DBUtil::insertObject($newmod, 'dizkus_forum_mods');     
            }
        }
    
        if (isset($rem_mods) && !empty($rem_mods)) {
            foreach ($rem_mods as $mod) {
                DBUtil::deleteWhere('dizkus_forum_mods', 'forum_id = '.DataUtil::formatForStore($args['forum_id']).' AND user_id = '.DataUtil::formatForStore($mod));
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
    public function deleteforum($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
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
        return DBUtil::deleteWhere('dizkus_posts', $whereforumid);
    }
    
    /**
     * get_pntopics
     */
    public function get_pntopics()
    {
        return false;
    }
    
    /**
     * get available admin panel links
     *
     * @author Mark West
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();
        if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN))
        {
            $links[] = array(
                'url'   => ModUtil::url('Dizkus', 'admin', 'reordertree'),
                'text'  => $this->__('Edit forum tree'),
                'title' => $this->__('Create, delete, edit and re-order categories and forums'),
                'links' => array(
                    array(
                        'url'   => ModUtil::url('Dizkus', 'admin', 'reordertree'),
                        'text'  => $this->__('Edit forum tree'),
                        'title' => $this->__('Create, delete, edit and re-order categories and forums')),
                    array(
                        'url' => ModUtil::url('Dizkus', 'admin', 'subforums'),
                        'text' => $this->__('Sub forums')),
                    array(
                        'url' => ModUtil::url('Dizkus', 'admin', 'syncforums'),
                        'text' => $this->__('Synchronize forum/topic index'),
                        'title' => $this->__('Synchronize forum and topic indexes to fix any discrepancies that might exist')
                )),
                'class' => 'z-icon-es-options',
            );
            $links[] = array('url' => ModUtil::url('Dizkus', 'admin', 'ranks', array('ranktype' => 0)),
                    'text' => $this->__('Edit user ranks'),
                    'class' => 'z-icon-es-group',
                    'title' => $this->__('Create, edit and delete user rankings acquired through the number of a user\'s posts'),
                    'links' => array(
                        array('url' => ModUtil::url('Dizkus', 'admin', 'ranks', array('ranktype' => 0)),
                              'text' => $this->__('Edit user ranks'),
                              'title' => $this->__('Create, edit and delete user rankings acquired through the number of a user\'s posts')),
                        array('url' => ModUtil::url('Dizkus', 'admin', 'ranks', array('ranktype' => 1)),
                              'text' => $this->__('Edit honorary ranks'),
                              'title' => $this->__('Create, delete and edit special ranks for particular users')),
                        array('url' => ModUtil::url('Dizkus', 'admin', 'assignranks'),
                              'text' => $this->__('Assign honorary rank'),
                              'title' => $this->__('Assign honorary user ranks to users'))
                    ));
            $links[] = array('url' => ModUtil::url('Dizkus', 'admin', 'managesubscriptions'),
                             'text' => $this->__('Manage subscriptions'),
                             'title' => $this->__('Remove a user\'s topic and forum subscriptions'),
                             'class' => 'z-icon-es-mail');
            $links[] = array(
                'url' => ModUtil::url('Dizkus', 'admin', 'preferences'),
                'text' => $this->__('Settings'),
                'title' => $this->__('Edit general forum-wide settings'),
                'class' => 'z-icon-es-config',
            );
        }
    
        return $links;
    }

}

/**
 * helper function
 */
function _get_rank_users($u)
{
    return $u['user_id'];
}
    
