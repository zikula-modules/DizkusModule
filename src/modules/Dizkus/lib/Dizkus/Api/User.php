<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_User extends Zikula_AbstractApi {

    /**
     * Instance of Zikula_View.
     *
     * @var Zikula_View
     */
    protected $view;

    /**
     * Initialize.
     *
     * @return void
     */
    protected function initialize()
    {
        $this->setView();
    }

    /**
     * Set view property.
     *
     * @param Zikula_View $view Default null means new Render instance for this module name.
     *
     * @return Zikula_AbstractController
     */
    protected function setView(Zikula_View $view = null)
    {
        if (is_null($view)) {
            $view = Zikula_View::getInstance($this->getName());
        }

        $this->view = $view;
        return $this;
    }
    
    /**
     * get_userdata_from_id
     *
     * @param array $args The arguments array.
     *
     * @deprecated since 4.0.0
     *
     * @return array of userdata information
     */
    public function get_userdata_from_id($args)
    {
        return ModUtil::apiFunc($this->name, 'UserData', 'getFromId', $args['userid']);
    }
    
    /**
     * Returns the total number of posts in the whole system, a forum, or a topic
     * Also can return the number of users on the system.
     *
     * @params $args['id'] int the id, depends on 'type' parameter
     * @params $args['type'] string, defines the id parameter
     * @returns int (depending on type and id)
     */
    public function boardstats($args)
    {
        $id   = isset($args['id']) ? $args['id'] : null;
        $type = isset($args['type']) ? $args['type'] : null;
    
        static $cache = array();
    
        switch ($type)
        {
            case 'all':
            case 'allposts':
                if (!isset($cache[$type])){
                    $cache[$type] = $this->countEntity('Posts');
                }
                
                return $cache[$type];
                break;
    
            case 'category':
                if (!isset($cache[$type])){
                   $cache[$type] = $this->countEntity('Categories');
                }
                
                return  $cache[$type];
                break;
    
            case 'forum':
                if (!isset($cache[$type])){
                   $cache[$type] = $this->countEntity('Forums');
                }
                
                return $cache[$type];
                break;
    
            case 'topic':
                if (!isset($cache[$type][$id])){
                   $cache[$type][$id] = $this->countEntity('Posts', 'topic_id', $id);
                }
                
                return  $cache[$type][$id];
                break;
    
            case 'forumposts':
                if (!isset($cache[$type][$id])){
                   $cache[$type][$id] = $this->countEntity('Posts', 'forum_id', $id);
                }
                
                return  $cache[$type][$id];
                break;
    
            case 'forumtopics':
                if (!isset($cache[$type][$id])){
                   $cache[$type][$id] = $this->countEntity('Topics', 'forum_id', $id);
                }
                
                return  $cache[$type][$id];
                break;
    
            case 'alltopics':
                if (!isset($cache[$type])){
                   $cache[$type] = $this->countEntity('Topics');
                }
                
                return  $cache[$type];
                break;
    
            case 'allmembers':
                if (!isset($cache[$type])){
                   $cache[$type] = count(UserUtil::getUsers());
                }
                
                return  $cache[$type];
                break;
    
            case 'lastmember':
            case 'lastuser':
                if (!isset($cache[$type])){
                    $res = DBUtil::selectObjectArray('users', null, 'uid DESC', 1, 1);
                    $cache[$type] = $res[0]['uname'];
                }
                
                return  $cache[$type];
                break;
    
            default:
                return LogUtil::registerError($this->__("Error! Wrong parameters in boardstats()."), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    }
    
    private function countEntity($entityname, $where = null, $parameter = null) {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(a)')
           ->from('Dizkus_Entity_'.$entityname, 'a');
        if (isset($where) && isset($parameter)) {
            $qb->andWhere('a.'.$where.' = :parameter')
               ->setParameter('parameter', $parameter);
            
        }
        return (int)$qb->getQuery()->getSingleScalarResult();
        
    }

    
    /**
     * Returns an array of all the moderators of a forum
     *
     * @param array $args The arguments array.
     *
     * @deprecated since 4.0.0
     *
     * @return array containing the pn_uid as index and the users name as value
     */
    public function get_moderators($args)
    {       
        return ModUtil::apiFunc($this->name, 'Moderators', 'get', $args);
    }
    
    /**
     * setcookies
     * 
     * reads the cookie, updates it and returns the last visit date in readable (%Y-%m-%d %H:%M)
     * and unix time format
     *
     * @params none
     * @returns array of (readable last visits data, unix time last visit date)
     *
     */
    public function setcookies()
    {
        /**
         * set last visit cookies and get last visit time
         * set LastVisit cookie, which always gets the current time and lasts one year
         */
        $path = System::getBaseUri();
        if (empty($path)) {
            $path = '/';
        } elseif (substr($path, -1, 1) != '/') {
            $path .= '/';
        }
    
        setcookie('DizkusLastVisit', time(), time()+31536000, $path);
    
        if (!isset($_COOKIE['DizkusLastVisitTemp'])){
            $temptime = isset($_COOKIE['DizkusLastVisit']) ? $_COOKIE['DizkusLastVisit'] : '';
        } else {
            $temptime = $_COOKIE['DizkusLastVisitTemp'];
        }
    
        if (empty($temptime)) {
            // check for old Cookies
            // TO-DO: remove this code in 3.2 or a bit later
            if (!isset($_COOKIE['phpBBLastVisitTemp'])){
                $temptime = isset($_COOKIE['phpBBLastVisit']) ? $_COOKIE['phpBBLastVisit'] : '';
            } else {
                $temptime = $_COOKIE['phpBBLastVisitTemp'];
            }
        }
    
        if (empty($temptime)) {
            $temptime = 0;
        }
    
        // set LastVisitTemp cookie, which only gets the time from the LastVisit and lasts for 30 min
        setcookie('DizkusLastVisitTemp', $temptime, time()+1800, $path);
    
        // set vars for all scripts
        $last_visit = DateUtil::formatDatetime($temptime, '%Y-%m-%d %H:%M:%S');
    
        return array($last_visit, $temptime);
    }
     
    
    // RNG
    function cmp_forumtopicsort($a, $b)
    {
        return strcmp($a['post_time_unix'], $b['post_time_unix']) * -1;
    }
     
    
    /**
     * get_viewip_data
     *
     * @param array $args The argument array.
     *        int $args['post_id] The postings id.
     *
     * @return array with informstion.
     */
    public function get_viewip_data($args)
    {
        $ztable = DBUtil::getTables();
    
        $viewip['poster_ip'] = DBUtil::selectField('dizkus_posts', 'poster_ip', 'post_id='.DataUtil::formatForStore($args['post_id']));
        $viewip['poster_host'] = gethostbyaddr($viewip['poster_ip']);
    
        $sql = "SELECT uid, uname, count(*) AS postcount
                FROM ".$ztable['dizkus_posts']." p, ".$ztable['users']." u
                WHERE poster_ip='".DataUtil::formatForStore($viewip['poster_ip'])."' && p.poster_id = u.uid
                GROUP BY uid";
        $res       = DBUtil::executeSQL($sql);
        $colarray  = array('uid', 'uname', 'postcount');
        $viewip['users'] = DBUtil::marshallObjects($res, $colarray);
    
        return $viewip;
    }
    
   
    /**
     * readuserforums
     * 
     * reads all forums the recent users is allowed to see
     *
     * @params $args['cat_id'] int a category id (optional, if set, only reads the forums in this category)
     * @params $args['forum_id'] int a forums id (optional, if set, only reads this category
     * @returns array of forums, maybe empty
     */
    public function readuserforums($args)
    {
        $where = '';
        if (isset($args['forum_id'])) {
            $where = 'WHERE tbl.forum_id=' . DataUtil::formatForStore($args['forum_id']) . ' ';
        } elseif (isset($args['cat_id'])) {
            $where = 'WHERE a.cat_id=' . DataUtil::formatForStore($args['cat_id']) . ' ';
        }
    
        $joinInfo = array();
        $joinInfo[] = array('join_table'          =>  'dizkus_categories',
                            'join_field'          =>  'cat_title',
                            'object_field_name'   =>  'cat_title',
                            'compare_field_table' =>  'cat_id',
                            'compare_field_join'  =>  'cat_id');
    
        $permFilter = array();
        $permFilter[]  = array ('component_left'   =>  'Dizkus',
                                'component_middle' =>  '',
                                'component_right'  =>  '',
                                'instance_left'    =>  'cat_id',
                                'instance_middle'  =>  'forum_id',
                                'instance_right'   =>  '',
                                'level'            =>  ACCESS_READ);
    
        // retrieve the admin module object array
        $forums = DBUtil::selectExpandedObjectArray('dizkus_forums', $joinInfo, $where, 'forum_id', -1, -1, 'forum_id', $permFilter);
    
        if ($forums === false) {
            return LogUtil::registerError($this->__('Error! The forum or topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        if (isset($args['forum_id']) && isset($forums[$args['forum_id']])) {
            return $forums[$args['forum_id']];
        }
    
        return $forums;
    }
    
    
    /**
     * Sending notify e-mail to users subscribed to the topic of the forum
     *
     * @params $args['topic_id'] int the topics id
     * @params $args['poster_id'] int the users uid
     * @params $args['post_message'] string the text
     * @params $args['type'] int, 0=new message, 2=reply
     * @returns void
     */
    public function notify_by_email($args)
    {

        $ztable = DBUtil::getTables();
    
        setlocale (LC_TIME, System::getVar('locale'));
        $modinfo = ModUtil::getInfo(ModUtil::getIDFromName(ModUtil::getName()));
    
        // generate the mailheader info
        $email_from = ModUtil::getVar('Dizkus', 'email_from');
        if ($email_from == '') {
            // nothing in forumwide-settings, use PN adminmail
            $email_from = System::getVar('adminmail');
        }
    
        // normal notification
        $sql = 'SELECT t.topic_title,
                       t.topic_poster,
                       t.topic_time,
                       f.cat_id,
                       c.cat_title,
                       f.forum_name,
                       f.forum_id
                FROM  '.$ztable['dizkus_topics'].' t
                LEFT JOIN '.$ztable['dizkus_forums'].' f ON t.forum_id = f.forum_id
                LEFT JOIN '.$ztable['dizkus_categories'].' c ON f.cat_id = c.cat_id
                WHERE t.topic_id = '.(int)DataUtil::formatForStore($args['topic_id']);
    
        $res = DBUtil::executeSQL($sql);
        $colarray = array('topic_title', 'topic_poster', 'topic_time', 'cat_id', 'cat_title', 'forum_name', 'forum_id');
        $myrow    = DBUtil::marshallObjects($res, $colarray);
    
        if (!is_array($myrow)) {
            // no results - topic does not exist
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        $topic_unixtime= strtotime($myrow[0]['topic_time']);
        $DU = new DateUtil();
        $topic_time_ml = $DU->formatDatetime($topic_unixtime, 'datetimebrief');
    
        $poster_name = UserUtil::getVar('uname',$args['poster_id']);
    
        $forum_id      = $myrow[0]['forum_id'];
        $forum_name    = $myrow[0]['forum_name'];
        $category_name = $myrow[0]['cat_title'];
        $topic_subject = $myrow[0]['topic_title'];
    
        $subject = ($args['type'] == 2) ? 'Re: ' : '';
        $subject .= $category_name . ' :: ' . $forum_name . ' :: ' . $topic_subject;
    
        // we do not want to notify the sender = the recent user
        $thisuser = UserUtil::getVar('uid');
        // anonymous does not have uid, so we need a sql to exclude real users
        $fs_wherenotuser = '';
        $ts_wherenotuser = '';
        if (!empty($thisuser)) {
            $fs_wherenotuser = ' AND fs.user_id <> ' . DataUtil::formatForStore($thisuser);
            $ts_wherenotuser = ' AND ts.user_id <> ' . DataUtil::formatForStore($thisuser);
        }
    
        //  get list of forum subscribers with non-empty emails
        $sql = 'SELECT DISTINCT fs.user_id,
                                c.cat_id
                FROM ' . $ztable['dizkus_subscription'] . ' as fs,
                     ' . $ztable['dizkus_forums'] . ' as f,
                     ' . $ztable['dizkus_categories'] . ' as c
                WHERE fs.forum_id='.DataUtil::formatForStore($forum_id).'
                  ' . $fs_wherenotuser . '
                  AND f.forum_id = fs.forum_id
                  AND c.cat_id = f.cat_id';
    
        $res = DBUtil::executeSQL($sql);
        $colarray = array('uid', 'cat_id');
        $result   = DBUtil::marshallObjects($res, $colarray);
    
        $recipients = array();
        // check if list is empty - then do nothing
        // we create an array of recipients here
        if (is_array($result) && !empty($result)) {
            foreach ($result as $resline) {
                // check permissions
                if (SecurityUtil::checkPermission('Dizkus::', $resline['cat_id'].':'.$forum_id.':', ACCESS_READ, $resline['uid'])) {
                    $emailaddress = UserUtil::getVar('email', $resline['uid']);
                    if (empty($emailaddress)) {
                        continue;
                    }
                    $email['name']    = UserUtil::getVar('uname', $resline['uid']);
                    $email['address'] = $emailaddress;
                    $email['uid']     = $resline['uid'];
                    $recipients[$email['name']] = $email;
                }
            }
        }
    
        //  get list of topic_subscribers with non-empty emails
        $sql = 'SELECT DISTINCT ts.user_id,
                                c.cat_id,
                                f.forum_id
                FROM ' . $ztable['dizkus_topic_subscription'] . ' as ts,
                     ' . $ztable['dizkus_forums'] . ' as f,
                     ' . $ztable['dizkus_categories'] . ' as c,
                     ' . $ztable['dizkus_topics'] . ' as t
                WHERE ts.topic_id='.DataUtil::formatForStore($args['topic_id']).'
                  ' . $ts_wherenotuser . '
                  AND t.topic_id = ts.topic_id
                  AND f.forum_id = t.forum_id
                  AND c.cat_id = f.cat_id';
    
        $res = DBUtil::executeSQL($sql);
        $colarray = array('uid', 'cat_id', 'forum_id');
        $result   = DBUtil::marshallObjects($res, $colarray);
    
        if (is_array($result) && !empty($result)) {
            foreach ($result as $resline) {
                // check permissions
                if (SecurityUtil::checkPermission('Dizkus::', $resline['cat_id'] . ':' . $resline['forum_id'] . ':', ACCESS_READ, $resline['uid'])) {
                    $emailaddress = UserUtil::getVar('email', $resline['uid']);
                    if (empty($emailaddress)) {
                        continue;
                    }
                    $email['name']    = UserUtil::getVar('uname', $resline['uid']);
                    $email['address'] = $emailaddress;
                    $email['uid']     = $resline['uid'];
                    $recipients[$email['name']] = $email;
                }
            }
        }
    
        if (count($recipients) > 0) {
            $sitename = System::getVar('sitename');
        
            $this->view->assign('sitename', $sitename);
            $this->view->assign('category_name', $category_name);
            $this->view->assign('forum_name', $forum_name);
            $this->view->assign('topic_subject', $topic_subject);
            $this->view->assign('poster_name', $poster_name);
            $this->view->assign('topic_time_ml', $topic_time_ml);
            $this->view->assign('post_message', $args['post_message']);
            $this->view->assign('topic_id', $args['topic_id']);
            $this->view->assign('forum_id', $forum_id);
            $this->view->assign('reply_url', ModUtil::url('Dizkus', 'post', 'reply', array('topic' => $args['topic_id'], 'forum' => $forum_id), null, null, true));
            $this->view->assign('topic_url', ModUtil::url('Dizkus', 'topic', 'viewtopic', array('topic' => $args['topic_id']), null, null, true));
            $this->view->assign('subscription_url', ModUtil::url('Dizkus', 'user', 'prefs', array(), null, null, true));
            $this->view->assign('base_url', System::getBaseUrl());
            $message = $this->view->fetch('mail/notifyuser.txt');
          
            foreach ($recipients as $subscriber) {
                // integrate contactlist's ignorelist here
                $ignorelist_setting = ModUtil::apiFunc('Dizkus','user','get_settings_ignorelist',array('uid' => $subscriber['uid']));
                if (ModUtil::available('ContactList') && 
                    (in_array($ignorelist_setting, array('medium', 'strict'))) && 
                    ModUtil::apiFunc('ContactList', 'user', 'isIgnored', array('uid' => $subscriber['uid'], 'iuid' => UserUtil::getVar('uid')))) {
                    $send = false;
                } else {
                    $send = true;
                }
                if ($send) {
                    $uid = UserUtil::getVar('uid');
                    $args = array( 'fromname'    => $sitename,
                                   'fromaddress' => $email_from,
                                   'toname'      => $subscriber['name'],
                                   'toaddress'   => $subscriber['address'],
                                   'subject'     => $subject,
                                   'body'        => $message,
                                   'headers'     => array('X-UserID: ' . md5($uid),
                                                          'X-Mailer: Dizkus v' . $modinfo['version'],
                                                          'X-DizkusTopicID: ' . $args['topic_id']));
                    ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $args);
                }
            }
        }
    
        return true;
    }
    
   
   
    
    /**
     * getfavorites
     * return the list of favorite forums for this user
     *
     * @params $args['user_id'] -Optional- the user id of the person who we want the favorites for
     * @params $args['last_visit'] timestamp date of last visit
     * @returns array of categories with an array of forums in the catgories
     *
     */
    public function getfavorites($args)
    {
        static $tree;
    
        // if we have already gone through this once then don't do it again
        // if we have a favorites block displayed and are looking at the
        // forums this will get called twice.
        if (isset($tree)) {
            return $tree;
        }
    
        // lets get all the forums just like we would a normal display
        // we'll figure out which ones aren't needed further down.
        $tree = ModUtil::apiFunc('Dizkus', 'category', 'readcategorytree', array('last_visit' => $args['last_visit'] ));
    
        // if they are anonymous they can't have favorites
        if (!UserUtil::isLoggedIn()) {
            return $tree;
        }
    
        if (!isset($args['user_id'])) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }
    
        $ztable = DBUtil::getTables();
        $objarray = DBUtil::selectObjectArray('dizkus_forum_favorites', $ztable['dizkus_forum_favorites_column']['user_id'] . '=' . (int)DataUtil::formatForStore($args['user_id']));
        $favorites = array_map(array($this,'_get_favorites'), $objarray);
    
        // categoryCount is needed since the categories aren't stored as numerical
        // indexes.  They are stored as associative arrays.
        $categoryCount=0;
        // loop through all the forums and delete all forums that aren't part of
        // the favorites.
        $deleteMe = array();
        foreach ($tree as $categoryIndex => $category)
        {
            // $count is needed because the index changes as we splice the array
            // but the foreach is working on a copy of the array so the $forumIndex
            // value will point to non-existent elements in the modified array.
            $count = 0;
            foreach ($category['forums'] as $forumIndex => $forum)
            {
                // if this isn't one of our favorites then we need to remove it
                if (!in_array((int)$forum['forum_id'], $favorites, true)){
                    // remove the forum that isn't one of the favorites
                    array_splice($tree[$categoryIndex]['forums'], ($forumIndex - $count) , 1);
                    // increment $count because we will need to subtract this number
                    // from the index the next time around since this many entries\
                    // has been removed from the original array.
                    $count++;
                }
            }
            // lets see if the category is empty.  If it is we don't want to
            // display it in the favorites
            if (count($tree[$categoryIndex]['forums']) === 0) {
                $deleteMe[] = $categoryCount;
            }
            // increase the index number to keep track of where we are in the array
            $categoryCount++;
        }
    
        // reverse the order so we don't need to do all the crazy subtractions
        // that we had to do above
        $deleteMe = array_reverse($deleteMe);
        foreach ($deleteMe as $category) {
            // remove the empyt category from the array
            array_splice($tree, $category , 1);
        }
    
        // return the modified array
        return $tree;
    }
    
    /**
     * get_favorite_status
     *
     * read the flag from the users table that indicates the users last choice: show all forum (0) or favorites only (1)
     * @params $args['user_id'] int the users id
     * @returns boolean
     *
     */
    public function get_favorite_status($args)
    {
        if (!isset($args['user_id'])) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }
    
        //$obj = DBUtil::selectObjectByID('dizkus__users', $args['user_id'], 'user_id', null, null, null, false);
    
        $fav_status = (int)UserUtil::getVar('dizkus_user_favorites', $args['user_id']);
        return ($fav_status == 1) ? true : false;
    }
    
    /**
     * change_favorite_status
     *
     * changes the flag in the users table that indicates the users last choice: show all forum (0) or favorites only (1)
     * @params $args['user_id'] int the users id
     * @returns 0 or 1
     *
     */
    public function change_favorite_status($args)
    {
        if (!isset($args['user_id'])) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }
    
        $recentstatus = $this->get_favorite_status(array('user_id' => $args['user_id']));
        $user_favorites = ($recentstatus==true) ? 0 : 1;
        UserUtil::setVar('dizkus_user_favorites', $user_favorites, $args['user_id']);
        // force reload from db
        UserUtil::getVars($args['user_id'], true);
        //DBUtil::updateObject($args, 'dizkus__users', '', 'user_id');
    
        return (bool)$user_favorites;
    }
    
    /**
     * get_user_post_order
     * Determines the users desired post order for topics.
     * Either Newest First or Oldest First
     * Returns 'ASC' (0) or 'DESC' (1) on success, false on failure.
     *
     * @params user_id - The user id of the person who's order we
     *                  are trying to determine
     * @returns string on success, false on failure
     */
    public function get_user_post_order($args = array())
    {
        $loggedIn = UserUtil::isLoggedIn();
    
        // if we are passed the user_id then lets use it
        if (isset($args['user_id'])) {
            // we got passed the id but it is the anonymous user
            // and the user isn't logged in, so we return the default order.
            // We use this check because we may want to call this function
            // from another module or function as an admin, moderator, etc
            // so the logged in user may not be the person we want the info about.
            if ($args['user_id'] < 2 || !$loggedIn) {
                return ModUtil::getVar('Dizkus', 'post_sort_order');
            }
        } else {
            // we didn't get a user_id passed into the function so if
            // the user is logged in then lets use their id.  If not
            // then return th default order.
            if ($loggedIn) {
                $args['user_id'] = UserUtil::getVar('uid');
            } else {
                return ModUtil::getVar('Dizkus', 'post_sort_order');
            }
        }
    
        //$obj = DBUtil::selectObjectByID('dizkus__users', $args['user_id'], 'user_id', null, null, null, false);
        $post_order = (UserUtil::getVar('dizkus_user_post_order', $args['user_id']) == 1) ? 'DESC' : 'ASC';
        return $post_order;
    }
    
    /**
     * change_user_post_order
     *
     * changes the flag in the users table that indicates the users preferred post order: Oldest First (0) or Newest First (1)
     * @params $args['user_id'] int the users id
     * @returns bool - true on success, false on failure
     *
     */
    public function change_user_post_order($args = array())
    {
        // if we didn't get a user_id and the user isn't logged in then
        // return false because there is no database entry to update
        if (!isset($args['user_id']) && UserUtil::isLoggedIn()) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }
    
        $post_order = $this->get_user_post_order();
        $new_post_order = ($post_order == 'DESC') ? 0 : 1; // new value, not recent!
    
        UserUtil::setVar('dizkus_user_post_order', $new_post_order, $args['user_id']);
        // force reload of data from db
        UserUtil::getVars($args['user_id'], true);
        return true;
    }
    
   
    

    /**
     * cron
     *
     * @params $args['forum'] array with forum information
     * @params $args['force'] boolean if true force connection no matter of active setting or interval
     * @params $args['debug'] boolean indicates debug mode on/off
     * @returns void
     */
    public function mailcron($args)
    {
        if (ModUtil::getVar('Dizkus', 'm2f_enabled') <> 'yes') {
            return;
        }
    
        $force = (isset($args['force'])) ? (boolean)$args['force'] : false;
        $forum = $args['forum'];
    
        include_once 'modules/Dizkus/lib/vendor/pop3.php';
        if ( (($forum['pop3_active'] == 1) && ($forum['pop3_last_connect'] <= time()-($forum['pop3_interval']*60)) ) || ($force == true) ) {
            mailcronecho('found active: ' . $forum['forum_id'] . ' = ' . $forum['forum_name'] . "\n", $args['debug']);
            // get new mails for this forum
            $pop3 = new pop3_class;
            $pop3->hostname = $forum['pop3_server'];
            $pop3->port     = $forum['pop3_port'];
            $error = '';
    
            // open connection to pop3 server
            if (($error = $pop3->Open()) == '') {
                mailcronecho("Connected to the POP3 server '".$pop3->hostname."'.\n", $args['debug']);
                // login to pop3 server
                if (($error = $pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0)) == '') {
                    mailcronecho( "User '" . $forum['pop3_login'] . "' logged into POP3 server '".$pop3->hostname."'.\n", $args['debug']);
                    // check for message
                    if (($error = $pop3->Statistics($messages,$size)) == '') {
                        mailcronecho("There are $messages messages in the mailbox, amounting to a total of $size bytes.\n", $args['debug']);
                        // get message list...
                        $result = $pop3->ListMessages('', 1);
                        if (is_array($result) && count($result) > 0) {
                            // logout the currentuser
                            mailcronecho("Logging out '" . UserUtil::getVar('uname') . "'.\n", $args['debug']);
                            UserUtil::logOut();
                            // login the correct user
                            if (UserUtil::logIn($forum['pop3_pnuser'], base64_decode($forum['pop3_pnpassword']), false)) {
                                mailcronecho('Done! User ' . UserUtil::getVar('uname') . ' successfully logged in.', $args['debug']);
                                if (!allowedtowritetocategoryandforum($forum['cat_id'], $forum['forum_id'])) {
                                    mailcronecho("Error! Insufficient permissions for " . UserUtil::getVar('uname') . " in forum " . $forum['forum_name'] . "(id=" . $forum['forum_id'] . ").", $args['debug']);
                                    UserUtil::logOut();
                                    mailcronecho('Done! User ' . UserUtil::getVar('uname') . ' logged out.', $args['debug']);
                                    return false;
                                }
                                mailcronecho("Adding new posts as user '" . UserUtil::getVar('uname') . "'.\n", $args['debug']);
                                // .cycle through the message list
                                for ($cnt = 1; $cnt <= count($result); $cnt++) {
                                    if (($error = $pop3->RetrieveMessage($cnt, $headers, $body, -1)) == '') {
                                        // echo "Message $i:\n---Message headers starts below---\n";
                                        $subject = '';
                                        $from = '';
                                        $msgid = '';
                                        $replyto = '';
                                        $original_topic_id = '';
                                        foreach ($headers as $header) {
                                            //echo htmlspecialchars($header),"\n";
                                            // get subject
                                            $header = strtolower($header);
                                            if (strpos(strtolower($header), 'subject:') === 0) {
                                                $subject = trim(strip_tags(substr($header, 8)));
                                            }
                                            // get sender
                                            if (strpos($header, 'from:') === 0) {
                                                $from = trim(strip_tags(substr($header, 5)));
                                                // replace @ and . to make it harder for email harvesers,
                                                // credits to Teb for this idea
                                                $from = str_replace(array('@','.'),array(' (at) ',' (dot) '), $from);
                                            }
                                            // get msgid from In-Reply-To: if this is an nswer to a prior
                                            // posting
                                            if (strpos($header, 'in-reply-to:') === 0) {
                                                $replyto = trim(strip_tags(substr($header, 12)));
                                            }
                                            // this msg id
                                            if (strpos($header, 'message-id:') === 0) {
                                                $msgid = trim(strip_tags(substr($header, 11)));
                                            }
    
                                            // check for X-DizkusTopicID, if set, then this is a possible
                                            // loop (mailinglist subscribed to the forum too)
                                            if (strpos($header, 'X-DizkusTopicID:') === 0) {
                                                $original_topic_id = trim(strip_tags(substr($header, 17)));
                                            }
                                        }
                                        if (empty($subject)) {
                                            $subject = DataUtil::formatForDisplay($this->__('Error! The post has no subject line.'));
                                        }
    
                                        // check if subject matches our matchstring
                                        if (empty($original_topic_id)) {
                                            if (empty($forum['pop3_matchstring']) || (preg_match($forum['pop3_matchstring'], $subject) <> 0)) {
                                                $message = '[code=htmlmail,user=' . $from . ']' . implode("\n", $body) . '[/code]';
                                                if (!empty($replyto)) {
                                                    // this seems to be a reply, we find the original posting
                                                    // and store this mail in the same thread
                                                    $topic_id = ModUtil::apiFunc('Dizkus', 'topic', 'get_topic_by_postmsgid',
                                                                             array('msgid' => $replyto));
                                                    if (is_bool($topic_id) && $topic_id == false) {
                                                        // msgid not found, we clear replyto to create a new topic
                                                        $replyto = '';
                                                    } else {
                                                        // topic_id found, add this posting as a reply there
                                                        list($start,
                                                             $post_id ) = ModUtil::apiFunc('Dizkus', 'post', 'storereply',
                                                                                       array('topic_id'         => $topic_id,
                                                                                             'message'          => $message,
                                                                                             'attach_signature' => 1,
                                                                                             'subscribe_topic'  => 0,
                                                                                             'msgid'            => $msgid));
                                                        mailcronecho("added new post '$subject' (post=$post_id) to topic $topic_id\n", $args['debug']);
                                                    }
                                                }
    
                                                // check again for replyto and create a new topic
                                                if (empty($replyto)) {
                                                    // store message in forum
                                                    $topic_id = ModUtil::apiFunc('Dizkus', 'post', 'storenewtopic',
                                                                             array('subject'          => $subject,
                                                                                   'message'          => $message,
                                                                                   'forum_id'         => $forum['forum_id'],
                                                                                   'attach_signature' => 1,
                                                                                   'subscribe_topic'  => 0,
                                                                                   'msgid'            => $msgid ));
                                                    mailcronecho("Added new topic '$subject' (topic ID $topic_id) to '".$forum['forum_name'] ."' forum.\n", $args['debug']);
                                                }
                                            } else {
                                                mailcronecho("Warning! Message subject  line '$subject' does not match requirements and will be ignored.", $args['debug']);
                                            }
                                        } else {
                                            mailcronecho("Warning! The message subject line '$subject' is a possible loop and will be ignored.", $args['debug']);
                                        }
                                        // mark message for deletion
                                        $pop3->DeleteMessage($cnt);
                                    }
                                }
                                // logout the mail2forum user
                                if (UserUtil::logOut()) {
                                    mailcronecho('Done! User ' . $forum['pop3_pnuser'] . ' logged out.', $args['debug']);
                                }
                            } else {
                                mailcronecho("Error! Could not log user '". $forum['pop3_pnuser'] ."' in.\n");
                            }
                            // close pop3 connection and finally delete messages
                            if ($error == '' && ($error=$pop3->Close()) == '') {
                                mailcronecho("Disconnected from POP3 server '".$pop3->hostname."'.\n");
                            }
                        } else {
                            $error = $result;
                        }
                    }
                }
            }
            if (!empty($error)) {
                mailcronecho( "error: ",htmlspecialchars($error) . "\n");
            }
    
            // store the timestamp of the last connection to the database
            $fobj['forum_pop3_lastconnect'] = time();
            $fobj['forum_id']               = $forum['forum_id'];
            DBUtil::updateObject($fobj, 'dizkus_forums', '', 'forum_id');
        }
    
        return;
    }
    
    /**
     * testpop3connection
     *
     * @params $args['forum_id'] int the id of the forum to test the pop3 connection
     * @returns array of messages from pop3 connection test
     *
     */
    public function testpop3connection($args)
    {
        if (!isset($args['forum_id']) || !is_numeric($args['forum_id'])) {
            return LogUtil::registerArgsError();
        }
    
        $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                              array('forum_id' => $args['forum_id']));
        Loader::includeOnce('modules/Dizkus/includes/pop3.php');
    
        $pop3 = new pop3_class;
        $pop3->hostname = $forum['pop3_server'];
        $pop3->port     = $forum['pop3_port'];
    
        $error = '';
        $pop3messages = array();
        if (($error=$pop3->Open()) == '') {
            $pop3messages[] = "connected to the POP3 server '".$pop3->hostname."'";
            if (($error=$pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0))=='') {
                $pop3messages[] = "user '" . $forum['pop3_login'] . "' logged in";
                if (($error=$pop3->Statistics($messages, $size))=='') {
                    $pop3messages[] = "There are $messages messages in the mailbox, amounting to a total of $size bytes.";
                    $result=$pop3->ListMessages('',1);
                    if (is_array($result) && count($result)>0) {
                        for ($cnt = 1; $cnt <= count($result); $cnt++) {
                            if (($error=$pop3->RetrieveMessage($cnt, $headers, $body, -1)) == '') {
                                foreach ($headers as $header) {
                                    if (strpos(strtolower($header), 'subject:') === 0) {
                                        $subject = trim(strip_tags(substr($header, 8)));
                                    }
                                }
                            }
                        }
                        if ($error == '' && ($error=$pop3->Close()) == '') {
                            $pop3messages[] = "Disconnected from POP3 server '".$pop3->hostname."'.\n";
                        }
                    } else {
                        $error=$result;
                    }
                }
            }
        }
        if (!empty($error)) {
            $pop3messages[] = 'error: ' . htmlspecialchars($error);
        }
    
        return $pop3messages;
    }
    
    
    /**
     * notify moderators
     *
     * @params $args['post'] array the post array
     * @returns void
     */
    public function notify_moderator($args)
    {
        setlocale (LC_TIME, System::getVar('locale'));
        $modinfo = ModUtil::getInfo(ModUtil::getIDFromName(ModUtil::getName()));
    
        $mods = ModUtil::apiFunc('Dizkus', 'admin', 'readmoderators',
                             array('forum_id' => $args['post']['forum_id']));
    
        // generate the mailheader
        $email_from = ModUtil::getVar('Dizkus', 'email_from');
        if ($email_from == '') {
            // nothing in forumwide-settings, use PN adminmail
            $email_from = System::getVar('adminmail');
        }
    
        $subject  = DataUtil::formatForDisplay($this->__('Moderation request')) . ': ' . strip_tags($args['post']['topic_rawsubject']);
        $sitename = System::getVar('sitename');
    
        $recipients = array();
        // check if list is empty - then do nothing
        // we create an array of recipients here
        $admin_is_mod = false;
        if (is_array($mods) && count($mods) <> 0) {
            foreach ($mods as $mod) {
                if ($mod['uid'] > 1000000) {
                    // mod_uid is gid
                    $group = ModUtil::apiFunc('Groups', 'user', 'get', array('gid' => (int)$mod['uid'] - 1000000));
                    if ($group <> false) {
                        foreach($group['members'] as $gm_uid)
                        {
                            $mod_email = UserUtil::getVar('email', $gm_uid);
                            $mod_uname = UserUtil::getVar('uname', $gm_uid);
                            if (!empty($mod_email)) {
                                array_push($recipients, array('uname' => $mod_uname,
                                                              'email' => $mod_email));
                            }
                            if ($gm_uid == 2) {
                                // admin is also moderator
                                $admin_is_mod = true;
                            }
                        }
                    }
    
                } else {
                    $mod_email = UserUtil::getVar('email', $mod['uid']);
                    //uname is alread stored in $mod['uname']
                    if (!empty($mod_email)) {
                        array_push($recipients, array('uname' => $mod['uname'],
                                                      'email' => $mod_email));
                    }
                    if ($mod['uid'] == 2) {
                        // admin is also moderator
                        $admin_is_mod = true;
                    }
                }
            }
        }
        // always inform the admin. he might be a moderator to so we check the
        // admin_is_mod flag now
        if ($admin_is_mod == false) {
            array_push($recipients, array('uname' => System::getVar('sitename'),
                                          'email' => $email_from));
        }
    
        $reporting_userid   = UserUtil::getVar('uid');
        $reporting_username = UserUtil::getVar('uname');
        if (is_null($reporting_username)) {
            $reporting_username = $this->__('Guest');
        }
    
        $start = ModUtil::apiFunc('Dizkus', 'topic', 'get_page_from_topic_replies',
                              array('topic_replies' => $args['post']['topic_replies']));
    
        // FIXME Move this to a translatable template?
        $message = $this->__f('Request for moderation on %s', System::getVar('sitename')) . "\n"
                . $args['post']['cat_title'] . '::' . $args['post']['forum_name'] . '::' . $args['post']['topic_rawsubject'] . "\n\n"
                . $this->__f('Reporting user: %s', $reporting_username) . "\n"
                . $this->__('Comment:') . "\n"
                . $args['comment'] . " \n\n"
                . "---------------------------------------------------------------------\n"
                . strip_tags($args['post']['post_text']) . " \n"
                . "---------------------------------------------------------------------\n\n"
                . $this->__f('<a href="%s">Link to topic</a>', DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $args['post']['topic_id'], 'start' => $start), null, 'pid'.$args['post']['post_id'], true))) . "\n"
                . "\n";

        if (count($recipients) > 0) {
            foreach($recipients as $recipient) {
                ModUtil::apiFunc('Mailer', 'user', 'sendmessage',
                              array( 'fromname'    => $sitename,
                                     'fromaddress' => $email_from,
                                     'toname'      => $recipient['uname'],
                                     'toaddress'   => $recipient['email'],
                                     'subject'     => $subject,
                                     'body'        => $message,
                                     'headers'     => array('X-UserID: ' . $reporting_userid,
                                                            'X-Mailer: ' . $modinfo['name'] . ' ' . $modinfo['version'])));
            }
        }
    
        return;
    }
    
   
    
    /**
     * insert rss
     *
     * @params $args['forum']    array with forum data
     * @params $args['items']    array with feed data as returned from Feeds module
     * @return boolean true or false
     */
    public function insertrss($args)
    {
        if (!$args['forum'] || !$args['items']) {
            return false;
        }
    
        $bbcode = ModUtil::available('BBCode');
        $boldstart = '';
        $boldend   = '';
        $urlstart  = '';
        $urlend    = '';
        if ($bbcode == true) {
            $boldstart = '[b]';
            $boldend   = '[/b]';
            $urlstart  = '[url]';
            $urlend    = '[/url]';
        }
    
        foreach ($args['items'] as $item)
        {
            // create the reference, we need it twice
            $dateTimestamp = $item->get_date("Y-m-d H:i:s");
            if (empty($dateTimestamp)) {
                $reference = md5($item->get_link());
                $dateTimestamp = date("Y-m-d H:i:s", time());
            } else {
                $reference = md5($item->get_link() . '-' . $dateTimestamp);
            }
    
            // Checking if the forum already has that news.
            $check = ModUtil::apiFunc('Dizkus', 'topic', 'get_topicid_by_reference',
                                  array('reference' => $reference));
    
            if ($check == false) {
                // Not found... we can add the news.
                $subject  = $item->get_title();
    
                // Adding little display goodies - finishing with the url of the news...
                $message  = $boldstart . $this->__('Summary') . ' :' . $boldend . "\n\n" . $item->get_description() . "\n\n" . $urlstart . $item->get_link() . $urlend . "\n\n";
    
                // store message in forum
                $topic_id = ModUtil::apiFunc('Dizkus', 'topic', 'storenewtopic',
                                         array('subject'          => $subject,
                                               'message'          => $message,
                                               'time'             => $dateTimestamp,
                                               'forum_id'         => $args['forum']['forum_id'],
                                               'attach_signature' => 0,
                                               'subscribe_topic'  => 0,
                                               'reference'        => $reference));
    
                if (!$topic_id) {
                    // An error occured... get away before screwing more.
                    return false;
                }
            }
        }
    
        return true;
    
    }
    
   
   
    
    /**
     * get_settings_ignorelist
     *
     * @params none
     * @params $args['uid']  int     the users id
     * @return string|boolean level for ignorelist handling as string
     */
    public function get_settings_ignorelist($args)
    {
        // if Contactlist is not available there will be no ignore settings
        if (!ModUtil::available('ContactList')) {
            return false;
        }
    
        // get parameters
        $uid = (int)$args['uid'];
        if (!($uid > 1)) {
            return false;
        }
    
        $attr = UserUtil::getVar('__ATTRIBUTES__', $uid);
        $ignorelist_myhandling = $attr['dzk_ignorelist_myhandling'];
        $default = ModUtil::getVar('Dizkus','ignorelist_handling');
        if (isset($ignorelist_myhandling) && ($ignorelist_myhandling != ''))
        {
            if (($ignorelist_myhandling == 'strict') && ($default != $ignorelist_myhandling)) {
                // maybe the admin value changed and the user's value is "higher" than the admin's value
                return $default;
            } else {
                // return user's value
                return $ignorelist_myhandling;
            }
        } else {
            // return admin's default value
            return $default;
        }
    }
    
    /**
    * helper function
    */
    private function _get_favorites($f)
    {
        return (int)$f['forum_id'];
    }
   
  
}
