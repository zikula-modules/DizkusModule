<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

Loader::includeOnce('modules/pnForum/common.php');

/**
 *	Initialize a new install of the pnForum module
 *
 *	This function will initialize a new installation of pnForum.
 *	It is accessed via the PostNuke Admin interface and should
 *	not be called directly.
 */

function pnForum_init()
{
    list($dbconn, $pntable) = pnfOpenDB();

//    pnSessionSetVar('upgrade_to_2_5_done', 0);
//    pnSessionSetVar('upgrade_to_2_6_done', 0);

    // creating categories table
    $pnforumcategoriestable = $pntable['pnforum_categories'];
    $pnforumcategoriescolumn = &$pntable['pnforum_categories_column'];

    $sql = "CREATE TABLE $pnforumcategoriestable (
                $pnforumcategoriescolumn[cat_id] int(10) NOT NULL auto_increment,
                $pnforumcategoriescolumn[cat_title] varchar(100) default NULL,
                $pnforumcategoriescolumn[cat_order] varchar(10) default NULL,
                PRIMARY KEY (cat_id))";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    // creating forum_mods table
    $pnforumforummodstable = $pntable['pnforum_forum_mods'];
    $pnforumforummodscolumn = &$pntable['pnforum_forum_mods_column'];

    $sql = "CREATE TABLE $pnforumforummodstable (
                $pnforumforummodscolumn[forum_id] int(10) NOT NULL default '0',
                $pnforumforummodscolumn[user_id] int(10) NOT NULL default '0')";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

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
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

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
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    // creating posts text table
    $pnforumpoststexttable = $pntable['pnforum_posts_text'];
    $pnforumpoststextcolumn = &$pntable['pnforum_posts_text_column'];

    $sql = "CREATE TABLE $pnforumpoststexttable (
            $pnforumpoststextcolumn[post_id] int(10) NOT NULL default '0',
            $pnforumpoststextcolumn[post_text] text,
			PRIMARY KEY  (post_id))";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    // creating subscription table
    $pnforumsubscriptiontable = $pntable['pnforum_subscription'];
    $pnforumsubscriptioncolumn = &$pntable['pnforum_subscription_column'];

    $sql = "CREATE TABLE $pnforumsubscriptiontable (
            $pnforumsubscriptioncolumn[msg_id] int(10) NOT NULL auto_increment,
            $pnforumsubscriptioncolumn[forum_id] int(10) NOT NULL default '0',
            $pnforumsubscriptioncolumn[user_id] int(10) NOT NULL default '0',
            PRIMARY KEY (msg_id))";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

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
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

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
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    // creating users table
    $pnforumuserstable = $pntable['pnforum_users'];
    $pnforumuserscolumn = &$pntable['pnforum_users_column'];

    // we do not create the user_favorites and user_post_order fields here because this would
    // break the upgrade from phpBB_14 to pnForum.
    // we first create the exact double of the phpBB_14 database layout and copy all data that
    // we are going to find, then we will extend the tables
    $sql = "CREATE TABLE $pnforumuserstable (
            $pnforumuserscolumn[user_id] int(10) unsigned DEFAULT '0' NOT NULL,
            $pnforumuserscolumn[user_posts] int(10) unsigned DEFAULT '0' NOT NULL,
            $pnforumuserscolumn[user_rank] int(10) unsigned DEFAULT '0' NOT NULL,
            $pnforumuserscolumn[user_level] int(10) unsigned DEFAULT '1' NOT NULL,
            $pnforumuserscolumn[user_lastvisit] timestamp(14),
            PRIMARY KEY (user_id))";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

	// creating topic_subscription table (new in 1.7.5)
    $pnforumtopicsubscriptiontable = $pntable['pnforum_topic_subscription'];
    $pnforumtopicsubscriptioncolumn = &$pntable['pnforum_topic_subscription_column'];

	$sql = "CREATE TABLE $pnforumtopicsubscriptiontable (
			$pnforumtopicsubscriptioncolumn[topic_id] int(10) DEFAULT '0' NOT NULL,
			$pnforumtopicsubscriptioncolumn[forum_id] int(10) DEFAULT '0' NOT NULL,
			$pnforumtopicsubscriptioncolumn[user_id] int(10) DEFAULT '0' NOT NULL
			)";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    if(crossupgrade()===false) {
        pnForum_delete();
        return false;
    }

    $upgrade_to_201 = pnForum_upgrade_to_2_0_1();
    if($upgrade_to_201 <> true) {
        return false;
    }

    // upgrade to 2.5
    $upgrade_to_2_5 = pnForum_upgrade_to_2_5(true);
    if($upgrade_to_2_5 <> true) {
        return false;
    }

    // upgrade to 2.6
    $upgrade_to_2_6 = pnForum_upgrade_to_2_6();
    if($upgrade_to_2_6 <> true) {
        return false;
    }

    // upgrade to 2.7 just adds a new modvar.... no need to check for
    // return value
    pnForum_upgrade_to_2_7();
    
	// Bulletin Board settings
	pnModSetVar('pnForum', 'posts_per_page', 15);
	pnModSetVar('pnForum', 'topics_per_page', 15);
	pnModSetVar('pnForum', 'hot_threshold', 20);
	pnModSetVar('pnForum', 'email_from', pnConfigGetVar('adminmail'));
	pnModSetVar('pnForum', 'default_lang', 'iso-8859-1');
	pnModSetVar('pnForum', 'url_ranks_images', "modules/pnForum/pnimages/ranks");
	pnModSetVar('pnForum', 'posticon', "modules/pnForum/pnimages/posticon.gif");
	pnModSetVar('pnForum', 'firstnew_image', "modules/pnForum/pnimages/firstnew.gif");
	pnModSetVar('pnForum', 'post_sort_order', 'ASC');
	pnModSetVar('pnForum', 'log_ip', 'yes');
	pnModSetVar('pnForum', 'slimforum', 'no');
	pnModSetVar('pnForum', 'hideusers', 'no');
	pnModSetVar('pnForum', 'removesignature', 'no');
	pnModSetVar('pnForum', 'striptags', 'no');
    pnModSetVar('pnForum', 'deletehookaction', 'lock');

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

    $sql = "DROP TABLE IF EXISTS $pntable[pnforum_forum_favorites]";
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

    // remove the hooks
    //
    // createhook
    //
    if (!pnModUnRegisterHook('item',
                             'create',
                             'API',
                             'pnForum',
                             'hook',
                             'createbyitem')) {
        pnSessionSetVar('errormsg', _PNFORUM_FAILEDTODELETEHOOK . ' (create)');
        return false;
    }

    //
    // updatehook
    //
    if (!pnModUnRegisterHook('item',
                             'update',
                             'API',
                             'pnForum',
                             'hook',
                             'updatebyitem')) {
        pnSessionSetVar('errormsg', _PNFORUM_FAILEDTODELETEHOOK . ' (update)');
        return false;
    }

    //
    // deletehook
    //
    if (!pnModUnRegisterHook('item',
                             'delete',
                             'API',
                             'pnForum',
                             'hook',
                             'deletebyitem')) {
        pnSessionSetVar('errormsg', _PNFORUM_FAILEDTODELETEHOOK . ' (delete)');
        return false;
    }

    //
    // displayhook
    //
    if (!pnModUnRegisterHook('item',
                             'display',
                             'GUI',
                             'pnForum',
                             'hook',
                             'showdiscussionlink')) {
        pnSessionSetVar('errormsg', _PNFORUM_FAILEDTODELETEHOOK . ' (display)');
        return false;
    }

	// remove module vars
	pnModDelVar('pnForum');

    // Deletion successful
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
	    list($dbconn, $pntable) = pnfOpenDB();

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
            $sql = "INSERT INTO ".$pntable[$newtables[$cnt]]."
                    SELECT * FROM ".$pntable[$oldtables[$cnt]].";";
            $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
            pnfCloseDB($result);
        }
    }
    return true;
}

/**
 * upgrade to v2.0.1
 *
 */
function pnForum_upgrade_to_2_0_1()
{
    list($dbconn, $pntable) = pnfOpenDB();

    // creating forum_favorites table
    $pnforumforumfavoritestable = $pntable['pnforum_forum_favorites'];
    $pnforumforumfavoritescolumn = &$pntable['pnforum_forum_favorites_column'];

    $sql = "CREATE TABLE $pnforumforumfavoritestable (
                $pnforumforumfavoritescolumn[forum_id] int(10) NOT NULL default '0',
                $pnforumforumfavoritescolumn[user_id] int(10) NOT NULL default '0',
                PRIMARY KEY (forum_id, user_id))";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    $pnforumuserstable = $pntable['pnforum_users'];
    $pnforumuserscolumn = &$pntable['pnforum_users_column'];

    $sql = "ALTER TABLE $pnforumuserstable
            ADD $pnforumuserscolumn[user_favorites] int(1) DEFAULT '0' NOT NULL,
            ADD $pnforumuserscolumn[user_post_order] int(1) DEFAULT '0' NOT NULL";

    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    // remove unused vars
	pnModDelVar('pnForum', 'locktopic_image');
	pnModDelVar('pnForum', 'unlocktopic_image');
	pnModDelVar('pnForum', 'stickytopic_image');
	pnModDelVar('pnForum', 'unstickytopic_image');
	pnModDelVar('pnForum', 'movetopic_image');
	pnModDelVar('pnForum', 'deltopic_image');
	pnModDelVar('pnForum', 'locked_image');
	pnModDelVar('pnForum', 'profile_image');
	pnModDelVar('pnForum', 'show_html');
	pnModDelVar('pnForum', 'show_bbcode');
	pnModDelVar('pnForum', 'show_smile');

    return true;
}

/**
 * upgrade to v2.5
 *
 */
function pnForum_upgrade_to_2_5($createindex=true)
{
    list($dbconn, $pntable) = pnfOpenDB();

    $pnforumforumstable = $pntable['pnforum_forums'];
    $pnforumforumscolumn = &$pntable['pnforum_forums_column'];

    $sql = "ALTER TABLE $pnforumforumstable
            ADD $pnforumforumscolumn[forum_pop3_active]      INT(1) DEFAULT '0' NOT NULL,
            ADD $pnforumforumscolumn[forum_pop3_server]      VARCHAR(60),
            ADD $pnforumforumscolumn[forum_pop3_port]        INT(5) DEFAULT '110' NOT NULL,
            ADD $pnforumforumscolumn[forum_pop3_login]       VARCHAR(60),
            ADD $pnforumforumscolumn[forum_pop3_password]    VARCHAR(60),
            ADD $pnforumforumscolumn[forum_pop3_interval]    INT(4) DEFAULT '0' NOT NULL,
            ADD $pnforumforumscolumn[forum_pop3_lastconnect] INT(11) DEFAULT '0' NOT NULL,
            ADD $pnforumforumscolumn[forum_pop3_pnuser]      VARCHAR(60),
            ADD $pnforumforumscolumn[forum_pop3_pnpassword]  VARCHAR(40),
            ADD $pnforumforumscolumn[forum_pop3_matchstring] VARCHAR(255)";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    $pnforumpoststable = $pntable['pnforum_posts'];
    $pnforumpostscolumn = &$pntable['pnforum_posts_column'];
    $sql = "ALTER TABLE $pnforumpoststable
            ADD $pnforumpostscolumn[post_msgid] VARCHAR(100) ,
            ADD INDEX post_msgid( post_msgid )";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    // check the result from the interactive upgrade

    // if we use an innodb table we cannot create inde fields
    $dbtabletype = $GLOBALS['pnconfig']['dbtabletype'];
    if(strtolower($dbtabletype) == 'innodb') {
        $createindex = false;
    }

    if($createindex == true) {
        $pnforumtopicstable = $pntable['pnforum_topics'];
        $sql = "ALTER TABLE $pnforumtopicstable
                ADD FULLTEXT (topic_title)";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);

        $pnforumpoststexttable = $pntable['pnforum_posts_text'];
        $sql = "ALTER TABLE $pnforumpoststexttable
                ADD FULLTEXT (post_text)";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        pnfCloseDB($result);

        // fulltext index created, set special forum modvar
        pnModSetVar('pnForum', 'fulltextindex', 1);
    } else {
        // no fulltext index created, set special forum modvar
        pnModSetVar('pnForum', 'fulltextindex', 0);
    }
    pnModSetVar('pnForum', 'extendedsearch', 0);

    // adding index fields to subscription tables
    $pnforumsubscriptiontable = $pntable['pnforum_subscription'];
    $pnforumsubscriptioncolumn = &$pntable['pnforum_subscription_column'];
    $sql = "ALTER TABLE $pnforumsubscriptiontable
            ADD INDEX forum_id( forum_id ),
            ADD INDEX user_id( user_id)";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    $pnforumtopicsubscriptiontable = $pntable['pnforum_topic_subscription'];
    $pnforumtopicsubscriptioncolumn = &$pntable['pnforum_topic_subscription_column'];
    $sql = "ALTER TABLE $pnforumtopicsubscriptiontable
            ADD INDEX topic_id( topic_id ),
            ADD INDEX forum_id( forum_id ),
            ADD INDEX user_id( user_id)";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    $pnforummodstable = $pntable['pnforum_forum_mods'];
    $pnforummodscolumn = &$pntable['pnforum_forum_mods_column'];
    $sql = "ALTER TABLE $pnforummodstable
            ADD INDEX forum_id( forum_id ),
            ADD INDEX user_id( user_id)";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    // no longer needed
	pnModDelVar('pnForum', 'url_smiles');
	pnModDelVar('pnForum', 'folder_image');
	pnModDelVar('pnForum', 'hot_folder_image');
	pnModDelVar('pnForum', 'newposts_image');
	pnModDelVar('pnForum', 'hot_newposts_image');
    pnModDelVar('pnForum', 'min_postings_for_anchor');

    // new
    pnModSetVar('pnForum', 'extendedsearch', 'no');
    pnModSetVar('pnForum', 'm2f_enabled', 'yes');
    pnModSetVar('pnForum', 'favorites_enabled', 'yes');
	pnModSetVar('pnForum', 'hideusers', 'no');
	pnModSetVar('pnForum', 'removesignature', 'no');
	pnModSetVar('pnForum', 'striptags', 'no');

    return true;
}

/**
 * upgrade to v2.6
 *
 */
function pnForum_upgrade_to_2_6()
{
    list($dbconn, $pntable) = pnfOpenDB();

    $pnforumforumstable = $pntable['pnforum_forums'];
    $pnforumforumscolumn = &$pntable['pnforum_forums_column'];

    $sql = "ALTER TABLE $pnforumforumstable
            ADD $pnforumforumscolumn[forum_moduleref] INT(11) ,
            ADD $pnforumforumscolumn[forum_pntopic] TINYINT(4) NOT NULL DEFAULT 0 ,
            ADD INDEX forum_moduleref( forum_moduleref )";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);

    pnfCloseDB($result);

    $pnforumtopicstable = $pntable['pnforum_topics'];
    $pnforumtopicscolumn = &$pntable['pnforum_topics_column'];

    $sql = "ALTER TABLE $pnforumtopicstable
            ADD $pnforumtopicscolumn[topic_reference] VARCHAR(60) ,
            ADD INDEX topic_reference( topic_reference )";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    // resize the subject field to 255 chars
    $sql = "ALTER TABLE $pnforumtopicstable
            CHANGE $pnforumtopicscolumn[topic_title] $pnforumtopicscolumn[topic_title] VARCHAR(255)";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    pnfCloseDB($result);

    // create the hooks: create, delete, display.
    // everything else is not needed , at least not atm.
    //
    // createhook
    //
    if (!pnModRegisterHook('item',
                           'create',
                           'API',
                           'pnForum',
                           'hook',
                           'createbyitem')) {
        pnSessionSetVar('errormsg', _PNFORUM_FAILEDTOCREATEHOOK . ' (create)');
        return false;
    }

    //
    // updatehook
    //
    if (!pnModRegisterHook('item',
                           'update',
                           'API',
                           'pnForum',
                           'hook',
                           'updatebyitem')) {
        pnSessionSetVar('errormsg', _PNFORUM_FAILEDTOCREATEHOOK . ' (update)');
        return false;
    }

    //
    // deletehook
    //
    if (!pnModRegisterHook('item',
                           'delete',
                           'API',
                           'pnForum',
                           'hook',
                           'deletebyitem')) {
        pnSessionSetVar('errormsg', _PNFORUM_FAILEDTOCREATEHOOK . ' (delete)');
        return false;
    }

    //
    // displayhook
    //
    if (!pnModRegisterHook('item',
                           'display',
                           'GUI',
                           'pnForum',
                           'hook',
                           'showdiscussionlink')) {
        pnSessionSetVar('errormsg', _PNFORUM_FAILEDTOCREATEHOOK . ' (display)');
        return false;
    }

    // modvars
    pnModSetVar('pnForum', 'deletehookaction', 'lock');
    pnModSetVar('pnForum', 'rss2f_enabled', 'yes');

    return true;
}

/**
 * upgrade to v2.7
 *
 */
function pnForum_upgrade_to_2_7()
{
    pnModSetVar('pnForum', 'shownewtopicconfirmation', 'no');
    pnModSetVar('pnForum', 'timespanforchanges', 24);
    pnModSetVar('pnForum', 'forum_enabled', 'yes');
    pnModSetVar('pnForum', 'forum_disabled_info', _PNFORUM_DISABLED_INFO);
    return true;
}

/**
 * interactiveupgrade
 *
 *
 */
function pnForum_init_interactiveupgrade($args)
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $oldversion = pnVarCleanFromInput('oldversion');
    
    extract($args);
    unset($args);

    global $modversion;
    include_once('modules/pnForum/pnversion.php');

    $authid = pnSecGenAuthKey('Modules');
    switch($oldversion) {
        case '2.0.0':
            pnForum_upgrade_to_2_0_1();
        case '2.0.1':
            $templatefile = 'pnforum_upgrade_25.html';
            break;
        case '2.5':
            $templatefile = 'pnforum_upgrade_26.html';
            break;
        case '2.6':
            $templatefile = 'pnforum_upgrade_27.html';
            break;
        default:
            // no interactive upgrade for version < 2.0.1
            // or latest step reached
           	$smarty =& new Smarty;
           	$smarty->compile_dir = pnConfigGetVar('temp') . '/pnRender_compiled';
           	$smarty->cache_dir = pnConfigGetVar('temp') . '/pnRender_cache';
           	$smarty->use_sub_dirs = false;
           	$smarty->clear_compiled_tpl();
            pnRedirect(pnModURL('Modules', 'admin', 'upgrade', array('authid' => $authid )));
            return true;
    }

    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('oldversion', $oldversion);
    $pnr->assign('newversion', $modversion['version']);
    $pnr->assign('authid', $authid);
    return $pnr->fetch($templatefile);
}

/**
 * interactiveupgrade_to_2_5
 *
 */
function pnForum_init_interactiveupgrade_to_2_5()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($submit, $createindex) = pnVarCleanFromInput('submit', 'createindex');

    if(!empty($submit)) {
        $createindex = ($createindex==1) ? true : false;
        $result = pnForum_upgrade_to_2_5($createindex);
        if($result<>true) {
            return showforumerror(_PNFORUM_TO25_FAILED, __FILE__, __LINE__);
        }
        return pnRedirect(pnModURL('pnForum', 'init', 'interactiveupgrade', array('oldversion' => '2.5' )));
    }
    pnRedirect(pnModURL('Modules', 'admin', 'view'));
    return true;
}

/**
 * interactiveupgrade_to_2_6
 *
 */
function pnForum_init_interactiveupgrade_to_2_6()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $submit = pnVarCleanFromInput('submit');

    if(!empty($submit)) {
        $result = pnForum_upgrade_to_2_6();
        if($result<>true) {
            return showforumerror(_PNFORUM_TO26_FAILED, __FILE__, __LINE__);
        }
        return pnRedirect(pnModURL('pnForum', 'init', 'interactiveupgrade', array('oldversion' => '2.6' )));
    }
    pnRedirect(pnModURL('Modules', 'admin', 'view'));
    return true;
}

/**
 * interactiveupgrade_to_2_7
 *
 */
function pnForum_init_interactiveupgrade_to_2_7()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $submit = pnVarCleanFromInput('submit');

    if(!empty($submit)) {
        pnForum_upgrade_to_2_7();
        return pnRedirect(pnModURL('pnForum', 'init', 'interactiveupgrade', array('oldversion' => '2.7' )));
    }
    pnRedirect(pnModURL('Modules', 'admin', 'view'));
    return true;
}
