<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
class Dizkus_Api_User extends Zikula_AbstractApi
{

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
        $id = isset($args['id']) ? $args['id'] : null;
        $type = isset($args['type']) ? $args['type'] : null;

        static $cache = array();

        switch ($type) {
            case 'all':
            case 'allposts':
                if (!isset($cache[$type])) {
                    $cache[$type] = $this->countEntity('Post');
                }

                return $cache[$type];
                break;

            case 'forum':
                if (!isset($cache[$type])) {
                    $cache[$type] = $this->countEntity('Forum');
                }

                return $cache[$type];
                break;

            case 'topic':
                if (!isset($cache[$type][$id])) {
                    $cache[$type][$id] = $this->countEntity('Post', 'topic', $id);
                }

                return $cache[$type][$id];
                break;

            case 'forumposts':
                if (!isset($cache[$type][$id])) {
                    $cache[$type][$id] = $this->countEntity('Post', 'forum_id', $id);
                }

                return $cache[$type][$id];
                break;

            case 'forumtopics':
                if (!isset($cache[$type][$id])) {
                    $cache[$type][$id] = $this->countEntity('Topic', 'forum', $id);
                }

                return $cache[$type][$id];
                break;

            case 'alltopics':
                if (!isset($cache[$type])) {
                    $cache[$type] = $this->countEntity('Topic');
                }

                return $cache[$type];
                break;

            case 'allmembers':
                if (!isset($cache[$type])) {
                    $cache[$type] = count(UserUtil::getUsers());
                }

                return $cache[$type];
                break;

            case 'lastmember':
            case 'lastuser':
                if (!isset($cache[$type])) {
                    $res = DBUtil::selectObjectArray('users', null, 'uid DESC', 1, 1);
                    $cache[$type] = $res[0]['uname'];
                }

                return $cache[$type];
                break;

            default:
                return LogUtil::registerError($this->__("Error! Wrong parameters in boardstats()."), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    }

    private function countEntity($entityname, $where = null, $parameter = null)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(a)')
                ->from('Dizkus_Entity_' . $entityname, 'a');
        if (isset($where) && isset($parameter)) {
            $qb->andWhere('a.' . $where . ' = :parameter')
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
            $option = (isset($args['first']) && $args['first'] == true) ? 'MIN' : 'MAX';
            $post_id = DBUtil::selectFieldMax('dizkus_posts', 'post_id', $option, $ztable['dizkus_posts_column']['topic_id'] . ' = ' . (int)$args['topic_id']);

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
            $post_id = DBUtil::selectfieldMax('dizkus_posts', 'post_id', 'MAX', $ztable['dizkus_posts_column']['forum_id'] . ' = ' . (int)$args['forum_id']);

            if (isset($args['id_only']) && $args['id_only'] == true) {
                return $post_id;
            }

            return $this->readpost(array('post_id' => $post_id));
        }

        return false;
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

        $time = time();
        CookieUtil::setCookie('DizkusLastVisit', "$time", $time + 31536000, $path, null, null, false);
        $lastVisitTemp = CookieUtil::getCookie('DizkusLastVisitTemp', false, null);
        $temptime = empty($lastVisitTemp) ? $time : $lastVisitTemp;

        // set LastVisitTemp cookie, which only gets the time from the LastVisit and lasts for 30 min
        CookieUtil::setCookie('DizkusLastVisitTemp', "$temptime", time() + 1800, $path, null, null, false);

        return array(DateUtil::formatDatetime($temptime, '%Y-%m-%d %H:%M:%S'), $temptime);
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

        $viewip['poster_ip'] = DBUtil::selectField('dizkus_posts', 'poster_ip', 'post_id=' . DataUtil::formatForStore($args['post_id']));
        $viewip['poster_host'] = gethostbyaddr($viewip['poster_ip']);

        $sql = "SELECT uid, uname, count(*) AS postcount
                FROM " . $ztable['dizkus_posts'] . " p, " . $ztable['users'] . " u
                WHERE poster_ip='" . DataUtil::formatForStore($viewip['poster_ip']) . "' && p.poster_id = u.uid
                GROUP BY uid";
        $res = DBUtil::executeSQL($sql);
        $colarray = array('uid', 'uname', 'postcount');
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
        $joinInfo[] = array('join_table' => 'dizkus_categories',
            'join_field' => 'cat_title',
            'object_field_name' => 'cat_title',
            'compare_field_table' => 'cat_id',
            'compare_field_join' => 'cat_id');

        $permFilter = array();
        $permFilter[] = array('component_left' => 'Dizkus',
            'component_middle' => '',
            'component_right' => '',
            'instance_left' => 'cat_id',
            'instance_middle' => 'forum_id',
            'instance_right' => '',
            'level' => ACCESS_READ);

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
     * Move topic
     *
     * This function moves a given topic to another forum
     *
     * @params $args['topic_id'] int the topics id
     * @params $args['forum_id'] int the destination forums id
     * @params $args['shadow']   boolean true = create shadow topic
     *
     * @returns void
     */
    public function movetopic($args)
    {
        // get the old forum id and old post date
        $topic = $this->entityManager->find('Dizkus_Entity_Topic', $args['topic_id'])->toArray();

        if ($topic['forum_id'] <> $args['forum_id']) {
            // set new forum id
            $newtopic['forum_id'] = $args['forum_id'];
            DBUtil::updateObject($newtopic, 'dizkus_topics', 'topic_id=' . (int)DataUtil::formatForStore($args['topic_id']), 'topic_id');

            $newpost['forum_id'] = $args['forum_id'];
            DBUtil::updateObject($newpost, 'dizkus_posts', 'topic_id=' . (int)DataUtil::formatForStore($args['topic_id']), 'post_id');

            if ($args['shadow'] == true) {
                // user wants to have a shadow topic
                $message = $this->__f('The original posting has been moved <a title="moved" href="%s">here</a>.', ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $args['topic_id'])));
                $subject = $this->__f("*** The original posting '%s' has been moved", $topic['topic_title']);

                $this->storenewtopic(array('subject' => $subject,
                    'message' => $message,
                    'forum_id' => $topic['forum_id'],
                    'time' => $topic['topic_time'],
                    'no_sig' => true));
            }
            ModUtil::apiFunc('Dizkus', 'admin', 'sync', array('id' => $args['forum_id'], 'type' => 'forum'));
            ModUtil::apiFunc('Dizkus', 'admin', 'sync', array('id' => $topic['forum_id'], 'type' => 'forum'));
        }

        return;
    }

    /**
     * Notify by e-mail
     *
     * Sending notify e-mail to users subscribed to the topic of the forum
     *
     * @params $args['topic_id'] int the topics id
     * @params $args['poster_id'] int the users uid
     * @params $args['post_message'] string the text
     * @params $args['type'] int, 0=new message, 2=reply
     *
     * @returns void
     */
    public function notify_by_email($args)
    {

        $ztable = DBUtil::getTables();

        setlocale(LC_TIME, System::getVar('locale'));
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
                FROM  ' . $ztable['dizkus_topics'] . ' t
                LEFT JOIN ' . $ztable['dizkus_forums'] . ' f ON t.forum_id = f.forum_id
                LEFT JOIN ' . $ztable['dizkus_categories'] . ' c ON f.cat_id = c.cat_id
                WHERE t.topic_id = ' . (int)DataUtil::formatForStore($args['topic_id']);

        $res = DBUtil::executeSQL($sql);
        $colarray = array('topic_title', 'topic_poster', 'topic_time', 'cat_id', 'cat_title', 'forum_name', 'forum_id');
        $myrow = DBUtil::marshallObjects($res, $colarray);

        if (!is_array($myrow)) {
            // no results - topic does not exist
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }

        $topic_unixtime = strtotime($myrow[0]['topic_time']);
        $DU = new DateUtil();
        $topic_time_ml = $DU->formatDatetime($topic_unixtime, 'datetimebrief');

        $poster_name = UserUtil::getVar('uname', $args['poster_id']);

        $forum_id = $myrow[0]['forum_id'];
        $forum_name = $myrow[0]['forum_name'];
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
                WHERE fs.forum_id=' . DataUtil::formatForStore($forum_id) . '
                  ' . $fs_wherenotuser . '
                  AND f.forum_id = fs.forum_id
                  AND c.cat_id = f.cat_id';

        $res = DBUtil::executeSQL($sql);
        $colarray = array('uid', 'cat_id');
        $result = DBUtil::marshallObjects($res, $colarray);

        $recipients = array();
        // check if list is empty - then do nothing
        // we create an array of recipients here
        if (is_array($result) && !empty($result)) {
            foreach ($result as $resline) {
                // check permissions
                if (SecurityUtil::checkPermission('Dizkus::', $resline['cat_id'] . ':' . $forum_id . ':', ACCESS_READ, $resline['uid'])) {
                    $emailaddress = UserUtil::getVar('email', $resline['uid']);
                    if (empty($emailaddress)) {
                        continue;
                    }
                    $email['name'] = UserUtil::getVar('uname', $resline['uid']);
                    $email['address'] = $emailaddress;
                    $email['uid'] = $resline['uid'];
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
                WHERE ts.topic_id=' . DataUtil::formatForStore($args['topic_id']) . '
                  ' . $ts_wherenotuser . '
                  AND t.topic_id = ts.topic_id
                  AND f.forum_id = t.forum_id
                  AND c.cat_id = f.cat_id';

        $res = DBUtil::executeSQL($sql);
        $colarray = array('uid', 'cat_id', 'forum_id');
        $result = DBUtil::marshallObjects($res, $colarray);

        if (is_array($result) && !empty($result)) {
            foreach ($result as $resline) {
                // check permissions
                if (SecurityUtil::checkPermission('Dizkus::', $resline['cat_id'] . ':' . $resline['forum_id'] . ':', ACCESS_READ, $resline['uid'])) {
                    $emailaddress = UserUtil::getVar('email', $resline['uid']);
                    if (empty($emailaddress)) {
                        continue;
                    }
                    $email['name'] = UserUtil::getVar('uname', $resline['uid']);
                    $email['address'] = $emailaddress;
                    $email['uid'] = $resline['uid'];
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
                $ignorelist_setting = ModUtil::apiFunc('Dizkus', 'user', 'get_settings_ignorelist', array('uid' => $subscriber['uid']));
                if (ModUtil::available('ContactList') &&
                        (in_array($ignorelist_setting, array('medium', 'strict'))) &&
                        ModUtil::apiFunc('ContactList', 'user', 'isIgnored', array('uid' => $subscriber['uid'], 'iuid' => UserUtil::getVar('uid')))) {
                    $send = false;
                } else {
                    $send = true;
                }
                if ($send) {
                    $uid = UserUtil::getVar('uid');
                    $args = array('fromname' => $sitename,
                        'fromaddress' => $email_from,
                        'toname' => $subscriber['name'],
                        'toaddress' => $subscriber['address'],
                        'subject' => $subject,
                        'body' => $message,
                        'headers' => array('X-UserID: ' . md5($uid),
                            'X-Mailer: Dizkus v' . $modinfo['version'],
                            'X-DizkusTopicID: ' . $args['topic_id']));
                    ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $args);
                }
            }
        }

        return true;
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
        $newtopic = array('topic_title' => $post['topic_subject'],
            'topic_poster' => $post['poster_data']['uid'],
            'forum_id' => $post['forum_id'],
            'topic_time' => DateUtil::getDatetime('', '%Y-%m-%d %H:%M:%S'));
        $newtopic = DBUtil::insertObject($newtopic, 'dizkus_topics', 'topic_id');

        // increment topics count by 1
        DBUtil::incrementObjectFieldById('dizkus_forums', 'forum_topics', $post['forum_id'], 'forum_id');

        // now we need to change the postings:
        // first step: count the number of posting we have to move
        $where = 'WHERE topic_id = ' . (int)DataUtil::formatForStore($post['topic_id']) . '
                  AND post_id >= ' . (int)DataUtil::formatForStore($post['post_id']);
        $posts_to_move = DBUtil::selectObjectCount('dizkus_posts', $where);



        // update the topic_id in the postings
        // starting with $post['post_id'] and then all post_id's where topic_id = $post['topic_id'] and
        // post_id > $post['post_id']
        $updateposts = array('topic_id' => $newtopic['topic_id']);
        $where = 'WHERE post_id >= ' . (int)DataUtil::formatForStore($post['post_id']) . '
                  AND topic_id = ' . $post['topic_id'];
        DBUtil::updateObject($updateposts, 'dizkus_posts', $where, 'post_id');

        // get the new topic_last_post_id of the old topic
        $where = 'WHERE topic_id=' . (int)DataUtil::formatForStore($post['topic_id']) . '
                  ORDER BY post_time DESC';
        $lastpost = DBUtil::selectObject('dizkus_posts', $where);

        $oldtopic = ModUtil::apiFunc($this->name, 'Topic', 'read0', $post['topic_id']);

        // update the new topic
        $newtopic['topic_replies'] = (int)$posts_to_move - 1;
        $newtopic['topic_last_post_id'] = $post['topic_last_post_id'];
        DBUtil::updateObject($newtopic, 'dizkus_topics', null, 'topic_id');


        // update the old topic
        $oldtopic['topic_replies'] = $oldtopic['topic_replies'] - $posts_to_move;
        $oldtopic['topic_last_post_id'] = $lastpost['post_id'];
        $oldtopic['topic_time'] = $lastpost['post_time'];
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
        if (!isset($args['topic_id']) || !isset($args['view'])) {
            return LogUtil::registerArgsError();
        }

        switch ($args['view']) {
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
        $ignorelist_setting = ModUtil::apiFunc('Dizkus', 'user', 'get_settings_ignorelist', array('uid' => UserUtil::getVar('uid')));
        if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
            // get user's ignore list
            $ignored_users = ModUtil::apiFunc('ContactList', 'user', 'getallignorelist', array('uid' => UserUtil::getVar('uid')));
            $ignored_uids = array();
            foreach ($ignored_users as $item) {
                $ignored_uids[] = (int)$item['iuid'];
            }
            if (count($ignored_uids) > 0) {
                $whereignorelist = " AND t1.topic_poster NOT IN (" . implode(',', $ignored_uids) . ")";
            }
        }

        $sql = 'SELECT t1.topic_id
                FROM ' . $ztable['dizkus_topics'] . ' AS t1,
                     ' . $ztable['dizkus_topics'] . ' AS t2
                WHERE t2.topic_id = ' . (int)DataUtil::formatForStore($args['topic_id']) . '
                  AND t1.topic_time ' . $math . ' t2.topic_time
                  AND t1.forum_id = t2.forum_id
                  AND t1.sticky = 0
                  ' . $whereignorelist . '
                ORDER BY t1.topic_time ' . $sort;

        $res = DBUtil::executeSQL($sql, -1, 1);
        $newtopic = DBUtil::marshallObjects($res, array('topic_id'));

        return isset($newtopic[0]['topic_id']) ? $newtopic[0]['topic_id'] : 0;
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
        if (!isset($args['topic_replies']) || !is_numeric($args['topic_replies']) || $args['topic_replies'] < 0) {
            return LogUtil::registerArgsError();
        }

        // get some enviroment
        $posts_per_page = ModUtil::getVar('Dizkus', 'posts_per_page');
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
        if ((($forum['pop3_active'] == 1) && ($forum['pop3_last_connect'] <= time() - ($forum['pop3_interval'] * 60)) ) || ($force == true)) {
            mailcronecho('found active: ' . $forum['forum_id'] . ' = ' . $forum['forum_name'] . "\n", $args['debug']);
            // get new mails for this forum
            $pop3 = new pop3_class;
            $pop3->hostname = $forum['pop3_server'];
            $pop3->port = $forum['pop3_port'];
            $error = '';

            // open connection to pop3 server
            if (($error = $pop3->Open()) == '') {
                mailcronecho("Connected to the POP3 server '" . $pop3->hostname . "'.\n", $args['debug']);
                // login to pop3 server
                if (($error = $pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0)) == '') {
                    mailcronecho("User '" . $forum['pop3_login'] . "' logged into POP3 server '" . $pop3->hostname . "'.\n", $args['debug']);
                    // check for message
                    if (($error = $pop3->Statistics($messages, $size)) == '') {
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
                                                $from = str_replace(array('@', '.'), array(' (at) ', ' (dot) '), $from);
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
                                                    $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topic_by_postmsgid', array('msgid' => $replyto));
                                                    if (is_bool($topic_id) && $topic_id == false) {
                                                        // msgid not found, we clear replyto to create a new topic
                                                        $replyto = '';
                                                    } else {
                                                        // topic_id found, add this posting as a reply there
                                                        list($start,
                                                                $post_id ) = ModUtil::apiFunc('Dizkus', 'user', 'storereply', array('topic_id' => $topic_id,
                                                                    'message' => $message,
                                                                    'attach_signature' => 1,
                                                                    'subscribe_topic' => 0,
                                                                    'msgid' => $msgid));
                                                        mailcronecho("added new post '$subject' (post=$post_id) to topic $topic_id\n", $args['debug']);
                                                    }
                                                }

                                                // check again for replyto and create a new topic
                                                if (empty($replyto)) {
                                                    // store message in forum
                                                    $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic', array('subject' => $subject,
                                                                'message' => $message,
                                                                'forum_id' => $forum['forum_id'],
                                                                'attach_signature' => 1,
                                                                'subscribe_topic' => 0,
                                                                'msgid' => $msgid));
                                                    mailcronecho("Added new topic '$subject' (topic ID $topic_id) to '" . $forum['forum_name'] . "' forum.\n", $args['debug']);
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
                                mailcronecho("Error! Could not log user '" . $forum['pop3_pnuser'] . "' in.\n");
                            }
                            // close pop3 connection and finally delete messages
                            if ($error == '' && ($error = $pop3->Close()) == '') {
                                mailcronecho("Disconnected from POP3 server '" . $pop3->hostname . "'.\n");
                            }
                        } else {
                            $error = $result;
                        }
                    }
                }
            }
            if (!empty($error)) {
                mailcronecho("error: ", htmlspecialchars($error) . "\n");
            }

            // store the timestamp of the last connection to the database
            $fobj['forum_pop3_lastconnect'] = time();
            $fobj['forum_id'] = $forum['forum_id'];
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

        $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums', array('forum_id' => $args['forum_id']));
        Loader::includeOnce('modules/Dizkus/includes/pop3.php');

        $pop3 = new pop3_class;
        $pop3->hostname = $forum['pop3_server'];
        $pop3->port = $forum['pop3_port'];

        $error = '';
        $pop3messages = array();
        if (($error = $pop3->Open()) == '') {
            $pop3messages[] = "connected to the POP3 server '" . $pop3->hostname . "'";
            if (($error = $pop3->Login($forum['pop3_login'], base64_decode($forum['pop3_password']), 0)) == '') {
                $pop3messages[] = "user '" . $forum['pop3_login'] . "' logged in";
                if (($error = $pop3->Statistics($messages, $size)) == '') {
                    $pop3messages[] = "There are $messages messages in the mailbox, amounting to a total of $size bytes.";
                    $result = $pop3->ListMessages('', 1);
                    if (is_array($result) && count($result) > 0) {
                        for ($cnt = 1; $cnt <= count($result); $cnt++) {
                            if (($error = $pop3->RetrieveMessage($cnt, $headers, $body, -1)) == '') {
                                foreach ($headers as $header) {
                                    if (strpos(strtolower($header), 'subject:') === 0) {
                                        $subject = trim(strip_tags(substr($header, 8)));
                                    }
                                }
                            }
                        }
                        if ($error == '' && ($error = $pop3->Close()) == '') {
                            $pop3messages[] = "Disconnected from POP3 server '" . $pop3->hostname . "'.\n";
                        }
                    } else {
                        $error = $result;
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
        $to_topic_id = $args['to_topic_id'];
        $post_id = $args['post_id'];

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
        $sql = 'UPDATE ' . $ztable['dizkus_posts'] . '
                SET topic_id=' . (int)DataUtil::formatForStore($to_topic_id) . '
                WHERE post_id = ' . (int)DataUtil::formatForStore($post_id);

        DBUtil::executeSQL($sql);

        // for to_topic
        // 2 . update topic_replies in dizkus_topics ( COUNT )
        // 3 . update topic_last_post_id in dizkus_topics
        // get the new topic_last_post_id of to_topic
        $sql = 'SELECT post_id, post_time
                FROM ' . $ztable['dizkus_posts'] . '
                WHERE topic_id = ' . (int)DataUtil::formatForStore($to_topic_id) . '
                ORDER BY post_time DESC';

        $res = DBUtil::executeSQL($sql, -1, 1);
        $colarray = array('post_id', 'post_time');
        $result = DBUtil::marshallObjects($res, $colarray);
        $to_last_post_id = $result[0]['post_id'];
        $to_post_time = $result[0]['post_time'];

        $sql = 'UPDATE ' . $ztable['dizkus_topics'] . '
                SET topic_replies = topic_replies + 1,
                    topic_last_post_id=' . (int)DataUtil::formatForStore($to_last_post_id) . ',
                    topic_time=\'' . DataUtil::formatForStore($to_post_time) . '\'
                WHERE topic_id=' . (int)DataUtil::formatForStore($to_topic_id);

        DBUtil::executeSQL($sql);

        // for old topic ($old_topic_id)
        // 4 . update topic_replies in nuke_dizkus_topics ( COUNT )
        // 5 . update topic_last_post_id in nuke_dizkus_topics if necessary
        // get the new topic_last_post_id of the old topic
        $sql = 'SELECT post_id, post_time
                FROM ' . $ztable['dizkus_posts'] . '
                WHERE topic_id = ' . (int)DataUtil::formatForStore($old_topic_id) . '
                ORDER BY post_time DESC';

        $res = DBUtil::executeSQL($sql, -1, 1);
        $colarray = array('post_id', 'post_time');
        $result = DBUtil::marshallObjects($res, $colarray);
        $old_last_post_id = $result[0]['post_id'];
        $old_post_time = $result[0]['post_time'];

        // update
        $sql = 'UPDATE ' . $ztable['dizkus_topics'] . '
                SET topic_replies = topic_replies - 1,
                    topic_last_post_id=' . (int)DataUtil::formatForStore($old_last_post_id) . ',
                    topic_time=\'' . DataUtil::formatForStore($old_post_time) . '\'
                WHERE topic_id=' . (int)DataUtil::formatForStore($old_topic_id);

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
        $where = 'WHERE topic_id=' . (int)DataUtil::formatForStore($from_topic['topic_id']);
        DBUtil::updateObject($post_temp, 'dizkus_posts', $where, 'post_id');

        // to_topic['topic_replies'] must be incremented by from_topic['topic_replies'] + 1 (initial
        // posting
        // update to_topic['topic_time'] and to_topic['topic_last_post_id']
        // get new topic_time and topic_last_post_id
        $where = 'WHERE topic_id=' . (int)DataUtil::formatForStore($to_topic['topic_id']) . '
                  ORDER BY post_time DESC';
        $res = DBUtil::selectObject('dizkus_posts', $where);
        $new_last_post_id = $res['post_id'];
        $new_post_time = $res['post_time'];

        // update to_topic
        $to_topic_temp = array('topic_id' => $to_topic['topic_id'],
            'topic_replies' => $to_topic['topic_replies'] + $from_topic['topic_replies'] + 1,
            'topic_last_post_id' => $new_last_post_id,
            'topic_time' => $new_post_time);
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
            $where = 'WHERE forum_id=' . (int)DataUtil::formatForStore($from_topic['forum_id']) . '
                      ORDER BY post_time DESC';
            $res = DBUtil::selectObject('dizkus_posts', $where);
            $from_forum_last_post_id = $res['post_id'];

            $where = 'WHERE forum_id=' . (int)DataUtil::formatForStore($to_topic['forum_id']) . '
                      ORDER BY post_time DESC';
            $res = DBUtil::selectObject('dizkus_posts', $where);
            $to_forum_last_post_id = $res['post_id'];

            // calculate posting count difference
            $post_count_difference = (int)DataUtil::formatForStore($from_topic['topic_replies'] + 1);
            // decrement from_topic's forum post_count
            $sql = "UPDATE " . $ztable['dizkus_forums'] . "
                    SET forum_posts = forum_posts - $post_count_difference,
                        forum_last_post_id = '" . (int)DataUtil::formatForStore($from_forum_last_post_id) . "'
                    WHERE forum_id='" . (int)DataUtil::formatForStore($from_topic['forum_id']) . "'";
            DBUtil::executeSQL($sql);

            // increment o_topic's forum post_count
            $sql = "UPDATE " . $ztable['dizkus_forums'] . "
                    SET forum_posts = forum_posts + $post_count_difference,
                        forum_last_post_id = '" . (int)DataUtil::formatForStore($to_forum_last_post_id) . "'
                    WHERE forum_id='" . (int)DataUtil::formatForStore($to_topic['forum_id']) . "'";
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
        setlocale(LC_TIME, System::getVar('locale'));
        $modinfo = ModUtil::getInfo(ModUtil::getIDFromName(ModUtil::getName()));

        $mods = ModUtil::apiFunc('Dizkus', 'admin', 'readmoderators', array('forum_id' => $args['post']['forum_id']));

        // generate the mailheader
        $email_from = ModUtil::getVar('Dizkus', 'email_from');
        if ($email_from == '') {
            // nothing in forumwide-settings, use PN adminmail
            $email_from = System::getVar('adminmail');
        }

        $subject = DataUtil::formatForDisplay($this->__('Moderation request')) . ': ' . strip_tags($args['post']['topic_rawsubject']);
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
                        foreach ($group['members'] as $gm_uid) {
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

        $reporting_userid = UserUtil::getVar('uid');
        $reporting_username = UserUtil::getVar('uname');
        if (is_null($reporting_username)) {
            $reporting_username = $this->__('Guest');
        }

        $start = ModUtil::apiFunc('Dizkus', 'user', 'get_page_from_topic_replies', array('topic_replies' => $args['post']['topic_replies']));

        // FIXME Move this to a translatable template?
        $message = $this->__f('Request for moderation on %s', System::getVar('sitename')) . "\n"
                . $args['post']['cat_title'] . '::' . $args['post']['forum_name'] . '::' . $args['post']['topic_rawsubject'] . "\n\n"
                . $this->__f('Reporting user: %s', $reporting_username) . "\n"
                . $this->__('Comment:') . "\n"
                . $args['comment'] . " \n\n"
                . "---------------------------------------------------------------------\n"
                . strip_tags($args['post']['post_text']) . " \n"
                . "---------------------------------------------------------------------\n\n"
                . $this->__f('<a href="%s">Link to topic</a>', DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $args['post']['topic_id'], 'start' => $start), null, 'pid' . $args['post']['post_id'], true))) . "\n"
                . "\n";

        if (count($recipients) > 0) {
            foreach ($recipients as $recipient) {
                ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array('fromname' => $sitename,
                    'fromaddress' => $email_from,
                    'toname' => $recipient['uname'],
                    'toaddress' => $recipient['email'],
                    'subject' => $subject,
                    'body' => $message,
                    'headers' => array('X-UserID: ' . $reporting_userid,
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

        $topic = $this->entityManager->getRepository('Dizkus_Entity_Topic')
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

        // ToDo: Remove BBCode
        $bbcode = ModUtil::available('BBCode');
        $boldstart = '';
        $boldend = '';
        $urlstart = '';
        $urlend = '';
        if ($bbcode == true) {
            $boldstart = '[b]';
            $boldend = '[/b]';
            $urlstart = '[url]';
            $urlend = '[/url]';
        }

        foreach ($args['items'] as $item) {
            // create the reference, we need it twice
            $dateTimestamp = $item->get_date("Y-m-d H:i:s");
            if (empty($dateTimestamp)) {
                $reference = md5($item->get_link());
                $dateTimestamp = date("Y-m-d H:i:s", time());
            } else {
                $reference = md5($item->get_link() . '-' . $dateTimestamp);
            }

            // Checking if the forum already has that news.
            $check = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_reference', array('reference' => $reference));

            if ($check == false) {
                // Not found... we can add the news.
                $subject = $item->get_title();

                // Adding little display goodies - finishing with the url of the news...
                $message = $boldstart . $this->__('Summary') . ' :' . $boldend . "\n\n" . $item->get_description() . "\n\n" . $urlstart . $item->get_link() . $urlend . "\n\n";

                // store message in forum
                $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic', array('subject' => $subject,
                            'message' => $message,
                            'time' => $dateTimestamp,
                            'forum_id' => $args['forum']['forum_id'],
                            'attach_signature' => 0,
                            'subscribe_topic' => 0,
                            'reference' => $reference));

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
        $default = ModUtil::getVar('Dizkus', 'ignorelist_handling');
        if (isset($ignorelist_myhandling) && ($ignorelist_myhandling != '')) {
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

    /**
     * Check if the useragent is a bot (blacklisted)
     *
     * @return boolean
     */
    function useragentIsBot()
    {
        // check the user agent - if it is a bot, return immediately
        $robotslist = array(
            'ia_archiver',
            'googlebot',
            'mediapartners-google',
            'yahoo!',
            'msnbot',
            'jeeves',
            'lycos'
        );
        $useragent = System::serverGetVar('HTTP_USER_AGENT');
        for ($cnt = 0; $cnt < count($robotslist); $cnt++) {
            if (strpos(strtolower($useragent), $robotslist[$cnt]) !== false) {
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