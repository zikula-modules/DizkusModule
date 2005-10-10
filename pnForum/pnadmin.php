<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                            *
 ************************************************************************
 * Modified version of: *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
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
 * admin functions
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2004 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

include_once("modules/pnForum/common.php");

/**
 * the main administration function
 *
 */
function pnForum_admin_main()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $categories = pnModAPIFunc('pnForum', 'admin', 'readcategories');
    $forums = pnModAPIFunc('pnForum', 'admin', 'readforums');
    $pnr =& new pnRender("pnForum");
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('total_categories', count($categories));
    $pnr->assign('categories', $categories);
    $pnr->assign('forums', $forums);
    return $pnr->fetch("pnforum_admin_main.html");
}

/**
 * preferences
 *
 */
function pnForum_admin_preferences()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $submit = pnVarCleanFromInput('submit');

    if(!$submit) {
        $checked = "checked=\"checked\" ";
        if (pnModGetVar('pnForum', 'post_sort_order') == "ASC") {
        	$post_sort_order_ascchecked  = $checked;
        	$post_sort_order_descchecked = " ";
        } else {
        	$post_sort_order_ascchecked  = "";
        	$post_sort_order_descchecked = $checked;
        }
        if (pnModGetVar('pnForum', 'log_ip') == "yes") {
        	$logiponchecked = $checked;
        	$logipoffchecked = " ";
        } else {
        	$logiponchecked = " ";
        	$logipoffchecked = $checked;
        }
        if (pnModGetVar('pnForum', 'slimforum') == "yes") {
        	$slimforumonchecked = $checked;
        	$slimforumoffchecked = " ";
        } else {
        	$slimforumonchecked = " ";
        	$slimforumoffchecked = $checked;
        }
        if (pnModGetVar('pnForum', 'autosubscribe') == "yes") {
        	$autosubscribeonchecked = $checked;
        	$autosubscribeoffchecked = " ";
        } else {
        	$autosubscribeonchecked = " ";
        	$autosubscribeoffchecked = $checked;
        }
        if (pnModGetVar('pnForum', 'm2f_enabled') == "yes") {
        	$m2f_enabledonchecked = $checked;
        	$m2f_enabledoffchecked = " ";
        } else {
        	$m2f_enabledonchecked = " ";
        	$m2f_enabledoffchecked = $checked;
        }
        if (pnModGetVar('pnForum', 'favorites_enabled') == "yes") {
        	$favorites_enabledonchecked = $checked;
        	$favorites_enabledoffchecked = " ";
        } else {
        	$favorites_enabledonchecked = " ";
        	$favorites_enabledoffchecked = $checked;
        }
        if (pnModGetVar('pnForum', 'hideusers') == "yes") {
        	$hideusers_onchecked = $checked;
        	$hideusers_offchecked = " ";
        } else {
        	$hideusers_onchecked = " ";
        	$hideusers_offchecked = $checked;
        }
        if (pnModGetVar('pnForum', 'removesignature') == "yes") {
        	$removesignature_onchecked = $checked;
        	$removesignature_offchecked = " ";
        } else {
        	$removesignature_onchecked = " ";
        	$removesignature_offchecked = $checked;
        }

        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('autosubscribe', $autosubscribechecked);
        $pnr->assign('signature_start', stripslashes(pnModGetVar('pnForum', 'signature_start')));
        $pnr->assign('signature_end', stripslashes(pnModGetVar('pnForum', 'signature_end')));
	    $pnr->assign('topics_per_page', pnModGetVar('pnForum', 'topics_per_page'));
	    $pnr->assign('posts_per_page', pnModGetVar('pnForum', 'posts_per_page'));
	    $pnr->assign('hot_threshold', pnModGetVar('pnForum', 'hot_threshold'));
	    $pnr->assign('email_from', pnModGetVar('pnForum', 'email_from'));
	    $pnr->assign('default_lang', pnModGetVar('pnForum', 'default_lang'));
        $pnr->assign('url_ranks_images', pnModGetVar('pnForum', 'url_ranks_images'));
        $pnr->assign('posticon', pnModGetVar('pnForum', 'posticon'));
        $pnr->assign('firstnew_image', pnModGetVar('pnForum', 'firstnew_image'));
        $pnr->assign('post_sort_order_ascchecked', $post_sort_order_ascchecked);
        $pnr->assign('post_sort_order_descchecked', $post_sort_order_descchecked);
        $pnr->assign('logiponchecked', $logiponchecked);
        $pnr->assign('logipoffchecked', $logipoffchecked);
        $pnr->assign('slimforumonchecked', $slimforumonchecked);
        $pnr->assign('slimforumoffchecked', $slimforumoffchecked);
        $pnr->assign('autosubscribeonchecked', $autosubscribeonchecked);
        $pnr->assign('autosubscribeoffchecked', $autosubscribeoffchecked);
        $pnr->assign('m2f_enabledonchecked', $m2f_enabledonchecked);
        $pnr->assign('m2f_enabledoffchecked', $m2f_enabledoffchecked);
        $pnr->assign('favorites_enabledonchecked', $favorites_enabledonchecked);
        $pnr->assign('favorites_enabledoffchecked', $favorites_enabledoffchecked);
        $pnr->assign('hideusers_onchecked',  $hideusers_onchecked);
        $pnr->assign('hideusers_offchecked', $hideusers_offchecked);
        $pnr->assign('removesignature_onchecked',  $removesignature_onchecked);
        $pnr->assign('removesignature_offchecked', $removesignature_offchecked);
        return $pnr->fetch( "pnforum_admin_preferences.html");
    } else { // submit is set
        $actiontype = pnVarCleanfromInput('actiontype');
        if($actiontype=="Save") {
            pnModSetVar('pnForum', 'removesignature', pnVarPrepForStore(pnVarCleanFromInput('removesignature')));
            pnModSetVar('pnForum', 'hideusers', pnVarPrepForStore(pnVarCleanFromInput('hideusers')));
            pnModSetVar('pnForum', 'favorites_enabled', pnVarPrepForStore(pnVarCleanFromInput('favorites_enabled')));
            pnModSetVar('pnForum', 'm2f_enabled', pnVarPrepForStore(pnVarCleanFromInput('m2f_enabled')));
            pnModSetVar('pnForum', 'autosubscribe', pnVarPrepForStore(pnVarCleanFromInput('autosubscribe')));
            pnModSetVar('pnForum', 'signature_start', pnVarPrepForStore(pnVarCleanFromInput('signature_start')));
            pnModSetVar('pnForum', 'signature_end', pnVarPrepForStore(pnVarCleanFromInput('signature_end')));
            pnModSetVar('pnForum', 'topics_per_page', pnVarPrepForStore(pnVarCleanFromInput('topics_per_page')));
            pnModSetVar('pnForum', 'posts_per_page', pnVarPrepForStore(pnVarCleanFromInput('posts_per_page')));
            pnModSetVar('pnForum', 'hot_threshold', pnVarPrepForStore(pnVarCleanFromInput('hot_threshold')));
            pnModSetVar('pnForum', 'email_from', pnVarPrepForStore(pnVarCleanFromInput('email_from')));
            pnModSetVar('pnForum', 'default_lang', pnVarPrepForStore(pnVarCleanFromInput('default_lang')));
            pnModSetVar('pnForum', 'url_ranks_images', pnVarPrepForStore(pnVarCleanFromInput('url_ranks_images')));
            pnModSetVar('pnForum', 'posticon', pnVarPrepForStore(pnVarCleanFromInput('posticon')));
            pnModSetVar('pnForum', 'firstnew_image', pnVarPrepForStore(pnVarCleanFromInput('firstnew_image')));
            pnModSetVar('pnForum', 'post_sort_order', pnVarPrepForStore(pnVarCleanFromInput('post_sort_order')));
            pnModSetVar('pnForum', 'log_ip', pnVarPrepForStore(pnVarCleanFromInput('log_ip')));
            pnModSetVar('pnForum', 'slimforum', pnVarPrepForStore(pnVarCleanFromInput('slimforum')));
        }
        if($actiontype=="RestoreDefaults")  {
            pnModSetVar('pnForum', 'removesignature', 'no');
            pnModSetVar('pnForum', 'hideusers', 'no');
            pnModSetVar('pnForum', 'favorites_enabled', 'yes');
            pnModSetVar('pnForum', 'm2f_enabled', 'yes');
            pnModSetVar('pnForum', 'autosubscribe', 'yes');
            pnModSetVar('pnForum', 'signature_start', '<div style="border: 1px solid black;">');
            pnModSetVar('pnForum', 'signature_end', '</div>');
		    pnModSetVar('pnForum', 'posts_per_page', 15);
		    pnModSetVar('pnForum', 'topics_per_page', 15);
		    pnModSetVar('pnForum', 'hot_threshold', 20);
		    pnModSetVar('pnForum', 'email_from', pnConfigGetVar('adminmail'));
		    pnModSetVar('pnForum', 'default_lang', 'iso-8859-1');
		    pnModSetVar('pnForum', 'url_ranks_images', "modules/pnForum/pnimages/ranks");
		    pnModSetVar('pnForum', 'posticon', "modules/pnForum/pnimages/posticon.gif");
		    pnModSetVar('pnForum', 'firstnew_image', "modules/pnForum/pnimages/firstnew.gif");
		    pnModSetVar('pnForum', 'post_sort_order', "ASC");
		    pnModSetVar('pnForum', 'log_ip', "yes");
		    pnModSetVar('pnForum', 'slimforum', "no");
        }
    }
    return pnRedirect(pnModURL('pnForum', 'admin', 'main'));
}

/**
 * advancedpreferences
 *
 */
function pnForum_admin_advancedpreferences()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $submit = pnVarCleanFromInput('submit');

    if(!$submit) {
        list($dbconn, $pntable) = pnfOpenDB();
        $sql = "SELECT  VERSION()";
        $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
        list($dbversion) = $result->fields;
        pnfCloseDB($result);

        $checked = "checked=\"checked\" ";
     	$fulltextindex_checked  = "";
        $extendedsearch_checked = "";
        if (pnModGetVar('pnForum', 'fulltextindex') == "1") {
        	$fulltextindex_checked  = $checked;
        }
        if (pnModGetVar('pnForum', 'extendedsearch') == "1") {
        	$extendedsearch_checked = $checked;
        }
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('dbversion', $dbversion);
        $pnr->assign('dbtype', $dbconn->databaseType);
        $pnr->assign('dbname', $dbconn->databaseName);
        $pnr->assign('fulltextindex_checked', $fulltextindex_checked);
        $pnr->assign('extendedsearch_checked', $extendedsearch_checked);
        return $pnr->fetch( "pnforum_admin_advancedpreferences.html");
    } else { // submit is set
        pnModSetVar('pnForum', 'fulltextindex', pnVarPrepForStore(pnVarCleanFromInput('fulltextindex')));
        pnModSetVar('pnForum', 'extendedsearch', pnVarPrepForStore(pnVarCleanFromInput('extendedsearch')));
    }
    return pnRedirect(pnModURL('pnForum', 'admin', 'main'));
}

/**
 * syncforums
 *
 */
function pnForum_admin_syncforums()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }
    $silent = pnVarCleanFromInput('silent');

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "all forums"));
	$message = pnVarPrepForDisplay(_PNFORUM_SYNC_FORUMINDEX) . "<br />";

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "all topics"));
	$message .= pnVarPrepForDisplay(_PNFORUM_SYNC_TOPICS) . "<br />";

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "all posts"));
	$message .= pnVarPrepForDisplay(_PNFORUM_SYNC_POSTSCOUNT) . "<br />";

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "users"));
	$message .= pnVarPrepForDisplay(_PNFORUM_SYNC_USERS) . "<br />";

	if ($silent != 1) {
        pnSessionSetVar('statusmsg', $message);
	}
    return pnRedirect(pnModURL('pnForum', 'admin', 'main'));
}

/**
 * addcategory
 *
 */
function pnForum_admin_category()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($submit, $cat_id) = pnVarCleanFromInput('submit', 'cat_id');
    if(!$submit)
    {
        if( $cat_id==-1) {
            $category = array('cat_title' => "",
                              'cat_id' => -1);
            $category['topic_count'] = 0;
            $category['post_count'] = 0;
        } else {
            $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                                     array( 'cat_id' => $cat_id ));
            $forums = pnModAPIFunc('pnForum', 'admin', 'readforums',
                       array('cat_id' => $cat_id));

            foreach($forums as $forum) {
                $category['topic_count'] += pnModAPIFunc('pnForum', 'user', 'boardstats',
                                                         array('type' => 'forumtopics',
                                                               'id'   => $forum['forum_id']));
                $category['post_count'] += pnModAPIFunc('pnForum', 'user', 'boardstats',
                                                        array('type' => 'forumposts',
                                                              'id'   => $forum['forum_id']));
            }
        }
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('category', $category );
        return $pnr->fetch("pnforum_admin_category.html");
    } else { // submit is set
        list($actiontype, $cat_title) = pnVarCleanFromInput('actiontype', 'cat_title');

        switch($actiontype)
        {
            case "Add":
                pnModAPIFunc('pnForum', 'admin', 'addcategory', array('cat_title' => $cat_title));
                break;
            case "Edit":
                pnModAPIFunc('pnForum', 'admin', 'updatecategory', array('cat_id' => $cat_id,
                                                                          'cat_title' => $cat_title));
                break;
            case "Delete":
                pnModAPIFunc('pnForum', 'admin', 'deletecategory', array('cat_id' => $cat_id));
                break;
            default:
        }
        return pnRedirect(pnModUrl('pnForum', 'admin', 'main'));
    }
}

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

    if(!$submit) {
        //
        if($forum_id==-1) {
            $forum = array('forum_name'       => "",
                           'forum_id'         => -1,
                           'forum_desc'       => "",
                           'forum_access'     => -1,
                           'forum_type'       => -1,
                           'forum_order'      => -1,
                           'cat_title'        => "",
                           'cat_id'           => -1,
                           'pop3_active'      => 0,
                           'pop3_server'      => "",
                           'pop3_port'        => 110,
                           'pop3_login'       => "",
                           'pop3_password'    => "",
                           'pop3_interval'    => 0,
                           'pop3_pnuser'      => "",
                           'pop3_pnpassword'  => "",
                           'pop3_matchstring' => "");
        } else {
            $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                                  array('forum_id' => $forum_id));
        }
        $forum['pop3_active_checked'] = ($forum['pop3_active']==1) ? 'checked' : '';
        $moderators = pnModAPIFunc('pnForum', 'admin', 'readmoderators',
                                    array('forum_id' => $forum['forum_id']));
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('forum', $forum);
        $pnr->assign('categories', pnModAPIFunc('pnForum', 'admin', 'readcategories'));
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
             $pop3_active,
             $pop3_server,
             $pop3_port,
             $pop3_login,
             $pop3_password,
             $pop3_passwordconfirm,
             $pop3_interval,
             $pop3_matchstring,
             $pop3_pnuser,
             $pop3_pnpassword,
             $pop3_pnpasswordconfirm,
             $actiontype,
             $pop3_test)   = pnVarCleanFromInput('forum_name',
                                                 'forum_id',
                                                 'cat_id',
                                                 'desc',
                                                 'mods',
                                                 'rem_mods',
                                                 'pop3_active',
                                                 'pop3_server',
                                                 'pop3_port',
                                                 'pop3_login',
                                                 'pop3_password',
                                                 'pop3_passwordconfirm',
                                                 'pop3_interval',
                                                 'pop3_matchstring',
                                                 'pop3_pnuser',
                                                 'pop3_pnpassword',
                                                 'pop3_pnpasswordconfirm',
                                                 'actiontype',
                                                 'pop3_test');
        $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                              array('forum_id' => $forum_id));

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

        if($pop3_pnpassword <> $pop3_pnpasswordconfirm) {
        	return showforumerror(_PNFORUM_PASSWORDNOMATCH, __FILE__, __LINE__);
        }
        // check if user has changed the password
        if($forum['pop3_pnpassword'] == $pop3_pnpassword) {
            // no change necessary
            $pop3_pnpassword = "";
        } else {
            $pop3_pnpassword = base64_encode($pop3_pnpassword);
        }
        switch($actiontype) {
            case "Add":
                $forum_id = pnModAPIFunc('pnForum', 'admin', 'addforum',
                                         array('forum_name'       => $forum_name,
                                               'cat_id'           => $cat_id,
                                               'desc'             => $desc,
                                               'mods'             => $mods,
                                               'pop3_active'      => $pop3_active,
                                               'pop3_server'      => $pop3_server,
                                               'pop3_port'        => $pop3_port,
                                               'pop3_login'       => $pop3_login,
                                               'pop3_password'    => $pop3_password,
                                               'pop3_interval'    => $pop3_interval,
                                               'pop3_pnuser'      => $pop3_pnuser,
                                               'pop3_pnpassword'  => $pop3_pnpassword,
                                               'pop3_matchstring' => $pop3_matchstring));
                break;
            case "Edit":
                pnModAPIFunc('pnForum', 'admin', 'editforum',
                             array('forum_name'       => $forum_name,
                                   'forum_id'         => $forum_id,
                                   'cat_id'           => $cat_id,
                                   'desc'             => $desc,
                                   'mods'             => $mods,
                                   'rem_mods'         => $rem_mods,
                                   'pop3_active'      => $pop3_active,
                                   'pop3_server'      => $pop3_server,
                                   'pop3_port'        => $pop3_port,
                                   'pop3_login'       => $pop3_login,
                                   'pop3_password'    => $pop3_password,
                                   'pop3_interval'    => $pop3_interval,
                                   'pop3_pnuser'      => $pop3_pnuser,
                                   'pop3_pnpassword'  => $pop3_pnpassword,
                                   'pop3_matchstring' => $pop3_matchstring));
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
    return pnRedirect(pnModURL('pnForum', 'admin', 'main'));
}

/**
 * ranks
 *
 */
function pnForum_admin_ranks()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($submit, $ranktype) = pnVarCleanFromInput('submit', 'ranktype');

    if(!is_numeric($ranktype)) {
        return _MODARGSERROR;
    }

    list($rankimages, $ranks) = pnModAPIFunc('pnForum', 'admin', 'readranks',
                                             array('ranktype' => $ranktype));

    if(!$submit) {
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('ranks', $ranks);
        $pnr->assign('ranktype', $ranktype);
        $pnr->assign('rankimages', $rankimages);
        if($ranktype==0) {
            return $pnr->fetch("pnforum_admin_ranks.html");
        } else {
            return $pnr->fetch("pnforum_admin_honoraryranks.html");
        }
    } else {
        list($actiontype,
             $ranktype,
             $rank_id,
             $title,
             $min_posts,
             $max_posts,
             $image ) = pnVarCleanFromInput('actiontype',
                                            'ranktype',
                                            'rank_id',
                                            'title',
                                            'min_posts',
                                            'max_posts',
                                            'image');
        pnModAPIFunc('pnForum', 'admin', 'saverank', array('actiontype'=> $actiontype,
                                                            'ranktype'  => $ranktype,
                                                            'rank_id'   => $rank_id,
                                                            'title'     => $title,
                                                            'min_posts' => $min_posts,
                                                            'max_posts' => $max_posts,
                                                            'image'     => $image));
    }
    return pnRedirect(pnModURL('pnForum','admin', 'ranks', array('ranktype' => $ranktype)));
}

/**
 * ranks
 *
 */
function pnForum_admin_assignranks()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($submit) = pnVarCleanFromInput('submit');

    $rankusers = pnModAPIFunc('pnForum', 'admin', 'readrankusers');
    $norankusers = pnModAPIFunc('pnForum', 'admin', 'readnorankusers');
    list($rankimages, $ranks) = pnModAPIFunc('pnForum', 'admin', 'readranks',
                                             array('ranktype' => 1));
    // remove the first rank, its used for adding new ranks only
    array_splice($ranks, 0, 1);

    if(!$submit) {
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('ranks', $ranks);
        $pnr->assign('rankimages', $rankimages);
        $pnr->assign('rankusers', $rankusers);
        $pnr->assign('norankusers', $norankusers);
        return $pnr->fetch("pnforum_admin_assignranks.html");
    } else {
        list($actiontype,
             $rank_id,
             $user_id ) = pnVarCleanFromInput('actiontype',
                                              'rank_id',
                                              'user_id');
        pnModAPIFunc('pnForum', 'admin', 'assignranksave', array('actiontype'=> $actiontype,
                                                                  'rank_id'   => $rank_id,
                                                                  'user_id'   => $user_id));
    }
    return pnRedirect(pnModURL('pnForum','admin', 'assignranks'));
}

/**
 * reordercategories
 *
 */
function pnForum_admin_reordercategories()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($direction) = pnVarCleanFromInput('direction');

    $categories = pnModAPIFunc('pnForum', 'admin', 'readcategories');

    if(!$direction) {
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('total_categories', count($categories));
        $pnr->assign('categories', $categories);
        return $pnr->fetch("pnforum_admin_reordercategories.html");
    } else {
        list( $cat_id,
              $cat_order,
              $direction ) = pnVarCleanFromInput('cat_id',
                                                 'cat_order',
                                                 'direction');
        pnModAPIFunc('pnForum', 'admin', 'reordercategoriessave',
                     array('cat_id'    => $cat_id,
                           'cat_order' => $cat_order,
                           'direction' => $direction));
    }
    return pnRedirect(pnModURL('pnForum', 'admin', 'reordercategories'));
}

/**
 * reorderforums
 *
 */
function pnForum_admin_reorderforums()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
        return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($direction,
            $cat_id,
            $forum_id,
            $direction,
            $editforumorder,
            $oldorder,
            $neworder) = pnVarCleanFromInput('direction',
                'cat_id',
                'forum_id',
                'direction',
                'editforumorder',
                'oldorder',
                'neworder');

    // we are re-sequencing with the arrow keys
    if (!empty($direction)) {
        // figure out the new order
        if ($direction=='up') {
            $neworder = $oldorder-1;
        } else {
            $neworder = $oldorder+1;
        }
    }

    // we either got the neworder because they were editing
    // an entry or because they used an arrow key and we calculated
    // it above
    if (isset($neworder) && is_numeric($neworder)) {
        // call the api function to figure out the new sequence for everything
        pnModAPIFunc('pnForum', 'admin', 'reorderforumssave',
                array('cat_id'      => $cat_id,
                    'forum_id'    => $forum_id,
                    'neworder'    => $neworder,
                    'oldorder'    => $oldorder));
    }

    // if we have been passed a cat_id then lets figure out which forums
    // belong to this category, and get the category details
    if(!empty($cat_id) && is_numeric($cat_id)) {
        // get the list of forums and their data
        $forums = pnModAPIFunc('pnForum', 'admin', 'readforums',
                array('cat_id' => $cat_id));
        // get the category information
        $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                array('cat_id' => $cat_id));
    }

    // show the list of forums and their order
    // NOTE: There is no need to do a pnRedirect because we figure
    // out the forum info after we set the new order if we were editing.
    $pnr =& new pnRender("pnForum");
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('forums', $forums);
    // editforumorder is used to determine if we want to edit the forum_order
    // and contains the forum_id of the forum we want to edit.
    $pnr->assign('editforumorder', $editforumorder);
    $pnr->assign('total_forums', count($forums));
    $pnr->assign('category', $category);
    return $pnr->fetch("pnforum_admin_reorderforums.html");
}

?>