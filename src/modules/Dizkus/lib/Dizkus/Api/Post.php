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
class Dizkus_Api_Post extends Zikula_AbstractApi {

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
        $minpost = ModUtil::apiFunc('Dizkus', 'user', 'get_firstlast_post_in_topic',
            array('topic_id' => $args['topic_id'],
                'first'    => true,
                'id_only'  => true
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
            ->from('Dizkus_Entity_Topics', 't')
            ->leftJoin('t.last_post', 'l')
            ->orderBy('l.post_time', 'DESC');


        // sql part per selected time frame
        switch ($args['selorder'])
        {
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
                $text= $this->__('Last week');
                break;
            case '5' : // last x hours
                // maximum two weeks back = 2 * 24 * 7 hours
                if (isset($args['nohours']) && $args['nohours'] > 336) {
                    $args['nohours'] = 336;
                }
                $qb->where('l.post_time > :wheretime')
                    ->setParameter('wheretime', new DateTime('-'.$args['nohours'].' hours'));
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
            case '1' :
            default:   // last 24 hours
            $qb->where('l.post_time > :wheretime')
                ->setParameter('wheretime', new DateTime('-24 hours'));
                $text  =$this->__('Last 24 hours');
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
            array(),
            array(),
            $text,
            $pager
        );



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


}
