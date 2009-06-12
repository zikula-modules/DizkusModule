<?php
/************************************************************************
 * Dizkus - The Zikula forum                                            *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the Dizkus Module Development Team        *
 * http://www.dizkus.com/                                               *
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
 * mailcron.phpadmin functions
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2004 by Frank Schummertz
 * @package Dizkus
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.dizkus.com
 *
 ***********************************************************************/

//
// store the absolut path to your Zikula folder here
//
chdir('/opt/webdev/htdocs/z10');

// NOTE : This will work with the Zikula backend... I did not
// tried other rss feed (1.0, 2.0, Atom)... RSS mod could
// return a different information (timestamp - array keys like title, etc.

// start Zikula
/****************************************************************/
include 'includes/pnAPI.php';
pnInit();
/****************************************************************/

// Checking if RSS2Forum is enabled
/****************************************************************/
if (!pnModGetVar('Dizkus', 'rss2f_enabled') == 'no') {
    return;
}

// Checking Feeds module availability
/****************************************************************/
if (!pnModAvailable('Feeds')) {
    return;
}
/****************************************************************/

// Getting All forums where RSS2DIZKUS is SET... this also loads modules/Dizkus/common.php
/**************************************************************************/
$forums = pnModAPIFunc('Dizkus', 'admin', 'readforums', array('permcheck' => 'nocheck'));

if (!$forums) {
    return;
}
/****************************************************************/

$loggedin = false;
$lastuser = '';
foreach($forums as $forum) {

    if($forum['externalsource'] == 2) {   // RSS

        if($lastuser <> $forum['pnuser']) {
            pnUserLogOut();
            $loggedin = false;
            // login the correct user
            if(pnUserLogIn($forum['pnuser'], base64_decode($forum['pnpassword']), false)) {
                $lastuser = $forum['pnuser'];
                $loggedin = true;
            } else {
                // unable to login
            }
        } else {
            // we have been here before
            $loggedin = true;
        }

        if($loggedin == true) {
            $rss = pnModAPIFunc('Feeds', 'user', 'get', array('fid' => $forum['externalsourceurl']));

            if (!$rss) {
                // Buzz off, this feed doesn't exists
                exit;
            }

            // Get the feed...
            $dump = pnModAPIFunc('Feeds', 'user', 'getfeed', array('fid' => $rss['fid'],
                                                                 'url' => $rss['url']));

            if (!$dump) {
                // Buzz off, this feed doesn't exists
                exit;
            }

            // Sorting ascending to store in the right order in the forum.
            // I tried to sort by the timestamp at first and lost my mind why it wasn't working...
            // Finally decided that since it was working with the link, the link was good enough
            // Change it to your liking. It probably won't work on other type of feed.
            // Important information is in the $dump->items
            $items = $dump->get_items(); // array_csort($dump->items, 'link', SORT_ASC);

            // See the function below...
            $insert = pnModAPIFunc('Dizkus', 'user', 'insertrss',
                                   array('items' => $items,
                                         'forum' => $forum));

            if (!$insert) {
                // Do your debug
            }

            // Done
        } // if loggedin
    }
}
