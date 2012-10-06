<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * This class provides the topic api functions
 */
class Dizkus_Api_Topic extends Zikula_AbstractApi {
    
    
    
    /**
     * Toggle a topic lock.
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] Topic id.
     *        string $args['mode'] Lock mode (lock|unlocked).
     *
     * @return void
     */
    public function toggleLock($args)
    {
        if (isset($args['topic_id']) && is_numeric($args['topic_id']) && isset($args['mode'])) {
            
            $topic = $this->entityManager->find('Dizkus_Entity_Topics', $args['topic_id']);
            if ($args['mode'] == 'lock') {
                $topic->lock();
            } else {
                $topic->unlock();
            }
            $this->entityManager->flush();
        }
        return;
    }
    
    
    /**
     * Sticky/unsticky a topic.
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] Topic id.
     *        string $args['mode'] Sticky mode (sticky|unsticky).
     *
     * @return void
     */
    public function toggleSticky($args)
    {
        if (isset($args['topic_id']) && is_numeric($args['topic_id']) && isset($args['mode'])) {
            
            $topic = $this->entityManager->find('Dizkus_Entity_Topics', $args['topic_id']);
            if ($args['mode'] == 'sticky') {
                $topic->sticky();
            } else {
                $topic->unsticky();
            }
            $this->entityManager->flush();
        }
        return;
    }
    
    
    /**
     * Subscribe a topic.
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] Topic id.
     *        int $args['user_id'] User id (optional: needs ACCESS_ADMIN).
     *
     * @return void|bool
     */
    public function subscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }
    
        $topic = ModUtil::apiFunc($this->name, 'User', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $args['topic_id']));
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $topic)) {
            return LogUtil::registerPermissionError();
        }

        $status = $this->getSubscriptionStatus(array('userid' => $args['user_id'], 'topic_id' => $args['topic_id']));
        if (!$status) {
            $subscription = new Dizkus_Entity_TopicSubscriptions();
            $subscription->settopic_id($args['topic_id']);
            $subscription->setuser_id($args['user_id']);
            $this->entityManager->persist($subscription);
            $this->entityManager->flush();
        }

        return;
    }
    
    
    /**
     * Unsubscribe a topic.
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] Topics id, if not set we unsubscribe all topics.
     *        int $args['user_id']  Users id (needs ACCESS_ADMIN).
     *
     * @return void|bool
     */
    public function unsubscribe($args)
    {
        $where = array();
        if (isset($args['user_id'])) {
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
            $where['user_id'] = $args[user_id];
        } else {
            $where['user_id'] = UserUtil::getVar('uid');
        }

        $where = $args['topic_id'];
        
        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_TopicSubscriptions')
                                             ->findBy($where);
        foreach ($subscriptions as $subscription) {
            $this->entityManager->remove($subscription);
        }
        $this->entityManager->flush();
    
        return;
    }
    
    /**
     * Get topic subscription status.
     *
     * @param array $args Arguments array.
     *        int $args['user_id'] Users uid.
     *        int $args['topic_id'] Topic id.
     *
     * @return bool true if the user is subscribed or false if not
     */
    public function getSubscriptionStatus($args)
    {
        
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(s)')
           ->from('Dizkus_Entity_TopicSubscriptions', 's')
           ->where('s.user_id = :user')
           ->setParameter('user', $args['user_id'])
           ->andWhere('s.topic_id = :topic')
           ->setParameter('topic', $args['topic_id'])
           ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();
        return $count > 0; 
    }

    /**
     * readtopic
     *
     * @param int $topic_id The topic id.
     *
     * @return array
     */
    public function read0($topic_id)
    {

        return $this->entityManager->getRepository('Dizkus_Entity_Topics')->find($topic_id)->toArray();
    }



    /**
     * read
     *
     * This function reads a topic with the last posts_per_page answers (incl the initial posting when on page #1)
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] The topics id.
     *        int $args['start'] Number of posting to start with (if on page 1+).
     *        boolean $args['complete'] If true, reads the complete thread and does not care about the posts_per_page
     *        setting, ignores 'start'.
     *        boolean $args['count'] True if we have raise the read counter, default false.
     *        boolean $args['nohook'] True if transform hooks should not modify post text.
     *
     * @return array Very complex array, see {debug} for more information
     */
    public function read($args)
    {
        $dizkusvars      = ModUtil::getVar('Dizkus');
        $posts_per_page  = $dizkusvars['posts_per_page'];
    
        $post_sort_order = ModUtil::apiFunc('Dizkus','user','get_user_post_order');
    
        $complete = (isset($args['complete'])) ? $args['complete'] : false;
        $count    = (isset($args['count'])) ? $args['count'] : false;
        $start    = (isset($args['start'])) ? $args['start'] : 0;
        $hooks    = (isset($args['nohook']) && $args['nohook'] == false) ? false : true;
    
        $currentuserid = UserUtil::getVar('uid');
        $now = time();
        $timespanforchanges = $this->getVar('timespanforchanges', 24);
        $timespansecs = $timespanforchanges * 60 * 60;

        $topic_id = (isset($args['topic_id'])) ? $args['topic_id'] : false;
        // no results - topic does not exist
        if (!$topic_id) {
            return LogUtil::registerError($this->__("Error! The topic you selected (ID: $topic_id) was not found. Please go back and try again."), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
        $topic = $this->entityManager->find('Dizkus_Entity_Topics', $topic_id)->toArray();

        // integrate forum and category information
        $forum                      = $this->entityManager->find('Dizkus_Entity_Forums', $topic['forum_id']);
        $topic['forum_name']        = (string)$forum->getforum_name();
        $topic['cat_id']            = (int)$forum->getcat_id();
        //$topic['cat_title']         = (string)$this->entityManager->find('Dizkus_Entity_Categories', $forum['cat_id'])->getcat_title();
        $topic['forum_pop3_active'] = (int)$forum->getforum_pop3_active();


        // integrate contactlist's ignorelist here (part 1/2)
        $ignored_uids = array();
        $ignorelist_setting = ModUtil::apiFunc('Dizkus','user','get_settings_ignorelist',array('uid' => UserUtil::getVar('uid')));
        if (($ignorelist_setting == 'strict') || ($ignorelist_setting == 'medium')) {
            // get user's ignore list
            $ignored_users = ModUtil::apiFunc('ContactList','user','getallignorelist',array('uid' => UserUtil::getVar('uid')));
            $ignored_uids = array();
            foreach ($ignored_users as $item) {
                $ignored_uids[] = (int)$item['iuid'];
            }
        }
    
        $topic['start']           = $start;
        $topic['topic_unixtime']  = $topic['topic_time']->GetTimestamp();
        $topic['post_sort_order'] = $post_sort_order;

        // pop3_active contains the external source (if any), create the correct var name
        // 0 - no external source
        // 1 - mail
        // 2 - rss
        $topic['externalsource'] = $topic['forum_pop3_active'];
        // kill the wrong var
        unset($topic['forum_pop3_active']);

        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $topic)) {
            return LogUtil::registerPermissionError();
        }

        $topic['forum_mods'] = ModUtil::apiFunc($this->name, 'Users', 'get_moderators', array('forum_id' => $topic['forum_id']));

        $topic['access_see']      = ModUtil::apiFunc($this->name, 'Permission', 'canSee', $topic);
        $topic['access_read']     = $topic['access_see'] && ModUtil::apiFunc($this->name, 'Permission', 'canRead', $topic);
        $topic['access_comment']  = false;
        $topic['access_moderate'] = false;
        $topic['access_admin']    = false;
        if ($topic['access_read'] == true) {
            $topic['access_comment']  = $topic['access_read'] && ModUtil::apiFunc($this->name, 'Permission', 'canWrite', $topic);
            if ($topic['access_comment'] == true) {
                $topic['access_moderate'] = $topic['access_comment'] && ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $topic);
                if ($topic['access_moderate'] == true) {
                    $topic['access_admin'] = $topic['access_moderate'] && ModUtil::apiFunc($this->name, 'Permission', 'canAdministrate', $topic);
                }
            }
        }
        // check permission to change the topic subject
        if ($topic['access_moderate']) {
            // user has moderate perms, copy this to topicsubjectedit
            $topic['access_topicsubjectedit'] = $topic['access_moderate'];
        } else {
            // check if user is the topic starter and give him the permission to
            // update the subject
            $topic['access_topicsubjectedit'] = (UserUtil::getVar('uid') == $topic['topic_poster']);
        }

        // get the next and previous topic_id's for the next / prev button
        $topic['next_topic_id'] = ModUtil::apiFunc($this->name, 'Users', 'get_previous_or_next_topic_id', array('topic_id' => $topic['topic_id'], 'view'=>'next'));
        $topic['prev_topic_id'] = ModUtil::apiFunc($this->name, 'Users', 'get_previous_or_next_topic_id', array('topic_id' => $topic['topic_id'], 'view'=>'previous'));

        // get the users topic_subscription status to show it in the quick repliy checkbox
        // correctly
        if (ModUtil::apiFunc($this->name, 'Users', 'get_topic_subscription_status', array('user_id'   => $currentuserid, 'topic_id' => $topic['topic_id'])) == true) {
            $topic['is_subscribed'] = 1;
        } else {
            $topic['is_subscribed'] = 0;
        }

        // update topic counter
        if ($count == true) {
            $this->entityManager->find('Dizkus_Entity_Topics', $topic['topic_id'])->counter();
            $this->entityManager->flush();
        }
        // more then one page in this topic?
        $topic['total_posts'] = ModUtil::apiFunc($this->name, 'Users', 'boardstats', array('id' => $topic['topic_id'], 'type' => 'topic'));

        if ($topic['total_posts'] > $posts_per_page) {
            $times = 0;
            for ($x = 0; $x < $topic['total_posts']; $x += $posts_per_page) {
                $times++;
            }
            $topic['pages'] = $times;
        }

        $topic['post_start'] = (!empty($start)) ? $start : 0;

        // topic_pager is obsolete, inform the user about this
        $topic['topic_pager'] = 'Error! Deprecated \'$topic.topic_pager\' data field used. Please update the template to incorporate the topic pager plug-in.';

        $topic['posts'] = array();

        // read posts
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('p')
            ->from('Dizkus_Entity_Posts', 'p')
            ->where('p.topic_id = :topic_id')
            ->setParameter('topic_id', $topic['topic_id'])
            ->orderBy('p.post_id', $post_sort_order);
        $query = $qb->getQuery();
        if (!$complete) {
            $query->setFirstResult($start);
            $query->setMaxResults($posts_per_page);
        }
        $result2 = $query->getArrayResult();

        // performance patch:
        // we store all userdata read for the single postings in the $userdata
        // array for later use. If user A is referenced more than once in the
        // topic, we do not need to load his dat again from the db.
        // array index = userid
        // array value = array with user information
        // this increases the amount of memory used but speeds up the loading of topics
        $userdata = array();

        if (is_array($result2) && !empty($result2)) {
            foreach ($result2 as $post) {
                $post['topic_id'] = $topic['topic_id'];

                // check if array_key_exists() with poster _id in $userdata
                //if (!array_key_exists($post['poster_id'], $userdata)) {
                if (!isset($userdata[$post['poster_id']])) {
                    // not in array, load the data now...
                    $userdata[$post['poster_id']] = ModUtil::apiFunc($this->name, 'UserData', 'getFromId', $post['poster_id']);
                }
                // we now have the data and use them
                $post['poster_data'] = $userdata[$post['poster_id']];
                $post['posted_unixtime'] = $post['post_time']->getTimestamp();
                // we use br2nl here for backwards compatibility
                //$message = phpbb_br2nl($message);
                //$post['post_text'] = phpbb_br2nl($post['post_text']);

                $post['post_text'] = dzk_replacesignature($post['post_text'], $post['poster_data']['signature']);

                if ($hooks == true) {
                    // call hooks for $message
                    // list($post['post_text']) = ModUtil::callHooks('item', 'transform', $post['post_id'], array($post['post_text']));
                }

                $post['post_text'] = dzkVarPrepHTMLDisplay($post['post_text']);
                //$post['post_text'] = DataUtil::formatForDisplayHTML($post['post_text']);

                $post['poster_data']['reply'] = false;
                if ($topic['access_comment'] || $topic['access_moderate'] || $topic['access_admin']) {
                    // user is allowed to reply
                    $post['poster_data']['reply'] = true;
                }

                $post['poster_data']['seeip'] = false;
                if (($topic['access_moderate'] || $topic['access_admin']) && $dizkusvars['log_ip'] == 'yes') {
                    //ModUtil::getVar('Dizkus', 'log_ip') == 'yes') {
                    // user is allowed to see ip
                    $post['poster_data']['seeip'] = true;
                }

                $post['poster_data']['moderate'] = false;
                $post['poster_data']['edit'] = false;
                if ($topic['access_moderate'] || $topic['access_admin']) {
                    // user is allowed to moderate
                    $post['poster_data']['moderate'] = true;
                    $post['poster_data']['edit'] = true;
                } elseif ($post['poster_data']['uid'] == $currentuserid) {
                    // user is allowed to moderate || own post
                    // if the timespanforchanges (in hrs!) setting allows it
                    // timespanforchanges is in hours, but we need seconds:
                    if (($now - $post['posted_unixtime']) <= $timespansecs ) {
                        $post['poster_data']['edit'] = true;
                    }
                }

                // integrate contactlist's ignorelist here (part 2/2)
                // the added variable will be handled in templates
                $post['contactlist_ignored'] = (in_array($post['poster_id'], $ignored_uids)) ? 1 : 0;
                //orignal von quan (e_all): if (in_array($post['poster_id'], $ignored_uids)) $post['contactlist_ignored'] = 1;

                array_push($topic['posts'], $post);
            }
        }

        unset($userdata);
        return $topic;
    }


    /**
     * getIdByReference
     *
     * Gets a topic reference as parameter and delivers the internal topic id used for Dizkus as comment module
     *
     * @param string $reference The reference.
     *
     * @return array Topic data as array
     */
    public function getIdByReference($reference)
    {
        if (empty($reference)) {
            return LogUtil::registerArgsError();
        }

        return $this->entityManager->getRepository('Dizkus_Entity_Topics')
                                   ->findOneBy(array('topic_reference' => $reference))
                                   ->toArray();
    }


    /**
     * email
     *
     * This functions emails a topic to a given email address.
     *
     * @param array $args Arguments array.
     *        string $args['sendto_email'] The recipients email address.
     *        string $args['message'] The text.
     *        string $args['subject'] The subject.
     *
     * @return boolean
     */
    public function email($args)
    {
        $sender_name = UserUtil::getVar('uname');
        $sender_email = UserUtil::getVar('email');
        if (!UserUtil::isLoggedIn()) {
            $sender_name = ModUtil::getVar('Users', 'anonymous');
            $sender_email = ModUtil::getVar('Dizkus', 'email_from');
        }

        $params = array(
            'fromname'    => $sender_name,
            'fromaddress' => $sender_email,
            'toname'      => $args['sendto_email'],
            'toaddress'   => $args['sendto_email'],
            'subject'     => $args['subject'],
            'body'        => $args['message'],
        );
        return ModUtil::apiFunc('Mailer', 'user', 'sendmessage', $params);
    }

    /**
     * deletet
     *
     * This function deletes a topic given by id.
     *
     * @param int $topic_id The topics id.
     *
     * @return int the forums id for redirecting
     */
    public function delete($topic)
    {
        if (!is_array($topic)) {
            $topic = $this->entityManager->getRepository('Dizkus_Entity_Topics')->findOneBy($topic);
        }
        $topic_id = $topic->gettopic_id();


        list($forum_id, $cat_id) = ModUtil::apiFunc($this->name, 'User', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $topic_id));
        $params = array(
            'cat_id' => $cat_id,
            'forum_id' => $forum_id
        );
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $params)) {
            return LogUtil::registerPermissionError();
        }

        // Update the users's post count, this might be slow on big topics but it makes other parts of the
        // forum faster so we win out in the long run.

        // step #1: get all post ids and posters ids
        $postings = $this->entityManager->getRepository('Dizkus_Entity_Posts')
            ->findBy(array('topic_id' => $topic_id));


        // step #2 go through the posting array and decrement the posting counter
        // TO-DO: for larger topics use IN(..., ..., ...) with 50 or 100 posting ids per sql
        // step #3 and delete postings
        foreach ($postings as $posting) {
            UserUtil::setVar('dizkus_user_posts', UserUtil::getVar('dizkus_user_posts', $posting->getposter_id()) - 1, $posting->getposter_id());
            //DBUtil::decrementObjectFieldByID('dizkus__users', 'user_posts', $posting['poster_id'], 'user_id');
            $this->entityManager->remove($posting);
        }


        // now delete the topic itself

        $this->entityManager->remove($topic);



        // remove topic subscriptions
        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_TopicSubscriptions')
            ->findBy(array('topic_id' => $topic_id));
        foreach ($subscriptions as $subscription) {
            $this->entityManager->remove($subscription);
        }

        // get forum info for adjustments


        $forum = $this->entityManager->find('Dizkus_Entity_TopicSubscriptions', $forum_id);
        // decrement forum_topics counter
        $forum['forum_topics']--;
        // decrement forum_posts counter
        $forum['forum_posts'] = $forum['forum_posts'] - count($postings);


        $this->entityManager->flush();

        // Let any hooks know that we have deleted an item (topic).
        // ModUtil::callHooks('item', 'delete', $args['topic_id'], array('module' => 'Dizkus'));

        ModUtil::apiFunc('Dizkus', 'admin', 'sync', array('id' => $forum_id, 'type' => 'forum'));
        return $forum_id;
    }


}