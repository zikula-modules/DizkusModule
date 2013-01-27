<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * This class provides the post api functions
 */
class Dizkus_Api_Post extends Zikula_AbstractApi
{

    /**
     * Check if this is the first post in a topic.
     *
     * @param array $args The argument array.
     *        int $args['topic_id'] The topics id.
     *        int $args['post_id'] The postings id.
     *
     * @return boolean
     */
    public function isFirst($args)
    {
        // compare the given post_id with the lowest post_id in the topic
        $minpost = ModUtil::apiFunc('Dizkus', 'user', 'get_firstlast_post_in_topic', array('topic_id' => $args['topic_id'],
                    'first' => true,
                    'id_only' => true
                        )
        );

        return ($minpost == $args['post_id']) ? true : false;
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
    public function getLatest($args)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'l')
                ->from('Dizkus_Entity_Topic', 't')
                ->leftJoin('t.last_post', 'l')
                ->orderBy('l.post_time', 'DESC');


        // sql part per selected time frame
        switch ($args['selorder']) {
            case '2' : // today
                $qb->where('l.post_time > :wheretime')
                        ->setParameter('wheretime', new DateTime('today'));
                $text = $this->__('Today');
                break;
            case '3' : // since yesterday
                $qb->where('l.post_time > :wheretime')
                        ->setParameter('wheretime', new DateTime('yesterday'));
                $text = $this->__('Yesterday');
                break;
            case '4' : // lastweek
                $qb->where('l.post_time > :wheretime')
                        ->setParameter('wheretime', new DateTime('-1 week'));
                $text = $this->__('Last week');
                break;
            case '5' : // last x hours
                // maximum two weeks back = 2 * 24 * 7 hours
                if (isset($args['nohours']) && $args['nohours'] > 336) {
                    $args['nohours'] = 336;
                }
                $qb->where('l.post_time > :wheretime')
                        ->setParameter('wheretime', new DateTime('-' . $args['nohours'] . ' hours'));
                $text = DataUtil::formatForDisplay($this->__f('Last %s hours', $args['nohours']));
                break;
            case '6' : // last visit
                $wheretime = " AND t.topic_time > '" . DataUtil::formatForStore($args['last_visit']) . "' ";
                $text = DataUtil::formatForDisplay($this->__f('Last visit: %s', DateUtil::formatDatetime($args['last_visit_unix'], 'datetimebrief')));
                break;
            case 'unanswered':
                $qb->where('t.topic_replies = 0');
                $text = $this->__('Unanswered');
                break;
            case 'unsolved':
                $qb->where('t.solved = 0');

                $text = $this->__('Unsolved');
                break;
            default:   // last 24 hours
                $qb->where('l.post_time > :wheretime')
                        ->setParameter('wheretime', new DateTime('-24 hours'));
                $text = $this->__('Last 24 hours');
                break;
        }


        $qb->setFirstResult(0)->setMaxResults(10);
        $topics = new Paginator($qb);
        $pager = array(
            'numitems' => count($topics),
            'itemsperpage' => 10
        );


        return array(
            $topics,
            $text,
            $pager
        );
    }

    public function search($args)
    {
        $text = '';
        $uname = '';
        $own = false;
        if (empty($args['uid'])) {
            $args['uid'] = UserUtil::getVar('uid');
            $own = true;
        }
        if (!is_int($args['uid'])) {
            $uname = $args['uid'];
            $args['uid'] = UserUtil::getIdFromName($uname);
        } else {
            $uname = UserUtil::getVar('uname', $args['uid']);
        }
        if (!$own && $args['uid'] == UserUtil::getVar('uid')) {
            $own = true;
        }
        if (empty($args['action'])) {
            $args['action'] = 'posts';
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('t', 'l')
                ->from('Dizkus_Entity_Topic', 't')
                ->leftJoin('t.last_post', 'l')
                ->leftJoin('t.posts', 'p')
                ->orderBy('l.post_time', 'DESC');

        if ($args['action'] == 'topics') {
            $qb->where('t.topic_poster = :uid');
            if ($own) {
                $text = $this->__('Your topics');
            } else {
                $text = $this->__f("%s's topics", array($uname));
            }
        } else {
            $qb->where('p.poster_id = :uid');
            if ($own) {
                $text = $this->__('Your posts');
            } else {
                $text = $this->__f("%s's posts", array($uname));
            }
        }
        $qb->setParameter('uid', $args['uid']);


        $qb->setFirstResult(0)->setMaxResults(10);
        $topics = new Paginator($qb);
        $pager = array(
            'numitems' => count($topics),
            'itemsperpage' => 10
        );


        return array(
            $topics,
            $text,
            $pager
        );
    }

}
