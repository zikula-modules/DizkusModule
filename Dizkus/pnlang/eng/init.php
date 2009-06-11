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

define('_DZK_FAILEDTOUPGRADEHOOK', 'Error upgrading the hooks');

define('_DZK_INSTALLATION', 'Install Dizkus');
define('_DZK_WELCOMETOINTERACTIVEINIT', 'Welcome to the Dizkus installation');
define('_DZK_INTERACTIVEINITHINT', 'You are now going to install Dizkus %version% on your site. Please refer to the documentation for more information about its confuguration and options.');

define('_DZK_WELCOMETOINTERACTIVEUPGRADE', 'Dizkus Upgrade');
define('_DZK_OLDVERSION', 'old version');
define('_DZK_NEWVERSION', 'new version');
define('_DZK_NEXTVERSION', 'next version');

define('_DZK_BACKUPHINT', 'Create a database dump before<br />performing this upgrade step!');
define('_DZK_UPGRADE_ADDINDEXNOW', 'Create index fields now');
define('_DZK_UPGRADE_ADDINDEXLATER', 'Create index fields manually with phpmyadmin etc.');

define('_DZK_TO25_HINT', 'This upgrade contains several database changes including the creation of two index fields to inrease the fulltext search performance. This might lead you into trouble in shared hosting environments with a large amount of postings in the database!');
define('_DZK_TO25_FAILED', 'Upgrade to Dizkus 2.5 failed');

define('_DZK_TO26_HINT', 'This upgrade contains several database for the forum comments feature.');
define('_DZK_TO26_FAILED', 'Upgrade to Dizkus 2.6 failed');

define('_DZK_TO27_HINT', 'This upgrade does not not change the database structure.<br />The new feature in this version is the use of Ajax (<a href="http://en.wikipedia.org/wiki/Ajax_(programming)">read more about it</a>).<br /><br />');
define('_DZK_TO27_FAILED', 'Upgrade to Dizkus 2.7 failed');

define('_DZK_ZIKULA10ISREQUIRED', 'This version of Dizkus requires Zikula 1.0 or later. Installation has been stopped because this requirement is not met');
define('_DZK_DISABLED_INFO', 'The forum is currently disabled for maintenance, please come back later.');
