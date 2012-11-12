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

}
