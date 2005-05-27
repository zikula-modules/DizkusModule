<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                            *
 ************************************************************************
 * Modified version of: *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License *
 ************************************************************************
 * This program is free software; you can redistribute it and/or modify *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation; either version 2 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * This program is distributed in the hope that it will be useful,      *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with this program; if not, write to the Free Software          *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 *
 * USA                                                                  *
 ************************************************************************
 *
 * table defines
 * @version $Id$
 * @author Andreas Krapohl
 * @copyright 2003 by Andreas Krapohl
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

/**
 * This function is called internally by the core whenever the module is
 * loaded.  It adds in the information
 */


function pnForum_pntables()
{
    // Initialise table array
    $pntable = array();

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.

    $pnforum_categories = pnConfigGetVar('prefix') . '_pnforum_categories';
    $pntable['pnforum_categories'] = $pnforum_categories;
    $pntable['pnforum_categories_column'] = array('cat_id'    => $pnforum_categories . '.cat_id',
                                                  'cat_title' => $pnforum_categories . '.cat_title',
                                                  'cat_order' => $pnforum_categories . '.cat_order');

    $pnforum_forum_mods = pnConfigGetVar('prefix') . '_pnforum_forum_mods';
    $pntable['pnforum_forum_mods'] = $pnforum_forum_mods;
    $pntable['pnforum_forum_mods_column'] = array('forum_id' => $pnforum_forum_mods . '.forum_id',
                                                  'user_id'  => $pnforum_forum_mods . '.user_id');

    $pnforum_forums = pnConfigGetVar('prefix') . '_pnforum_forums';
    $pntable['pnforum_forums'] = $pnforum_forums;
    $pntable['pnforum_forums_column'] = array('forum_id'               => $pnforum_forums . '.forum_id',
                                              'forum_name'             => $pnforum_forums . '.forum_name',
                                              'forum_desc'             => $pnforum_forums . '.forum_desc',
                                              'forum_access'           => $pnforum_forums . '.forum_access',
                                              'forum_topics'           => $pnforum_forums . '.forum_topics',
                                              'forum_posts'            => $pnforum_forums . '.forum_posts',
                                              'forum_last_post_id'     => $pnforum_forums . '.forum_last_post_id',
                                              'cat_id'                 => $pnforum_forums . '.cat_id',
                                              'forum_type'             => $pnforum_forums . '.forum_type',
                                              'forum_order'            => $pnforum_forums . '.forum_order',
                                              'forum_pop3_active'      => $pnforum_forums . '.forum_pop3_active',
                                              'forum_pop3_server'      => $pnforum_forums . '.forum_pop3_server',
                                              'forum_pop3_port'        => $pnforum_forums . '.forum_pop3_port',
                                              'forum_pop3_login'       => $pnforum_forums . '.forum_pop3_login',
                                              'forum_pop3_password'    => $pnforum_forums . '.forum_pop3_password',
                                              'forum_pop3_interval'    => $pnforum_forums . '.forum_pop3_interval',
                                              'forum_pop3_lastconnect' => $pnforum_forums . '.forum_pop3_lastconnect',
                                              'forum_pop3_pnuser'      => $pnforum_forums . '.forum_pop3_pnuser',
                                              'forum_pop3_pnpassword'  => $pnforum_forums . '.forum_pop3_pnpassword',
                                              'forum_pop3_matchstring' => $pnforum_forums . '.forum_pop3_matchstring',);

    $pnforum_posts = pnConfigGetVar('prefix') . '_pnforum_posts';
    $pntable['pnforum_posts'] = $pnforum_posts;
    $pntable['pnforum_posts_column'] = array('post_id'    => $pnforum_posts . '.post_id',
                                             'topic_id'   => $pnforum_posts . '.topic_id',
                                             'forum_id'   => $pnforum_posts . '.forum_id',
                                             'poster_id'  => $pnforum_posts . '.poster_id',
                                             'post_time'  => $pnforum_posts . '.post_time',
                                             'poster_ip'  => $pnforum_posts . '.poster_ip',
                                             'post_msgid' => $pnforum_posts . '.msgid');

    $pnforum_posts_text = pnConfigGetVar('prefix') . '_pnforum_posts_text';
    $pntable['pnforum_posts_text'] = $pnforum_posts_text;
    $pntable['pnforum_posts_text_column'] = array('post_id'   => $pnforum_posts_text . '.post_id',
                                                  'post_text' => $pnforum_posts_text . '.post_text');

    $pnforum_ranks = pnConfigGetVar('prefix') . '_pnforum_ranks';
    $pntable['pnforum_ranks'] = $pnforum_ranks;
    $pntable['pnforum_ranks_column'] = array('rank_id'      => $pnforum_ranks . '.rank_id',
                                             'rank_title'   => $pnforum_ranks . '.rank_title',
                                             'rank_min'     => $pnforum_ranks . '.rank_min',
                                             'rank_max'     => $pnforum_ranks . '.rank_max',
                                             'rank_special' => $pnforum_ranks . '.rank_special',
                                             'rank_image'   => $pnforum_ranks . '.rank_image',
                                             'rank_style'   => $pnforum_ranks . '.rank_style');

    $pnforum_subscription = pnConfigGetVar('prefix') . '_pnforum_subscription';
    $pntable['pnforum_subscription'] = $pnforum_subscription;
    $pntable['pnforum_subscription_column'] = array('msg_id'   => $pnforum_subscription . '.msg_id',
                                                    'forum_id' => $pnforum_subscription . '.forum_id',
                                                    'user_id'  => $pnforum_subscription . '.user_id');

    $pnforum_topics = pnConfigGetVar('prefix') . '_pnforum_topics';
    $pntable['pnforum_topics'] = $pnforum_topics;
    $pntable['pnforum_topics_column'] = array('topic_id'           => $pnforum_topics . '.topic_id',
                                              'topic_title'        => $pnforum_topics . '.topic_title',
                                              'topic_poster'       => $pnforum_topics . '.topic_poster',
                                              'topic_time'         => $pnforum_topics . '.topic_time',
                                              'topic_views'        => $pnforum_topics . '.topic_views',
                                              'topic_replies'      => $pnforum_topics . '.topic_replies',
                                              'topic_last_post_id' => $pnforum_topics . '.topic_last_post_id',
                                              'forum_id'           => $pnforum_topics . '.forum_id',
                                              'topic_status'       => $pnforum_topics . '.topic_status',
                                              'topic_notify'       => $pnforum_topics . '.topic_notify',
                                              'sticky'             => $pnforum_topics . '.sticky',
                                              'sticky_label'       => $pnforum_topics . '.sticky_label',
                                              'poll_id'            => $pnforum_topics . '.poll_id');


    $pnforum_users = pnConfigGetVar('prefix') . '_pnforum_users';
    $pntable['pnforum_users'] = $pnforum_users;
    $pntable['pnforum_users_column'] = array('user_id'         => $pnforum_users . '.user_id',
                                             'user_posts'      => $pnforum_users . '.user_posts',
                                             'user_rank'       => $pnforum_users . '.user_rank',
                                             'user_level'      => $pnforum_users . '.user_level',
                                             'user_lastvisit'  => $pnforum_users . '.user_lastvisit',
                                             'user_favorites'  => $pnforum_users . '.user_favorites',
                                             'user_post_order' => $pnforum_users . '.user_post_order');

    // new in 1.7.5
    $pnforum_topic_subscription = pnConfigGetVar('prefix') . '_pnforum_topic_subscription';
    $pntable['pnforum_topic_subscription'] = $pnforum_topic_subscription;
    $pntable['pnforum_topic_subscription_column'] = array('topic_id' => $pnforum_topic_subscription . '.topic_id',
                                                          'forum_id' => $pnforum_topic_subscription . '.forum_id',
                                                          'user_id'  => $pnforum_topic_subscription . '.user_id');

    // new in 2.0.1
    $pnforum_forum_favorites = pnConfigGetVar('prefix') . '_pnforum_forum_favorites';
    $pntable['pnforum_forum_favorites'] = $pnforum_forum_favorites;
    $pntable['pnforum_forum_favorites_column'] = array('forum_id' => $pnforum_forum_favorites . '.forum_id',
                                                       'user_id'  => $pnforum_forum_favorites . '.user_id');


    // Return the table information
    return $pntable;
}


?>
