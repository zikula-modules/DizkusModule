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
 * initialize module
 * @version $Id$
 * @author Andreas Krapohl 
 * @copyright 2003 by Andreas Krapohl, Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html> 
 * @link http://www.pnforum.de
 *
 ***********************************************************************/
 
include_once("modules/pnForum/common.php");

/**
 *	Initialize a new install of the pnForum module
 *
 *	This function will initialize a new installation of pnForum.
 *	It is accessed via the PostNuke Admin interface and should
 *	not be called directly. 
 */
 
function pnForum_init()
{
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	
    // creating categories table
    $pnforumcategoriestable = $pntable['pnforum_categories'];
    $pnforumcategoriescolumn = &$pntable['pnforum_categories_column'];

    $sql = "CREATE TABLE $pnforumcategoriestable (
                $pnforumcategoriescolumn[cat_id] int(10) NOT NULL auto_increment,
                $pnforumcategoriescolumn[cat_title] varchar(100) default NULL,
                $pnforumcategoriescolumn[cat_order] varchar(10) default NULL,
                PRIMARY KEY (cat_id))";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    // creating forum_mods table
    $pnforumforummodstable = $pntable['pnforum_forum_mods'];
    $pnforumforummodscolumn = &$pntable['pnforum_forum_mods_column'];

    $sql = "CREATE TABLE $pnforumforummodstable (
                $pnforumforummodscolumn[forum_id] int(10) NOT NULL default '0',
                $pnforumforummodscolumn[user_id] int(10) NOT NULL default '0')";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    // creating forums table
    $pnforumforumstable = $pntable['pnforum_forums'];
    $pnforumforumscolumn = &$pntable['pnforum_forums_column'];

    $sql = "CREATE TABLE $pnforumforumstable (
            $pnforumforumscolumn[forum_id] int(10) NOT NULL auto_increment,
            $pnforumforumscolumn[forum_name] varchar(150),
            $pnforumforumscolumn[forum_desc] text,
            $pnforumforumscolumn[forum_access] int(10) DEFAULT '1',
            $pnforumforumscolumn[forum_topics] int(10) unsigned DEFAULT '0' NOT NULL,
            $pnforumforumscolumn[forum_posts] int(10) unsigned DEFAULT '0' NOT NULL,
            $pnforumforumscolumn[forum_last_post_id] int(10) unsigned NOT NULL,
            $pnforumforumscolumn[cat_id] int(10),
            $pnforumforumscolumn[forum_type] int(10) DEFAULT '0',
            $pnforumforumscolumn[forum_order] mediumint(8) unsigned,
            PRIMARY KEY (forum_id),
            KEY forum_last_post_id (forum_last_post_id))";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    // creating posts table
    $pnforumpoststable = $pntable['pnforum_posts'];
    $pnforumpostscolumn = &$pntable['pnforum_posts_column'];

    $sql = "CREATE TABLE $pnforumpoststable (
            $pnforumpostscolumn[post_id] int(10) NOT NULL auto_increment,
            $pnforumpostscolumn[topic_id] int(10) NOT NULL default '0',
            $pnforumpostscolumn[forum_id] int(10) NOT NULL default '0',
            $pnforumpostscolumn[poster_id] int(10) NOT NULL default '0',
            $pnforumpostscolumn[post_time] varchar(20) default NULL,
            $pnforumpostscolumn[poster_ip] varchar(16) default NULL,
            PRIMARY KEY (post_id),
            KEY post_id(post_id),
            KEY forum_id(forum_id),
            KEY topic_id(topic_id),
            KEY poster_id(poster_id))";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    // creating posts text table
    $pnforumpoststexttable = $pntable['pnforum_posts_text'];
    $pnforumpoststextcolumn = &$pntable['pnforum_posts_text_column'];

    $sql = "CREATE TABLE $pnforumpoststexttable (
            $pnforumpoststextcolumn[post_id] int(10) NOT NULL default '0',
            $pnforumpoststextcolumn[post_text] text,
			PRIMARY KEY  (post_id))";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    // creating subscription table
    $pnforumsubscriptiontable = $pntable['pnforum_subscription'];
    $pnforumsubscriptioncolumn = &$pntable['pnforum_subscription_column'];

    $sql = "CREATE TABLE $pnforumsubscriptiontable (
            $pnforumsubscriptioncolumn[msg_id] int(10) NOT NULL auto_increment,
            $pnforumsubscriptioncolumn[forum_id] int(10) NOT NULL default '0',
            $pnforumsubscriptioncolumn[user_id] int(10) NOT NULL default '0',
            PRIMARY KEY (msg_id))";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    // creating ranks table
    $pnforumrankstable = $pntable['pnforum_ranks'];
    $pnforumrankscolumn = &$pntable['pnforum_ranks_column'];

    $sql = "CREATE TABLE $pnforumrankstable (
            $pnforumrankscolumn[rank_id] int(10) NOT NULL auto_increment,
            $pnforumrankscolumn[rank_title] varchar(50) NOT NULL,
            $pnforumrankscolumn[rank_min] int(10) DEFAULT '0' NOT NULL,
            $pnforumrankscolumn[rank_max] int(10) DEFAULT '0' NOT NULL,
            $pnforumrankscolumn[rank_special] int(2) DEFAULT '0',
            $pnforumrankscolumn[rank_image] varchar(255),
            $pnforumrankscolumn[rank_style] varchar(255) NOT NULL,
            PRIMARY KEY (rank_id),
            KEY rank_min (rank_min),
            KEY rank_max (rank_max))";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    // creating topics table
    $pnforumtopicstable = $pntable['pnforum_topics'];
    $pnforumtopicscolumn = &$pntable['pnforum_topics_column'];

    $sql = "CREATE TABLE $pnforumtopicstable (
            $pnforumtopicscolumn[topic_id] int(10) NOT NULL auto_increment,
            $pnforumtopicscolumn[topic_title] varchar(100),
            $pnforumtopicscolumn[topic_poster] int(10),
            $pnforumtopicscolumn[topic_time] varchar(20),
            $pnforumtopicscolumn[topic_views] int(10) DEFAULT '0' NOT NULL,
            $pnforumtopicscolumn[topic_replies] int(10) unsigned DEFAULT '0' NOT NULL,
            $pnforumtopicscolumn[topic_last_post_id] int(10) unsigned NOT NULL,
            $pnforumtopicscolumn[forum_id] int(10) DEFAULT '0' NOT NULL,
            $pnforumtopicscolumn[topic_status] int(10) DEFAULT '0' NOT NULL,
            $pnforumtopicscolumn[topic_notify] int(2) DEFAULT '0',
            $pnforumtopicscolumn[sticky] tinyint(3) unsigned DEFAULT '0' NOT NULL,
            $pnforumtopicscolumn[sticky_label] varchar(255),
            $pnforumtopicscolumn[poll_id] int(10) unsigned DEFAULT '0' NOT NULL,
            PRIMARY KEY (topic_id),
            KEY forum_id (forum_id),
            KEY topic_last_post_id (topic_last_post_id))";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    // creating users table
    $pnforumuserstable = $pntable['pnforum_users'];
    $pnforumuserscolumn = &$pntable['pnforum_users_column'];

    $sql = "CREATE TABLE $pnforumuserstable (
            $pnforumuserscolumn[user_id] int(10) unsigned DEFAULT '0' NOT NULL,
            $pnforumuserscolumn[user_posts] int(10) unsigned DEFAULT '0' NOT NULL,
            $pnforumuserscolumn[user_rank] int(10) unsigned DEFAULT '0' NOT NULL,
            $pnforumuserscolumn[user_level] int(10) unsigned DEFAULT '1' NOT NULL,
            $pnforumuserscolumn[user_lastvisit] timestamp(14),
            PRIMARY KEY (user_id))";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

	// creating topic_subsription table (new in 1.7.5)
    $pnforumtopicsubscriptiontable = $pntable['pnforum_topic_subscription'];
    $pnforumtopicsubscriptioncolumn = &$pntable['pnforum_topic_subscription_column'];
  
	$sql = "CREATE TABLE $pnforumtopicsubscriptiontable (
			$pnforumtopicsubscriptioncolumn[topic_id] int(10) DEFAULT '0' NOT NULL,
			$pnforumtopicsubscriptioncolumn[forum_id] int(10) DEFAULT '0' NOT NULL,
			$pnforumtopicsubscriptioncolumn[user_id] int(10) DEFAULT '0' NOT NULL
			)";

    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    if(crossupgrade()===false) {
        pnForum_delete();
        return false;
    }
	
	// Bulletin Board settings
	$module = 'pnForum';
	$adminmail = pnConfigGetVar('adminmail');
	
	pnModSetVar('pnForum', 'posts_per_page', 15);
	pnModSetVar('pnForum', 'topics_per_page', 15);
	pnModSetVar('pnForum', 'hot_threshold', 20);
	pnModSetVar('pnForum', 'email_from', "$adminmail");
	pnModSetVar('pnForum', 'default_lang', 'iso-8859-1');
	pnModSetVar('pnForum', 'url_smiles', "modules/$module/pnimages/smiles");
	pnModSetVar('pnForum', 'url_ranks_images', "modules/$module/pnimages/ranks");
	pnModSetVar('pnForum', 'folder_image', "modules/$module/pnimages/folder.gif");
	pnModSetVar('pnForum', 'hot_folder_image', "modules/$module/pnimages/hot_folder.gif");
	pnModSetVar('pnForum', 'newposts_image', "modules/$module/pnimages/red_folder.gif");
	pnModSetVar('pnForum', 'hot_newposts_image', "modules/$module/pnimages/hot_red_folder.gif");
	pnModSetVar('pnForum', 'posticon', "modules/$module/pnimages/posticon.gif");
	pnModSetVar('pnForum', 'profile_image', "modules/$module/pnimages/profile.gif");
	pnModSetVar('pnForum', 'locked_image', "modules/$module/pnimages/lock.gif");
	pnModSetVar('pnForum', 'locktopic_image', "modules/$module/pnimages/lock_topic.gif");
	pnModSetVar('pnForum', 'deltopic_image', "modules/$module/pnimages/del_topic.gif");
	pnModSetVar('pnForum', 'movetopic_image', "modules/$module/pnimages/move_topic.gif");
	pnModSetVar('pnForum', 'unlocktopic_image', "modules/$module/pnimages/unlock_topic.gif");
	pnModSetVar('pnForum', 'stickytopic_image', "modules/$module/pnimages/sticky.gif");
	pnModSetVar('pnForum', 'unstickytopic_image', "modules/$module/pnimages/unsticky.gif");
	pnModSetVar('pnForum', 'firstnew_image', "modules/$module/pnimages/firstnew.gif");
	pnModSetVar('pnForum', 'post_sort_order', "ASC");
	pnModSetVar('pnForum', 'show_html', "yes");
	pnModSetVar('pnForum', 'show_bbcode', "yes");
	pnModSetVar('pnForum', 'show_smile', "yes");
	pnModSetVar('pnForum', 'log_ip', "yes");

    // Initialisation successful
    return true;

}


/**
 *	Deletes an install of the pnForum module
 *
 *	This function removes pnForum from your
 *	PostNuke install and should be accessed via
 *	the PostNuke Admin interface
 */

function pnForum_delete()
{
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_categories]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_forum_mods]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_forums]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_posts]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_posts_text]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_subscription]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_ranks]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_topics]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_users]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_topic_subscription]";
    $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', $dbconn->ErrorMsg());
        return false;
    }

	// remove module vars
    $modvarstable = $pntable['module_vars'];
    $modvarscolumn = $pntable['module_vars_column'];
    $sql = "DELETE FROM $modvarstable
            WHERE $modvarscolumn[modname]='pnForum'";
    $result = $dbconn->Execute($sql);
    if ($dbconn->ErrorNo() != 0) {
    	pnSessionSetVar('errormsg', $dbconn->ErrorMsg() . " : $sql");
    	return -1;
    }
	

    // Deletion successful
    return true;
}

/**
 *  upgrade the pnForum module
 *
 *	This function is used to upgrade an old version
 *	of pnForum.  It is accessed via the PostNuke
 *	Admin interface and should not be called directly.
 */

function pnForum_upgrade($oldversion)
{
    
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

	switch($oldversion) {
        default: break;			
    }
    
    return true;
}

/**
 * cross upgrade from phpBB_14
 *
 * returns true if crossupgrade has been done or false if not. In this case the init()
 * function can create its tables
 *
 */
function crossupgrade()
{
    if(pnModAvailable('phpBB_14')) {
        pnModDBInfoLoad('phpBB_14');  
    	$dbconn =& pnDBGetConn(true);
	    $pntable =& pnDBGetTables();
    
        $oldtables = array('phpbb14_categories',
                           'phpbb14_forums',
                           'phpbb14_forum_mods',
                           'phpbb14_posts',
                           'phpbb14_posts_text',
                           'phpbb14_ranks',
                           'phpbb14_subscription',
                           'phpbb14_topic_subscription',
                           'phpbb14_topics',
                           'phpbb14_users');
        $newtables = array('pnforum_categories',
                           'pnforum_forums',
                           'pnforum_forum_mods',
                           'pnforum_posts',
                           'pnforum_posts_text',
                           'pnforum_ranks',
                           'pnforum_subscription',
                           'pnforum_topic_subscription',
                           'pnforum_topics',
                           'pnforum_users');
        
        for($cnt=0; $cnt<10; $cnt++) {    
            //$oldtable = $pntable['phpbb14_categories'];
            //$newtable = $pntable['pnforum_categories'];
            $sql = "INSERT INTO ".$pntable[$newtables[$cnt]]." 
                    SELECT * FROM ".$pntable[$oldtables[$cnt]].";";
            $dbconn->Execute($sql);
            if ($dbconn->ErrorNo() != 0) {
            	pnSessionSetVar('errormsg', $dbconn->ErrorMsg() . " : $sql");
            	return -1;
            }
        }
    }    
    return true;
}
?>