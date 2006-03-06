<?php

/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                               *
 *                                                                      *
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
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2005 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

include_once 'modules/pnForum/common.php';

/**
 * forum
 *
 */
function pnForum_admin_forum()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($submit, $forum_id) = pnVarCleanFromInput('submit', 'forum_id');

    $categories = pnModAPIFunc('pnForum', 'admin', 'readcategories');
    if(count($categories) == 0) {
        // no categories found, redirect to category admin
        pnSessionSetVar('statusmsg', _PNFORUM_NOCATEGORIES);
        return pnRedirect(pnModURL('pnForum', 'admin', 'category',
                                   array('cat_id' => -1)));
    }

    if(!$submit) {
        //
        if($forum_id==-1) {
            $forum = array('forum_name'       => '',
                           'forum_id'         => -1,
                           'forum_desc'       => '',
                           'forum_access'     => -1,
                           'forum_type'       => -1,
                           'forum_order'      => -1,
                           'cat_title'        => '',
                           'cat_id'           => -1,
                           'pop3_active'      => 0,
                           'pop3_server'      => '',
                           'pop3_port'        => 110,
                           'pop3_login'       => '',
                           'pop3_password'    => '',
                           'pop3_interval'    => 0,
                           'pop3_pnuser'      => '',
                           'pop3_pnpassword'  => '',
                           'pop3_matchstring' => '',
                           'forum_moduleref'  => '',
                           'forum_pntopic'    => 0);
        } else {
            $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                                  array('forum_id' => $forum_id));
        }
        $externalsourceoptions = array( 0 => array('checked'  => '',
                                                   'name'     => _PNFORUM_NOEXTERNALSOURCE,
                                                   'ok'       => '',
                                                   'extended' => false),   // none
                                        1 => array('checked'  => '',
                                                   'name'     => _PNFORUM_MAIL2FORUM,
                                                   'ok'       => '',
                                                   'extended' => true),  // mail
                                        2 => array('checked'  => '',
                                                   'name'     => _PNFORUM_RSS2FORUM,
                                                   'ok'       => (pnModAvailable('RSS')==true) ? '' : _PNFORUM_RSSMODULENOTAVAILABLE,
                                                   'extended' => true)); // rss
        $externalsourceoptions[$forum['pop3_active']]['checked'] = ' checked="checked"';
        $hooked_modules_raw = pnModAPIFunc('modules', 'admin', 'gethookedmodules',
                                       array('hookmodname' => 'pnForum'));
        $hooked_modules = array(array('name' => _PNFORUM_NOHOOKEDMODULES,
                                               'id'   => 0));
        $foundsel = false;
        foreach($hooked_modules_raw as $hookmod => $dummy) {
            $hookmodid = pnModGetIDFromName($hookmod);
            $sel = false;
            if($forum['forum_moduleref'] == $hookmodid) {
                $sel = true;
                $foundsel = true;
            }
            $hooked_modules[] = array('name' => $hookmod,
                                               'id'   => $hookmodid,
                                               'sel'  => $sel);
        }
        if($foundsel == false) {
            $hooked_modules[0]['sel'] = true;
        }

        // read all RSS feeds
        $rssfeeds = array();
        if(pnModAvailable('RSS')) {
            $rssfeeds = pnModAPIFunc('RSS', 'user', 'getall');
        }

        $moderators = pnModAPIFunc('pnForum', 'admin', 'readmoderators',
                                    array('forum_id' => $forum['forum_id']));
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('forum', $forum);
        $pnr->assign('hooked_modules', $hooked_modules);
        $pnr->assign('rssfeeds', $rssfeeds);
        $pnr->assign('externalsourceoptions', $externalsourceoptions);
        $pnr->assign('pntopics', pnModAPIFunc('pnForum', 'admin', 'get_pntopics'));
        $pnr->assign('categories', $categories);
        $pnr->assign('moderators', $moderators);
        $hideusers = pnModGetVar('pnForum', 'hideusers');
        if($hideusers == 'no') {
            $users = pnModAPIFunc('pnForum', 'admin', 'readusers',
                                  array('moderators' => $moderators));
        } else {
            $users = array();
        }
        $pnr->assign('users', $users);
        $pnr->assign('groups', pnModAPIFunc('pnForum', 'admin', 'readgroups',
                                            array('moderators' => $moderators)));
        return $pnr->fetch("pnforum_admin_forum.html");
    } else {
        //
        list($forum_name,
             $forum_id,
             $cat_id,
             $desc,
             $mods,
             $rem_mods,
             $extsource,
             $rssfeed,
             $pop3_server,
             $pop3_port,
             $pop3_login,
             $pop3_password,
             $pop3_passwordconfirm,
             $pop3_interval,
             $pop3_matchstring,
             $pnuser,
             $pnpassword,
             $pnpasswordconfirm,
             $moduleref,
             $pntopic,
             $actiontype,
             $pop3_test)   = pnVarCleanFromInput('forum_name',
                                                 'forum_id',
                                                 'cat_id',
                                                 'desc',
                                                 'mods',
                                                 'rem_mods',
                                                 'extsource',
                                                 'rssfeed',
                                                 'pop3_server',
                                                 'pop3_port',
                                                 'pop3_login',
                                                 'pop3_password',
                                                 'pop3_passwordconfirm',
                                                 'pop3_interval',
                                                 'pop3_matchstring',
                                                 'pnuser',
                                                 'pnpassword',
                                                 'pnpasswordconfirm',
                                                 'moduleref',
                                                 'pntopic',
                                                 'actiontype',
                                                 'pop3_test');
        $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                              array('forum_id' => $forum_id));

        if($extsource == 2) {
            // store the rss feed in the pop3_server field
            $pop3_server = $rssfeed;
        }

        if($pop3_password <> $pop3_passwordconfirm) {
        	return showforumerror(_PNFORUM_PASSWORDNOMATCH, __FILE__, __LINE__);
        }
        // check if user has changed the password
        if($forum['pop3_password'] == $pop3_password) {
            // no change necessary
            $pop3_password = "";
        } else {
            $pop3_password = base64_encode($pop3_password);
        }

        if($pnpassword <> $pnpasswordconfirm) {
        	return showforumerror(_PNFORUM_PASSWORDNOMATCH, __FILE__, __LINE__);
        }
        // check if user has changed the password
        if($forum['pop3_pnpassword'] == $pnpassword) {
            // no change necessary
            $pnpassword = "";
        } else {
            $pnpassword = base64_encode($pnpassword);
        }
        switch($actiontype) {
            case "Add":
                $forum_id = pnModAPIFunc('pnForum', 'admin', 'addforum',
                                         array('forum_name'       => $forum_name,
                                               'cat_id'           => $cat_id,
                                               'desc'             => $desc,
                                               'mods'             => $mods,
                                               'pop3_active'      => $extsource,
                                               'pop3_server'      => $pop3_server,
                                               'pop3_port'        => $pop3_port,
                                               'pop3_login'       => $pop3_login,
                                               'pop3_password'    => $pop3_password,
                                               'pop3_interval'    => $pop3_interval,
                                               'pop3_pnuser'      => $pnuser,
                                               'pop3_pnpassword'  => $pnpassword,
                                               'pop3_matchstring' => $pop3_matchstring,
                                               'moduleref'        => $moduleref,
                                               'pntopic'          => $pntopic));
                break;
            case "Edit":
                pnModAPIFunc('pnForum', 'admin', 'editforum',
                             array('forum_name'       => $forum_name,
                                   'forum_id'         => $forum_id,
                                   'cat_id'           => $cat_id,
                                   'desc'             => $desc,
                                   'mods'             => $mods,
                                   'rem_mods'         => $rem_mods,
                                   'pop3_active'      => $extsource,
                                   'pop3_server'      => $pop3_server,
                                   'pop3_port'        => $pop3_port,
                                   'pop3_login'       => $pop3_login,
                                   'pop3_password'    => $pop3_password,
                                   'pop3_interval'    => $pop3_interval,
                                   'pop3_pnuser'      => $pnuser,
                                   'pop3_pnpassword'  => $pnpassword,
                                   'pop3_matchstring' => $pop3_matchstring,
                                   'moduleref'        => $moduleref,
                                   'pntopic'          => $pntopic));
                break;
            case "Delete":
                // no security check!!!
                pnModAPIFunc('pnForum', 'admin', 'deleteforum',
                             array('forum_id'   => $forum_id,
                                   'ok'         => 1 ));
                break;
            default:
        }
        if($pop3_test==1) {
            $pop3testresult = pnModAPIFunc('pnForum', 'user', 'testpop3connection',
                                           array('forum_id' => $forum_id));
            $pnr =& new pnRender('pnForum');
            $pnr->caching = false;
            $pnr->add_core_data();
            $pnr->assign('messages', $pop3testresult);
            $pnr->assign('forum_id', $forum_id);
            return $pnr->fetch('pnforum_admin_pop3test.html');
        }
    }
    if($actiontype == 'Delete') {
        return pnRedirect(pnModURL('pnForum', 'admin', 'main'));
    } else {
        return pnRedirect(pnModURL('pnForum', 'admin', 'forum', array('forum_id' => $forum_id)));
    }
}

?>