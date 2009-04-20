<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */


// removed
// define('_DZK_TOGGLEALL', 'Remove all topic subscriptions');

// new for contactlist integration
define('_DZK_PREFS_HASTOBEINSTALLED', 'has to be installed');
define('_DZK_PREFS_IGNORELISTHANDLINGNOTAVAILABLE', 'Handling of ignored users is not available, ');
define('_DZK_PREFS_IGNORELISTHANDLING', 'Handling of ignored users');
define('_DZK_PREFS_STRICT', 'strict');
define('_DZK_PREFS_MEDIUM', 'medium');
define('_DZK_PREFS_NONE', 'none');
define('_DZK_PREFS_IGNORELISTLEVELS', 'Users that are ignored by a topic poster can not reply to this topic in level "strict". In medium, they can reply, but postings will generally not be shown to users who ignore the poster. Also email notifications will not be sent. With just a click in the posting, not shown postings will be shown.');
define('_DZK_IGNORELISTHANDLING', 'Ignorelist configuration');
define('_DZK_MANAGEIGNORELIST', 'Manage my settings for my ignorelist');
define('_DZK_PREFS_NOCONFIGPOSSIBLE', 'No ignorelist configuration possible');
define('_DZK_PREFS_IGNORELISTMYHANDLING', 'My individual handling of ignored users');
define('_DZK_IGNORELISTSETTINGSUPDATED', 'Ignorelist configuration updated');
define('_DZK_IGNORELISTNOREPLY', 'Sorry - the user who started this topic is ignoring you and does not want that you are able to write replies into this topic. Please contact the topic starter for more details.');
define('_DZK_SHOWIGNOREDPOSTINGOF', 'Show hidden posting of ignored user');
define('_DZK_CLICKHERE', 'Click here');

// changed
define('_DZK_ADMINUSERRANK_INFO','To add a new rank simply enter the values in the fields and select ADD.<br />To modify a ranking simply change the values in the text boxes in the table that follows and click the SUBMIT button.<br />To remove a ranking simply check the corresponding checkbox and click the SUBMIT button.');
define('_DZK_PREFS_SENDEMAILSWITHSQLERRORS', 'Send emails, if SQL-errors occur');
define('_DZK_FORUM_SEQUENCE_DESCRIPTION','You can use drag & drop to manipulate the forum tree as you like. When done, click on save to store your changes.');
define('_DZK_NEW_THREADS','New Topic in forum');
define('_DZK_ADD_FAVORITE_FORUM','Add to favorites');

// new
define('_DZK_SEARCHLENGTHHINT', 'The forum accepts searchstrings with a length between %minlen% and %maxlen% chars only!');
define('_DZK_PREFS_MINSEARCHLENGTH', 'Minimum length of search string (>=1 char)');
define('_DZK_PREFS_MAXSEARCHLENGTH', 'Maximum length of search string (<=50 chars)');
define('_DZK_PREFS_SHOWTEXTINSEARCHRESULTS', 'Show text in search results<br /><em>Turn this off in high volume sites to improve search performance or take care about constant cleaning of the search results table.</em>');
define('_DZK_ADMINADDNEWRANK', 'Add a new rank');
define('_DZK_ADMINDELETERANK', 'Delete this rank');
define('_DZK_ADMINGENERALOPTIONS', 'General options');
define('_DZK_ADMINUSERRELATEDOPTIONS', 'User related options');
define('_DZK_ADMINSECURITYOPTIONS', 'Security options');
define('_DZK_ADMINFEATURESOPTIONS', 'Features');
define('_DZK_DISABLED_INFO', 'The forum is currently disabled for maintenance, please come back later.');
define('_DZK_CONFIGRESTORED', 'The configuration has been reset to the default values');
define('_DZK_CONFIGCHANGED', 'The configuration has been changed');
define('_DZK_SIGNATUREUPDATED', 'Signature updated');
define('_DZK_TO30_HINT', 'This step will upgrade pnForum 2.7.1 to Dizkus 3.0 including all necessary database changes.');
define('_DZK_POSTSAPPEARANCE', 'Posting appearance');
define('_DZK_MANAGESIGNATURE', 'Manage my signature');
define('_DZK_PREFS_ENABLESIGNATUREMANAGEMENT',	'Enable signature management via Dizkus-User-Settings');
define('_DZK_SEARCHWHERE', 'Search in');
define('_DZK_SEARCH_POSTINGS', 'postings');
define('_DZK_SEARCH_AUTHOR', 'authors');

define('_DZK_PREFS_ENABLEDISABLE', 'Dizkus is available<br /><em>(turning it off allows access for Admins only)</em>');
define('_DZK_PREFS_DISABLEDTEXT', 'Enter info text for users if forum is disabled');
define('_DZK_SOURCEEQUALSTARGETFORUM', 'Error: Source forum must differ from target forum.');
define('_DZK_SOURCEEQUALSTARGETTOPIC', 'Error: Source topic must differ from target topic.');
define('_DZK_FOUNDIN', 'found in');
define('_DZK_FORUM_SETTINGS','Personal settings per forum');
define('_DZK_HOURSSHORT', 'hrs');
define('_DZK_PREFS_TIMESPANFORCHANGES', 'Allow changes in postings within x hours');
define('_DZK_LATESTRSS', 'RSS');
define('_DZK_VIEWYOURPOSTS', 'View your posts');
define('_DZK_PNCATEGORIES', 'Select Category');
define('_DZK_LINKTOTHISPOST', 'Link to this posting');
define('_DZK_SIMILARTOPICS', 'similar topics');
define('_DZK_RANK', 'Rank');
define('_DZK_CLICKTOEDIT', 'Click to edit');
define('_DZK_EDITSHORT', 'Edit');
define('_DZK_GOTOSTART', 'go to the forums startpage');
define('_DZK_YOUAREHERE', 'You are here');
define('_DZK_CURRENTSORTORDER', 'recent sort order');
define('_DZK_ORDER_ASC', 'oldest submissions on top');
define('_DZK_ORDER_DESC','newest submissions on top');
define('_DZK_CANCEL','cancel');
define('_DZK_MOVEPOSTSHORT', 'Move');
define('_DZK_SPLITSHORT', 'Split');
define('_DZK_LOGIN', 'Login');
define('_DZK_SEARCHSHORT', 'Search');
define('_DZK_SEARCHINCLUDE_BYSCORE', 'by score');
define('_DZK_MAILTO_NOSUBJECT','You must enter a subject for this email.');
define('_DZK_NOFORUMSUBSCRIPTIONSFOUND','no forum subscriptions found');
define('_DZK_TOGGLEALLFORUMS', 'Remove all forum subscriptions');
define('_DZK_TOGGLEALLTOPICS', 'Remove all topic subscriptions');
define('_DZK_THISFUNCTIONNEEDSJAVASCRIPT', 'The Dizkus-administration needs javascript enabled!');
define('_DZK_MANAGEFORUMSUBSCRIPTIONS', 'Manage forum subscriptions');
define('_DZK_SHOWSUBSCRIPTIONS', 'Show users subscriptions');
define('_DZK_ADMINMANAGESUBSCRIPTIONS', 'Manage subscriptions');
define('_DZK_ADMINMANAGESUBSCRIPTIONS_INFO', 'Remove the users topic and forum subscriptions');
define('_DZK_REDIRECTINGTONEWTOPIC', '...now redirecting to the new topic...');
define('_DZK_PREFS_SHOWNEWTOPICCONFIRMATION', 'Show confirmation when a new topic has been created');
define('_DZK_THANKSFORNEWTOPIC', 'Thanks for your submission');
define('_DZK_CLICKHERETOGOTONEWTOPICORWAITFORREDIRECT', 'Click here to go the new topic or wait a few seconds to be redirected');
define('_DZK_CLICKHERETOGOTONEWTOPIC', 'Click here to go the new topic.');
define('_DZK_CLICKHERETOGOTOFORUM', 'Click here to go back to the forum');
define('_DZK_EMPTYCATEGORY', 'This category does not contain any forums yet');
define('_DZK_HIDEFORUMS', 'Hide forums');
define('_DZK_SHOWFORUMS', 'Show forums');
define('_DZK_HIDECATEGORY', 'Hide category');
define('_DZK_SHOWCATEGORY', 'Show category');
define('_DZK_HIDEFORUM', 'Hide forum');
define('_DZK_SHOWFORUM', 'Show forum');
define('_DZK_LOADCATEGORYDATA', 'Load category data');
define('_DZK_LOADFORUMDATA', 'Load forum data');
define('_DZK_ADMINREORDERTREE', 'Manipulate forum tree');
define('_DZK_ADMINREORDERTREE_INFO', 'Here you can reorder the categories and forums');
define('_DZK_REORDERFORUMTREE', 'Re-order forum tree');

define('_DZK_STORINGNEWSORTORDER', '... storing new sort order ...');
define('_DZK_TOGGLEUSERINFO', 'toggle user details');
define('_DZK_HIDEUSERINFO', 'hide user details');
define('_DZK_FAVORITESDISABLED', 'favorites disabled');
define('_DZK_STATUS_NOTCHANGED', 'not changed');
define('_DZK_STATUS_CHANGED', 'changed');
define('_DZK_STORINGPOST', '... storing post ...');
define('_DZK_UPDATINGPOST', '... updating post ...');
define('_DZK_DELETINGPOST', '... deleting post ...');
define('_DZK_PREPARINGPREVIEW', '... preparing preview ...');
define('_DZK_STORINGREPLY', '... storing reply ...');

define('_DZK_CATEGORYOVERVIEW', 'Category overview');
define('_DZK_FORUMSOVERVIEW', 'Forums overview');

// alphasorting starts here

//
// A
//
define('_DZK_ACCOUNT_INFORMATION', 'Users IP and Account information');
define('_DZK_ACTIONS','Actions');
define('_DZK_ACTIVE_FORUMS','top active Forums:');
define('_DZK_ACTIVE_POSTERS','top active Posters:');
define('_DZK_ADD','Add');
define('_DZK_ADDNEWCATEGORY', '-- add new category --');
define('_DZK_ADDNEWFORUM', '-- add new forum --');
define('_DZK_ADMINADVANCEDCONFIG', 'Advanced configuration');
define('_DZK_ADMINADVANCEDCONFIG_HINT', 'Caution: wrong settings here can lead to unwanted side effects. If you do not understand what is going on here, stay away and leave the settings as they are!');
define('_DZK_ADMINADVANCEDCONFIG_INFO', 'Set advanced configuration, caution!');
define('_DZK_ADMINBADWORDS_TITLE','Bad words filtering administration');
define('_DZK_ADMINCATADD','Add a Category');
define('_DZK_ADMINCATADD_INFO','This link will allow you to add a new category to put forums into');
define('_DZK_ADMINCATDELETE','Remove a Catetegory');
define('_DZK_ADMINCATDELETE_INFO','This link allows you to remove any category from the database');
define('_DZK_ADMINCATEDIT','Edit a Category Title');
define('_DZK_ADMINCATEDIT_INFO','This link will allow you edit the title of a category');
define('_DZK_ADMINCATORDER','Re-Order Categories');
define('_DZK_ADMINCATORDER_INFO','This link will allow you to change the order in which your categories display on the index page');
define('_DZK_ADMINFORUMADD','Add a Forum');
define('_DZK_ADMINFORUMADD_INFO','This Link will take you to a page where you can add a forum to the database.');
define('_DZK_ADMINFORUMEDIT','Edit a Forum');
define('_DZK_ADMINFORUMEDIT_INFO','This link will allow you to edit an existing forum.');
define('_DZK_ADMINFORUMOPTIONS','Configuration');
define('_DZK_ADMINFORUMOPTIONS_INFO','This link will allow you to set various forum-wide options.');
define('_DZK_ADMINFORUMORDER','Re-Order Forums');
define('_DZK_ADMINFORUMORDER_INFO','This allows you to change the order in which your forums display on the index page');
define('_DZK_ADMINFORUMSPANEL','Dizkus Administration');
define('_DZK_ADMINFORUMSYNC','Sync forum/topic index');
define('_DZK_ADMINFORUMSYNC_INFO','This link will allow you to sync up the forum and topic indexes to fix any discrepancies that might exist');
define('_DZK_ADMINHONORARYASSIGN','Assign honorary rank');
define('_DZK_ADMINHONORARYASSIGN_INFO','This link will allow you to assign honorary user rankings to users');
define('_DZK_ADMINHONORARYRANKS','Edit honorary ranks');
define('_DZK_ADMINHONORARYRANKS_INFO','This link will allow you to create special ranks for special users.');
define('_DZK_ADMINRANKS','Edit user ranks');
define('_DZK_ADMINRANKS_INFO','This link will allow you to add/edit/delete different user rankings depending of the number of user posts.');
define('_DZK_ADMINUSERRANK_IMAGE','Image');
define('_DZK_ADMINUSERRANK_INFO2','Use this form to add a ranking to the database.');
define('_DZK_ADMINUSERRANK_MAX','Max posts');
define('_DZK_ADMINUSERRANK_MIN','Min posts');
define('_DZK_ADMINUSERRANK_TITLE','Users Ranks Administration');
define('_DZK_ADMINUSERRANK_TITLE2','User rank');
define('_DZK_ADMIN_SYNC','Sync');
define('_DZK_ALLPNTOPIC', 'all topics');
define('_DZK_AND', 'and');
define('_DZK_ASSIGN','Assign');
define('_DZK_ATTACHSIGNATURE', 'Attach my signature');
define('_DZK_ATTACHMENTS', 'Attachments');
define('_DZK_ATTACHMENTSTITLE', 'File attachments');
define('_DZK_AUTHOR','Author');
define('_DZK_AUTOMATICDISCUSSIONMESSAGE', 'Automatically created topic for discussion of submitted entries');
define('_DZK_AUTOMATICDISCUSSIONSUBJECT', 'Automatically created topic');

//
// B
//
define('_DZK_BACKTOFORUMADMIN', 'Back to forum admin');
define('_DZK_BACKTOSUBMISSION', 'Go to this submission');
define('_DZK_BASEDONLASTXMINUTES', 'This list is based on the users active over the last %m% minutes.');
define('_DZK_BLOCK_PARAMETERS', 'Parameters');
define('_DZK_BLOCK_PARAMETERS_HINT', 'comma separated list, e.g.. maxposts=5,forum_id=27 ');
define('_DZK_BLOCK_TEMPLATENAME', 'Name of templatefile');
define('_DZK_BODY','Message Body');
define('_DZK_BOTTOM','Bottom');

//
// C
//
define('_DZK_CANCELPOST','Cancel');
define('_DZK_CATEGORIES','Categories');
define('_DZK_CATEGORY','Category');
define('_DZK_CATEGORYINFO', 'Category info');
define('_DZK_CB_RECENTPOSTS','Recent postings:');
define('_DZK_CHANGE_FORUM_ORDER','Change Forum Order');
define('_DZK_CHANGE_POST_ORDER','Change Post Order');
define('_DZK_CHOOSECATWITHFORUMS4REORDER','Select category containing forums you want to reorder');
define('_DZK_CHOOSEFORUMEDIT','Select forum to edit');
define('_DZK_CREATEFORUM_INCOMPLETE','You did not fill in all required parts of the form.<br /> Did you assign at least one moderator? Please go back and correct the form');
define('_DZK_CREATESHADOWTOPIC','Create shadow topic');
define('_DZK_CURRENT', 'current');

//
// D
//
define('_DZK_DATABASEINUSE', 'Database in use');
define('_DZK_DATE','Date');
define('_DZK_DELETE','Delete this Post');
define('_DZK_DELETETOPIC','Delete this topic');
define('_DZK_DELETETOPICS','Delete selected topics');
define('_DZK_DELETETOPIC_INFO', 'When you press the delete button at the bottom of this form the topic you have selected, and all its related posts, will be <strong>permanently</strong> removed.');
define('_DZK_DESCRIPTION', 'Description');
define('_DZK_DISCUSSINFORUM', 'Discuss this submission in the forums');
define('_DZK_DOWN','Down');

//
// E
//
define('_DZK_EDITBY','edited by:');
define('_DZK_EDITDELETE', 'edit/delete');
define('_DZK_EDITFORUMS','Edit forums');
define('_DZK_EDITPREFS','Edit Your Preferences');
define('_DZK_EDIT_POST','Edit post');
define('_DZK_EMAILTOPICMSG','Hi! Check this link, I think it should be interesting to you');
define('_DZK_EMAIL_TOPIC', 'Send as email');
define('_DZK_EMPTYMSG','You must type a message to post. You cannot post an empty message. Please go back and try again.');
define('_DZK_ERRORLOGGINGIN', 'Unable to log you in, wrong username or wrong password used?');
define('_DZK_ERRORMAILTO', 'Send bug report');
define('_DZK_ERROROCCURED', 'The following error occured:');
define('_DZK_ERROR_CONNECT','Error connecting to the database!<br />');
define('_DZK_EXTENDEDOPTIONSAFTERSAVING', 'Extended options available after saving');
define('_DZK_EXTERNALSOURCE', 'External source');
define('_DZK_EXTERNALSOURCEURL_HINT', 'In case of an RSS feed enter the id of the feed as configured in the RSS module');

//
// F
//
define('_DZK_FAILEDTOCREATEHOOK', 'Failed to create hook');
define('_DZK_FAILEDTODELETEHOOK', 'Failed to delete hook');
define('_DZK_FAVORITES','Favorites');
define('_DZK_FAVORITE_STATUS','Favorite status');
define('_DZK_FORUM','Forum');
define('_DZK_FORUMID', 'Forums ID');
define('_DZK_FORUMINFO', 'Forum Info');
define('_DZK_FORUMS','Forums');
define('_DZK_FORUMSINDEX','Forum-Index');
define('_DZK_FORUM_EDIT_FORUM','Edit Forum');
define('_DZK_FORUM_EDIT_ORDER','Edit Order');
define('_DZK_FORUM_NOEXIST','Error - The forum/topic you selected does not exist. Please go back and try again.');
define('_DZK_FORUM_REORDER','Re-Order');

//
// G
//
define('_DZK_GOTOPAGE','Goto page');
define('_DZK_GOTO_CAT','go to category');
define('_DZK_GOTO_FORUM','go to forum');
define('_DZK_GOTO_LATEST', 'View latest post');
define('_DZK_GOTO_TOPIC','go to topic');
define('_DZK_GROUP', 'Group');

//
// H
//
define('_DZK_HOMEPAGE','Homepage');
define('_DZK_HONORARY_RANK','Honorary rank');
define('_DZK_HONORARY_RANKS','Honorary ranks');
define('_DZK_HOST', 'Host');
define('_DZK_HOTNEWTOPIC', 'hot topic with new postings');
define('_DZK_HOTTHRES','More than %d posts');
define('_DZK_HOTTOPIC', 'hot topic');
define('_DZK_HOURS','hours');

//
// I
//
define('_DZK_ILLEGALMESSAGESIZE', 'Illegal message size, max. 65535 chars');
define('_DZK_IMAGE', 'Image');
define('_DZK_IP_USERNAMES', 'Usernames of users that posted from this IP + post counts');
define('_DZK_ISLOCKED','Topic is Locked. No new posts may be made in it');

//
// J
//
define('_DZK_JOINTOPICS', 'Join topics');
define('_DZK_JOINTOPICS_INFO', 'Joins two topics together');
define('_DZK_JOINTOPICS_TOTOPIC', 'Target topic');

//
// L
//
define('_DZK_LAST','last');
define('_DZK_LAST24','last 24 hours');
define('_DZK_LASTCHANGE','last change on');
define('_DZK_LASTPOST','Last Post');
define('_DZK_LASTPOSTINGBY', 'last posting by');
define('_DZK_LASTPOSTSTRING','%s<br />by %s');
define('_DZK_LASTVISIT', 'last visit');
define('_DZK_LASTWEEK','last week');
define('_DZK_LAST_SEEN', 'last visit');
define('_DZK_LATEST','Latest posts');
define('_DZK_LEGEND','Legend');
define('_DZK_LOCKTOPIC','Lock this topic');
define('_DZK_LOCKTOPICS','Lock selected topics');
define('_DZK_LOCKTOPIC_INFO', 'When you press the lock button at the bottom of this form the topic you have selected will be <strong>locked</strong>. You may unlock it at a later time if you like.');

//
// M
//
define('_DZK_MAIL2FORUM', 'Mail2Forum');
define('_DZK_MAIL2FORUMPOSTS', 'Mailinglists');
define('_DZK_MAILTO_NOBODY','You must enter a message.');
define('_DZK_MAILTO_WRONGEMAIL','You did not enter an email address for the person to send the email to, or you entered an invalid email address.');
define('_DZK_MANAGETOPICSUBSCRIPTIONS', 'Manage topic subscriptions');
define('_DZK_MANAGETOPICSUBSCRIPTIONS_HINT', 'Here you can manage your topic subscriptions.');
define('_DZK_MINSHORT', 'min');
define('_DZK_MODERATE','Moderate');
define('_DZK_MODERATEDBY','Moderated by');
define('_DZK_MODERATE_JOINTOPICS_HINT', 'If you want to join topics, select the target topic here');
define('_DZK_MODERATE_MOVETOPICS_HINT','Choose target forum for moving topics:');
define('_DZK_MODERATION_NOTICE', 'Moderation request');
define('_DZK_MODERATOR','Moderator');
define('_DZK_MODERATORSOPTIONS', 'Moderators options');
define('_DZK_MODULEREFERENCE', 'Modulereference');
define('_DZK_MODULEREFERENCE_HINT', 'Used for comment feature, all topics to submission in this module go into this forum. This list only contains the modules where the Dizkus hooks are activated for.');
define('_DZK_MORETHAN','More than');
define('_DZK_MOVED_SUBJECT', 'moved');
define('_DZK_MOVEPOST', 'Move post');
define('_DZK_MOVEPOST_INFO', 'Move a post from one topic to another');
define('_DZK_MOVEPOST_TOTOPIC', 'Target topic');
define('_DZK_MOVETOPIC','Move this topic');
define('_DZK_MOVETOPICS','Move selected topics');
define('_DZK_MOVETOPICTO','Move topic to:');
define('_DZK_MOVETOPIC_INFO', 'When you press the move button at the bottom of this form the topic you have selected, and its related posts, will be <strong>moved</strong> to the forum you have selected. Note: You will only be able to move to a forum where you are moderator. Administrator is allowed to move any topic to any forum.');

//
// N
//
define('_DZK_NEWEST_FIRST','Display the newest post first');
define('_DZK_NEWPOSTS','New posts since your last visit.');
define('_DZK_NEWTOPIC','New topic');
define('_DZK_NEXTPAGE','Next Page');
define('_DZK_NEXT_TOPIC','to next topic');
define('_DZK_NOAUTH', 'No permission for this action');
define('_DZK_NOAUTHPOST','Note: not authorised to post comments');
define('_DZK_NOAUTH_MODERATE','You are not the moderator of this forum therefore you cannot perform this function.');
define('_DZK_NOAUTH_TOADMIN', 'You have no permission to admin this module');
define('_DZK_NOAUTH_TOMODERATE', 'You have no permission to moderate this category or forum');
define('_DZK_NOAUTH_TOREAD', 'You have no permission to read the content of this category or forum');
define('_DZK_NOAUTH_TOSEE', 'You have no permission to see this category or forum');
define('_DZK_NOAUTH_TOWRITE', 'You have no permission to write into this category or forum');
define('_DZK_NOCATEGORIES', 'no categories defined');
define('_DZK_NOEXTERNALSOURCE', 'no external source');
define('_DZK_NOFAVORITES','No favorites defined');
define('_DZK_NOFORUMS', 'no forums defined');
define('_DZK_NOHOOKEDMODULES', 'no hooked module found');
define('_DZK_NOHTMLALLOWED', 'No HTML-tags allowed (only inside of [code][/code])');
define('_DZK_NOJOINTO', 'No target topic for joining selected');
define('_DZK_NOMODERATORSASSIGNED', 'no moderator assigned');
define('_DZK_NOMOVETO', 'No target forum for moving selected');
define('_DZK_NONE', 'none');
define('_DZK_NONEWPOSTS','No new posts since your last visit.');
define('_DZK_NOPNTOPIC', 'no topic');
define('_DZK_NOPOSTLOCK','You cannot post a reply to this topic, it has been locked.');
define('_DZK_NOPOSTS','No Posts');
define('_DZK_NORANK', 'no rank');
define('_DZK_NORANKSINDATABASE', 'no ranks defined');
define('_DZK_NORMALNEWTOPIC', 'normal topic with new postings');
define('_DZK_NORMALTOPIC', 'normal topic');
define('_DZK_NOSMILES','There are no smilies in database');
define('_DZK_NOSPECIALRANKSINDATABASE', 'No Special Ranks in the Database. You can add one by entering into the form below.');
define('_DZK_NOSUBJECT', 'no subject');
define('_DZK_NOTEDIT','You cannot edit a post that is not yours.');
define('_DZK_NOTIFYBODY1','Forums');
define('_DZK_NOTIFYBODY2','wrote at');
define('_DZK_NOTIFYBODY3','Reply to this message:');
define('_DZK_NOTIFYBODY4','Browse thread:');
define('_DZK_NOTIFYBODY5','You are receiving this Email because you are subscribed to be notified of events in forums at:');
define('_DZK_NOTIFYBODY6', 'Link for maintaining topic and forum subscriptions:');
define('_DZK_NOTIFYME', 'Notify me when a reply is posted');
define('_DZK_NOTIFYMODBODY1', 'Request for moderation');
define('_DZK_NOTIFYMODBODY2', 'Comment');
define('_DZK_NOTIFYMODBODY3', 'Link to topic');
define('_DZK_NOTIFYMODERATOR', 'Notify mod');
define('_DZK_NOTIFYMODERATOR_INFO', 'A moderator will be notified about the selected posting.<br />Important reasons are<br /><dl><dd>copryright violations</dd><dd>personal insults</dd><dd>etc.</dd></dl>but not<dl><dd>typos</dd><dd>different opinion about the topic</dd><dd>etc.</dd></dl><br /><br />Comment:');
define('_DZK_NOTIFYMODERATOR_TITLE', 'Notify a moderator about a posting');
define('_DZK_NOTOPICS','There are no topics for this forum.');
define('_DZK_NOTOPICSUBSCRIPTIONSFOUND', 'no topic subscriptions found');
define('_DZK_NOTSUBSCRIBED','You are not subscribed to this forum');
define('_DZK_NOUSER_OR_POST','Error - No such user or post in the database.');
define('_DZK_NO_FORUMS_DB', 'No Forums in DB');
define('_DZK_NO_FORUMS_MOVE', 'No more Forums moderated by you to move to');

//
// O
//
define('_DZK_OFFLINE', 'offline');
define('_DZK_OKTODELETE','delete?');
define('_DZK_OLDEST_FIRST','Display the oldest post first');
define('_DZK_ONEREPLY','reply');
define('_DZK_ONLINE', 'online');
define('_DZK_OPTIONS','Options');
define('_DZK_OR', 'or');
define('_DZK_OURLATESTPOSTS','Latest Forum-Posts');

//
// P
//
define('_DZK_PAGE','Page #');
define('_DZK_PASSWORD', 'Password');
define('_DZK_PASSWORDNOMATCH', 'Passwords do not match, please go back and correct');
define('_DZK_PERMDENY','Access denied!');
define('_DZK_PERSONAL_SETTINGS','Personal settings');
define('_DZK_PNPASSWORD', 'Zikula password');
define('_DZK_PNPASSWORDCONFIRM', 'Zikula password confirmation');
define('_DZK_PNTOPIC', 'Zikula Topic');
define('_DZK_PNTOPIC_HINT', '');
define('_DZK_PNUSER', 'Zikula username');
define('_DZK_POP3ACTIVE', 'Mail2Forum active');
define('_DZK_POP3INTERVAL', 'Poll interval');
define('_DZK_POP3LOGIN', 'Pop3 login');
define('_DZK_POP3MATCHSTRING', 'Rule');
define('_DZK_POP3MATCHSTRINGHINT', 'The rule is a regular expression that the mails subject as to match to avoid spa posigns. An empty rule means no checks!');
define('_DZK_POP3PASSWORD', 'Pop3 password');
define('_DZK_POP3PASSWORDCONFIRM', 'Pop3 password confirmation');
define('_DZK_POP3PORT', 'Pop3 port');
define('_DZK_POP3SERVER', 'Pop3 Server');
define('_DZK_POP3TEST', 'Perform Pop3 test after saving');
define('_DZK_POP3TESTRESULTS', 'Pop3 test results');
define('_DZK_POST','post');
define('_DZK_POSTED','Posted');
define('_DZK_POSTER','Poster');
define('_DZK_POSTS','Posts');
define('_DZK_POST_GOTO_NEWEST','go to the newest post in ');
define('_DZK_POWEREDBY', 'Powered by <a href="http://www.dizkus.com/" title="Dizkus">Dizkus</a> Version');
define('_DZK_PREFS_ASCENDING', 'Ascending');
define('_DZK_PREFS_AUTOSUBSCRIBE', 'Autosubscribe to new topics or posts');
define('_DZK_PREFS_CHARSET', 'Default charset<br /><em>(This is the charset that will be used in e-mail headers)</em>');
define('_DZK_PREFS_DELETEHOOKACTION', 'Action to be performed when deletehook is called');
define('_DZK_PREFS_DELETEHOOKACTIONLOCK', 'close topic');
define('_DZK_PREFS_DELETEHOOKACTIONREMOVE', 'delete topic');
define('_DZK_PREFS_DESCENDING', 'Descending');
define('_DZK_PREFS_EMAIL', 'Email From Address<br /><em>(This is the address that will appear on every email sent by the forums)</em>');
define('_DZK_PREFS_FAVORITESENABLED', 'Favorites enabled');
define('_DZK_PREFS_FIRSTNEWPOSTICON', 'First new post Icon:');
define('_DZK_PREFS_HIDEUSERSINFORUMADMIN', 'Hide users in forum admin');
define('_DZK_PREFS_HOTNEWPOSTSICON', 'Topic with both many and new posts image:');
define('_DZK_PREFS_HOTTOPIC', 'Hot Topic Threshold');
define('_DZK_PREFS_HOTTOPICICON', 'Hot topic image<br /><em>(Topic with many posts)</em>');
define('_DZK_PREFS_ICONS','<br /><strong>Icons</strong>');
define('_DZK_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX', 'Use extended fulltext search in internal search');
define('_DZK_PREFS_INTERNALSEARCHWITHEXTENDEDFULLTEXTINDEX_HINT', '<i>The extended fulltext search enables parameters like "+dizkus -skype" for postings that contain "dizkus" but not "skype". Minimum requirement is MySQL 4.01.</i><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-boolean.html" title="Extended fulltest search in MySQL">Extended fulltest search in MySQL</a>.');
define('_DZK_PREFS_LOGIP', 'Log IP addresses');
define('_DZK_PREFS_M2FENABLED', 'Mail2Forum enabled');
define('_DZK_PREFS_NO', 'No');
define('_DZK_PREFS_POSTSORTORDER', 'Posts sorting order');
define('_DZK_PREFS_POSTSPERPAGE', 'Posts per Page<br /><em>(This is the number of posts per topic that will be displayed per page of a topic. 15 by default.)</em>');
define('_DZK_PREFS_RANKLOCATION', 'Ranks icons location');
define('_DZK_PREFS_REMOVESIGNATUREFROMPOST', 'Remove users signature from posting');
define('_DZK_PREFS_RESTOREDEFAULTS', 'Restore defaults');
define('_DZK_PREFS_RSS2FENABLED', 'RSS2Forum enabled');
define('_DZK_PREFS_SAVE', 'Save');
define('_DZK_PREFS_SEARCHWITHFULLTEXTINDEX', 'Search forums with fulltext index');
define('_DZK_PREFS_SEARCHWITHFULLTEXTINDEX_HINT', '<em>Searching the forums with fulltext index fields needs min. MySQL 4 or later and does not work with InnoDB databases. This flag will normally be set during installation when the index fields have been created. The search result might by empty if the query string is present in too many postings. This is a "feature" of MySQL.</em><br /><a href="http://dev.mysql.com/doc/mysql/en/fulltext-search.html" title="Fulltext search in MySQL">Fulltext search in MySQL</a>.');
define('_DZK_PREFS_SIGNATUREEND', 'End of signature format');
define('_DZK_PREFS_SIGNATURESTART', 'Start of signature format');
define('_DZK_PREFS_SLIMFORUM', 'Hide category view with one category only');
define('_DZK_PREFS_STRIPTAGSFROMPOST', 'Strip all html tags from new posts (does not alter content of [code][/code]');
define('_DZK_PREFS_TOPICICON', 'Topic image:');
define('_DZK_PREFS_TOPICSPERPAGE', 'Topics per Forum<br /><em>(This is the number of topics per forum that will be displayed per page of a forum. 15 by default.)</em>');
define('_DZK_PREFS_YES', 'Yes');
define('_DZK_PREVIEW','Preview');
define('_DZK_PREVIOUS_TOPIC','to previous topic');
define('_DZK_PREVPAGE','Previous Page');
define('_DZK_PRINT_POST','Print post');
define('_DZK_PRINT_TOPIC','Print topic');
define('_DZK_PROFILE', 'Profile');

//
// Q
//
define('_DZK_QUICKREPLY', 'Quick reply');
define('_DZK_QUICKSELECTFORUM','- select -');

//
// R
//
define('_DZK_RECENT_POSTS','recent Topics:');
define('_DZK_RECENT_POST_ORDER', 'Recent post order in topic view');
define('_DZK_REGISTER','Register');
define('_DZK_REGISTRATION_NOTE','Note: Registered users can subscribe to notifications about new posts');
define('_DZK_REG_SINCE', 'Registered');
define('_DZK_REMEMBERME', 'Remember me');
define('_DZK_REMOVE', 'remove');
define('_DZK_REMOVE_FAVORITE_FORUM','Remove favorite forum');
define('_DZK_REORDER','Reorder');
define('_DZK_REORDERCATEGORIES','Reorder categories');
define('_DZK_REORDERFORUMS','Reorder forums');
define('_DZK_REPLACE_WORDS','Replace words');
define('_DZK_REPLIES','Replies');
define('_DZK_REPLY', 'reply');
define('_DZK_REPLYLOCKED', 'closed');
define('_DZK_REPLYQUOTE', 'Quote');
define('_DZK_REPLY_POST','Reply to');
define('_DZK_REPORTINGUSERNAME', 'Reporting user');
define('_DZK_RETURNTOTOPIC', 'Back to the topic');
define('_DZK_RSS2FORUM', 'RSS2Forum');
define('_DZK_RSS2FORUMPOSTS', 'RSS feeds');
define('_DZK_RSSMODULENOTAVAILABLE', '<span style="color: red;">Feeds module not available!</span>');
define('_DZK_RSS_SUMMARY', 'Summary');

//
// S
//
define('_DZK_SAVEPREFS','Save Preferences');
define('_DZK_SEARCH','search Dizkus');
define('_DZK_SEARCHALLFORUMS', 'All forums');
define('_DZK_SEARCHAND','all words [AND]');
define('_DZK_SEARCHBOOL', 'Connection');
define('_DZK_SEARCHFOR','Search for');
define('_DZK_SEARCHINCLUDE_ALLTOPICS', 'all');
define('_DZK_SEARCHINCLUDE_AUTHOR','Author');
define('_DZK_SEARCHINCLUDE_BYDATE','by date');
define('_DZK_SEARCHINCLUDE_BYFORUM','by forum');
define('_DZK_SEARCHINCLUDE_BYTITLE','by title');
define('_DZK_SEARCHINCLUDE_DATE','Date');
define('_DZK_SEARCHINCLUDE_FORUM','Category and forum');
define('_DZK_SEARCHINCLUDE_HITS', 'hits');
define('_DZK_SEARCHINCLUDE_LIMIT', 'limit search to');
define('_DZK_SEARCHINCLUDE_MISSINGPARAMETERS', 'missing search parameters');
define('_DZK_SEARCHINCLUDE_NEWWIN','Show in new window');
define('_DZK_SEARCHINCLUDE_NOENTRIES','No messages in forums found');
define('_DZK_SEARCHINCLUDE_NOLIMIT', 'no limits');
define('_DZK_SEARCHINCLUDE_ORDER','Order');
define('_DZK_SEARCHINCLUDE_REPLIES','Replies');
define('_DZK_SEARCHINCLUDE_RESULTS','Forums');
define('_DZK_SEARCHINCLUDE_TITLE','Search forums');
define('_DZK_SEARCHINCLUDE_VIEWS','Views');
define('_DZK_SEARCHOR','single words [OR]');
define('_DZK_SEARCHRESULTSFOR','Search results for');
define('_DZK_SELECTACTION', 'select action');
define('_DZK_SELECTED','Selection');
define('_DZK_SELECTEDITCAT','Select category');
define('_DZK_SELECTREFERENCEMODULE', 'select hooked module');
define('_DZK_SELECTRSSFEED', 'Select RSS feed');
define('_DZK_SELECTTARGETFORUM', 'select target forum');
define('_DZK_SELECTTARGETTOPIC', 'select target topic');
define('_DZK_SENDTO','Send to');
define('_DZK_SEND_PM', 'send PM');
define('_DZK_SEPARATOR','&nbsp;::&nbsp;');
define('_DZK_SETTING', 'Settings');
define('_DZK_SHADOWTOPIC_MESSAGE', 'The original posting has been moved <a title="moved" href="%s">here</a>.');
define('_DZK_SHOWALLFORUMS','Show all forums');
define('_DZK_SHOWFAVORITES','Show favorites');
define('_DZK_SMILES','Smilies:');
define('_DZK_SPLIT','Split');
define('_DZK_SPLITTOPIC','Split topic');
define('_DZK_SPLITTOPIC_INFO','This will split the topic before the selected posting.');
define('_DZK_SPLITTOPIC_NEWTOPIC','Subject for the new topic');
define('_DZK_START', 'Start');
define('_DZK_STATSBLOCK','Total messages:');
define('_DZK_STATUS', 'Status');
define('_DZK_STICKY', 'Sticky');
define('_DZK_STICKYTOPIC','Mark this topic sticky');
define('_DZK_STICKYTOPICS','Make selected topics sticky');
define('_DZK_STICKYTOPIC_INFO', 'When you press the sticky button at the bottom of this form the topic you have selected will be made <strong>\'sticky\'</strong>. You may unstick it again at a later time if you like.');
define('_DZK_SUBJECT','Subject');
define('_DZK_SUBJECT_MAX','(100 symbols max)');
define('_DZK_SUBMIT','Submit');
define('_DZK_SUBMIT_HINT','BEWARE: Dizkus will not ask you for any confirmation! Clicking on Submit will immediately start the selected action!');
define('_DZK_SUBSCRIBE_FORUM', 'Subscribe forum');
define('_DZK_SUBSCRIBE_STATUS','Subscription status');
define('_DZK_SUBSCRIBE_TOPIC','Subscribe topic');
define('_DZK_SYNC_FORUMINDEX', 'Forum index synced');
define('_DZK_SYNC_POSTSCOUNT', 'Posts counter synced');
define('_DZK_SYNC_TOPICS', 'Topics synced');
define('_DZK_SYNC_USERS', 'Zikula and Dizkus users synchronized');

//
// T
//
define('_DZK_TODAY','today');
define('_DZK_TOP','Top');
define('_DZK_TOPIC','Topic');
define('_DZK_TOPICLOCKED','Topic locked');
define('_DZK_TOPICS','Topics');
define('_DZK_TOPIC_NOEXIST','Error - The topic you selected does not exist. Please go back and try again.');
define('_DZK_TOPIC_STARTER','started by');
define('_DZK_TOTAL','Total');

//
// U
//
define('_DZK_UALASTWEEK', 'last week, unanswered');
define('_DZK_UNKNOWNIMAGE', 'unknown image');
define('_DZK_UNKNOWNUSER', '**unknown user**');
define('_DZK_UNLOCKTOPIC','Unlock this topic');
define('_DZK_UNLOCKTOPICS','Open selected topics');
define('_DZK_UNLOCKTOPIC_INFO', 'When you press the unlock button at the bottom of this form the topic you have selected will be <strong>unlocked</strong>. You may lock it again at a later time if you like.');
define('_DZK_UNREGISTERED','Unregistered User');
define('_DZK_UNSTICKYTOPIC','Unsticky topic');
define('_DZK_UNSTICKYTOPICS','Make selected topics unsticky');
define('_DZK_UNSTICKYTOPIC_INFO', 'When you press the unstick button at the bottom of this form the topic you have selected will be <strong>\'unsticky\'</strong>. You may mark it sticky it again at a later time if you like.');
define('_DZK_UNSUBSCRIBE_FORUM','Unsubscribe forum');
define('_DZK_UNSUBSCRIBE_TOPIC','Unsubscribe topic');
define('_DZK_UP','Up');
define('_DZK_UPDATE','Update');
define('_DZK_USERLOGINTITLE', 'This functionality is for registered users only');
define('_DZK_USERNAME','Username');
define('_DZK_USERSONLINE', 'Users online');
define('_DZK_USERS_RANKS','Users ranks');
define('_DZK_USER_IP', 'User IP');

//
// V
//
define('_DZK_VIEWIP', 'View ip address');
define('_DZK_VIEWS','Views');
define('_DZK_VIEW_IP', 'View IP');
define('_DZK_VISITCATEGORY', 'Visit this category');
define('_DZK_VISITFORUM', 'Visit this forum');

//
// W
//
define('_DZK_WRITTENON', 'written on');

//
// Y
//
define('_DZK_YESTERDAY','yesterday');
