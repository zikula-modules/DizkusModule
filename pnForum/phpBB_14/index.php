<?php
/**
 * pnForum
 * main entry point - used for bckwards compatibility to old phpBB_14 links in search engines
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

if(pnModAvailable('pnForum')) {
    $action = FormUtil::getPassedValue('action', 'index', 'GET');
    switch ($action){
        case 'index':
            $viewcat = (int)FormUtil::getPassedValue('viewcat', null, 'GET');
            return pnRedirect(pnModURL('pnForum', 'user', 'main',
                                       array('viewcat' => $viewcat)));
            break;
        case 'viewforum':
            $forum = (int)FormUtil::getPassedValue('forum', null, 'GET');
            $start = (int)FormUtil::getPassedValue('start', null, 'GET');
            return pnRedirect(pnModURL('pnForum', 'user', 'viewforum',
                                       array('forum' => $forum,
                                             'start' => $start)));
            break;
        case 'viewtopic':
            $topic = (int)FormUtil::getPassedValue('topic', null, 'GET');
            $start = (int)FormUtil::getPassedValue('start', 0, 'GET');
            return pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
                                       array('topic' => $topic,
                                             'start' => $start)));
            break;
        case 'latest':
            $selorder   = FormUtil::getPassedValue('selorder', null, 'GET');
            $nohours    = FormUtil::getPassedValue('nohours', null, 'GET');
            $unanswered = FormUtil::getPassedValue('unanswered', null, 'GET');
            return pnRedirect(pnModURL('pnForum', 'user', 'viewlatest',
                                       array('selorder'   => $selorder,
                                             'nohours'    => $nohours,
                                             'unanswered' => $unanswered)));
            break;
        default:
            return pnRedirect(pnModURL('pnForum', 'user', 'main'));
            break;
    }
} else {
    return pnRedirect('index.php');
}
