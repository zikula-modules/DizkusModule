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
     * read
     *
     * Reads a single posting
     *
     * @param int $post_id The postings id.
     *
     * @return array with posting information.
     *
     */
    public function read($post_id)
    {
        $ztable = DBUtil::getTables();
        $postscols = DBUtil::_getAllColumnsQualified('dizkus_posts', 'p');

        $sql = 'SELECT '. $postscols .',
                          t.topic_title,
                          t.topic_replies,
                          f.forum_name,
                          c.cat_title,
                          c.cat_id
                FROM '.$ztable['dizkus_posts'].' p
                LEFT JOIN '.$ztable['dizkus_topics'].' t ON t.topic_id = p.topic_id
                LEFT JOIN '.$ztable['dizkus_forums'].' f ON f.forum_id = t.forum_id
                LEFT JOIN '.$ztable['dizkus_categories'].' c ON c.cat_id = f.cat_id
                WHERE p.post_id = '.(int)DataUtil::formatForStore($post_id);

        $result = DBUtil::executeSQL($sql);
        if ($result === false) {
            return LogUtil::registerError($this->__('Error! The topic you selected was not found. Please go back and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }

        $colarray   = DBUtil::getColumnsArray ('dizkus_posts');
        $colarray[] = 'topic_title';
        $colarray[] = 'topic_replies';
        $colarray[] = 'forum_name';
        $colarray[] = 'cat_title';
        $colarray[] = 'cat_id';

        $objarray = DBUtil::marshallObjects ($result, $colarray);
        $post = $objarray[0];
        if (!allowedtoreadcategoryandforum($post['cat_id'], $post['forum_id'])) {
            return LogUtil::registerPermissionError();
        }

        $post['post_id']          = DataUtil::formatForDisplay($post['post_id']);
        $post['post_time']        = DataUtil::formatForDisplay($post['post_time']);
        $message                  = $post['post_text'];
        $post['has_signature']    = (substr($message, -8, 8)=='[addsig]');
        $post['post_rawtext']     = dzk_replacesignature($message, '');
        $post['post_rawtext']     = preg_replace("#<!-- editby -->(.*?)<!-- end editby -->#si", '', $post['post_rawtext']);
        $post['post_rawtext']     = str_replace('<br />', '', $post['post_rawtext']);
        $post['topic_id']         = DataUtil::formatForDisplay($post['topic_id']);
        $post['topic_rawsubject'] = strip_tags($post['topic_title']);
        $post['topic_subject']    = DataUtil::formatForDisplay($post['topic_title']);
        $post['topic_replies']    = DataUtil::formatForDisplay($post['topic_replies']);
        $post['forum_id']         = DataUtil::formatForDisplay($post['forum_id']);
        $post['forum_name']       = DataUtil::formatForDisplay($post['forum_name']);
        $post['cat_title']        = DataUtil::formatForDisplay($post['cat_title']);
        $post['cat_id']           = DataUtil::formatForDisplay($post['cat_id']);
        $post['poster_data']      = ModUtil::apiFunc($this->name, 'user', 'get_userdata_from_id', array('userid' => $post['poster_id']));

        // create unix timestamp
        $post['post_unixtime'] = strtotime($post['post_time']);
        $post['posted_unixtime'] = $post['post_unixtime'];

        $pn_uid = UserUtil::getVar('uid');
        $post['moderate'] = false;
        if (allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
            $post['moderate'] = true;
        }

        $post['poster_data']['edit'] = false;
        $post['poster_data']['reply'] = false;
        $post['poster_data']['seeip'] = false;
        $post['poster_data']['moderate'] = false;

        if ($post['poster_data']['uid']==$pn_uid) {
            // user is allowed to moderate || own post
            $post['poster_data']['edit'] = true;
        }
        if (allowedtowritetocategoryandforum($post['cat_id'], $post['forum_id'])) {
            // user is allowed to reply
            $post['poster_data']['reply'] = true;
        }

        if (allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id']) &&
            ModUtil::getVar('Dizkus', 'log_ip') == 'yes') {
            // user is allowed to see ip
            $post['poster_data']['seeip'] = true;
        }
        if (allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
            // user is allowed to moderate
            $post['poster_data']['moderate'] = true;
            $post['poster_data']['edit'] = true;
        }

        $post['post_textdisplay'] = phpbb_br2nl($message);
        $post['post_textdisplay'] = dzk_replacesignature($post['post_textdisplay'], $post['poster_data']['signature']);

        // call hooks for $message_display ($message remains untouched for the textarea)
        // list($post['post_textdisplay']) = ModUtil::callHooks('item', 'transform', $post['post_id'], array($post['post_textdisplay']));
        $post['post_textdisplay'] = dzkVarPrepHTMLDisplay($post['post_textdisplay']);
        $post['post_text'] = $post['post_textdisplay'];

        // allow to edit the subject if first post
        $post['first_post'] = $this->isFirst(array('topic_id' => $post['topic_id'], 'post_id' => $post['post_id']));

        return $post;
    }

}
