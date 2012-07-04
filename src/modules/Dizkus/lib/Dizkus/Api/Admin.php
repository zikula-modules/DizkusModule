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
     * read catgory
     * 
     * Read the categories from database, if cat_id is set, only this one will be read.
     *
     * @param int $cat_id The category id to read (optional).
     *
     * @return array of category information
     *
     */
    public function readcategory($cat_id)
    {
        return $this->entityManager->find('Dizkus_Entity_Categories', $cat_id)->toArray();
    }
    
    
    
    /**
     * updatecategory
     *
     * Update a category in database, either cat_title or cat_order.
    
     * @param string $args['cat_title'] Category title.
     * @param int    $args['cat_id']    Category id.
     *
     * @return boolean
     */
    public function updatecategory($args)
    {        
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        if (!isset($args['cat_id'])) {
            return LogUtil::registerArgsError();
        }
        
        $category = $this->entityManager->find('Dizkus_Entity_Categories', $args['cat_id']);
        $category->merge($args);
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    
        return true;
    }
    
    /**
     * addcategory
     *
     * Adds a new category.
     *
     * @params string $args['cat_title'] The categories title.
     *
     * @return int|boolean
     */
    public function addcategory($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        if (empty($args['cat_title'])) {
            return false;
        }
        
        $count = count($this->entityManager->getRepository('Dizkus_Entity_Categories')->findAll());
        $args['cat_order'] = $count+1;
        $category = new Dizkus_Entity_Categories();            
        $category->merge($args);
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        return $category->getcat_id();
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
            $category = $this->entityManager->find('Dizkus_Entity_Categories', $args['cat_id']);
            $this->entityManager->remove($category);
            $this->entityManager->flush();
            return true;
            
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
     * 
     * @params ranktype
     * 
     * @return array
     *
     */
    public function readranks($args)
    {
        // read images
        $path     = $this->getVar('url_ranks_images');
        $handle   = opendir($path);
        $filelist = array();
        while ($file = readdir($handle)) {
            if (dzk_isimagefile($path.'/'.$file)) {
                $filelist[] = $file;
            }
        }
        asort($filelist);

        if ($args['ranktype'] == 0) {
            $orderby = 'rank_min';
        } else {
            $orderby = 'rank_title';
        }
                
        $ranks = $this->entityManager->getRepository('Dizkus_Entity_Ranks')
                                     ->findBy(array('rank_special' => $args['ranktype']), array($orderby => 'ASC'));
        
        return array($filelist, $ranks);
    }
    
    /**
     * saverank
     * 
     * @params rank_special, rank_id, rank_min, rank_max, rank_image, rank_id
     */
    public function saverank($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        foreach ($args['ranks'] as $rankid => $rank) {
            if ($rankid == '-1') {                
                $r = new Dizkus_Entity_Ranks();
                $r->merge($rank);
                $this->entityManager->persist($r);
            } else {
                $r = $this->entityManager->find('Dizkus_Entity_Ranks', $rankid);
                
                if ($rank['rank_delete'] == '1') {
                    $this->entityManager->remove($r);
                } else {
                    $r->merge($rank);
                    $this->entityManager->persist($r);
                }
            }
        }
        $this->entityManager->flush();

        return true;
    }
    
    /**
     * assignranksave
     * 
     * setrank array(uid) = rank_id
     */
    public function assignranksave($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        if (is_array($args['setrank'])) {
            $ranksavearray = array();
            foreach ($args['setrank'] as $user_id => $rank_id) {
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
            foreach ($mods as $singlemod) {
                $newmod = array(
                            'forum_id' => $newforum['forum_id'],
                            'user_id'  => $singlemod
                          ); // [0]??
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
     * Delete  a forum
     *
     * @params $args['forum_id']
     *
     * @return boolean
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
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();
        if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url'   => ModUtil::url('Dizkus', 'admin', 'reordertree'),
                'text'  => $this->__('Edit forum tree'),
                'title' => $this->__('Create, delete, edit and re-order categories and forums'),
                'links' => array(
                    array(
                        'url'   => ModUtil::url('Dizkus', 'admin', 'reordertree'),
                        'text'  => $this->__('Edit forum tree'),
                        'title' => $this->__('Create, delete, edit and re-order categories and forums')
                    ),
                    array(
                        'url'   => ModUtil::url('Dizkus', 'admin', 'tree'),
                        'text'  => $this->__('New forum tree'),
                        'title' => $this->__('Create, delete, edit and re-order the forum tree')
                    ),
                    array(
                        'url'   => ModUtil::url('Dizkus', 'admin', 'syncforums'),
                        'text'  => $this->__('Synchronize forum/topic index'),
                        'title' => $this->__('Synchronize forum and topic indexes to fix any discrepancies that might exist'
                    )
                )),
                'class' => 'z-icon-es-options',
            );
            $links[] = array('url' => ModUtil::url('Dizkus', 'admin', 'ranks', array('ranktype' => 0)),
                    'text' => $this->__('Edit user ranks'),
                    'class' => 'z-icon-es-group',
                    'title' => $this->__('Create, edit and delete user rankings acquired through the number of a user\'s posts'),
                    'links' => array(
                        array(
                            'url'   => ModUtil::url('Dizkus', 'admin', 'ranks', array('ranktype' => 0)),
                            'text'  => $this->__('Edit user ranks'),
                            'title' => $this->__('Create, edit and delete user rankings acquired through the number of a user\'s posts')),
                        array(
                            'url'   => ModUtil::url('Dizkus', 'admin', 'ranks', array('ranktype' => 1)),
                            'text'  => $this->__('Edit honorary ranks'),
                            'title' => $this->__('Create, delete and edit special ranks for particular users')
                        ),
                        array(
                            'url'   => ModUtil::url('Dizkus', 'admin', 'assignranks'),
                            'text'  => $this->__('Assign honorary rank'),
                            'title' => $this->__('Assign honorary user ranks to users'))
                        )
                    );
            $links[] = array(
                            'url'   => ModUtil::url('Dizkus', 'admin', 'managesubscriptions'),
                            'text'  => $this->__('Manage subscriptions'),
                            'title' => $this->__('Remove a user\'s topic and forum subscriptions'),
                            'class' => 'z-icon-es-mail'
                       );
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