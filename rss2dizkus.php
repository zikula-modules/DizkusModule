<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

//
// store the absolute path to your Zikula folder here
//
chdir('/opt/webdev/htdocs/z121');

// NOTE : This will work with the Zikula backend... I did not
// try other rss feed (1.0, 2.0, Atom)... RSS mod could
// return a different information (timestamp - array keys like title, etc.

//
// start Zikula
//
include 'lib/ZLoader.php';
ZLoader::register();
System::init();

//
// Checking if RSS2Forum is enabled
//
if (!ModUtil::getVar('Dizkus', 'rss2f_enabled') == 'no') {
    return;
}

//
// Checking Feeds module availability
//
if (!ModUtil::available('Feeds')) {
    return;
}

//
// Getting All forums where RSS2DIZKUS is SET... this also loads modules/Dizkus/common.php
//
$forums = ModUtil::apiFunc('Dizkus', 'admin', 'readforums', array('permcheck' => 'nocheck'));

if (!$forums) {
    return;
}

$loggedin = false;
$lastuser = '';
foreach ($forums as $forum)
{
    if ($forum['externalsource'] == 2) {   // RSS

        if ($lastuser <> $forum['pnuser']) {
            UserUtil::logOut();
            $loggedin = false;
            // login the correct user
            if (UserUtil::logIn($forum['pnuser'], base64_decode($forum['pnpassword']), false)) {
                $lastuser = $forum['pnuser'];
                $loggedin = true;
            } else {
                // unable to login
            }
        } else {
            // we have been here before
            $loggedin = true;
        }

        if ($loggedin == true) {
            $rss = ModUtil::apiFunc('Feeds', 'user', 'get', array('fid' => $forum['externalsourceurl']));

            if (!$rss) {
                // Buzz off, this feed doesn't exists
                exit;
            }

            // Get the feed...
            $dump = ModUtil::apiFunc('Feeds', 'user', 'getfeed', array('fid' => $rss['fid'],
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
            $items = $dump['feed']->get_items();

            // See the function below...
            $insert = ModUtil::apiFunc('Dizkus', 'user', 'insertrss',
                                   array('items' => $items,
                                         'forum' => $forum));

            if (!$insert) {
                // Do your debug
            }
            // Done
        }
        // endif loggedin
    }
}
