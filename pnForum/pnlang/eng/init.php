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

?>