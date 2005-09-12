<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                               *
 ************************************************************************
 * Modified version of:                                                 *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License                                                              *
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
 * english language defines
 * @version $Id$
 * @author various
 * @copyright 2004 by pnForum team
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/


define('_PNFORUM_CB_RECENTPOSTS','Recent postings:');

define('_PNFORUM_SEARCHINCLUDE_MISSINGPARAMETERS', 'Missing parameters to perform search');
define('_PNFORUM_NOMOVETO', 'No target forum for moving selected');
define('_PNFORUM_NOJOINTO', 'No target topic for joining selected');
define('_PNFORUM_SELECTACTION', 'select action');
define('_PNFORUM_SELECTTARGETFORUM', 'select target forum');
define('_PNFORUM_SELECTTARGETTOPIC', 'select target topic');
define('_PNFORUM_OR', 'or');
define('_PNFORUM_MODERATE_JOINTOPICS_HINT', 'If you want to join topics, select the target topic here');
define('_PNFORUM_MODERATORSOPTIONS', 'Moderators options');
define('_PNFORUM_JOINTOPICS', 'Join topics');

define('_PNFORUM_HOTTOPIC', 'hot topic');
define('_PNFORUM_HOTNEWTOPIC', 'hot topic with new postings');
define('_PNFORUM_NORMALTOPIC', 'normal topic');
define('_PNFORUM_NORMALNEWTOPIC', 'normal topic with new postings');

define('_PNFORUM_BLOCK_TEMPLATENAME', 'Name of templatefile');
define('_PNFORUM_BLOCK_PARAMETERS', 'Parameters');
define('_PNFORUM_BLOCK_PARAMETERS_HINT', 'comma separated list, e.g.. maxposts=5,forum_id=27 ');

define('_PNFORUM_MODERATE','Moderate');
define('_PNFORUM_SELECTED','Selection');
define('_PNFORUM_STICKYTOPICS','Make selected topics sticky');
define('_PNFORUM_UNSTICKYTOPICS','Make selected topics unsticky');
define('_PNFORUM_LOCKTOPICS','Lock selected topics');
define('_PNFORUM_UNLOCKTOPICS','Open selected topics');
define('_PNFORUM_DELETETOPICS','Delete selected topics');
define('_PNFORUM_MOVETOPICS','Move selected topics');
define('_PNFORUM_MODERATE_MOVETOPICS_HINT','Choose target forum for moving topics:');
define('_PNFORUM_SUBMIT_HINT','BEWARE: pnForum will not ask you for any confirmation! Clicking on Submit will immediately start the selected action!');

// new
define('_PNFORUM_TOGGLEALL', 'Remove all topic subscriptions');
define('_PNFORUM_PREFS_HIDEUSERSINFORUMADMIN', 'Hide users in forum admin');
define('_PNFORUM_UNKNOWNUSER', '**unknown user**');
define('_PNFORUM_MANAGETOPICSUBSCRIPTIONS_HINT', 'Here you can manage your topic subscriptions.');
define('_PNFORUM_NOTOPICSUBSCRIPTIONSFOUND', 'no topic subscriptions found');
define('_PNFORUM_MANAGETOPICSUBSCRIPTIONS', 'Manage topic subscriptions');
define('_PNFORUM_GROUP', 'Group');
define('_PNFORUM_NOSPECIALRANKSINDATABASE', 'No Special Ranks in the Database. You can add one by entering into the form below.');
define('_PNFORUM_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX', 'Use extended fulltext search in internal search');
define('_PNFORUM_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX_HINT', '<i>The extended fulltext search enables parameters like "+pnforum -skype" for postings that contain "pnforum" but not "skype". Minimum requirement is MySQL 4.01.</i><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-boolean.html" title="Extended fulltest search in MySQL">Extended fulltest search in MySQL</a>.');
define('_PNFORUM_DATABASEINUSE', 'Database in use');
define('_PNFORUM_PREFS_SEARCHWITHFULLTEXTINDEX', 'Search forums with fulltext index');
define('_PNFORUM_PREFS_SEARCHWITHFULLTEXTINDEX_HINT', '<i>Searching the forums with fulltext index fields needs min. MySQL 4 or later and does not work with InnoDB databases. This flag will normally be set during installation when the index fields have been created. The search result might by empty if the query string is present in too many postings. This is a "feature" of MySQL.</i><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-search.html" title="Fulltext search in MySQL">Fulltext search in MySQL</a>.');
define('_PNFORUM_ADMINADVANCEDCONFIG', 'Advanced configuration');
define('_PNFORUM_ADMINADVANCEDCONFIG_HINT', 'Caution: wrong settings here can lead to unwanted side effects. If you do not understand what is going on here, stay away and leave the settings as they are!');
define('_PNFORUM_ADMINADVANCEDCONFIG_INFO', 'Set advanced configuration, caution!');
define('_PNFORUM_MODERATION_NOTICE', 'Moderation request');
define('_PNFORUM_NOTIFYMODERATORTITLE', 'Notify a moderator about a posting');
define('_PNFORUM_REPORTINGUSERNAME', 'Reporting user');
define('_PNFORUM_NOTIFYMODBODY1', 'Request for moderation');
define('_PNFORUM_NOTIFYMODBODY2', 'Comment');
define('_PNFORUM_NOTIFYMODBODY3', 'Link to topic');
define('_PNFORUM_NOTIFYMODERATOR', 'notify moderator');
define('_PNFORUM_JOINTOPICS', 'Join topics');
define('_PNFORUM_JOINTOPICS_INFO', 'Joins two topics together');
define('_PNFORUM_JOINTOPICS_TOTOPIC', 'Target topic');

define('_PNFORUM_MOVEPOST', 'Move post');
define('_PNFORUM_MOVEPOST_INFO', 'Move a post from one topic to another');
define('_PNFORUM_MOVEPOST_TOTOPIC', 'Target topic');

define('_PNFORUM_MAIL2FORUMPOSTS', 'Mailinglists');
define('_PNFORUM_NOSUBJECT', 'no subject');
define('_PNFORUM_PREFS_FAVORITESENABLED', 'Favorites enabled');
define('_PNFORUM_PREFS_M2FENABLED', 'Mail2Forum enabled');
define('_PNFORUM_POP3TESTRESULTS', 'Pop3 test results');
define('_PNFORUM_BACKTOFORUMADMIN', 'Back to forum admin');
define('_PNFORUM_WRONGPNVERSIONFORMAIL2FORUM', 'Mail2Forum minimum requirement is PostNuke .760 or later!');
define('_PNFORUM_MINSHORT', 'min');
define('_PNFORUM_MAIL2FORUM', 'Mail2Forum');
define('_PNFORUM_POP3ACTIVE', 'Mail2Forum active');
define('_PNFORUM_POP3TEST', 'Perform Pop3 test after saving');
define('_PNFORUM_POP3SERVER', 'Pop3 server');
define('_PNFORUM_POP3PORT', 'Pop3 port');
define('_PNFORUM_POP3LOGIN', 'Pop3 login');
define('_PNFORUM_POP3PASSWORD', 'Pop3 password');
define('_PNFORUM_POP3PASSWORDCONFIRM', 'Pop3 password confirmation');
define('_PNFORUM_POP3INTERVAL', 'Poll interval');
define('_PNFORUM_POP3MATCHSTRING', 'Rule');
define('_PNFORUM_POP3MATCHSTRINGHINT', 'The rule is a regular expression that the mails subject as to match to avoid spa posigns. An empty rule means no checks!');
define('_PNFORUM_PASSWORDNOMATCH', 'Passwords do not match, please go back and correct');
define('_PNFORUM_POP3PNUSER', 'PN username');
define('_PNFORUM_POP3PNPASSWORD', 'PN password');
define('_PNFORUM_POP3PNPASSWORDCONFIRM', 'PN password confirmation');

define('_PNFORUM_WHATISBBCODE', 'What is BBCode?');
define('_PNFORUM_START', 'Start');
define('_PNFORUM_PREFS_AUTOSUBSCRIBE', 'Autosubscribe to new topics or posts');

//
// alphasorting starts here
//
// A
//
define('_PNFORUM_ACCOUNT_INFORMATION', 'Users IP and Account information');
define('_PNFORUM_ACTIONS','Actions');
define('_PNFORUM_ACTIVE_FORUMS','top active Forums:');
define('_PNFORUM_ACTIVE_POSTERS','top active Posters:');
define('_PNFORUM_ADD_FAVORITE_FORUM','Add favorite forum');
define('_PNFORUM_ADD','Add');
define('_PNFORUM_ADDNEWCATEGORY', '-- add new category --');
define('_PNFORUM_ADDNEWFORUM', '-- add new forum --');
define('_PNFORUM_ADMIN_SYNC','Sync');
define('_PNFORUM_ADMINBADWORDS_TITLE','Bad words filtering administration');
define('_PNFORUM_ADMINCATADD_INFO','This link will allow you to add a new category to put forums into');
define('_PNFORUM_ADMINCATADD','Add a Category');
define('_PNFORUM_ADMINCATDELETE_INFO','This link allows you to remove any category from the database');
define('_PNFORUM_ADMINCATDELETE','Remove a Catetegory');
define('_PNFORUM_ADMINCATEDIT_INFO','This link will allow you edit the title of a category');
define('_PNFORUM_ADMINCATEDIT','Edit a Category Title');
define('_PNFORUM_ADMINCATORDER_INFO','This link will allow you to change the order in which your categories display on the index page');
define('_PNFORUM_ADMINCATORDER','Re-Order Categories');
define('_PNFORUM_ADMINFORUMADD_INFO','This Link will take you to a page where you can add a forum to the database.');
define('_PNFORUM_ADMINFORUMADD','Add a Forum');
define('_PNFORUM_ADMINFORUMEDIT_INFO','This link will allow you to edit an existing forum.');
define('_PNFORUM_ADMINFORUMEDIT','Edit a Forum');
define('_PNFORUM_ADMINFORUMOPTIONS_INFO','This link will allow you to set various forum-wide options.');
define('_PNFORUM_ADMINFORUMOPTIONS','Forum-wide Options');
define('_PNFORUM_ADMINFORUMORDER_INFO','This allows you to change the order in which your forums display on the index page');
define('_PNFORUM_ADMINFORUMORDER','Re-Order Forums');
define('_PNFORUM_ADMINFORUMSPANEL','pnForum Administration');
define('_PNFORUM_ADMINFORUMSYNC_INFO','This link will allow you to sync up the forum and topic indexes to fix any discrepancies that might exist');
define('_PNFORUM_ADMINFORUMSYNC','Sync forum/topic index');
define('_PNFORUM_ADMINHONORARYASSIGN_INFO','This link will allow you to assign honorary user rankings to users');
define('_PNFORUM_ADMINHONORARYASSIGN','Assign honorary rank');
define('_PNFORUM_ADMINHONORARYRANKS_INFO','Here you can individually assign honorary ranks to specific users.');
define('_PNFORUM_ADMINHONORARYRANKS','Administer honorary ranks');
define('_PNFORUM_ADMINRANKS_INFO','This link will allow you to add/edit/delete different user rankings depending of the number of user posts.');
define('_PNFORUM_ADMINRANKS','Edit user ranks');
define('_PNFORUM_ADMINUSERRANK_IMAGE','Image');
define('_PNFORUM_ADMINUSERRANK_INFO','To modify a ranking simply change the values in the text boxes and click the Edit button.<br />To remove a ranking simply click on the "Delete" button next to the ranking.');
define('_PNFORUM_ADMINUSERRANK_INFO2','Use this form to add a ranking to the database.');
define('_PNFORUM_ADMINUSERRANK_MAX','Max posts');
define('_PNFORUM_ADMINUSERRANK_MIN','Min posts');
define('_PNFORUM_ADMINUSERRANK_TITLE','Users Ranks Administration');
define('_PNFORUM_ADMINUSERRANK_TITLE2','User rank');
define('_PNFORUM_ASSIGN','Assign');
define('_PNFORUM_ATTACHSIGNATURE', 'Attach my signature');
define('_PNFORUM_AUTHOR','Author');

//
// B
//
define('_PNFORUM_BODY','Message Body');
define('_PNFORUM_BOTTOM','Bottom');

//
// C
//
define('_PNFORUM_CANCELPOST','Cancel Post');
define('_PNFORUM_CATEGORIES','Categories');
define('_PNFORUM_CATEGORY','Category');
define('_PNFORUM_CATEGORYINFO', 'Category info');
define('_PNFORUM_CHANGE_FORUM_ORDER','Change Forum Order');
define('_PNFORUM_CHANGE_POST_ORDER','Change Post Order');
define('_PNFORUM_CHOOSECATWITHFORUMS4REORDER','Select category containing forums you want to reorder');
define('_PNFORUM_CHOOSEFORUMEDIT','Select forum to edit');
define('_PNFORUM_CREATEFORUM_INCOMPLETE','You did not fill in all required parts of the form.<br /> Did you assign at least one moderator? Please go back and correct the form');
define('_PNFORUM_CREATESHADOWTOPIC','Create shadow topic');
define('_PNFORUM_CURRENT', 'current');

//
// D
//
define('_PNFORUM_DATE','Date');
define('_PNFORUM_DELETE','Delete this Post');
define('_PNFORUM_DELETETOPIC_INFO', 'When you press the delete button at the bottom of this form the topic you have selected, and all its related posts, will be <strong>permanently</strong> removed.');
define('_PNFORUM_DELETETOPIC','Delete this topic');
define('_PNFORUM_DESCRIPTION', 'Description');
define('_PNFORUM_DOWN','Down');

//
// E
//
define('_PNFORUM_EDIT_POST','Edit post');
define('_PNFORUM_EDITBY','edited by:');
define('_PNFORUM_EDITDELETE', 'edit/delete');
define('_PNFORUM_EDITFORUMS','Edit forums');
define('_PNFORUM_EDITPREFS','Edit Your Preferences');
define('_PNFORUM_EMAIL_TOPIC', 'send as email');
define('_PNFORUM_EMAILTOPICMSG','Hi! Check this link, I think it should be interesting to you');
define('_PNFORUM_EMPTYMSG','You must type a message to post. You cannot post an empty message. Please go back and try again.');
define('_PNFORUM_ERROR_CONNECT','Error connecting to the database!<br />');
define('_PNFORUM_ERRORMAILTO', 'Send bug report');
define('_PNFORUM_ERROROCCURED', 'The following error occured:');

//
// F
//
define('_PNFORUM_FAVORITE_STATUS','Favorite status');
define('_PNFORUM_FAVORITES','Favorites');
define('_PNFORUM_FORUM_EDIT_FORUM','Edit Forum');
define('_PNFORUM_FORUM_EDIT_ORDER','Edit Order');
define('_PNFORUM_FORUM_NOEXIST','Error - The forum/topic you selected does not exist. Please go back and try again.');
define('_PNFORUM_FORUM_REORDER','Re-Order');
define('_PNFORUM_FORUM_SEQUENCE_DESCRIPTION','If you only want to move a forum by one position then click on the up or down arrow.  If a forum has an order number of 0 it will be ordered alphabetically by forum name.  The final display order will be Alphabetical Forums (with order=0) then by numerical order.  Click on the order number to assign a new order.');
define('_PNFORUM_FORUM','Forum');
define('_PNFORUM_FORUMID', 'Forums ID');
define('_PNFORUM_FORUMINFO', 'Forum Info');
define('_PNFORUM_FORUMS','Forums');
define('_PNFORUM_FORUMSINDEX','Forum-Index');

//
// G
//
define('_PNFORUM_GOTO_CAT','go to category');
define('_PNFORUM_GOTO_FORUM','go to forum');
define('_PNFORUM_GOTO_LATEST', 'View latest post');
define('_PNFORUM_GOTO_TOPIC','go to topic');
define('_PNFORUM_GOTOPAGE','Goto page');

//
// H
//
define('_PNFORUM_HOMEPAGE','Homepage');
define('_PNFORUM_HONORARY_RANK','Honorary rank');
define('_PNFORUM_HONORARY_RANKS','Honorary ranks');
define('_PNFORUM_HOST', 'Host');
define('_PNFORUM_HOTTHRES','More than %d posts');
define('_PNFORUM_HOURS','hours');

//
// I
//
define('_PNFORUM_IMAGE', 'Image');
define('_PNFORUM_IP_USERNAMES', 'Usernames of users that posted from this IP + post counts');
define('_PNFORUM_ISLOCKED','Topic is Locked. No new posts may be made in it');

//
// L
//
define('_PNFORUM_LAST_SEEN', 'last visit');
define('_PNFORUM_LAST','last');
define('_PNFORUM_LAST24','last 24 hours');
define('_PNFORUM_LASTCHANGE','last change on');
define('_PNFORUM_LASTPOST','Last Post');
define('_PNFORUM_LASTPOSTSTRING','%s<br />by %s');
define('_PNFORUM_LASTVISIT', 'last visit');
define('_PNFORUM_LASTWEEK','last week');
define('_PNFORUM_LATEST','latest posts');
define('_PNFORUM_LOCKTOPIC_INFO', 'When you press the lock button at the bottom of this form the topic you have selected will be <strong>locked</strong>. You may unlock it at a later time if you like.');
define('_PNFORUM_LOCKTOPIC','Lock this topic');

//
// M
//
define('_PNFORUM_MAILTO_NOBODY','You must enter a message.');
define('_PNFORUM_MAILTO_WRONGEMAIL','You did not enter an email address for the person to send the email to, or you entered an invalid email address.');
define('_PNFORUM_MODERATEDBY','Moderated by');
define('_PNFORUM_MODERATOR','Moderator');
define('_PNFORUM_MORETHAN','More than');
define('_PNFORUM_MOVED_SUBJECT', 'moved');
define('_PNFORUM_MOVETOPIC_INFO', 'When you press the move button at the bottom of this form the topic you have selected, and its related posts, will be <strong>moved</strong> to the forum you have selected. Note: You will only be able to move to a forum where you are moderator. Administrator is allowed to move any topic to any forum.');
define('_PNFORUM_MOVETOPIC','Move this topic');
define('_PNFORUM_MOVETOPICTO','Move topic to:');

//
// N
//
define('_PNFORUM_NEW_THREADS','New Topic');
define('_PNFORUM_NEWEST_FIRST','Display the newest post first');
define('_PNFORUM_NEWPOSTS','New posts since your last visit.');
define('_PNFORUM_NEWTOPIC','new topic');
define('_PNFORUM_NEXT_TOPIC','to next topic');
define('_PNFORUM_NEXTPAGE','Next Page');
define('_PNFORUM_NO_FORUMS_DB', 'No Forums in DB');
define('_PNFORUM_NO_FORUMS_MOVE', 'No more Forums moderated by you to move to');
define('_PNFORUM_NOAUTH_MODERATE','You are not the moderator of this forum therefore you cannot perform this function.');
define('_PNFORUM_NOAUTH_TOADMIN', 'You have no permission to admin this module');
define('_PNFORUM_NOAUTH_TOMODERATE', 'You have no permission to moderate this category or forum');
define('_PNFORUM_NOAUTH_TOREAD', 'You have no permission to read the content of this category or forum');
define('_PNFORUM_NOAUTH_TOSEE', 'You have no permission to see this category or forum');
define('_PNFORUM_NOAUTH_TOWRITE', 'You have no permission to write into this category or forum');
define('_PNFORUM_NOAUTH', 'No permission for this action');
define('_PNFORUM_NOAUTHPOST','Note: not authorised to post comments');
define('_PNFORUM_NOCATEGORIES', 'no categories defined');
define('_PNFORUM_NOFAVORITES','No favorites defined');
define('_PNFORUM_NOFORUMS', 'no forums defined');
define('_PNFORUM_NOMODERATORSASSIGNED', 'no moderator assigned');
define('_PNFORUM_NONE', 'none');
define('_PNFORUM_NONEWPOSTS','No new posts since your last visit.');
define('_PNFORUM_NOPOSTLOCK','You cannot post a reply to this topic, it has been locked.');
define('_PNFORUM_NOPOSTS','No Posts');
define('_PNFORUM_NORANK', 'no rank');
define('_PNFORUM_NORANKSINDATABASE', 'no ranks defined');
define('_PNFORUM_NOSMILES','There are no smilies in database');
define('_PNFORUM_NOTEDIT','You cannot edit a post that is not yours.');
define('_PNFORUM_NOTIFYBODY1','Forums');
define('_PNFORUM_NOTIFYBODY2','wrote at');
define('_PNFORUM_NOTIFYBODY3','Reply to this message:');
define('_PNFORUM_NOTIFYBODY4','Browse thread:');
define('_PNFORUM_NOTIFYBODY5','You are receiving this Email because you are subscribed to be notified of events in forums at:');
define('_PNFORUM_NOTIFYME', 'Notify me when a reply is posted');
define('_PNFORUM_NOTOPICS','There are no topics for this forum.');
define('_PNFORUM_NOTSUBSCRIBED','You are not subscribed to this forum');
define('_PNFORUM_NOUSER_OR_POST','Error - No such user or post in the database.');

//
// O
//
define('_PNFORUM_OFFLINE', 'offline');
define('_PNFORUM_OKTODELETE','delete?');
define('_PNFORUM_OLDEST_FIRST','Display the oldest post first');
define('_PNFORUM_ONEREPLY','reply');
define('_PNFORUM_ONLINE', 'online');
define('_PNFORUM_OPTIONS','Options');
define('_PNFORUM_OURLATESTPOSTS','Latest Forum-Posts');

//
// P
//
define('_PNFORUM_PAGE','Page #');
define('_PNFORUM_PERMDENY','Access denied!');
define('_PNFORUM_PERSONAL_SETTINGS','Personal Settings');
define('_PNFORUM_POST_GOTO_NEWEST','go to the newest post in ');
define('_PNFORUM_POST','post');
define('_PNFORUM_POSTED','Posted');
define('_PNFORUM_POSTER','Poster');
define('_PNFORUM_POSTS','Posts');
define('_PNFORUM_POWEREDBY', 'Powered by <a href="http://www.pnforum.de/" title="pnForum">pnForum</a> Version');
define('_PNFORUM_PREFS_ASCENDING', 'Ascending');
define('_PNFORUM_PREFS_CHARSET', 'Default charset:<br /><em>(This is the charset that will be used in e-mail headers)</em>');
define('_PNFORUM_PREFS_DESCENDING', 'Descending');
define('_PNFORUM_PREFS_EMAIL', 'Email From Address:<br /><em>(This is the address that will appear on every email sent by the forums)</em>');
define('_PNFORUM_PREFS_FIRSTNEWPOSTICON', 'First new post Icon:');
define('_PNFORUM_PREFS_HOTNEWPOSTSICON', 'Topic with both many and new posts image:');
define('_PNFORUM_PREFS_HOTTOPIC', 'Hot Topic Threshold:');
define('_PNFORUM_PREFS_HOTTOPICICON', 'Hot topic image:<br /><em>(Topic with many posts)</em>');
define('_PNFORUM_PREFS_ICONS','<br /><strong>Icons</strong>');
define('_PNFORUM_PREFS_LOGIP', 'Log IP addresses :');
define('_PNFORUM_PREFS_MINPOSTINGSFORANCHOR', 'Minimum number of before inserting an anchor:<br /><em>An anchor enables the user to go directly to the last posting in a large thread</em>');
define('_PNFORUM_PREFS_NEWPOSTSICON', 'New posts image:<br /><em>(Folder with new posts since user\'s last visit)</em>');
define('_PNFORUM_PREFS_NO', 'No');
define('_PNFORUM_PREFS_POSTICON', 'Post Icon:');
define('_PNFORUM_PREFS_POSTSORTORDER', 'Posts sorting order:');
define('_PNFORUM_PREFS_POSTSPERPAGE', 'Posts per Page:<br /><em>(This is the number of posts per topic that will be displayed per page of a topic. 15 by default.)</em>');
define('_PNFORUM_PREFS_RANKLOCATION', 'Ranks icons location:');
define('_PNFORUM_PREFS_RESTOREDEFAULTS', 'Restore defaults');
define('_PNFORUM_PREFS_SAVE', 'Save');
define('_PNFORUM_PREFS_SIGNATUREEND', 'End of signature format:');
define('_PNFORUM_PREFS_SIGNATURESTART', 'Start of signature format:');
define('_PNFORUM_PREFS_SLIMFORUM', 'Hide category view with one forum only');
define('_PNFORUM_PREFS_TOPICICON', 'Topic image:');
define('_PNFORUM_PREFS_TOPICSPERPAGE', 'Topics per Forum:<br /><em>(This is the number of topics per forum that will be displayed per page of a forum. 15 by default.)</em>');
define('_PNFORUM_PREFS_YES', 'Yes');
define('_PNFORUM_PREVIEW','Preview');
define('_PNFORUM_PREVIOUS_TOPIC','to previous topic');
define('_PNFORUM_PREVPAGE','Previous Page');
define('_PNFORUM_PRINT_POST','Print post');
define('_PNFORUM_PRINT_TOPIC','Print topic');
define('_PNFORUM_PROFILE', 'Profile');

//
// Q
//
define('_PNFORUM_QUICKREPLY', 'Quick reply');
define('_PNFORUM_QUICKSELECTFORUM','- select -');

//
// R
//
define('_PNFORUM_RECENT_POST_ORDER', 'Recent post order in topic view');
define('_PNFORUM_RECENT_POSTS','recent Topics:');
define('_PNFORUM_REG_SINCE', 'registered');
define('_PNFORUM_REGISTER','Register');
define('_PNFORUM_REGISTRATION_NOTE','Note: Registered users can subscribe to notifications about new posts');
define('_PNFORUM_REMOVE_FAVORITE_FORUM','Remove favorite forum');
define('_PNFORUM_REMOVE', 'remove');
define('_PNFORUM_REORDER','Reorder');
define('_PNFORUM_REORDERCATEGORIES','Reorder categories');
define('_PNFORUM_REORDERFORUMS','Reorder forums');
define('_PNFORUM_REPLACE_WORDS','Replace words');
define('_PNFORUM_REPLIES','Replies');
define('_PNFORUM_REPLY_POST','Reply to');
define('_PNFORUM_REPLY', 'reply');
define('_PNFORUM_REPLYLOCKED', 'closed');
define('_PNFORUM_REPLYQUOTE', 'quote');
define('_PNFORUM_RETURNTOTOPIC', 'Back to the topic');

//
// S
//
define('_PNFORUM_SAVEPREFS','Save Preferences');
define('_PNFORUM_SEARCH','search pnForum');
define('_PNFORUM_SEARCHALLFORUMS', 'alle forums');
define('_PNFORUM_SEARCHAND','all words [AND]');
define('_PNFORUM_SEARCHBOOL', 'Connection');
define('_PNFORUM_SEARCHFOR','Search for');
define('_PNFORUM_SEARCHINCLUDE_ALLTOPICS', 'all');
define('_PNFORUM_SEARCHINCLUDE_AUTHOR','Author');
define('_PNFORUM_SEARCHINCLUDE_BYDATE','by date');
define('_PNFORUM_SEARCHINCLUDE_BYFORUM','by forum');
define('_PNFORUM_SEARCHINCLUDE_BYTITLE','by title');
define('_PNFORUM_SEARCHINCLUDE_DATE','Date');
define('_PNFORUM_SEARCHINCLUDE_FORUM','Category and forum');
define('_PNFORUM_SEARCHINCLUDE_HITS', 'hits');
define('_PNFORUM_SEARCHINCLUDE_LIMIT', 'limit search to');
define('_PNFORUM_SEARCHINCLUDE_MISSINGPARAMETERS', 'missing search parameters');
define('_PNFORUM_SEARCHINCLUDE_NEWWIN','Show in new window');
define('_PNFORUM_SEARCHINCLUDE_NOENTRIES','No messages in forums found');
define('_PNFORUM_SEARCHINCLUDE_NOLIMIT', 'no limits');
define('_PNFORUM_SEARCHINCLUDE_ORDER','Order');
define('_PNFORUM_SEARCHINCLUDE_REPLIES','Replies');
define('_PNFORUM_SEARCHINCLUDE_RESULTS','Forums');
define('_PNFORUM_SEARCHINCLUDE_TITLE','Search forums');
define('_PNFORUM_SEARCHINCLUDE_VIEWS','Views');
define('_PNFORUM_SEARCHOR','single words [OR]');
define('_PNFORUM_SEARCHRESULTSFOR','Searchresults for');
define('_PNFORUM_SELECTEDITCAT','Select category');
define('_PNFORUM_SEND_PM', 'send PM');
define('_PNFORUM_SENDTO','Send to');
define('_PNFORUM_SEPARATOR','&nbsp;::&nbsp;');
define('_PNFORUM_SETTING', 'Settings');
define('_PNFORUM_SHADOWTOPIC_MESSAGE', 'The original posting has been moved <a title="moved" href="%s">here</a>.');
define('_PNFORUM_SHOWALLFORUMS','Show all forums');
define('_PNFORUM_SHOWFAVORITES','Show favorites');
define('_PNFORUM_SMILES','Smilies:');
define('_PNFORUM_SPLIT','Split');
define('_PNFORUM_SPLITTOPIC_INFO','This will split the topic before the selected posting.');
define('_PNFORUM_SPLITTOPIC_NEWTOPIC','Subject for the new topic');
define('_PNFORUM_SPLITTOPIC','Split topic');
define('_PNFORUM_STATSBLOCK','Total messages:');
define('_PNFORUM_STATUS', 'Status');
define('_PNFORUM_STICKY', 'Sticky');
define('_PNFORUM_STICKYTOPIC_INFO', 'When you press the sticky button at the bottom of this form the topic you have selected will be made <strong>\'sticky\'</strong>. You may unstick it again at a later time if you like.');
define('_PNFORUM_STICKYTOPIC','Mark this topic sticky');
define('_PNFORUM_SUBJECT_MAX','(100 symbols max)');
define('_PNFORUM_SUBJECT','Subject');
define('_PNFORUM_SUBMIT','Submit');
define('_PNFORUM_SUBSCRIBE_FORUM', 'subscribe forum');
define('_PNFORUM_SUBSCRIBE_STATUS','Subscription status');
define('_PNFORUM_SUBSCRIBE_TOPIC','subscribe topic');
define('_PNFORUM_SYNC_FORUMINDEX', 'Forum index synced');
define('_PNFORUM_SYNC_POSTSCOUNT', 'Posts counter synced');
define('_PNFORUM_SYNC_TOPICS', 'Topics synced');
define('_PNFORUM_SYNC_USERS', 'PostNuke and pnForum users synchronized');

//
// T
//
define('_PNFORUM_TODAY','today');
define('_PNFORUM_TOP','Top');
define('_PNFORUM_TOPIC_NOEXIST','Error - The topic you selected does not exist. Please go back and try again.');
define('_PNFORUM_TOPIC_STARTER','started by');
define('_PNFORUM_TOPIC','Topic');
define('_PNFORUM_TOPICLOCKED','Topic locked');
define('_PNFORUM_TOPICS','Topics');
define('_PNFORUM_TOTAL','Total');

//
// U
//
define('_PNFORUM_UALASTWEEK', 'last week, unanswered');
define('_PNFORUM_UNLOCKTOPIC_INFO', 'When you press the unlock button at the bottom of this form the topic you have selected will be <strong>unlocked</strong>. You may lock it again at a later time if you like.');
define('_PNFORUM_UNLOCKTOPIC','Unlock this topic');
define('_PNFORUM_UNREGISTERED','Unregistered User');
define('_PNFORUM_UNSTICKYTOPIC_INFO', 'When you press the unstick button at the bottom of this form the topic you have selected will be <strong>\'unsticky\'</strong>. You may mark it sticky it again at a later time if you like.');
define('_PNFORUM_UNSTICKYTOPIC','Unsticky topic');
define('_PNFORUM_UNSUBSCRIBE_FORUM','unsubscribe forum');
define('_PNFORUM_UNSUBSCRIBE_TOPIC','unsubscribe topic');
define('_PNFORUM_UP','Up');
define('_PNFORUM_UPDATE','Update');
define('_PNFORUM_USEBBCODE','Click on the buttons to add <a href="modules.php?op=modload&amp;name=Messages&amp;file=bbcode_ref">BBCode</a> to your message:');
define('_PNFORUM_USER_IP', 'User IP');
define('_PNFORUM_USERNAME','Username');
define('_PNFORUM_USERS_RANKS','Users ranks');

//
// V
//
define('_PNFORUM_VIEW_IP', 'View IP');
define('_PNFORUM_VIEWIP', 'view ip address');
define('_PNFORUM_VIEWS','Views');
define('_PNFORUM_VISITCATEGORY', 'visit this category');
define('_PNFORUM_VISITFORUM', 'visit this forum');
define('_PNFORUM_YESTERDAY','yesterday');

?>
