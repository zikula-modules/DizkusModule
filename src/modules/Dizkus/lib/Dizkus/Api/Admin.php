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
                'url'   => ModUtil::url('Dizkus', 'admin', 'tree'),
                'text'  => $this->__('Edit forum tree'),
                'title' => $this->__('Create, delete, edit and re-order categories and forums'),
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
                            'url'   => ModUtil::url('Dizkus', 'admin', 'manageSubscriptions'),
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