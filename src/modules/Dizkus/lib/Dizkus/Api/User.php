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
     * get_firstlast_post_in_topic
     * gets the first or last post in a topic, false if no posts
     *
     * @params $args['topic_id'] int the topics id
     * @params $args['first']   boolean if true then get the first posting, otherwise the last
     * @params $args['id_only'] boolean if true, only return the id, not the complete post information
     * @returns array with post information or false or (int)id if id_only is true
     */
    public function get_firstlast_post_in_topic($args)
    {
        if (!empty($args['topic_id']) && is_numeric($args['topic_id'])) {
            $ztable = DBUtil::getTables();
            $option  = (isset($args['first']) && $args['first'] == true) ? 'MIN' : 'MAX';
            $post_id = DBUtil::selectFieldMax('dizkus_posts', 'post_id', $option, $ztable['dizkus_posts_column']['topic_id'].' = '.(int)$args['topic_id']);
    
            if ($post_id <> false) {
                if (isset($args['id_only']) && $args['id_only'] == true) {
                    return $post_id;
                }
                return $this->readpost(array('post_id' => $post_id));
            }
        }
    
        return false;
    }
    
    /**
     * get_last_post_in_forum
     * gets the last post in a forum, false if no posts
     *
     * @params $args['forum_id'] int the forums id
     * @params $args['id_only'] boolean if true, only return the id, not the complete post information
     * @returns array with post information of false
     */
    public function get_last_post_in_forum($args)
    {
        if (!empty($args['forum_id']) && is_numeric($args['forum_id'])) {
            $ztable = DBUtil::getTables();
            $post_id = DBUtil::selectfieldMax('dizkus_posts', 'post_id', 'MAX', $ztable['dizkus_posts_column']['forum_id'].' = '.(int)$args['forum_id']);
    
            if (isset($args['id_only']) && $args['id_only'] == true) {
                return $post_id;
            }
    
            return $this->readpost(array('post_id' => $post_id));
        }
    
        return false;
    }
    
    /**
     * readcategorytree
     * read all catgories and forums the recent user has access to
     *
     * @params $args['last_visit'] string the users last visit date as returned from setcookies() function
     * @returns array of categories with an array of forums in the catgories
     *
     */
    public function readcategorytree($args)
    {
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('e')
            ->from('Dizkus_Entity_Category', 'e');

        $query = $qb->getQuery();
        return $query->getResult();




        extract($args);
        if(empty($last_visit)) {
            $last_visit = 0;
        }

        static $tree;
    
        $dizkusvars = ModUtil::getVar('Dizkus');
    
        // if we have already called this once during the script
        if (isset($tree)) {
            return $tree;
        }
    
        $ztable = DBUtil::getTables();
        $cattable    = $ztable['dizkus_categories'];
        $forumstable = $ztable['dizkus_forums'];
        $poststable  = $ztable['dizkus_posts'];
        $topicstable = $ztable['dizkus_topics'];
        $userstable  = $ztable['users'];
    


        if(!empty($cat_id)) {
            $cat = ' AND '.$forumstable.'.cat_id='.$cat_id;
        } else {
            $cat = '';
        }


        $sql = 'SELECT ' . $cattable . '.cat_id AS cat_id,
                       ' . $cattable . '.cat_title AS cat_title,
                       ' . $cattable . '.cat_order AS cat_order,
                       ' . $forumstable . '.forum_id AS forum_id,
                       ' . $forumstable . '.forum_name AS forum_name,
                       ' . $forumstable . '.forum_desc AS forum_desc,
                       ' . $forumstable . '.forum_topics AS forum_topics,
                       ' . $forumstable . '.forum_posts AS forum_posts,
                       ' . $forumstable . '.forum_last_post_id AS forum_last_post_id,
                       ' . $forumstable . '.forum_moduleref AS forum_moduleref,
                       ' . $forumstable . '.forum_pntopic AS forum_pntopic,
                       ' . $topicstable . '.topic_title AS topic_title,
                       ' . $topicstable . '.topic_replies AS topic_replies,
                       ' . $userstable . '.uname AS pn_uname,
                       ' . $userstable . '.uid AS pn_uid,
                       ' . $poststable . '.topic_id AS topic_id,
                       ' . $poststable . '.post_time AS post_time
                FROM ' . $cattable . '
                LEFT JOIN ' . $forumstable . ' ON ' . $forumstable . '.cat_id=' . $cattable . '.cat_id
                AND '.$forumstable.'.parent_id=0'.$cat.' 
                LEFT JOIN ' . $poststable . ' ON ' . $poststable . '.post_id=' . $forumstable . '.forum_last_post_id
                LEFT JOIN ' . $topicstable . ' ON ' . $topicstable . '.topic_id=' . $poststable . '.topic_id
                LEFT JOIN ' . $userstable . ' ON ' . $userstable . '.uid=' . $poststable . '.poster_id
                ORDER BY ' . $cattable . '.cat_order, ' . $forumstable . '.forum_order, ' . $forumstable . '.forum_name';
        $res = DBUtil::executeSQL($sql);
        $colarray = array('cat_id', 'cat_title', 'cat_order', 'forum_id', 'forum_name', 'forum_desc', 'forum_topics', 'forum_posts',
                          'forum_last_post_id', 'forum_moduleref', 'forum_pntopic', 'topic_title', 'topic_replies', 'pn_uname', 'pn_uid', 
                          'topic_id', 'post_time');
        $result = DBUtil::marshallObjects($res, $colarray);

    
        $posts_per_page = ModUtil::getVar('Dizkus', 'posts_per_page');
    
        $tree = array();
        if (is_array($result) && !empty($result)) {
            foreach ($result as $row) {
                $cat   = array();
                $forum = array();
                $cat['last_post'] = array(); // get the last post in this category, this is an array
                $cat['new_posts'] = false;
                $cat['forums'] = array();
                $cat['cat_id']                = $row['cat_id'];
                $cat['cat_title']             = $row['cat_title'];
                $cat['cat_order']             = $row['cat_order'];
                $forum['forum_id']            = $row['forum_id'];
                $forum['forum_name']          = $row['forum_name'];
                $forum['forum_desc']          = $row['forum_desc'];
                $forum['forum_topics']        = $row['forum_topics'];
                $forum['forum_posts']         = $row['forum_posts'];
                $forum['forum_last_post_id']  = $row['forum_last_post_id'];
                $forum['forum_moduleref']     = $row['forum_moduleref'];
                $forum['forum_pntopic']       = $row['forum_pntopic'];
                $topic_title                  = $row['topic_title'];
                $topic_replies                = $row['topic_replies'];
                $forum['pn_uname']            = $row['pn_uname']; // fixme
                $forum['pn_uid']              = $row['pn_uid'];  // fixme
                $forum['uname']               = $row['pn_uname'];
                $forum['uid']                 = $row['pn_uid'];
                $forum['topic_id']            = $row['topic_id'];
                $forum['post_time']           = $row['post_time'];

                $allowedToSee = ModUtil::apiFunc($this->name, 'Permission', 'canSee', $forum);
                if ($allowedToSee) {
                    if (!array_key_exists($cat['cat_title'], $tree)) {
                        $tree[$cat['cat_title']] = $cat;
                    }
                    $last_post_data = array();
                    if (!empty($forum['forum_id'])) {
                        if ($forum['forum_topics'] != 0) {
                            // are there new topics since last_visit?
                            if ($forum['post_time'] > $last_visit) {
                                $forum['new_posts'] = true;
                                // we have new posts
                            } else {
                                // no new posts
                                $forum['new_posts'] = false;
                            }
            
                            $posted_unixtime= strtotime($forum['post_time']);
                            $posted_ml = DateUtil::formatDatetime($posted_unixtime, 'datetimebrief');
                            if ($posted_unixtime) {
                                if ($forum['uid']==1) {
                                    $username = ModUtil::getVar('Users', 'anonymous');
                                } else {
                                    $username = $forum['uname'];
                                }
            
                                $last_post = DataUtil::formatForDisplay($this->__f('%1$s<br />by %2$s', array($posted_ml, $username)));
                                $last_post = $last_post.' <a href="' . ModUtil::url('Dizkus','user','viewtopic', array('topic' => $forum['topic_id'])). '">
                                                          <img src="modules/Dizkus/images/icon_latest_topic.gif" alt="' . $posted_ml . ' ' . $username . '" height="9" width="18" /></a>';
                                // new in 2.0.2 - no more preformattd output
                                $last_post_data['name']     = $username;
                                $last_post_data['subject']  = $topic_title;
                                $last_post_data['time']     = $posted_ml;
                                $last_post_data['unixtime'] = $posted_unixtime;
                                $last_post_data['topic']    = $forum['topic_id'];
                                $last_post_data['post']     = $forum['forum_last_post_id'];
                                $last_post_data['url']      = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                       array('topic' => $forum['topic_id'],
                                                                             'start' => (ceil(($topic_replies + 1)  / $posts_per_page) - 1) * $posts_per_page));
                                $last_post_data['url_anchor'] = $last_post_data['url'] . '#pid' . $forum['forum_last_post_id'];
                            } else {
                                // no posts in forum
                                $last_post = $this->__('No posts');
                                $last_post_data['name']       = '';
                                $last_post_data['subject']    = '';
                                $last_post_data['time']       = '';
                                $last_post_data['unixtime']   = '';
                                $last_post_data['topic']      = '';
                                $last_post_data['post']       = '';
                                $last_post_data['url']        = '';
                                $last_post_data['url_anchor'] = '';
                            }
                            $forum['last_post_data'] = $last_post_data;
                        } else {
                            // there are no posts in this forum
                            $forum['new_posts']= false;
                            $last_post = $this->__('No posts');
                        }
                        $forum['last_post']  = $last_post;
                        $forum['forum_mods'] = $this->get_moderators(array('forum_id' => $forum['forum_id']));
            
                        // is the user subscribed to the forum?
                        $forum['is_subscribed'] = 0;
                        if ($this->get_forum_subscription_status(array('user_id' => UserUtil::getVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
                            $forum['is_subscribed'] = 1;
                        }
            
                        // is this forum in the favorite list?
                        $forum['is_favorite'] = 0;
                        if ($dizkusvars['favorites_enabled'] == 'yes') {
                            if ($this->get_forum_favorites_status(array('user_id' => UserUtil::getVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
                                $forum['is_favorite'] = 1;
                            }
                        }
            
                        // set flag if new postings in category
                        if ($tree[$cat['cat_title']]['new_posts'] == false) {
                            $tree[$cat['cat_title']]['new_posts'] = $forum['new_posts'];
                        }
            
                        // make sure that the most recent posting is stored in the category too
                        if ((count($tree[$cat['cat_title']]['last_post']) == 0)
                          || (isset($last_post_data['unixtime']) && $tree[$cat['cat_title']]['last_post']['unixtime'] < $last_post_data['unixtime'])) {
                            // nothing stored before or a is older than b (a < b for timestamps)
                            $tree[$cat['cat_title']]['last_post'] = $last_post_data;
                        }
            
                        array_push($tree[$cat['cat_title']]['forums'], $forum);
                    }
                }
            }
        }
    
        // sort the array by cat_order
        uasort($tree, 'cmp_catorder');
    
        return $tree;
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
    
    /**
     * readforum
     * reads the forum information and the last posts_per_page topics incl. poster data
     *
     * @params $args['forum_id'] int the forums id
     * @params $args['start'] int number of topic to start with (if on page 1+)
     * @params $args['last_visit'] string users last visit date
     * @params $args['last_visit_unix'] string users last visit date as timestamp
     * @params $args['topics_per_page'] int number of topics to read, -1 = all topics
     * @returns array Very complex array, see {debug} for more information
     */
    public function readforum($args)
    {
        $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                              array('forum_id' => $args['forum_id'],
                                    'permcheck' => 'nocheck' ));
        if ($forum == false) {
            return LogUtil::registerError($this->__('Error! The forum or topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }

        $allowedToSee = ModUtil::apiFunc($this->name, 'Permission', 'canSee', $forum);
        if (!$allowedToSee) {
            return LogUtil::registerPermissionError();
        }
    
        $ztable = DBUtil::getTables();
    
        $posts_per_page  = ModUtil::getVar('Dizkus', 'posts_per_page');
        if (empty($args['topics_per_page'])) {
            $args['topics_per_page'] = ModUtil::getVar('Dizkus', 'topics_per_page');
        }
        $hot_threshold   = ModUtil::getVar('Dizkus', 'hot_threshold');
        $post_sort_order = ModUtil::apiFunc('Dizkus','user','get_user_post_order');
    
        // read moderators
        $forum['forum_mods'] = $this->get_moderators(array('forum_id' => $forum['forum_id']));
        $forum['last_visit'] = $args['last_visit'];
    
        $forum['topic_start'] = (!empty ($args['start'])) ? $args['start'] : 0;
    
        // is the user subscribed to the forum?
        $forum['is_subscribed'] = 0;
        if ($this->get_forum_subscription_status(array('user_id' => UserUtil::getVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
            $forum['is_subscribed'] = 1;
        }
    
        // is this forum in the favorite list?
        $forum['is_favorite'] = 0;
        if ($this->get_forum_favorites_status(array('user_id' => UserUtil::getVar('uid'), 'forum_id' => $forum['forum_id'])) == true) {
            $forum['is_favorite'] = 1;
        }
    
        // if user can write into Forum, set a flag
        $forum['access_comment'] = ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $forum);
    
        // if user can moderate Forum, set a flag
        $forum['access_moderate'] = ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $forum);
    
        // forum_pager is obsolete, inform the user about this
        $forum['forum_pager'] = 'Error! Deprecated \'$forum.forum_pager\' data field used. Please update the template to incorporate the forum pager plug-in.';
    
        // integrate contactlist's ignorelist here
        $whereignorelist = '';
        $ignorelist_setting = ModUtil::apiFunc('Dizkus','user','get_settings_ignorelist',array('uid' => UserUtil::getVar('uid')));
        if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
            // get user's ignore list
            $ignored_users = ModUtil::apiFunc('ContactList','user','getallignorelist',array('uid' => UserUtil::getVar('uid')));
            $ignored_uids = array();
            foreach ($ignored_users as $item) {
                $ignored_uids[]=(int)$item['iuid'];
            }
            if (count($ignored_uids) > 0) {
                $whereignorelist = " AND t.topic_poster NOT IN (".implode(',',$ignored_uids).")";
            }
        }
    
        $sql = 'SELECT t.topic_id,
                       t.topic_title,
                       t.topic_views,
                       t.topic_replies,
                       t.sticky,
                       t.topic_status,
                       t.topic_last_post_id,
                       t.topic_poster,
                       u.uname,
                       u2.uname as last_poster,
                       p.post_time,
                       t.topic_poster
                FROM ' . $ztable['dizkus_topics'] . ' AS t
                LEFT JOIN ' . $ztable['users'] . ' AS u ON t.topic_poster = u.uid
                LEFT JOIN ' . $ztable['dizkus_posts'] . ' AS p ON t.topic_last_post_id = p.post_id
                LEFT JOIN ' . $ztable['users'] . ' AS u2 ON p.poster_id = u2.uid
                WHERE t.forum_id = ' .(int)DataUtil::formatForStore($forum['forum_id']) . '
                ' . $whereignorelist . '
                ORDER BY t.sticky DESC, p.post_time DESC';
                //ORDER BY t.sticky DESC"; // RNG
                //ORDER BY t.sticky DESC, p.post_time DESC";
                //FC ORDER BY t.sticky DESC"; // RNG
                //FC //ORDER BY t.sticky DESC, p.post_time DESC";
    
        $res = DBUtil::executeSQL($sql, $args['start'], $args['topics_per_page']);
        $colarray = array('topic_id', 'topic_title', 'topic_views', 'topic_replies', 'sticky', 'topic_status',
                          'topic_last_post_id', 'topic_poster', 'uname', 'last_poster', 'post_time');
        $result    = DBUtil::marshallObjects($res, $colarray);
    
        //    $forum['forum_id'] = $forum['forum_id'];
        $forum['topics']   = array();
    
        if (is_array($result) && !empty($result)) {
            foreach ($result as $topic) {
                if ($topic['last_poster'] == 'Anonymous') {
                    $topic['last_poster'] = ModUtil::getVar('Users', 'anonymous');
                }
                if ($topic['uname'] == 'Anonymous') {
                    $topic['uname'] = ModUtil::getVar('Users', 'anonymous');
                }
                $topic['total_posts'] = $topic['topic_replies'] + 1;
            
                $topic['post_time_unix'] = strtotime($topic['post_time']);
                $posted_ml = DateUtil::formatDatetime($topic['post_time_unix'], 'datetimebrief');
                $topic['last_post'] = DataUtil::formatForDisplay($this->__f('%1$s<br />by %2$s', array($posted_ml, $topic['last_poster'])));
            
                // does this topic have enough postings to be hot?
                $topic['hot_topic'] = ($topic['topic_replies'] >= $hot_threshold) ? true : false;
                // does this posting have new postings?
                $topic['new_posts'] = ($topic['post_time'] < $args['last_visit']) ? false : true;
            
                // pagination
                $pagination = '';
                $lastpage = 0;
                if ($topic['topic_replies']+1 > $posts_per_page)
                {
                    if ($post_sort_order == 'ASC') {
                        $hc_dlink_times = 0;
                        if (($topic['topic_replies']+1-$posts_per_page) >= 0) {
                            $hc_dlink_times = 0;
                            for ($x = 0; $x < $topic['topic_replies']+1-$posts_per_page; $x+= $posts_per_page) {
                                $hc_dlink_times++;
                            }
                        }
                        $topic['last_page_start'] = $hc_dlink_times * $posts_per_page;
                    } else {
                        // latest topic is on top anyway...
                        $topic['last_page_start'] = 0;
                    }
            
                    $pagination .= '&nbsp;&nbsp;&nbsp;<span class="z-sub">(' . DataUtil::formatForDisplay($this->__('Go to page')) . '&nbsp;';
                    $pagenr = 1;
                    $skippages = 0;
                    for ($x = 0; $x < $topic['topic_replies'] + 1; $x += $posts_per_page)
                    {
                        $lastpage = (($x + $posts_per_page) >= $topic['topic_replies'] + 1);
            
                        if ($lastpage) {
                            $args['start'] = $x;
                        } elseif ($x != 0) {
                            $args['start'] = $x;
                        }
            
                        if ($pagenr > 3 && $skippages != 1 && !$lastpage) {
                            $pagination .= ', ... ';
                            $skippages = 1;
                        }
            
                        if(empty($start)) {
                            $start = 0;
                        }

                        if ($skippages != 1 || $lastpage) {
                            if ($x!=0) $pagination .= ', ';
                            $pagination .= '<a href="' . ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic['topic_id'], 'start' => $start)) . '" title="' . $topic['topic_title'] . ' ' . DataUtil::formatForDisplay($this->__('Page #')) . ' ' . $pagenr . '">' . $pagenr . '</a>';
                        }
            
                        $pagenr++;
                    }
                    $pagination .= ')</span>';
                }
                $topic['pagination'] = $pagination;
                // we now create the url to the last post in the thread. This might
                // on site 1, 2 or what ever in the thread, depending on topic_replies
                // count and the posts_per_page setting
            
                // we keep this for backwardscompatibility
                $topic['last_post_url'] = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                   array('topic' => $topic['topic_id'],
                                                         'start' => (ceil(($topic['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page));
                $topic['last_post_url_anchor'] = $topic['last_post_url'] . '#pid' . $topic['topic_last_post_id'];
            
                array_push($forum['topics'], $topic);
            }
        }
    
        $topics_start = $args['start']; // FIXME is this still correct?
    
        //usort ($forum['topics'], 'cmp_forumtopicsort'); // RNG
    
        return $forum;
    }
    
    // RNG
    function cmp_forumtopicsort($a, $b)
    {
        return strcmp($a['post_time_unix'], $b['post_time_unix']) * -1;
    }
    
    
    /**
     * readtopic
     * reads a topic with the last posts_per_page answers (incl the initial posting when on page #1)
     *
     * @params $args['topic_id'] it the topics id
     * @params $args['start'] int number of posting to start with (if on page 1+)
     * @params $args['complete'] bool if true, reads the complete thread and does not care about
     *                               the posts_per_page setting, ignores 'start'
     * @params $args['count']      bool  true if we have raise the read counter, default false
     * @params $args['nohook']     bool  true if transform hooks should not modify post text
     * @returns array Very complex array, see {debug} for more information
     */
    public function readtopic($args)
    {
        return ModUtil::apiFunc($this->name, 'Topic', 'read', $args);    
    }

    /**
     * storereply
     * store the users reply in the database
     *
     * @params $args['message'] string the text
     * @params $args['title'] string the posting title
     * @params $args['topic_id'] int the topics id
     * @params $args['forum_id'] int the forums id
     * @params $args['attach_signature'] int 1=yes, otherwise no
     * @params $args['subscribe_topic'] int 1=yes, otherwise no
     * @returns array(int, int) start parameter and new post_id
     */
    public function storereply($args)
    {
        $topic = ModUtil::apiFunc('Dizkus', 'user', 'get_forumid_and_categoryid_from_topicid',
                                                array('topic_id' => $args['topic_id']));
    
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $topic)) {
            return LogUtil::registerPermissionError();
        }
        
        if ($this->isSpam($args['message'])) {
            return LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
        }
    
        if (trim($args['message']) == '') {
            return LogUtil::registerError($this->__('Error! You tried to post a blank message. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        /*
        it's a submitted page and message is not empty
        */
    
        // grab message for notification
        // without html-specialchars, bbcode, smilies <br> and [addsig]
        $posted_message = stripslashes($args['message']);
    
        // signature is always on, except anonymous user
        // anonymous user has uid=0, but needs uid=1
        $islogged = UserUtil::isLoggedIn();
        if ($islogged) {
            if ($args['attach_signature'] == 1) {
                $args['message'] .= '[addsig]';
            }
            $pn_uid = UserUtil::getVar('uid');
        } else {
            $pn_uid = 1;
        }
        
        if (ModUtil::getVar('Dizkus', 'log_ip') == 'no') {
            // for privacy issues ip logging can be deactivated
            $poster_ip = '127.0.0.1';
        } else {
            // some enviroment for logging ;)
            if (System::serverGetVar('HTTP_X_FORWARDED_FOR')) {
                $poster_ip = System::serverGetVar('REMOTE_ADDR')."/".System::serverGetVar('HTTP_X_FORWARDED_FOR');
            } else {
                $poster_ip = System::serverGetVar('REMOTE_ADDR');
             }
        }


        // Prep for DB is done by DBUtil
        $obj['post_time']  = date('Y-m-d H:i:s');
        $obj['topic_id']   = $args['topic_id'];
        $obj['forum_id']   = $topic['forum_id'];
        $obj['post_text']  = $args['message'];
        $obj['poster_id']  = $pn_uid;
        $obj['poster_ip']  = $poster_ip;
        $obj['post_title'] = $args['title'];
    
        DBUtil::insertObject($obj, 'dizkus_posts', 'post_id');
    
        // update topics table
        $tobj['topic_last_post_id'] = $obj['post_id'];
        $tobj['topic_time']         = $obj['post_time'];
        $tobj['topic_id']           = $obj['topic_id'];
        DBUtil::updateObject($tobj, 'dizkus_topics', null, 'topic_id');
        DBUtil::incrementObjectFieldByID('dizkus_topics', 'topic_replies', $obj['topic_id'], 'topic_id');
    
        if ($islogged) {
            // user logged in we have to update users attributes
            UserUtil::setVar('dizkus_user_posts', UserUtil::getVar('dizkus_user_posts') + 1);
            //DBUtil::incrementObjectFieldByID('dizkus__users', 'user_posts', $obj['poster_id'], 'user_id');
    
            // update subscription
            if ($args['subscribe_topic']==1) {
                // user wants to subscribe the topic
                $this->subscribe_topic(array('topic_id' => $obj['topic_id']));
            } else {
                // user does not want to subscribe the topic
                $this->unsubscribe_topic(array('topic_id' => $obj['topic_id'],
                                                       'silent'   => true));
            }
        }
    
        // update forums table
        $fobj['forum_last_post_id'] = $obj['post_id'];
        $fobj['forum_id']           = $obj['forum_id'];
        DBUtil::updateObject($fobj, 'dizkus_forums', null, 'forum_id');
        DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_posts', $obj['forum_id'], 'forum_id');
    
        // get the last topic page
        $start = $this->get_last_topic_page(array('topic_id' => $obj['topic_id']));
    
        // Let any hooks know that we have created a new item.
        //ModUtil::callHooks('item', 'create', $this_post, array('module' => 'Dizkus'));
//        ModUtil::callHooks('item', 'update', $obj['topic_id'], array('module' => 'Dizkus',
//                                                          'post_id' => $obj['post_id']));
    
        $this->notify_by_email(array('topic_id' => $obj['topic_id'], 'poster_id' => $obj['poster_id'], 'post_message' => $posted_message, 'type' => '2'));
    
        return array($start, $obj['post_id']);
    }
    


    /**
     * preparenewtopic
     *
     * @params $args['message'] string the text (only set when preview is selected)
     * @params $args['subject'] string the subject (only set when preview is selected)
     * @params $args['forum_id'] int the forums id
     * @params $args['topic_start'] bool true if we start a new topic
     * @params $args['attach_signature'] int 1= attach signature, otherwise no
     * @params $args['subscribe_topic'] int 1= subscribe topic, otherwise no
     * @returns array with information....
     */
    public function preparenewtopic($args)
    {
        $ztable = DBUtil::getTables();

        $newtopic = array();
        $newtopic['forum_id'] = $args['forum_id'];
        $newtopic['topic_id'] = 0;

        // select forum name and cat title based on forum_id
        $sql = "SELECT f.forum_name,
                       c.cat_id,
                       c.cat_title
                FROM ".$ztable['dizkus_forums']." AS f,
                    ".$ztable['dizkus_categories']." AS c
                WHERE (forum_id = '".(int)DataUtil::formatForStore($args['forum_id'])."'
                AND f.cat_id=c.cat_id)";
        $res = DBUtil::executeSQL($sql);
        $colarray = array('forum_name', 'cat_id', 'cat_title');
        $myrow    = DBUtil::marshallObjects($res, $colarray);

        $newtopic['cat_id']     = $myrow[0]['cat_id'];
        $newtopic['forum_name'] = DataUtil::formatForDisplay($myrow[0]['forum_name']);
        $newtopic['cat_title']  = DataUtil::formatForDisplay($myrow[0]['cat_title']);

        $newtopic['topic_unixtime'] = time();

        // need at least "comment" to add newtopic
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $newtopic)) {
            // user is not allowed to post
            return LogUtil::registerPermissionError();
        }

        $newtopic['poster_data'] = ModUtil::apiFunc('Dizkus', 'user', 'get_userdata_from_id',
                                                    array('userid' => UserUtil::getVar('uid')));
        $newtopic['subject'] = $args['subject'];
        $newtopic['message'] = $args['message'];
        $newtopic['message_display'] = $args['message']; // phpbb_br2nl($args['message']);

        // list($newtopic['message_display']) = ModUtil::callHooks('item', 'transform', '', array($newtopic['message_display']));
        $newtopic['message_display'] = nl2br($newtopic['message_display']);

        if (UserUtil::isLoggedIn()) {
            if ($args['topic_start'] == true) {
                $newtopic['attach_signature'] = true;
                $newtopic['subscribe_topic']  = ((int)UserUtil::getVar('dizkus_autosubscription', -1, 1)==1) ? true : false;
            } else {
                $newtopic['attach_signature'] = $args['attach_signature'];
                $newtopic['subscribe_topic']  = $args['subscribe_topic'];
            }
        } else {
            $newtopic['attach_signature'] = false;
            $newtopic['subscribe_topic']  = false;
        }

        return $newtopic;
    }
    
    /**
     * storenewtopic
     *
     * @params $args['subject'] string the subject
     * @params $args['message'] string the text
     * @params $args['forum_id'] int the forums id
     * @params $args['time'] string (optional) the time, only needed when creating a shadow
     *                             topic
     * @params $args['attach_signature'] int 1=yes, otherwise no
     * @params $args['subscribe_topic'] int 1=yes, otherwise no
     * @params $args['reference']  string for comments feature: <modname>-<objectid>
     * @params $args['post_as']    int used id under which this topic should be posted
     * @returns int the new topics id
     */
    public function storenewtopic($args)
    {
        $args['cat_id'] = $this->get_forum_category(array('forum_id' => $args['forum_id']));
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $args)) {
            return LogUtil::registerPermissionError();
        }
        
        if ($this->isSpam($args['message'])) {
            return LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
        }
        

        if (trim($args['message']) == '' || trim($args['subject']) == '') {
            // either message or subject is empty
            return LogUtil::registerError($this->__('Error! You tried to post a blank message. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        /*
        it's a submitted page and message and subject are not empty
        */
    
        //  grab message for notification
        //  without html-specialchars, bbcode, smilies <br /> and [addsig]
        $posted_message = stripslashes($args['message']);
    
        //  anonymous user has uid=0, but needs uid=1
        if (isset($args['post_as']) && !empty($args['post_as']) && is_numeric($args['post_as'])) {
            $pn_uid = $args['post_as'];
        } else {
            if (UserUtil::isLoggedIn()) {
                if ($args['attach_signature'] == 1) {
                    $args['message'] .= '[addsig]';
                }
                $pn_uid = UserUtil::getVar('uid');
            } else  {
                $pn_uid = 1;
            }
        }
        
        // some enviroment for logging ;)
        if (System::serverGetVar('HTTP_X_FORWARDED_FOR')){
            $poster_ip = System::serverGetVar('REMOTE_ADDR')."/".System::serverGetVar('HTTP_X_FORWARDED_FOR');
        } else {
            $poster_ip = System::serverGetVar('REMOTE_ADDR');
        }
        // for privavy issues ip logging can be deactivated
        if (ModUtil::getVar('Dizkus', 'log_ip') == 'no') {
            $poster_ip = '127.0.0.1';
        }
    
        $time = (isset($args['time'])) ? $args['time'] : DateUtil::getDatetime('', '%Y-%m-%d %H:%M:%S');
    
        // create topic
        $obj['topic_title']     = $args['subject'];
        $obj['topic_poster']    = $pn_uid;
        $obj['forum_id']        = $args['forum_id'];
        $obj['topic_time']      = $time;
        $obj['topic_reference'] = (isset($args['reference'])) ? $args['reference'] : '';
        DBUtil::insertObject($obj, 'dizkus_topics', 'topic_id');
    
        // create posting
        $pobj['topic_id']   = $obj['topic_id'];
        $pobj['forum_id']   = $obj['forum_id'];
        $pobj['poster_id']  = $obj['topic_poster'];
        $pobj['post_time']  = $obj['topic_time'];
        $pobj['poster_ip']  = $poster_ip;
        $pobj['post_msgid'] = (isset($msgid)) ? $msgid : '';
        $pobj['post_text']  = $args['message'];
        $pobj['post_title'] = $obj['topic_title'];
        DBUtil::insertObject($pobj, 'dizkus_posts', 'post_id');
    
        if ($pobj['post_id']) {
            //  updates topics-table
            $obj['topic_last_post_id'] = $pobj['post_id'];
            DBUtil::updateObject($obj, 'dizkus_topics', '', 'topic_id');
    
            // Let any hooks know that we have created a new item.
//            ModUtil::callHooks('item', 'create', $obj['topic_id'], array('module' => 'Dizkus'));
        }
    
        if (UserUtil::isLoggedIn()) {
            // user logged in we have to update users-table
            UserUtil::setVar('dizkus_user_posts', UserUtil::getVar('dizkus_user_posts') + 1);
            //DBUtil::incrementObjectFieldByID('dizkus__users', 'user_posts', $obj['topic_poster'], 'user_id');
    
            // update subscription
            if ($args['subscribe_topic'] == 1) {
                // user wants to subscribe the new topic
                $this->subscribe_topic(array('topic_id' => $obj['topic_id']));
            }
        }
    
        // update forums-table
        $fobj['forum_id']           = $obj['forum_id'];
        $fobj['forum_last_post_id'] = $pobj['post_id'];
        DBUtil::updateObject($fobj, 'dizkus_forums', null, 'forum_id');
        DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_posts',  $obj['forum_id'], 'forum_id');
        DBUtil::incrementObjectFieldByID('dizkus_forums', 'forum_topics', $obj['forum_id'], 'forum_id');
    
        // notify for newtopic
        $this->notify_by_email(array('topic_id' => $obj['topic_id'], 'poster_id' => $obj['topic_poster'], 'post_message' => $posted_message, 'type' => '0'));
    
        // delete temporary session var
        SessionUtil::delVar('topic_started');
    
        //  switch to topic display
        return $obj['topic_id'];
    }
    

    /**
     * Update post
     *
     * Updates a posting in the db after editing it.
     *
     * @param array $args The arguments array.
     *        int $args['post_id'] The postings id.
     *        int $args['topic_id'] The topic id (might be empty!!!).
     *        string $args['subject'] The subject.
     *        string $args['message'] The text.
     *        boolean $args['delete'] True if the posting is to be deleted.
     *        boolean $args['attach_signature'] True if the addsig place holder has to be appended.
     *
     * @return string url to redirect to after action (topic of forum if the (last) posting has been deleted)
     */
    public function updatepost($args)
    {
        if (!isset($args['topic_id']) || empty($args['topic_id']) || !is_numeric($args['topic_id'])) {
            $args['topic_id'] = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_postid', array('post_id' => $args['post_id']));
        }
        
        if ($this->isSpam($args['message'])) {
            return LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
        }
    
        $ztable = DBUtil::getTables();
    
        $sql = "SELECT p.poster_id,
                       p.forum_id,
                       t.topic_status,
                       f.cat_id
                FROM  ".$ztable['dizkus_posts']." as p,
                      ".$ztable['dizkus_topics']." as t,
                      ".$ztable['dizkus_forums']." as f
                WHERE (p.post_id = '".(int)DataUtil::formatForStore($args['post_id'])."')
                  AND (t.topic_id = p.topic_id)
                  AND (f.forum_id = p.forum_id)";

        $res = DBUtil::executeSQL($sql);
        $colarray = array('poster_id', 'forum_id', 'topic_status', 'cat_id');
        $result = DBUtil::marshallObjects($res, $colarray);
        $row = $result[0];




        if (!is_array($row)) {
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        if (trim($args['message']) == '') {
            // no message
            return LogUtil::registerError($this->__('Error! You tried to post a blank message. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        if ((($row['poster_id'] != UserUtil::getVar('uid')) || ($row['topic_status'] == 1)) &&
            !ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $row)) {
            // user is not allowed to edit post
            return LogUtil::registerPermissionError();
        }
    
    
        if (empty($args['delete'])) {
    
            // update the posting
            if (!ModUtil::apiFunc($this->name, 'Permission', 'canAdministrate', $row['cat_id'])) {
                // if not admin then add a edited by line
                // If it's been edited more than once, there might be old "edited by" strings with
                // escaped HTML code in them. We want to fix this up right here:
                $args['message'] = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $args['message']);
                // who is editing?
                $edit_name  = UserUtil::isLoggedIn() ? UserUtil::getVar('uname') : ModUtil::getVar('Users', 'anonymous');
                $edit_date = DateUtil::formatDatetime('', 'datetimebrief');
                $args['message'] .= '<br /><br /><!-- editby --><br /><br /><em>' . $this->__f('Edited by %1$s on %2$s.', array($edit_name, $edit_date)) . '</em><!-- end editby --> ';
            }
    
            // add signature placeholder
            if ($row['poster_id'] <> 1 && $args['attach_signature'] == true) {
                $args['message'] .= '[addsig]';
            }
    
            $updatepost = array('post_id'   => $args['post_id'],
                                'post_text' => $args['message']);
            DBUtil::updateObject($updatepost, 'dizkus_posts', null, 'post_id');
    
            if (trim($args['subject']) != '') {
                //  topic has a new subject
                $updatetopic = array('topic_id'    => $args['topic_id'],
                                     'topic_title' => $args['subject']);
                DBUtil::updateObject($updatetopic, 'dizkus_topics', null, 'topic_id');
            }
    
            // Let any hooks know that we have updated an item.
            // ModUtil::callHooks('item', 'update', $post_id, array('module' => 'Dizkus'));
            // ModUtil::callHooks('item', 'update', $args['post_id'], array('module'  => 'Dizkus',
            // 'post_id' => $args['post_id']));
    
            // update done, return now
            return ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $args['topic_id']));
    
        } else {
            // we are going to delete this posting
            // read raw posts in this topic, sorted by post_time asc
            $posts = DBUtil::selectObjectArray('dizkus_posts', 'topic_id='.$args['topic_id'], 'post_time asc, post_id asc', 1, -1, 'post_id');
            
            // figure out first and last posting and the one to delete
            reset($posts);
            $firstpost = current($posts);
            $lastpost = end($posts);
            $post_to_delete = $posts[$args['post_id']];
            
            // read the raw topic itself
            $topic = ModUtil::apiFunc('Dizkus', 'Topic', 'read0', $args['topic_id']);
            // read the raw forum
            $forum = DBUtil::selectObjectById('dizkus_forums', $firstpost['forum_id'], 'forum_id');

            if ($args['post_id'] == $lastpost['post_id']) {
                // posting is the last one in the array
                // if it is the first one too, delete the topic
                if ($args['post_id'] == $firstpost['post_id']) {
                    // ... and it is also the first posting in the topic, so we can simply
                    // delete the complete topic
                    // this also adjusts the counters
                    ModUtil::apiFunc('Dizkus', 'user', 'deletetopic', array('topic_id' => $args['topic_id']));
                    // cannot return to the topic, must return to the forum
                    return System::redirect(ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $row['forum_id'])));
                } else {
                    // it was the last one, but there is still more in this topic
                    // find the new "last posting" in this topic
                    $cutofflastpost = array_pop($posts);
                    $newlastpost = end($posts);
                    $topic['topic_replies']--;
                    $topic['topic_last_post_id'] = $newlastpost['post_id'];
                    $topic['topic_time']         = $newlastpost['post_time'];
                    $forum['forum_posts']--;
                    // get the forums last posting id - may be from another topic and may have changed - does not need to
                    $forum['forum_last_post_id'] = DBUtil::selectFieldMax('dizkus_posts', 'post_id', 'MAX', 'forum_id='.DataUtil::formatForStore($forum['forum_id']).' AND post_id<>'.DataUtil::formatForStore($args['post_id']));
                }
            } else {
                // posting is not the last one, so we can simply decrement the posting counters in the forum and the topic
                // last_posts ids do not change, neither in the topic nor the forum
                $forum['forum_posts']--;
                $topic['topic_replies']--;
            }
            
            // finally delete the posting now
            DBUtil::deleteObjectByID('dizkus_posts', $args['post_id'], 'post_id');
            
            // decrement user post counter 
            UserUtil::setVar('dizkus_user_posts', UserUtil::getVar('dizkus_user_posts', $post_to_delete['poster_id']) - 1, $post_to_delete['poster_id']);
            //DBUtil::decrementObjectFieldByID('dizkus__users', 'user_posts', $post_to_delete['poster_id'], 'user_id');
             
            // update forum
            DBUtil::updateObject($forum, 'dizkus_forums', null, 'forum_id');
            
            // update topic
            DBUtil::updateObject($topic, 'dizkus_topics', null, 'topic_id');

            if (SessionUtil::getVar('zk_ajax_call', '')  <> 'ajax') {
                $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic['topic_id']));
                return System::redirect($url);
            }
        }
    
        // we should not get here, but who knows...
        return System::redirect(ModUtil::url('Dizkus', 'user', 'main'));
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
     * get_forumid_and categoryid_from_topicid
     * used for permission checks
     *
     * @params $args['topic_id'] int the topics id
     * @returns array(forum_id, category_id)
     */
    public function get_forumid_and_categoryid_from_topicid($args)
    {
        $ztable = DBUtil::getTables();

        // we know about the topic_id, let's find out the forum and catgeory name for permission checks
        $sql = "SELECT f.forum_id,
                       c.cat_id
                FROM  ".$ztable['dizkus_topics']." t
                LEFT JOIN ".$ztable['dizkus_forums']." f ON f.forum_id = t.forum_id
                LEFT JOIN ".$ztable['dizkus_categories']." AS c ON c.cat_id = f.cat_id
                WHERE t.topic_id = '".(int)DataUtil::formatForStore($args['topic_id'])."'";
    
        $res = DBUtil::executeSQL($sql);
        if ($res === false) {
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        $colarray = array('forum_id' => 'forum_id','cat_id' => 'cat_id');
        $objarray = DBUtil::marshallObjects($res, $colarray);

        return $objarray[0]; // forum_id, cat_id
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
     * movetopic
     * moves a topic to another forum
     *
     * @params $args['topic_id'] int the topics id
     * @params $args['forum_id'] int the destination forums id
     * @params $args['shadow']   boolean true = create shadow topic
     * @returns void
     */
    public function movetopic($args)
    {
        // get the old forum id and old post date
	$topic = $this->entityManager->find('Dizkus_Entity_Topics', $args['topic_id'])->toArray();

	if ($topic['forum_id'] <> $args['forum_id']) {
            // set new forum id
            $newtopic['forum_id'] = $args['forum_id'];
            DBUtil::updateObject($newtopic, 'dizkus_topics', 'topic_id='.(int)DataUtil::formatForStore($args['topic_id']), 'topic_id');
    
            $newpost['forum_id'] = $args['forum_id'];
            DBUtil::updateObject($newpost, 'dizkus_posts', 'topic_id='.(int)DataUtil::formatForStore($args['topic_id']), 'post_id');
    
            if ($args['shadow'] == true) {
                // user wants to have a shadow topic
                $message = $this->__f('The original posting has been moved <a title="moved" href="%s">here</a>.', ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $args['topic_id'])));
                $subject = $this->__f("*** The original posting '%s' has been moved", $topic['topic_title']);
    
                $this->storenewtopic(array('subject'  => $subject,
                                                    'message'  => $message,
                                                    'forum_id' => $topic['forum_id'],
                                                    'time'     => $topic['topic_time'],
                                                    'no_sig'   => true));
            }
            ModUtil::apiFunc('Dizkus', 'admin', 'sync', array('id' => $args['forum_id'], 'type' => 'forum'));
            ModUtil::apiFunc('Dizkus', 'admin', 'sync', array('id' => $topic['forum_id'], 'type' => 'forum'));
        }
    
        return;
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
            $this->view->assign('reply_url', ModUtil::url('Dizkus', 'user', 'reply', array('topic' => $args['topic_id'], 'forum' => $forum_id), null, null, true));
            $this->view->assign('topic_url', ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $args['topic_id']), null, null, true));
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
     * getTopicSubscriptions
     *
     * @params none
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     * @returns array with topic ids, may be empty
     */
    public function getTopicSubscriptions($uid)
    {
        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_TopicSubscriptions')
                                   ->findBy(array('user_id' => $uid));
    
        return $subscriptions;
    }
    
    
    /**
     * get_topic_subscriptions
     *
     * @params none
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     *
     * @return array with topic ids, may be empty
     */
    public function get_topic_subscriptions($args)
    {
        $ztable = DBUtil::getTables();
    
        if (isset($args['user_id'])) {
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
    
        // read the topic ids
        $sql = 'SELECT ts.topic_id,
                       t.topic_title,
                       t.topic_poster,
                       t.topic_time,
                       t.topic_replies,
                       t.topic_last_post_id,
                       u.uname,
                       f.forum_id,
                       f.forum_name
                FROM '.$ztable['dizkus_topic_subscription'].' AS ts,
                     '.$ztable['dizkus_topics'].' AS t,
                     '.$ztable['users'].' AS u,
                     '.$ztable['dizkus_forums'].' AS f
                WHERE (ts.user_id='.(int)DataUtil::formatForStore($args['user_id']).'
                  AND t.topic_id=ts.topic_id
                  AND u.uid=ts.user_id
                  AND f.forum_id=t.forum_id)
                ORDER BY f.forum_id, ts.topic_id';
    
        $res = DBUtil::executeSQL($sql);
        $colarray = array('topic_id', 'topic_title', 'topic_poster', 'topic_time', 'topic_replies', 'topic_last_post_id', 'poster_name',
                          'forum_id', 'forum_name');
        $subscriptions    = DBUtil::marshallObjects($res, $colarray);
    
        $post_sort_order = ModUtil::apiFunc('Dizkus', 'user', 'get_user_post_order', array('user_id' => $args['user_id']));
        $posts_per_page  = ModUtil::getVar('Dizkus', 'posts_per_page');
    
        if (is_array($subscriptions) && !empty($subscriptions)) {
            for ($cnt=0; $cnt<count($subscriptions); $cnt++) {
                 
                if ($post_sort_order == 'ASC') {
                    $start = ((ceil(($subscriptions[$cnt]['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page);
                } else {
                    // latest topic is on top anyway...
                    $start = 0;
                }
                // we now create the url to the last post in the thread. This might
                // on site 1, 2 or what ever in the thread, depending on topic_replies
                // count and the posts_per_page setting
                $subscriptions[$cnt]['last_post_url'] = DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                                                     array('topic' => $subscriptions[$cnt]['topic_id'],
                                                                                           'start' => $start)));
                
                $subscriptions[$cnt]['last_post_url_anchor'] = $subscriptions[$cnt]['last_post_url'] . '#pid' . $subscriptions[$cnt]['topic_last_post_id'];
            }
        }
    
        return $subscriptions;
    }



    /**
     * unsubscribe_topic_by_id
     *
     * @params $args['forum_id'] int the forums id, if empty then we unsubscribe all forums
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     * @returns void
     */
    public function unsubscribe_topic_by_id($id)
    {
        $subscription = $this->entityManager->find('Dizkus_Entity_TopicSubscriptions', $id);
        $this->entityManager->remove($subscription);
        $this->entityManager->flush();
    }
    

    
    /**
     * get_latest_posts
     *
     * @params $args['selorder'] int 1-6, see below
     * @params $args['nohours'] int posting within these hours
     * @params $args['unanswered'] int 0 or 1(= postings with no answers)
     * @params $args['last_visit'] string the users last visit data
     * @params $args['last_visit_unix'] string the users last visit data as unix timestamp
     * @params $args['limit'] int limits the numbers hits read (per list), defaults and limited to 250
     * @returns array (postings, mail2forumpostings, rsspostings, text_to_display)
     */
    public function get_latest_posts($args)
    {
        $ztable = DBUtil::getTables();
    
        // init some arrays
        $posts = array();
        $m2fposts = array();
        $rssposts = array();
    
        if (!isset($args['limit']) || empty($args['limit']) || ($args['limit'] < 0) || ($args['limit'] > 100)) {
            $args['limit'] = 100;
        }
    
        $dizkusvars      = ModUtil::getVar('Dizkus');
        $posts_per_page  = $dizkusvars['posts_per_page'];
        $post_sort_order = $dizkusvars['post_sort_order'];
        $hot_threshold   = $dizkusvars['hot_threshold'];
    
        if ($args['unanswered'] == 1) {
            $args['unanswered'] = "AND t.topic_replies = '0' ORDER BY t.topic_time DESC";
        } else {
            $args['unanswered'] = 'ORDER BY t.topic_time DESC';
        }
    
        // sql part per selected time frame
        switch ($args['selorder'])
        {
            case '2' : // today
                       $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) = 0 ";
                       $text = $this->__('Today');
                       break;
            case '3' : // yesterday
                       $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) = 1 ";
                       $text = $this->__('Yesterday');
                       break;
            case '4' : // lastweek
                       $wheretime = " AND TO_DAYS(NOW()) - TO_DAYS(t.topic_time) < 8 ";
                       $text= $this->__('Last week');
                       break;
            case '5' : // last x hours
                       $wheretime  = " AND t.topic_time > DATE_SUB(NOW(), INTERVAL " . DataUtil::formatForStore($args['nohours']) . " HOUR) ";
                       $text = DataUtil::formatForDisplay($this->__f('Last %s hours', $args['nohours']));
                       break;
            case '6' : // last visit
                       $wheretime = " AND t.topic_time > '" . DataUtil::formatForStore($args['last_visit']) . "' ";
                       $text = DataUtil::formatForDisplay($this->__f('Last visit: %s', DateUtil::formatDatetime($args['last_visit_unix'], 'datetimebrief')));
                       break;
            case '1' :
            default:   // last 24 hours
                       $wheretime = " AND t.topic_time > DATE_SUB(NOW(), INTERVAL 1 DAY) ";
                       $text  =$this->__('Last 24 hours');
                       break;
        }
    
        // get all forums the user is allowed to read
        $userforums = ModUtil::apiFunc('Dizkus', 'user', 'readuserforums');
        if (!is_array($userforums) || count($userforums) == 0) {
            // error or user is not allowed to read any forum at all
            // return empty result set without even doing a db access
            return array($posts, $m2fposts, $rssposts, $text);
        }
    
        // now create a very simple array of forum_ids only. we do not need
        // all the other stuff in the $userforums array entries
        $allowedforums = array_map('_get_forum_ids', $userforums);
        $whereforum = ' f.forum_id IN (' . DataUtil::formatForStore(implode(',', $allowedforums)) . ') ';
    
        // integrate contactlist's ignorelist here
        $whereignorelist = '';
        if ((isset($dizkusvars['ignorelist_options']) && $dizkusvars['ignorelist_options'] <> 'none') && ModUtil::available('ContactList')) {
            $ignorelist_setting = ModUtil::apiFunc('Dizkus', 'user', 'get_settings_ignorelist', array('uid' => UserUtil::getVar('uid')));
            if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
                // get user's ignore list
                $ignored_users = ModUtil::apiFunc('ContactList', 'user', 'getallignorelist', array('uid' => UserUtil::getVar('uid')));
                $ignored_uids = array();
                foreach ($ignored_users as $item) {
                    $ignored_uids[] = (int)$item['iuid'];
                }
                if (count($ignored_uids) > 0) {
                    $whereignorelist = " AND t.topic_poster NOT IN (".DataUtil::formatForStore(implode(',', $ignored_uids)).")";
                }
            }
        }
    
        $topicscols = DBUtil::_getAllColumnsQualified('dizkus_topics', 't');
        // build the tricky sql
        $sql = 'SELECT '. $topicscols .',
                          f.forum_name,
                          f.forum_pop3_active,
                          c.cat_id,
                          c.cat_title,
                          p.post_time,
                          p.poster_id
                     FROM '.$ztable['dizkus_topics'].' AS t,
                          '.$ztable['dizkus_forums'].' AS f,
                          '.$ztable['dizkus_categories'].' AS c,
                          '.$ztable['dizkus_posts'].' AS p
                    WHERE f.forum_id = t.forum_id
                      AND c.cat_id = f.cat_id
                      AND p.post_id = t.topic_last_post_id
                      AND '.$whereforum
                           .$wheretime
                           .$whereignorelist
                           .$args['unanswered'];
    
        $res = DBUtil::executeSQL($sql, -1, $args['limit']);
    
        $colarray   = DBUtil::getColumnsArray ('dizkus_topics');
        $colarray[] = 'forum_name';
        $colarray[] = 'forum_pop3_active';
        $colarray[] = 'cat_id';
        $colarray[] = 'cat_title';
        $colarray[] = 'post_time';
        $colarray[] = 'poster_id';
        $postarray  = DBUtil::marshallObjects ($res, $colarray);
    
        foreach ($postarray as $post) {
            $post = DataUtil::formatForDisplay($post);
    
            // does this topic have enough postings to be hot?
            $post['hot_topic'] = ($post['topic_replies'] >= $hot_threshold) ? true : false;
    
            // get correct page for latest entry
            if ($post_sort_order == 'ASC') {
                $hc_dlink_times = 0;
                if (($post['topic_replies'] + 1 - $posts_per_page) >= 0) {
                    $hc_dlink_times = 0;
                    for ($x = 0; $x < $post['topic_replies'] + 1 - $posts_per_page; $x += $posts_per_page) {
                        $hc_dlink_times++;
                    }
                }
                $start = $hc_dlink_times * $posts_per_page;
            } else {
                // latest topic is on top anyway...
                $start = 0;
            }
            $post['start'] = $start;
    
            if ($post['poster_id'] == 1) {
                $post['poster_name'] = ModUtil::getVar('Users', 'anonymous');
            } else {
                $post['poster_name'] = UserUtil::getVar('uname', $post['poster_id']);
            }
    
            $post['posted_unixtime'] = strtotime($post['post_time']);
            $post['post_time'] = DateUtil::formatDatetime($post['posted_unixtime'], 'datetimebrief');
    
            $post['last_post_url'] = DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewtopic',
                                                         array('topic' => $post['topic_id'],
                                                               'start' => (ceil(($post['topic_replies'] + 1)  / $posts_per_page) - 1) * $posts_per_page), null, null, true));
    
            $post['last_post_url_anchor'] = $post['last_post_url'] . '#pid' . $post['topic_last_post_id'];
    
            switch ((int)$post['forum_pop3_active'])
            {
                case 1: // mail2forum
                    array_push($m2fposts, $post);
                    break;
                case 2:
                    array_push($rssposts, $post);
                    break;
                case 0: // normal posting
                default:
                    array_push($posts, $post);
            }
        }
    
        return array($posts, $m2fposts, $rssposts, $text);
    }


    /**
     * splittopic
     *
     * param array $args The argument array.
     *
     * @params $args['post'] array with posting data as returned from readpost()
     *
     * @deprecated since 4.0.0
     *
     * @return int id of the new topic
     */
    public function splittopic($args)
    {
        return ModUtil::apiFunc($this->name, 'Forum', 'unsubscribeById', $id);

        $post = $args['post'];

        // before we do anything we will read the topic_last_post_id because we will need
        // this one later (it will become the topic_last_post_id of the new thread)
        // DBUtil:: read complete topic
        $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopci0', $post['topic_id']);
    
        //  insert values into topics-table
        $newtopic = array('topic_title'  => $post['topic_subject'],
                          'topic_poster' => $post['poster_data']['uid'],
                          'forum_id'     => $post['forum_id'],
                          'topic_time'   => DateUtil::getDatetime('', '%Y-%m-%d %H:%M:%S'));
        $newtopic = DBUtil::insertObject($newtopic, 'dizkus_topics', 'topic_id');
    
        // increment topics count by 1
        DBUtil::incrementObjectFieldById('dizkus_forums', 'forum_topics', $post['forum_id'], 'forum_id');
    
        // now we need to change the postings:
        // first step: count the number of posting we have to move
        $where = 'WHERE topic_id = '.(int)DataUtil::formatForStore($post['topic_id']).'
                  AND post_id >= '.(int)DataUtil::formatForStore($post['post_id']);
        $posts_to_move = DBUtil::selectObjectCount('dizkus_posts', $where);



        // update the topic_id in the postings
        // starting with $post['post_id'] and then all post_id's where topic_id = $post['topic_id'] and
        // post_id > $post['post_id']
        $updateposts = array('topic_id' => $newtopic['topic_id']);
        $where = 'WHERE post_id >= '.(int)DataUtil::formatForStore($post['post_id']).'
                  AND topic_id = '.$post['topic_id'];
        DBUtil::updateObject($updateposts, 'dizkus_posts', $where, 'post_id');
    
        // get the new topic_last_post_id of the old topic
        $where = 'WHERE topic_id='.(int)DataUtil::formatForStore($post['topic_id']).'
                  ORDER BY post_time DESC';
        $lastpost = DBUtil::selectObject('dizkus_posts', $where);

        $oldtopic = ModUtil::apiFunc($this->name, 'Topic', 'read0', $post['topic_id']);

        // update the new topic
        $newtopic['topic_replies']      = (int)$posts_to_move - 1;
        $newtopic['topic_last_post_id'] = $post['topic_last_post_id'];
        DBUtil::updateObject($newtopic, 'dizkus_topics', null, 'topic_id');


        // update the old topic
        $oldtopic['topic_replies']      = $oldtopic['topic_replies'] - $posts_to_move;
        $oldtopic['topic_last_post_id'] = $lastpost['post_id'];
        $oldtopic['topic_time']         = $lastpost['post_time'];
        DBUtil::updateObject($oldtopic, 'dizkus_topics', null, 'topic_id');

        return $newtopic['topic_id'];
    }
    
    /**
     * get_previous_or_next_topic_id
     * returns the next or previous topic_id in the same forum of a given topic_id
     *
     * @params $args['topic_id'] int the reference topic_id
     * @params $args['view']     string either "next" or "previous"
     * @returns int topic_id maybe the same as the reference id if no more topics exist in the selectd direction
     */
    public function get_previous_or_next_topic_id($args)
    {
        if (!isset($args['topic_id']) || !isset($args['view']) ) {
            return LogUtil::registerArgsError();
        }
    
        switch ($args['view'])
        {
            case 'previous':
                $math = '<';
                $sort = 'DESC';
                break;
    
            case 'next':
                $math = '>';
                $sort = 'ASC';
                break;
    
            default:
                return LogUtil::registerArgsError();
        }
    
        $ztable = DBUtil::getTables();
    
        // integrate contactlist's ignorelist here
        $whereignorelist = '';
        $ignorelist_setting = ModUtil::apiFunc('Dizkus', 'user', 'get_settings_ignorelist',array('uid' => UserUtil::getVar('uid')));
        if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
            // get user's ignore list
            $ignored_users = ModUtil::apiFunc('ContactList', 'user', 'getallignorelist',array('uid' => UserUtil::getVar('uid')));
            $ignored_uids = array();
            foreach ($ignored_users as $item) {
                $ignored_uids[]=(int)$item['iuid'];
            }
            if (count($ignored_uids) > 0) {
                $whereignorelist = " AND t1.topic_poster NOT IN (".implode(',',$ignored_uids).")";
            }
        }
    
        $sql = 'SELECT t1.topic_id
                FROM '.$ztable['dizkus_topics'].' AS t1,
                     '.$ztable['dizkus_topics'].' AS t2
                WHERE t2.topic_id = '.(int)DataUtil::formatForStore($args['topic_id']).'
                  AND t1.topic_time '.$math.' t2.topic_time
                  AND t1.forum_id = t2.forum_id
                  AND t1.sticky = 0
                  '.$whereignorelist.'
                ORDER BY t1.topic_time '.$sort;
    
        $res      = DBUtil::executeSQL($sql, -1, 1);
        $newtopic = DBUtil::marshallObjects($res, array('topic_id'));
    
        return isset($newtopic[0]['topic_id']) ? $newtopic[0]['topic_id'] : 0;
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
     * get_page_from_topic_replies
     * Uses the number of topic_replies and the posts_per_page settings to determine the page
     * number of the last post in the thread. This is needed for easier navigation.
     *
     * @params $args['topic_replies'] int number of topic replies
     * @return int page number of last posting in the thread
     */
    public function get_page_from_topic_replies($args)
    {
        if (!isset($args['topic_replies']) || !is_numeric($args['topic_replies']) || $args['topic_replies'] < 0 ) {
            return LogUtil::registerArgsError();
        }
    
        // get some enviroment
        $posts_per_page  = ModUtil::getVar('Dizkus', 'posts_per_page');
        $post_sort_order = ModUtil::getVar('Dizkus', 'post_sort_order');
    
        $last_page = 0;
        if ($post_sort_order == 'ASC') {
            // +1 for the initial posting
            $last_page = floor(($args['topic_replies'] + 1) / $posts_per_page);
        }
    
        // if not ASC then DESC which means latest topic is on top anyway...
        return $last_page;
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
                                if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $forum)) {
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
                                                    $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topic_by_postmsgid',
                                                                             array('msgid' => $replyto));
                                                    if (is_bool($topic_id) && $topic_id == false) {
                                                        // msgid not found, we clear replyto to create a new topic
                                                        $replyto = '';
                                                    } else {
                                                        // topic_id found, add this posting as a reply there
                                                        list($start,
                                                             $post_id ) = ModUtil::apiFunc('Dizkus', 'user', 'storereply',
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
                                                    $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic',
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
     * get_topic_by_postmsgid
     * gets a topic_id from the postings msgid
     *
     * @params $args['msgid'] string the msgid
     * @returns int topic_id or false if not found
     *
     */
    public function get_topic_by_postmsgid($args)
    {
        if (!isset($args['msgid']) || empty($args['msgid'])) {
            return LogUtil::registerArgsError();
        }
    
        return DBUtil::selectFieldByID('dizkus_posts', 'topic_id', $args['msgid'], 'post_msgid');
    }
    
    /**
     * get_topicid_by_postid
     * gets a topic_id from the post_id
     *
     * @params $args['post_id'] string the post_id
     * @returns int topic_id or false if not found
     *
     */
    public function get_topicid_by_postid($args)
    {
        if (!isset($args['post_id']) || empty($args['post_id'])) {
            return LogUtil::registerArgsError();
        }
    
        return DBUtil::selectFieldByID('dizkus_posts', 'topic_id', $args['post_id'], 'post_id');
    }
    
    /**
     * movepost
     *
     * @params $args['post'] array with posting data as returned from readpost()
     * @params $args['to_topic']
     * @returns int id of the new topic
     */
    public function movepost($args)
    {
        $old_topic_id = $args['old_topic_id'];
        $to_topic_id     = $args['to_topic_id'];
        $post_id      = $args['post_id'];
        
        // 1 . update topic_id, post_time in posts table
        // for post[post_id]
        // 2 . update topic_replies in nuke_dizkus_topics ( COUNT )
        // for old_topic
        // 3 . update topic_last_post_id in nuke_dizkus_topics
        // for old_topic
        // 4 . update topic_replies in nuke_dizkus_topics ( COUNT )
        // 5 . update topic_last_post_id in nuke_dizkus_topics if necessary
    
        $ztable = DBUtil::getTables();
    
        // 1 . update topic_id in posts table
        $sql = 'UPDATE '.$ztable['dizkus_posts'].'
                SET topic_id='.(int)DataUtil::formatForStore($to_topic_id).'
                WHERE post_id = '.(int)DataUtil::formatForStore($post_id);
    
        DBUtil::executeSQL($sql);
    
        // for to_topic
        // 2 . update topic_replies in dizkus_topics ( COUNT )
        // 3 . update topic_last_post_id in dizkus_topics
        // get the new topic_last_post_id of to_topic
        $sql = 'SELECT post_id, post_time
                FROM '.$ztable['dizkus_posts'].'
                WHERE topic_id = '.(int)DataUtil::formatForStore($to_topic_id).'
                ORDER BY post_time DESC';
    
        $res = DBUtil::executeSQL($sql, -1, 1);
        $colarray = array('post_id', 'post_time');
        $result    = DBUtil::marshallObjects($res, $colarray);
        $to_last_post_id = $result[0]['post_id'];
        $to_post_time    = $result[0]['post_time'];
    
        $sql = 'UPDATE '.$ztable['dizkus_topics'].'
                SET topic_replies = topic_replies + 1,
                    topic_last_post_id='.(int)DataUtil::formatForStore($to_last_post_id).',
                    topic_time=\''.DataUtil::formatForStore($to_post_time).'\'
                WHERE topic_id='.(int)DataUtil::formatForStore($to_topic_id);
    
        DBUtil::executeSQL($sql);
    
        // for old topic ($old_topic_id)
        // 4 . update topic_replies in nuke_dizkus_topics ( COUNT )
        // 5 . update topic_last_post_id in nuke_dizkus_topics if necessary
    
        // get the new topic_last_post_id of the old topic
        $sql = 'SELECT post_id, post_time
                FROM '.$ztable['dizkus_posts'].'
                WHERE topic_id = '.(int)DataUtil::formatForStore($old_topic_id).'
                ORDER BY post_time DESC';
    
        $res = DBUtil::executeSQL($sql, -1, 1);
        $colarray = array('post_id', 'post_time');
        $result    = DBUtil::marshallObjects($res, $colarray);
        $old_last_post_id = $result[0]['post_id'];
        $old_post_time    = $result[0]['post_time'];
    
        // update
        $sql = 'UPDATE '.$ztable['dizkus_topics'].'
                SET topic_replies = topic_replies - 1,
                    topic_last_post_id='.(int)DataUtil::formatForStore($old_last_post_id).',
                    topic_time=\''.DataUtil::formatForStore($old_post_time).'\'
                WHERE topic_id='.(int)DataUtil::formatForStore($old_topic_id);
    
        DBUtil::executeSQL($sql);
    
        return $this->get_last_topic_page(array('topic_id' => $old_topic_id));
    }
    
    /**
     * get_last_topic_page
     * returns the number of the last page of the topic if more than posts_per_page entries
     * eg. for use as the start parameter in urls
     *
     * @params $args['topic_id'] int the topic id
     * @returns int the page number
     */
    public function get_last_topic_page($args)
    {
        // get some enviroment
        $posts_per_page = ModUtil::getVar('Dizkus', 'posts_per_page');
        $post_sort_order = ModUtil::getVar('Dizkus', 'post_sort_order');
    
        if (!isset($args['topic_id']) || !is_numeric($args['topic_id'])) {
            return LogUtil::registerArgsError();
        }
    
        if ($post_sort_order == 'ASC') {
            $num_postings = DBUtil::selectFieldByID('dizkus_topics', 'topic_replies', $args['topic_id'], 'topic_id');
            // add 1 for the initial posting as we deal with the replies here
            $num_postings++;
            $last_page = floor($num_postings / $posts_per_page);
        } else {
            // DESC = latest topic is on top = page 0 anyway...
            $last_page = 0;
        }
    
        return $last_page;
    }
    
    /**
     * jointopics
     * joins two topics together
     *
     * @params $args['from_topic_id'] int this topic get integrated into to_topic
     * @params $args['to_topic_id'] int   the target topic that will contain the post from from_topic
     */
    public function jointopics($args)
    {
        // check if from_topic exists. this function will return an error if not
        $from_topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $args['from_topic_id'], 'complete' => false, 'count' => false));
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $from_topic)) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
        // check if to_topic exists. this function will return an error if not
        $to_topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array('topic_id' => $args['to_topic_id'], 'complete' => false, 'count' => false));
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $to_topic)) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
        $ztable = DBUtil::getTables();
        
        // join topics: update posts with from_topic['topic_id'] to contain to_topic['topic_id']
        // and from_topic['forum_id'] to to_topic['forum_id']
        $post_temp = array('topic_id' => $to_topic['topic_id'],
                           'forum_id' => $to_topic['forum_id']);
        $where = 'WHERE topic_id='.(int)DataUtil::formatForStore($from_topic['topic_id']);
        DBUtil::updateObject($post_temp, 'dizkus_posts', $where, 'post_id');                         
    
        // to_topic['topic_replies'] must be incremented by from_topic['topic_replies'] + 1 (initial
        // posting
        // update to_topic['topic_time'] and to_topic['topic_last_post_id']
        // get new topic_time and topic_last_post_id
        $where = 'WHERE topic_id='.(int)DataUtil::formatForStore($to_topic['topic_id']).'
                  ORDER BY post_time DESC';
        $res = DBUtil::selectObject('dizkus_posts', $where);
        $new_last_post_id = $res['post_id'];
        $new_post_time    = $res['post_time'];
    
        // update to_topic
        $to_topic_temp = array('topic_id'           => $to_topic['topic_id'],
                               'topic_replies'      => $to_topic['topic_replies'] + $from_topic['topic_replies'] + 1,
                               'topic_last_post_id' => $new_last_post_id,
                               'topic_time'         => $new_post_time);
        DBUtil::updateObject($to_topic_temp, 'dizkus_topics', null, 'topic_id');  
    
        // delete from_topic from dizkus_topics
        DBUtil::deleteObjectByID('dizkus_topics', $from_topic['topic_id'], 'topic_id');
    
        // update forums table
        // get topics count: decrement from_topic['forum_id']'s topic count by 1
        DBUtil::decrementObjectFieldById('dizkus_forums', 'forum_topics', $from_topic['forum_id'], 'forum_id');
    
        // get posts count: if both topics are in the same forum, we just have to increment
        // the post count by 1 for the initial posting that is now part of the to_topic,
        // if they are in different forums, we have to decrement the post count
        // in from_topic's forum and increment it in to_topic's forum by from_topic['topic_replies'] + 1
        // for the initial posting
        // get last_post: if both topics are in the same forum, everything stays
        // as-is, if not, we update both, even if it is not necessary
    
        if ($from_topic['forum_id'] == $to_topic['forum_id']) {
            // same forum, post count in the forum doesn't change
        } else {
            // different forum
            // get last post in forums
            $where = 'WHERE forum_id='.(int)DataUtil::formatForStore($from_topic['forum_id']).'
                      ORDER BY post_time DESC';
            $res = DBUtil::selectObject('dizkus_posts', $where);
            $from_forum_last_post_id = $res['post_id'];
    
            $where = 'WHERE forum_id='.(int)DataUtil::formatForStore($to_topic['forum_id']).'
                      ORDER BY post_time DESC';
            $res = DBUtil::selectObject('dizkus_posts', $where);
            $to_forum_last_post_id = $res['post_id'];
            
            // calculate posting count difference
            $post_count_difference = (int)DataUtil::formatForStore($from_topic['topic_replies']+1);
            // decrement from_topic's forum post_count
            $sql = "UPDATE ".$ztable['dizkus_forums']."
                    SET forum_posts = forum_posts - $post_count_difference,
                        forum_last_post_id = '" . (int)DataUtil::formatForStore($from_forum_last_post_id) . "'
                    WHERE forum_id='".(int)DataUtil::formatForStore($from_topic['forum_id'])."'";
            DBUtil::executeSQL($sql);
    
            // increment o_topic's forum post_count
            $sql = "UPDATE ".$ztable['dizkus_forums']."
                    SET forum_posts = forum_posts + $post_count_difference,
                        forum_last_post_id = '" . (int)DataUtil::formatForStore($to_forum_last_post_id) . "'
                    WHERE forum_id='".(int)DataUtil::formatForStore($to_topic['forum_id'])."'";
            DBUtil::executeSQL($sql);
        }
        return $to_topic['topic_id'];
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
    
        $start = ModUtil::apiFunc('Dizkus', 'user', 'get_page_from_topic_replies',
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
     * get_topicid_by_reference
     * gets a topic reference as parameter and delivers the internal topic id
     * used for Dizkus as comment module
     *
     * @params $args['reference'] string the refernce
     */
    public function get_topicid_by_reference($args)
    {
        if (!isset($args['reference']) || empty($args['reference'])) {
            return LogUtil::registerArgsError();
        }
    
        $topic = $this->entityManager->getRepository('Dizkus_Entity_Topics')
                      ->findOneBy(array('topic_reference' => $args['reference']));
        return $topic->toArray();
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
            $check = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_reference',
                                  array('reference' => $reference));
    
            if ($check == false) {
                // Not found... we can add the news.
                $subject  = $item->get_title();
    
                // Adding little display goodies - finishing with the url of the news...
                $message  = $boldstart . $this->__('Summary') . ' :' . $boldend . "\n\n" . $item->get_description() . "\n\n" . $urlstart . $item->get_link() . $urlend . "\n\n";
    
                // store message in forum
                $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic',
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
     * getTopicSubscriptions
     *
     * @params none
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     * @returns array with topic ids, may be empty
     */
    public function getForumSubscriptions($uid)
    {
        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_ForumSubscriptionsJoin')
                                   ->findBy(array('user_id' => $uid));
    
        return $subscriptions;
    }
    
    /**
     * get_forum_subscriptions
     *
     * @params none
     * @params $args['user_id'] int the users id (needs ACCESS_ADMIN)
     * @returns array with forum ids, may be empty
     */
    public function get_forum_subscriptions($args)
    {
        if (isset($args['user_id'])) {
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
    
        $ztable = DBUtil::getTables();
    
        // read the topic ids
        $sql = 'SELECT f.' . $ztable['dizkus_forums_column']['forum_id'] . ',
                       f.' . $ztable['dizkus_forums_column']['forum_name'] . ',
                       c.' . $ztable['dizkus_categories_column']['cat_id'] . ',
                       c.' . $ztable['dizkus_categories_column']['cat_title'] . '
                FROM ' . $ztable['dizkus_subscription'] . ' AS fs,
                     ' . $ztable['dizkus_forums'] . ' AS f,
                     ' . $ztable['dizkus_categories'] . ' AS c 
                WHERE fs.' . $ztable['dizkus_subscription_column']['user_id'] . '=' . (int)DataUtil::formatForStore($args['user_id']) . '
                  AND f.' . $ztable['dizkus_forums_column']['forum_id'] . '=fs.' . $ztable['dizkus_subscription_column']['forum_id'] . '
                  AND c.' . $ztable['dizkus_categories_column']['cat_id'] . '=f.' . $ztable['dizkus_forums_column']['cat_id']. '
                ORDER BY c.' . $ztable['dizkus_categories_column']['cat_order'] . ', f.' . $ztable['dizkus_forums_column']['forum_order'];
    
        $res           = DBUtil::executeSQL($sql);
        $colarray      = array('forum_id', 'forum_name', 'cat_id', 'cat_title');
        $subscriptions = DBUtil::marshallObjects($res, $colarray);
    
        return $subscriptions;
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
     * toggle new topic subscription
     *
     */
    public function togglenewtopicsubscription($args)
    {
        $user_id = (isset($args['user_id'])) ? $args['user_id'] : UserUtil::getVar('uid');
        if (is_null($user_id)) {
            $user_id = 1;
        }
        
        $asmode = (int)UserUtil::getVar('dizkus_autosubscription', $user_id);
        $asmode = ($asmode == 0) ? 1 : 0;
        UserUtil::setVar('dizkus_autosubscription', $asmode, $user_id);
        return $asmode;
    }



 
    
    public function isSpam($message)
    {        
        // Akismet
        if (ModUtil::available('Akismet') && $this->getVar('spam_protector') == 'Akismet') {
            if (ModUtil::apiFunc('Akismet', 'user', 'isspam', array('content' => $message))) {
                return true;
            }
        }
        
        return false;
    }

}


/**
 * helper function to extract forum_ids from forum array
 */
function _get_forum_ids($f)
{
    return $f['forum_id'];
}