<?php
/**
 * main entry point - used for bckwards compatibility to old phpBB_14 links in search engines
 * @version $Id$
 * @author Andreas Krapohl, Frank Schummertz 
 * @copyright 2003 by Andreas Krapohl, Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html> 
 * @link http://www.pnforum.de
 */

if (!defined("LOADED_AS_MODULE")) {
    die ("You can't access this file directly...");
}

if(pnModAvailable('pnForum')) {
    $action = pnVarCleanFromInput('action');
    if(!isset($action)) {
    	$action = 'index';
    }
    switch ($action){
        case 'index':
            $viewcat = (int)pnVarCleanFromInput('viewcat');
            pnRedirect(pnModURL('pnForum', 'user', 'main',
                                array('viewcat' => $viewcat)));
            return true;
            break;
        case 'viewforum':
            $forum = (int)pnVarCleanFromInput('forum');
            $start = (int)pnVarCleanFromInput('start');
            pnRedirect(pnModURL('pnForum', 'user', 'viewforum',
                                array('forum' => $forum,
                                      'start' => $start)));
            return true;
            break;
        case 'viewtopic':
            $topic = (int)pnVarCleanFromInput('topic');
            $start = (int)pnVarCleanFromInput('start');
            pnRedirect(pnModURL('pnForum', 'user', 'viewtopic',
                                array('topic' => $topic,
                                      'start' => $start)));
            return true;
            break;
        case 'latest':
            list($selorder, $nohours, $unanswered) = pnVarCleanFromInput('selorder', 'nohours', 'unanswered');
            pnRedirect(pnModURL('pnForum', 'user', 'viewlatest',
                                array('selorder'   => $selorder,
                                      'nohours'    => $nohours,
                                      'unanswered' => $unanswered)));
            return true;
            break;
        default:
            pnRedirect(pnModURL('pnForum', 'user', 'main'));
            return true;
            break;
    }
} else {
    pnRedirect('index.php');
    return true;
}

?>