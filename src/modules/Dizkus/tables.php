<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

function Dizkus_tables()
{
    // Initialise table array
    $ztable = array();

    //
    // categories
    //
    $ztable['dizkus_categories'] = DBUtil::getLimitedTablename('dizkus_categories');
    $ztable['dizkus_categories_column'] = array('cat_id'    => 'cat_id',
                                                 'cat_title' => 'cat_title',
                                                 'cat_order' => 'cat_order');
    $ztable['dizkus_categories_column_def'] = array('cat_id'    => 'I AUTO PRIMARY',
                                                     'cat_title' => 'C(100) NOTNULL DEFAULT \'\'',
                                                     'cat_order' => 'C(10) NOTNULL DEFAULT \'\'');

    //
    // forum_mods
    //
    $ztable['dizkus_forum_mods'] = DBUtil::getLimitedTablename('dizkus_forum_mods');
    $ztable['dizkus_forum_mods_column'] = array('id'       => 'id',
                                                 'forum_id' => 'forum_id',
                                                 'user_id'  => 'user_id');
    $ztable['dizkus_forum_mods_column_def'] = array('id'        => 'I AUTO PRIMARY',
                                                     'forum_id'  => 'I NOTNULL DEFAULT 0',
                                                     'user_id'   => 'I NOTNULL DEFAULT 0');
    $ztable['dizkus_forum_mods_column_idx'] = array ('forum_id'  => 'forum_id',
                                                      'user_id'   => 'user_id');
    
    //
    // forums
    //
    $ztable['dizkus_forums'] = DBUtil::getLimitedTablename('dizkus_forums');
    $ztable['dizkus_forums_column'] = array('forum_id'               => 'forum_id',
                                             'forum_name'             => 'forum_name',
                                             'forum_desc'             => 'forum_desc',
                                             'forum_topics'           => 'forum_topics',
                                             'forum_posts'            => 'forum_posts',
                                             'forum_last_post_id'     => 'forum_last_post_id',
                                             'cat_id'                 => 'cat_id',
                                             'is_subforum'            => 'is_subforum',
                                             'forum_order'            => 'forum_order',
                                             'forum_pop3_active'      => 'forum_pop3_active',
                                             'forum_pop3_server'      => 'forum_pop3_server',
                                             'forum_pop3_port'        => 'forum_pop3_port',
                                             'forum_pop3_login'       => 'forum_pop3_login',
                                             'forum_pop3_password'    => 'forum_pop3_password',
                                             'forum_pop3_interval'    => 'forum_pop3_interval',
                                             'forum_pop3_lastconnect' => 'forum_pop3_lastconnect',
                                             'forum_pop3_pnuser'      => 'forum_pop3_pnuser',
                                             'forum_pop3_pnpassword'  => 'forum_pop3_pnpassword',
                                             'forum_pop3_matchstring' => 'forum_pop3_matchstring',
                                             'forum_moduleref'        => 'forum_moduleref',
                                             'forum_pntopic'          => 'forum_pntopic');
    $ztable['dizkus_forums_column_def'] = array('forum_id'               => 'I AUTO PRIMARY',
                                                 'forum_name'             => 'C(150) NOTNULL DEFAULT \'\'',
                                                 'forum_desc'             => 'X NOTNULL DEFAULT \'\'',
                                                 'forum_topics'           => 'I UNSIGNED NOTNULL DEFAULT 0',
                                                 'forum_posts'            => 'I UNSIGNED NOTNULL DEFAULT 0',
                                                 'forum_last_post_id'     => 'I NOTNULL DEFAULT 0',
                                                 'cat_id'                 => 'I NOTNULL DEFAULT 0',
                                                 'is_subforum'            => 'I(1) NOTNULL DEFAULT 0',
                                                 'forum_order'            => 'I NOTNULL DEFAULT 0',
                                                 'forum_pop3_active'      => 'I(1) NOTNULL DEFAULT 0',
                                                 'forum_pop3_server'      => 'C(60) NOTNULL DEFAULT \'\'',
                                                 'forum_pop3_port'        => 'I(5) NOTNULL DEFAULT 110',
                                                 'forum_pop3_login'       => 'C(60) NOTNULL DEFAULT \'\'',
                                                 'forum_pop3_password'    => 'C(60) NOTNULL DEFAULT \'\'',
                                                 'forum_pop3_interval'    => 'I(4) NOTNULL DEFAULT 0',
                                                 'forum_pop3_lastconnect' => 'I NOTNULL DEFAULT 0',
                                                 'forum_pop3_pnuser'      => 'C(60) NOTNULL DEFAULT \'\'',
                                                 'forum_pop3_pnpassword'  => 'C(40) NOTNULL DEFAULT \'\'',
                                                 'forum_pop3_matchstring' => 'C(255) NOTNULL DEFAULT \'\'', 
                                                 'forum_moduleref'        => 'I NOTNULL DEFAULT 0',
                                                 'forum_pntopic'          => 'I(4) NOTNULL DEFAULT 0');  
    $ztable['dizkus_forums_column_idx'] = array ('forum_last_post_id'  => 'forum_last_post_id',
                                                  'forum_moduleref'     => 'forum_moduleref');

    //
    // posts
    //
    $ztable['dizkus_posts'] = DBUtil::getLimitedTablename('dizkus_posts');
    $ztable['dizkus_posts_column'] = array('post_id'    => 'post_id',
                                            'topic_id'   => 'topic_id',
                                            'forum_id'   => 'forum_id',
                                            'poster_id'  => 'poster_id',
                                            'post_time'  => 'post_time',
                                            'poster_ip'  => 'poster_ip',
                                            'post_msgid' => 'post_msgid',
                                            'post_text'  => 'post_text',
                                            'post_title' => 'post_title');
    $ztable['dizkus_posts_column_def'] = array('post_id'     => 'I AUTO PRIMARY',
                                                'topic_id'    => 'I NOTNULL DEFAULT 0',
                                                'forum_id'    => 'I NOTNULL DEFAULT 0',
                                                'poster_id'   => 'I NOTNULL DEFAULT 1',
                                                'post_time'   => 'C(20) NOTNULL DEFAULT \'\'',
                                                'poster_ip'   => 'C(50) NOTNULL DEFAULT \'\'',
                                                'post_msgid'  => 'C(100) NOTNULL DEFAULT \'\'',
                                                'post_text'   => 'X NOTNULL DEFAULT \'\'',
                                                'post_title'  => 'C(255) NOTNULL DEFAULT \'\'');
    $ztable['dizkus_posts_column_idx'] = array ('topic_id'   => 'topic_id',
                                                 'forum_id'   => 'forum_id',
                                                 'poster_id'  => 'poster_id',
                                                 'post_msgid' => 'post_msgid');

    // Enable categorization services
    $ztable['dizkus_posts_db_extra_enable_categorization'] = true;
    $ztable['dizkus_posts_primary_key_column'] = 'post_id';

    // 
    // posts_text - obsolete in 3.1, but still needed for upgrade purposes
    //
    $ztable['dizkus_posts_text'] = DBUtil::getLimitedTablename('dizkus_posts_text');
    $ztable['dizkus_posts_text_column'] = array('post_id'   => 'post_id',
                                                 'post_text' => 'post_text');
    $ztable['dizkus_posts_text_column_def'] = array('post_id'    => 'I NOTNULL DEFAULT 0',
                                                     'post_text'  => 'X NOTNULL DEFAULT \'\'');
    $ztable['dizkus_posts_text_column_idx'] = array ('post_id'  => 'post_id');

    //
    // ranks
    //
    $ztable['dizkus_ranks'] = DBUtil::getLimitedTablename('dizkus_ranks');
    $ztable['dizkus_ranks_column'] = array('rank_id'      => 'rank_id',
                                            'rank_title'   => 'rank_title',
                                            'rank_desc'    => 'rank_desc',
                                            'rank_min'     => 'rank_min',
                                            'rank_max'     => 'rank_max',
                                            'rank_special' => 'rank_special',
                                            'rank_image'   => 'rank_image');
    $ztable['dizkus_ranks_column_def'] = array('rank_id'       => 'I AUTO PRIMARY',
                                                'rank_title'    => 'C(50) NOTNULL DEFAULT \'\'',
                                                'rank_desc'     => 'C(255) NOTNULL DEFAULT \'\'',
                                                'rank_min'      => 'I NOTNULL DEFAULT 0',
                                                'rank_max'      => 'I NOTNULL DEFAULT 0',
                                                'rank_special'  => 'I(2) NOTNULL DEFAULT 0',
                                                'rank_image'    => 'C(255) NOTNULL DEFAULT \'\'');
    $ztable['dizkus_ranks_column_idx'] = array ('rank_min'  => 'rank_min',
                                                 'rank_max'  => 'rank_max');

    // 
    // subscriptions
    //
    $ztable['dizkus_subscription'] = DBUtil::getLimitedTablename('dizkus_subscription');
    $ztable['dizkus_subscription_column'] = array('msg_id'   => 'msg_id',
                                                   'forum_id' => 'forum_id',
                                                   'user_id'  => 'user_id');
    $ztable['dizkus_subscription_column_def'] = array('msg_id'      => 'I AUTO PRIMARY',
                                                       'forum_id'    => 'I NOTNULL DEFAULT 0',
                                                       'user_id'     => 'I NOTNULL DEFAULT 0');
    $ztable['dizkus_subscription_column_idx'] = array ('forum_id' => 'forum_id',
                                                        'user_id'  => 'user_id');

    //
    // topics
    //
    $ztable['dizkus_topics'] = DBUtil::getLimitedTablename('dizkus_topics');
    $ztable['dizkus_topics_column'] = array('topic_id'           => 'topic_id',
                                             'topic_title'        => 'topic_title',
                                             'topic_poster'       => 'topic_poster',
                                             'topic_time'         => 'topic_time',
                                             'topic_views'        => 'topic_views',
                                             'topic_replies'      => 'topic_replies',
                                             'topic_last_post_id' => 'topic_last_post_id',
                                             'forum_id'           => 'forum_id',
                                             'topic_status'       => 'topic_status',
                                             'sticky'             => 'sticky',
                                             'topic_reference'    => 'topic_reference');
    $ztable['dizkus_topics_column_def'] = array('topic_id'           => 'I AUTO PRIMARY',
                                                 'topic_title'        => 'C(255) NOTNULL DEFAULT \'\'',
                                                 'topic_poster'       => 'I NOTNULL DEFAULT 0',
                                                 'topic_time'         => 'C(20) NOTNULL DEFAULT \'\'',
                                                 'topic_views'        => 'I NOTNULL DEFAULT 0',
                                                 'topic_replies'      => 'I UNSIGNED NOTNULL DEFAULT 0',
                                                 'topic_last_post_id' => 'I NOTNULL DEFAULT 0',
                                                 'forum_id'           => 'I NOTNULL DEFAULT 0',
                                                 'topic_status'       => 'I NOTNULL DEFAULT 0',
                                                 'sticky'             => 'I(3) UNSIGNED NOTNULL DEFAULT 0',
                                                 'topic_reference'    => 'C(60) NOTNULL DEFAULT \'\'');
    $ztable['dizkus_topics_column_idx'] = array('forum_id'           => 'forum_id',
                                                  'topic_last_post_id' => 'topic_last_post_id');

    //
    // users - obsole since 3.2.0 as these data have been moved to attributes, remove in a later version
    //
    $ztable['dizkus_users'] = DBUtil::getLimitedTablename('dizkus_users');
    $ztable['dizkus_users_column'] = array('user_id'         => 'user_id',
                                            'user_posts'      => 'user_posts',
                                            'user_rank'       => 'user_rank',
                                            'user_level'      => 'user_level',
                                            'user_lastvisit'  => 'user_lastvisit',
                                            'user_favorites'  => 'user_favorites',
                                            'user_post_order' => 'user_post_order');
    $ztable['dizkus_users_column_def'] = array('user_id'         => 'I PRIMARY',
                                                'user_posts'      => 'I UNSIGNED NOTNULL DEFAULT 0',
                                                'user_rank'       => 'I UNSIGNED NOTNULL DEFAULT 0',
                                                'user_level'      => 'I UNSIGNED NOTNULL DEFAULT 1',
                                                'user_lastvisit'  => 'T DEFAULT NULL',
                                                'user_favorites'  => 'I(1) NOTNULL DEFAULT 0',
                                                'user_post_order' => 'I(1) NOTNULL DEFAULT 0');

    // new in 1.7.5
    // 
    // topic_subscriptions
    //
    $ztable['dizkus_topic_subscription'] = DBUtil::getLimitedTablename('dizkus_topic_subscription');
    $ztable['dizkus_topic_subscription_column'] = array('id'       => 'id',
                                                         'topic_id' => 'topic_id',
                                                         'user_id'  => 'user_id');
    $ztable['dizkus_topic_subscription_column_def'] = array('id'        => 'I AUTO PRIMARY',
                                                             'topic_id'  => 'I NOTNULL DEFAULT 0',
                                                             'user_id'   => 'I NOTNULL DEFAULT 0');
    $ztable['dizkus_topic_subscription_column_idx'] = array ('topic_id' => 'topic_id',
                                                              'user_id'  => 'user_id');

    // new in 2.0.1
    //
    // favorites
    //
    $ztable['dizkus_forum_favorites'] = DBUtil::getLimitedTablename('dizkus_forum_favorites');
    $ztable['dizkus_forum_favorites_column'] = array('forum_id' => 'forum_id',
                                                      'user_id'  => 'user_id');
    $ztable['dizkus_forum_favorites_column_def'] = array('forum_id'  => 'I NOTNULL DEFAULT 0',
                                                          'user_id'   => 'I NOTNULL DEFAULT 0');
    $ztable['dizkus_forum_favorites_column_idx'] = array('forum_id' => 'forum_id',
                                                          'user_id'  => 'user_id');


    // Return the table information
    return $ztable;
}
