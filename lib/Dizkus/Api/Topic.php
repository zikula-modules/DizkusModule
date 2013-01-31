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
class Dizkus_Api_Topic extends Zikula_AbstractApi
{

    function changeStatus($args)
    {
        if ($args['action'] == 'subscribe') {
            ModUtil::apiFunc($this->name, 'Topic', 'subscribe', array('topic_id' => $args['topic_id']));
        } else if ($args['action'] == 'unsubscribe') {
            ModUtil::apiFunc($this->name, 'Topic', 'unsubscribe', array('topic_id' => $args['topic_id']));
        } else {
            $topic = new Dizkus_Manager_Topic($args['topic_id']);
            switch ($args['action']) {
                case 'sticky':
                    $topic->sticky();
                    break;
                case 'unsticky':
                    $topic->unsticky();
                    break;
                case 'lock':
                    $topic->lock();
                    break;
                case 'unlock':
                    $topic->unlock();
                    break;
                case 'solve':
                    $topic->solve();
                    break;
                case 'unsolve':
                    $topic->unsolve();
                    break;
                case 'setTitle':
                    $topic->setTitle($args['title']);
                    break;
            }
        }
    }

    /**
     * Subscribe a topic.
     *
     * @param array $args Arguments array.
     *        int $args['topic_id'] Topic id.
     *        int $args['user_id'] User id (optional: needs ACCESS_ADMIN).
     *
     * @return void
     */
    public function subscribe($args)
    {
        if (isset($args['user_id']) && !SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        } else {
            $args['user_id'] = UserUtil::getVar('uid');
        }

        // Todo Permission check

        $status = $this->getSubscriptionStatus(array('user_id' => $args['user_id'], 'topic_id' => $args['topic_id']));
        if (!$status) {
            $subscription = new Dizkus_Entity_TopicSubscriptions();

            $topic = $this->entityManager->find('Dizkus_Entity_Topic', $args['topic_id']);
            $subscription->settopic($topic);
            $subscription->setuser_id($args['user_id']);
            $this->entityManager->persist($subscription);
            $this->entityManager->flush();
        }
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
        // Todo Permission check

        $where = array();
        if (isset($args['user_id'])) {
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
                return LogUtil::registerPermissionError();
            }
            $where['user_id'] = $args['user_id'];
        } else {
            $where['user_id'] = UserUtil::getVar('uid');
        }

        $where['topic_id'] = $args['topic_id'];

        $subscriptions = $this->entityManager->getRepository('Dizkus_Entity_TopicSubscriptions')->findBy($where);
        if (isset($subscriptions)) {
            foreach ($subscriptions as $subscription) {
                $this->entityManager->remove($subscription);
            }
            $this->entityManager->flush();
        }
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
        // check input
        if (empty($args['topic_id'])) {
            return LogUtil::registerArgsError();
        }
        if (empty($args['user_id'])) {
            $args['user_id'] = UserUtil::getVar('uid');
        }

        // doctrine query
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

        // Return true if the user is subscribed or false if not
        return ($count > 0) ? true : false;
        ;
    }

    /**
     * Get topic subscriptions
     *
     * @params none
     *
     * @returns array with topic ids, may be empty
     */
    public function getSubscriptions($args)
    {
        if (empty($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
        }
        $subscriptions = $this->entityManager
                ->getRepository('Dizkus_Entity_TopicSubscriptions')
                ->findBy(array('user_id' => $args['uid']));

        return $subscriptions;
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

        return $this->entityManager->getRepository('Dizkus_Entity_Topic')
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
            'fromname' => $sender_name,
            'fromaddress' => $sender_email,
            'toname' => $args['sendto_email'],
            'toaddress' => $args['sendto_email'],
            'subject' => $args['subject'],
            'body' => $args['message'],
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
            $topic = $this->entityManager->getRepository('Dizkus_Entity_Topic')->findOneBy($topic);
        }
        $topic_id = $topic->getTopic_id();


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
        $postings = $this->entityManager->getRepository('Dizkus_Entity_Post')
                ->findBy(array('topic_id' => $topic_id));


        // step #2 go through the posting array and decrement the posting counter
        // TO-DO: for larger topics use IN(..., ..., ...) with 50 or 100 posting ids per sql
        // step #3 and delete postings
        foreach ($postings as $posting) {
            UserUtil::setVar('dizkus_user_posts', UserUtil::getVar('dizkus_user_posts', $posting->getPoster_id()) - 1, $posting->getPoster_id());
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