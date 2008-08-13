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

define('_PNFORUM_INSTALLATION', 'Install pnForum');
define('_PNFORUM_WELCOMETOINTERACTIVEINIT', 'Welcome to the pnForum installation');
define('_PNFORUM_INTERACTIVEINITHINT', 'You are now going to install pnForum %version% on your site. Please refer to the documentation for more information about its confuguration and options.');

define('_PNFORUM_WELCOMETOINTERACTIVEUPGRADE', 'pnForum Upgrade');
define('_PNFORUM_OLDVERSION', 'old version');
define('_PNFORUM_NEWVERSION', 'new version');
define('_PNFORUM_NEXTVERSION', 'next version');

define('_PNFORUM_BACKUPHINT', 'Create a database dump before<br/>performing this upgrade step!');
define('_PNFORUM_UPGRADE_ADDINDEXNOW', 'Create index fields now');
define('_PNFORUM_UPGRADE_ADDINDEXLATER', 'Create index fields manually with phpmyadmin etc.');

define('_PNFORUM_TO25_HINT', 'This upgrade contains several database changes including the creation of two index fields to inrease the fulltext search performance. This might lead you into trouble in shared hosting environments with a large amount of postings in the database!');
define('_PNFORUM_TO25_FAILED', 'Upgrade to pnForum 2.5 failed');

define('_PNFORUM_TO26_HINT', 'This upgrade contains several database for the forum comments feature.');
define('_PNFORUM_TO26_FAILED', 'Upgrade to pnForum 2.6 failed');

define('_PNFORUM_TO27_HINT', 'This upgrade does not not change the database structure.<br />The new feature in this version is the use of Ajax (<a href="http://en.wikipedia.org/wiki/Ajax_(programming)">read more about it</a>).<br /><br />');
define('_PNFORUM_TO27_FAILED', 'Upgrade auf pnForum 2.7 fehlgeschlagen');

define('_PNFORUM_ZIKULA10ISREQUIRED', 'This version of pnForum requires Zikula 1.0 or later. Installation has been stopped because this requirement is not met');
define('_PNFORUM_DISABLED_INFO', 'The forum is currently disabled for maintenance, please come back later.');
