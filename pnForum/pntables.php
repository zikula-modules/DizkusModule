<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.post-nuke.net/                                            *
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

    // Get the name for the template item table.  This is not necessary
    // but helps in the following statements and keeps them readable
    $pnforum_banlist = pnConfigGetVar('prefix') . '_pnforum_banlist';

    // Set the table name
    $pntable['pnforum_banlist'] = $pnforum_banlist;

    // Set the column names.  Note that the array has been formatted
    // on-screen to be very easy to read by a user.

    $pntable['pnforum_banlist_column'] = array('ban_id'    => $pnforum_banlist . '.ban_id',
                                        'ban_userid'   => $pnforum_banlist . '.ban_userid',
                                        'ban_ip'   => $pnforum_banlist . '.ban_ip',
                                        'ban_start'   => $pnforum_banlist . '.ban_start',
                                        'ban_end'   => $pnforum_banlist . '.ban_end',
                                        'ban_time_type'   => $pnforum_banlist . '.ban_time_type');

    $pnforum_access = pnConfigGetVar('prefix') . '_pnforum_access';
    $pntable['pnforum_access'] = $pnforum_access;
    $pntable['pnforum_access_column'] = array('access_id'    => $pnforum_access . '.access_id',
                                        'access_title'   => $pnforum_access . '.access_title');

    $pnforum_events = pnConfigGetVar('prefix') . '_pnforum_events';
    $pntable['pnforum_events'] = $pnforum_events;
    $pntable['pnforum_events_column'] = array('event_id'    => $pnforum_events . '.event_id',
                                        'event_title'   => $pnforum_events . '.event_title');

    $pnforum_categories = pnConfigGetVar('prefix') . '_pnforum_categories';
    $pntable['pnforum_categories'] = $pnforum_categories;
    $pntable['pnforum_categories_column'] = array('cat_id'    => $pnforum_categories . '.cat_id',
                                        'cat_title'   => $pnforum_categories . '.cat_title',
                                        'cat_order'   => $pnforum_categories . '.cat_order');
                                        
    $pnforum_forum_access = pnConfigGetVar('prefix') . '_pnforum_forum_access';
    $pntable['pnforum_forum_access'] = $pnforum_forum_access;
    $pntable['pnforum_forum_access_column'] = array('forum_id'    => $pnforum_forum_access . '.forum_id',
                                        'user_id'   => $pnforum_forum_access . '.user_id',
                                        'can_post'   => $pnforum_forum_access . '.can_post');
                                                                  
    $pnforum_forum_mods = pnConfigGetVar('prefix') . '_pnforum_forum_mods';
    $pntable['pnforum_forum_mods'] = $pnforum_forum_mods;
    $pntable['pnforum_forum_mods_column'] = array('forum_id'    => $pnforum_forum_mods . '.forum_id',
                                        'user_id'   => $pnforum_forum_mods . '.user_id');

    $pnforum_forums = pnConfigGetVar('prefix') . '_pnforum_forums';
    $pntable['pnforum_forums'] = $pnforum_forums;
    $pntable['pnforum_forums_column'] = array('forum_id'    => $pnforum_forums . '.forum_id',
                                        'forum_name'   => $pnforum_forums . '.forum_name',
                                        'forum_desc'   => $pnforum_forums . '.forum_desc',
                                        'forum_access'   => $pnforum_forums . '.forum_access',
                                        'forum_topics'   => $pnforum_forums . '.forum_topics',
                                        'forum_posts'   => $pnforum_forums . '.forum_posts',
                                        'forum_last_post_id'   => $pnforum_forums . '.forum_last_post_id',
                                        'cat_id'   => $pnforum_forums . '.cat_id',
                                        'forum_type'   => $pnforum_forums . '.forum_type',
                                        'forum_order'   => $pnforum_forums . '.forum_order');

    $pnforum_posts = pnConfigGetVar('prefix') . '_pnforum_posts';
    $pntable['pnforum_posts'] = $pnforum_posts;
    $pntable['pnforum_posts_column'] = array('post_id'    => $pnforum_posts . '.post_id',
                                        'topic_id'   => $pnforum_posts . '.topic_id',
                                        'forum_id'   => $pnforum_posts . '.forum_id',
                                        'poster_id'   => $pnforum_posts . '.poster_id',
                                        'post_time'   => $pnforum_posts . '.post_time',
                                        'poster_ip'   => $pnforum_posts . '.poster_ip');

    $pnforum_posts_text = pnConfigGetVar('prefix') . '_pnforum_posts_text';
    $pntable['pnforum_posts_text'] = $pnforum_posts_text;
    $pntable['pnforum_posts_text_column'] = array('post_id'    => $pnforum_posts_text . '.post_id',
                                        'post_text'   => $pnforum_posts_text . '.post_text');

    $pnforum_ranks = pnConfigGetVar('prefix') . '_pnforum_ranks';
    $pntable['pnforum_ranks'] = $pnforum_ranks;
    $pntable['pnforum_ranks_column'] = array('rank_id'    => $pnforum_ranks . '.rank_id',
                                        'rank_title'   => $pnforum_ranks . '.rank_title',
                                        'rank_min'   => $pnforum_ranks . '.rank_min',
                                        'rank_max'   => $pnforum_ranks . '.rank_max',
                                        'rank_special'   => $pnforum_ranks . '.rank_special',
                                        'rank_image'   => $pnforum_ranks . '.rank_image',
                                        'rank_style'   => $pnforum_ranks . '.rank_style');

    $pnforum_smiles = pnConfigGetVar('prefix') . '_pnforum_smiles';
    $pntable['pnforum_smiles'] = $pnforum_smiles;
    $pntable['pnforum_smiles_column'] = array('id'    => $pnforum_smiles . '.id',
                                        'code'   => $pnforum_smiles . '.code',
                                        'smile_url'   => $pnforum_smiles . '.smile_url',
                                        'emotion'   => $pnforum_smiles . '.emotion');

    $pnforum_subscription = pnConfigGetVar('prefix') . '_pnforum_subscription';
    $pntable['pnforum_subscription'] = $pnforum_subscription;
    $pntable['pnforum_subscription_column'] = array('msg_id'    => $pnforum_subscription . '.msg_id',
                                        'forum_id'   => $pnforum_subscription . '.forum_id',
                                        'user_id'   => $pnforum_subscription . '.user_id');

    $pnforum_topics = pnConfigGetVar('prefix') . '_pnforum_topics';
    $pntable['pnforum_topics'] = $pnforum_topics;
    $pntable['pnforum_topics_column'] = array('topic_id'    => $pnforum_topics . '.topic_id',
                                        'topic_title'   => $pnforum_topics . '.topic_title',
                                        'topic_poster'   => $pnforum_topics . '.topic_poster',
                                        'topic_time'   => $pnforum_topics . '.topic_time',
                                        'topic_views'   => $pnforum_topics . '.topic_views',
                                        'topic_replies'   => $pnforum_topics . '.topic_replies',
                                        'topic_last_post_id'   => $pnforum_topics . '.topic_last_post_id',
                                        'forum_id'   => $pnforum_topics . '.forum_id',
                                        'topic_status'   => $pnforum_topics . '.topic_status',
                                        'topic_notify'   => $pnforum_topics . '.topic_notify',
                                        'sticky'   => $pnforum_topics . '.sticky',
                                        'sticky_label'   => $pnforum_topics . '.sticky_label',
                                        'poll_id'   => $pnforum_topics . '.poll_id');


    $pnforum_users = pnConfigGetVar('prefix') . '_pnforum_users';
    $pntable['pnforum_users'] = $pnforum_users;
    $pntable['pnforum_users_column'] = array('user_id'    => $pnforum_users . '.user_id',
                                        'user_posts'   => $pnforum_users . '.user_posts',
                                        'user_rank'   => $pnforum_users . '.user_rank',
                                        'user_level'   => $pnforum_users . '.user_level',
                                        'user_lastvisit'   => $pnforum_users . '.user_lastvisit');

            
    $pnforum_words = pnConfigGetVar('prefix') . '_pnforum_words';
    $pntable['pnforum_words'] = $pnforum_words;
    $pntable['pnforum_words_column'] = array('word_id'		=> $pnforum_words . '.word_id',
	 										'word'				=> $pnforum_words . '.word',
	 										'replacement'		=> $pnforum_words . '.replacement');

    // these tables defined for removal check

    $pnforum_disallow = pnConfigGetVar('prefix') . '_pnforum_disallow';
    $pntable['pnforum_disallow'] = $pnforum_disallow;
    
    // new in 1.7.5
    $pnforum_topic_subscription = pnConfigGetVar('prefix') . '_pnforum_topic_subscription';
    $pntable['pnforum_topic_subscription'] = $pnforum_topic_subscription;
    $pntable['pnforum_topic_subscription_column'] = array('topic_id'		=> $pnforum_topic_subscription . '.topic_id',
	 										'forum_id'				=> $pnforum_topic_subscription . '.forum_id',
	 										'user_id'		=> $pnforum_topic_subscription . '.user_id');
    

    // Return the table information
    return $pntable;
}


?>