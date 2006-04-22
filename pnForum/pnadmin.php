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
        if (pnModGetVar('pnForum', 'striptags') == "yes") {
        	$striptags_onchecked = $checked;
        	$striptags_offchecked = " ";
        } else {
        	$striptags_onchecked = " ";
        	$striptags_offchecked = $checked;
        }

        if (pnModGetVar('pnForum', 'deletehookaction') == 'lock') {
        	$deletehookaction_lock = $checked;
        	$deletehookaction_remove  = ' ';
        } else {
        	$deletehookaction_lock = ' ';
        	$deletehookaction_remove  = $checked;
        }

        if (pnModGetVar('pnForum', 'rss2f_enabled') == "yes") {
        	$rss2f_enabledonchecked = $checked;
        	$rss2f_enabledoffchecked = " ";
        } else {
        	$rss2f_enabledonchecked = " ";
        	$rss2f_enabledoffchecked = $checked;
        }

        if (pnModGetVar('pnForum', 'newtopicconfirmation') == "yes") {
        	$newtopicconf_onchecked = $checked;
        	$newtopicconf_offchecked = " ";
        } else {
        	$newtopicconf_onchecked = " ";
        	$newtopicconf_offchecked = $checked;
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
        $pnr->assign('striptags_onchecked',  $striptags_onchecked);
        $pnr->assign('striptags_offchecked', $striptags_offchecked);
        $pnr->assign('deletehookaction_lock',   $deletehookaction_lock);
        $pnr->assign('deletehookaction_remove', $deletehookaction_remove);
        $pnr->assign('rss2f_enabledonchecked', $rss2f_enabledonchecked);
        $pnr->assign('rss2f_enabledoffchecked', $rss2f_enabledoffchecked);
        $pnr->assign('newtopicconf_onchecked',  $newtopicconf_onchecked);
        $pnr->assign('newtopicconf_offchecked', $newtopicconf_offchecked);
        return $pnr->fetch( "pnforum_admin_preferences.html");
    } else { // submit is set
        $actiontype = pnVarCleanfromInput('actiontype');
        if($actiontype=="Save") {
            pnModSetVar('pnForum', 'newtopicconfirmation', pnVarPrepForStore(pnVarCleanFromInput('newtopicconfirmation')));
            pnModSetVar('pnForum', 'rss2f_enabled', pnVarPrepForStore(pnVarCleanFromInput('rss2f_enabled')));
            pnModSetVar('pnForum', 'deletehookaction', pnVarPrepForStore(pnVarCleanFromInput('deletehookaction')));
            pnModSetVar('pnForum', 'striptags', pnVarPrepForStore(pnVarCleanFromInput('striptags')));
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
            pnModSetVar('pnForum', 'newtopicconfirmation', 'no');
            pnModSetVar('pnForum', 'rss2f_enabled', 'yes');
            pnModSetVar('pnForum', 'deletehookaction', 'lock');
            pnModSetVar('pnForum', 'striptags', 'no');
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
	                    'type' => "users"));
	$message = pnVarPrepForDisplay(_PNFORUM_SYNC_USERS) . "<br />";

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "all forums"));
	$message .= pnVarPrepForDisplay(_PNFORUM_SYNC_FORUMINDEX) . "<br />";

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "all topics"));
	$message .= pnVarPrepForDisplay(_PNFORUM_SYNC_TOPICS) . "<br />";

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "all posts"));
	$message .= pnVarPrepForDisplay(_PNFORUM_SYNC_POSTSCOUNT) . "<br />";

	if ($silent != 1) {
        pnSessionSetVar('statusmsg', $message);
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

    list($submit, $letter) = pnVarCleanFromInput('submit', 'letter');

    // check for a letter parameter
    if (empty($letter) && strlen($letter) != 1) {
        $letter = 'A';
    }

    list($rankimages, $ranks) = pnModAPIFunc('pnForum', 'admin', 'readranks',
                                             array('ranktype' => 1));
    for($cnt=0; $cnt<count($ranks); $cnt++) {
        $ranks[$cnt]['users'] = pnModAPIFunc('pnForum', 'admin', 'readrankusers',
                                             array('rank_id' => $ranks[$cnt]['rank_id']));
    }
    // remove the first rank, its used for adding new ranks only
    array_splice($ranks, 0, 1);

    $users = pnUserGetAll();
    $allusers = array();
    foreach ($users as $user) {
        if ($user['uname'] == 'Anonymous')  continue;
        if (strtoupper($user['uname'][0]) == strtoupper($letter) || $letter == '*') {
            $alias = '';
            if (!empty($user['name'])) {
                $alias = ' (' . $user['name'] . ')';
            }
            $allusers[$user['uid']]['name'] = $user['uname'] . $alias;
        }
        $chrid = ord(strtoupper($user['uname'][0]));
        if ($letter == '?' && !($chrid >=65 && $chrid <=90)) {
            if (!empty($user['name'])) {
              $alias = ' (' . $user['name'] . ')';
            }
            $allusers[$user['uid']]['name'] = $user['uname'] . $alias;
        }
        $allusers[$user['uid']]['rank_id'] = 0;
        for($cnt=0; $cnt<count($ranks); $cnt++) {
            if(in_array($user['uid'], $ranks[$cnt]['users'])) {
                $allusers[$user['uid']]['rank_id'] = $ranks[$cnt]['rank_id'];
            }
        }
    }

    if(!$submit) {
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('ranks', $ranks);
        $pnr->assign('rankimages', $rankimages);
        $pnr->assign('allusers', $allusers);
        return $pnr->fetch("pnforum_admin_assignranks.html");
    } else {
        $setrank = pnVarCleanFromInput('setrank');
        pnModAPIFunc('pnForum', 'admin', 'assignranksave', 
                     array('setrank' => $setrank));
    }
    return pnRedirect(pnModURL('pnForum','admin', 'assignranks'));
}


/** 
 * reordertree
 *
 */
function pnForum_admin_reordertree()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
        return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    $categorytree = pnModAPIFunc('pnForum', 'user', 'readcategorytree');
    $catids = array();
    $forumids = array();
    if(is_array($categorytree) && count($categorytree) > 0) {
        foreach($categorytree as $category) {
            $catids[] = $category['cat_id'];
            if(is_array($category['forums']) && count($category['forums']) > 0) {
                foreach($category['forums'] as $forum) {
                    $forumids[] = $forum['forum_id'];
                }
            }
        }
    }
    $pnr =& new pnRender("pnForum");
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('categorytree', $categorytree);
    $pnr->assign('catids', $catids);
    $pnr->assign('forumids', $forumids);
    return $pnr->fetch("pnforum_admin_reordertree.html");
}


/**
 * reordertreesave
 *
 * AJAX result function
 *
 */
function pnForum_admin_reordertreesave()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
        pnf_ajaxerror(_PNFORUM_NOAUTH_TOADMIN);
    }

    pnSessionsetVar('pn_ajax_call', 'ajax');
    
    if(!pnSecConfirmAuthKey()) {
//        pnf_ajaxerror(_BADAUTHKEY);
    }
    
    $categoryarray = pnVarCleanFromInput('category');
    
    // the last entry in the $category is the placeholder for a new
    // category, we need ot remove this
    array_pop($categoryarray);
    if(is_array($categoryarray) && count($categoryarray) > 0) {
        foreach($categoryarray as $catorder => $cat_id) {
            // array key start with 0, but we need 1, so we increase the order
            // value
            $catorder++;
            if(pnModAPIFunc('pnForum', 'admin', 'storenewcategoryorder',
                                              array('cat_id' => $cat_id,
                                                    'order'  => $catorder)) == false) {
                pnf_ajaxerror('storenewcategoryorder(): cannot reorder category ' . $cat_id . ' (' . $catorder . ')');
            }

            $forumsincategoryarray = pnVarCleanFromInput('cid_' . $cat_id);
            if(is_array($forumsincategoryarray) && count($forumsincategoryarray) > 0) {
                foreach($forumsincategoryarray as $forumorder => $forum_id) {
                    if(!empty($forum_id) && is_numeric($forum_id)) {
                        // array key start with 0, but we need 1, so we increase the order
                        // value
                        $forumorder++;
                        if(pnModAPIFunc('pnForum', 'admin', 'storenewforumorder',
                                                          array('forum_id' => $forum_id,
                                                                'cat_id'   => $cat_id,
                                                                'order'    => $forumorder)) == false) {
                            pnf_ajaxerror('storenewforumorder(): cannot reorder forum ' . $forum_id . ' in category ' . $cat_id . ' (' . $forumorder . ')');
                        }
                    }
                }
            }
        } 
    }
    pnf_jsonizeoutput('', true, true);
    
}

/**
 * editforum
 *
 * AJAX function
 *
 */
function pnForum_admin_editforum($args=array())
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
        pnf_ajaxerror(_PNFORUM_NOAUTH_TOADMIN);
    }
    
    if(count($args)>0) {
        extract($args);
        // forum_id, returnhtml
    } else {
        $forum_id = pnVarCleanFromInput('forum');
    }
     
    if(empty($forum_id) || !is_numeric($forum_id)) {
        pnf_ajaxerror(_MODARGSERROR . ': forum_id ' . pnVarPrepForDisplay($forum_id) . ' in pnForum_admin_editforum()');
    }
    
    if($forum_id == -1) {
        // create a new forum 
        $new = true;
        $cat_id = pnVarCleanFromInput('cat');
        $forum = array('forum_name'       => _PNFORUM_ADDNEWFORUM,
                       'forum_id'         => time(), /* for new forums only! */
                       'forum_desc'       => '',
                       'forum_access'     => -1,
                       'forum_type'       => -1,
                       'forum_order'      => -1,
                       'cat_title'        => '',
                       'cat_id'           => $cat_id,
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
        // we are editing
        $new = false;            
        $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                              array('forum_id'  => $forum_id,
                                    'permcheck' => 'admin'));

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


    $pnr = new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('hooked_modules', $hooked_modules);
    $pnr->assign('rssfeeds', $rssfeeds);
    $pnr->assign('externalsourceoptions', $externalsourceoptions);
    
    if(is_dot8()==true) {
        $pnr->assign('is_dot8', true);
        Loader::loadClass('CategoryUtil');
        $cats        = CategoryUtil::getSubCategories (1, true, true, true, true, true);
        $catselector = CategoryUtil::getSelector_Categories($cats, $forum['forum_pntopic'], 'pncategory');
        $pnr->assign('categoryselector', $catselector);        
    } else {   
        $pnr->assign('is_dot8', false);
        $pnr->assign('pntopics', pnModAPIFunc('pnForum', 'admin', 'get_pntopics'));
    }
    
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
    $pnr->assign('forum', $forum);
    $pnr->assign('newforum', $new);
    $html = $pnr->fetch('pnforum_ajax_editforum.html');
    if(!isset($returnhtml)) {
        pnf_jsonizeoutput(array('forum_id' => $forum['forum_id'],
                                'cat_id'   => $forum['cat_id'],
                                'new'      => $new,
                                'data'     => $html),
                          false);
    }
    return $html; 
}

/**
 * editcategory
 *
 */
function pnForum_admin_editcategory($args=array())
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    if(!empty($args)) {
        extract($args);
        $cat_id = $cat;
    } else {
        $cat_id = pnVarCleanFromInput('cat');
    }   
    if( $cat_id == 'new') {
        $new = true;
        $category = array('cat_title'    => _PNFORUM_ADDNEWCATEGORY,
                          'cat_id'       => time(),
                          'forum_count'  => 0);
        // we add a new category
    } else {
        $new = false;
        $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                                 array( 'cat_id' => $cat_id ));
        $forums = pnModAPIFunc('pnForum', 'admin', 'readforums',
                               array('cat_id'    => $cat_id,
                                     'permcheck' => 'nocheck'));
        $category['forum_count'] = count($forums);
    }
    $pnr =& new pnRender("pnForum");
    $pnr->caching = false;
    $pnr->add_core_data();
    $pnr->assign('category', $category );
    $pnr->assign('newcategory', $new);
    pnf_jsonizeoutput(array('data'     => $pnr->fetch('pnforum_ajax_editcategory.html'),
                            'cat_id'   => $category['cat_id'],
                            'new'      => $new),
                      false,
                      true);
}

/**
 * storecategory
 *
 * AJAX function
 *
 */
function pnForum_admin_storecategory()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    if(!pnSecConfirmAuthKey()) {
        pnf_ajaxerror(_BADAUTHKEY);
    }

    pnSessionSetVar('pn_ajax_call', 'ajax');

    list($cat_id, 
         $cat_title, 
         $add,
         $delete) = pnVarCleanFromInput('cat_id', 
                                        'cat_title', 
                                        'add',
                                        'delete');
    
    $cat_title = utf8_decode($cat_title);
    if(!empty($delete)) {
        $forums = pnModAPIFunc('pnForum', 'admin', 'readforums',
                               array('cat_id'    => $cat_id,
                                     'permcheck' => 'nocheck'));
        if(count($forums) > 0) {
            $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                                     array( 'cat_id' => $cat_id ));
            pnf_ajaxerror('error: category "' . $category['cat_title'] . '" contains ' . count($forums) . ' forums!');
        }
        $res = pnModAPIFunc('pnForum', 'admin', 'deletecategory',
                            array('cat_id' => $cat_id));
        if($res==true) {
            pnf_jsonizeoutput(array('cat_id' => $cat_id,
                                    'old_id' => $cat_id,
                                    'action' => 'delete'),
                              true,
                              true); 
        } else {
            pnf_ajaxerror('error deleting category ' . pnVarPrepForDisplay($cat_id));
        }
        
    } else if(!empty($add)) {
        $original_catid = $cat_id;
        $cat_id = pnModAPIFunc('pnForum', 'admin', 'addcategory',
                               array('cat_title' => $cat_title));
        if(!is_bool($cat_id)) {
            $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                                     array( 'cat_id' => $cat_id ));
            $pnr =& new pnRender("pnForum");
            $pnr->caching = false;
            $pnr->add_core_data();
            $pnr->assign('category', $category );
            $pnr->assign('newcategory', false);
            pnf_jsonizeoutput(array('cat_id'      => $cat_id,
                                    'old_id'      => $original_catid,
                                    'cat_title'   => $cat_title,
                                    'action'      => 'add',
                                    'edithtml'    => $pnr->fetch('pnforum_ajax_editcategory.html'),
                                    'cat_linkurl' => pnModURL('pnForum', 'user', 'main', array('viewcat' => $cat_id))),
                              true,
                              true); 
        } else {
            pnf_ajaxerror('error creating category "' . pnVarPrepForDisplay($cat_title) . '"');
        }
        
    } else {
        if(pnModAPIFunc('pnForum', 'admin', 'updatecategory',
                        array('cat_title' => $cat_title,
                              'cat_id'    => $cat_id)) == true) {
            pnf_jsonizeoutput(array('cat_id'      => $cat_id,
                                    'old_id'      => $cat_id,
                                    'cat_title'   => $cat_title,
                                    'action'      => 'update',
                                    'cat_linkurl' => pnModURL('pnForum', 'user', 'main', array('viewcat' => $cat_id))),
                              true,
                              true); 
        } else {
            pnf_ajaxerror('error updating cat_id ' . pnVarPrepForDisplay($cat_id) . ' with title "' . pnVarPrepForDisplay($cat_title) . '"');
        }
    }
}

/**
 * storeforum
 *
 * AJAX function
 */
function pnForum_admin_storeforum()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	pnf_ajaxerror(_PNFORUM_NOAUTH_TOADMIN);
    }

    if(!pnSecConfirmAuthKey()) {
        pnf_ajaxerror(_BADAUTHKEY);
    }

    pnSessionSetVar('pn_ajax_call', 'ajax');
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
         /* $pntopic, */
         $pop3_test,
         $add,
         $delete)   = pnVarCleanFromInput('forum_name',
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
                                          /* 'pntopic', */
                                          'pop3_test',
                                          'add',
                                          'delete');

    if(is_dot8()==true) {
        $pntopic = (int)FormUtil::getpassedValue('pncategory', 0);
    } else {
        $pntopic = pnVarCleanFromInput('pntopic');
    }

    $forum_name           = utf8_decode($forum_name);           
    $desc                 = utf8_decode($desc);                 
    $pop3_server          = utf8_decode($pop3_server);          
    $pop3_login           = utf8_decode($pop3_login);           
    $pop3_password        = utf8_decode($pop3_password);        
    $pop3_passwordconfirm = utf8_decode($pop3_passwordconfirm); 
    $pop3_matchstring     = utf8_decode($pop3_matchstring);     
    $pnuser               = utf8_decode($pnuser);               
    $pnpassword           = utf8_decode($pnpassword);           
    $pnpasswordconfirm    = utf8_decode($pnpasswordconfirm);    

    $pop3testresulthtml = '';
    if(!empty($delete)) {
        $action = 'delete';
        $newforum = array();
        $forumtitle = '';
        $editforumhtml = '';
        $old_id = $forum_id;
        $cat_id = pnModAPIFunc('pnForum', 'user', 'get_forum_category',
                               array('forum_id' => $forum_id)); 
        // no security check!!!
        pnModAPIFunc('pnForum', 'admin', 'deleteforum',
                     array('forum_id'   => $forum_id,
                           'ok'         => 1 ));
    } else {
        // add or update - the next steps are the same for both
        if($extsource == 2) {
            // store the rss feed in the pop3_server field
            $pop3_server = $rssfeed;
        }

        if($pop3_password <> $pop3_passwordconfirm) {
        	pnf_ajaxerror(_PNFORUM_PASSWORDNOMATCH);
        }
        if($pnpassword <> $pnpasswordconfirm) {
        	pnf_ajaxerror(_PNFORUM_PASSWORDNOMATCH);
        }
        
        if(!empty($add)) {
            $action = 'add';
            $old_id = $forum_id;
            $pop3_password = base64_encode($pop3_password);
            $pnpassword = base64_encode($pnpassword);
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
        } else {
            $action = 'update';
            $old_id = '';
            $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                                  array('forum_id' => $forum_id));
            // check if user has changed the password
            if($forum['pop3_password'] == $pop3_password) {
                // no change necessary
                $pop3_password = "";
            } else {
                $pop3_password = base64_encode($pop3_password);
            }
            
            // check if user has changed the password
            if($forum['pop3_pnpassword'] == $pnpassword) {
                // no change necessary
                $pnpassword = "";
            } else {
                $pnpassword = base64_encode($pnpassword);
            }
             
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
        }
        $editforumhtml = pnForum_admin_editforum(array('forum_id'   => $forum_id,
                                                       'returnhtml' => true));
        $forumtitle = '<a href="' . pnModURL('pnForum', 'user', 'viewforum', array('forum' => $forum_id)) .'">' . $forum_name . '</a> (' . $forum_id . ')';
        // re-read forum data 
        $newforum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                              array('forum_id'  => $forum_id,
                                    'permcheck' => 'nocheck'));
        if($pop3_test==1) {
            $pop3testresult = pnModAPIFunc('pnForum', 'user', 'testpop3connection',
                                           array('forum_id' => $forum_id));
            $pnr =& new pnRender('pnForum');
            $pnr->caching = false;
            $pnr->add_core_data();
            $pnr->assign('messages', $pop3testresult);
            $pnr->assign('forum_id', $forum_id);
            $pop3testresulthtml = $pnr->fetch('pnforum_admin_pop3test.html');
        }
    } 
      
    pnf_jsonizeoutput(array('action'         => $action,
                            'forum'          => $newforum,
                            'cat_id'         => $cat_id,
                            'old_id'         => $old_id,
                            'forumtitle'     => $forumtitle,
                            'pop3resulthtml' => $pop3testresulthtml,
                            'editforumhtml'  => $editforumhtml),
                      true);
}

/**
 * managesubscriptions
 *
 */
function pnForum_admin_managesubscriptions()
{
    if (!pnSecAuthAction(0, 'pnForum::', "::", ACCESS_ADMIN)) {
    	return showforumerror(_PNFORUM_NOAUTH_TOADMIN, __FILE__, __LINE__);
    }

    list($submit, $pnusername) = pnVarCleanFromInput('submit', 'pnusername');
    
    if(!empty($pnusername)) {
        $pnuid = pnUserGetIDFromName($pnusername);
        if(!empty($pnuid)) {
            $topicsubscriptions = pnModAPIFunc('pnForum', 'user', 'get_topic_subscriptions', array('user_id' => $pnuid));
            $forumsubscriptions = pnModAPIFunc('pnForum', 'user', 'get_forum_subscriptions', array('user_id' => $pnuid));
        }
    }
    if(!$submit) {
        // submit is empty
        $pnr = new pnRender('pnForum');
        $pnr->caching = false;
        $pnr->add_core_data();
        $pnr->assign('pnusername', $pnusername);
        $pnr->assign('pnuid', $pnuid);
        $pnr->assign('topicsubscriptions', $topicsubscriptions);
        $pnr->assign('forumsubscriptions', $forumsubscriptions);
        
        return $pnr->fetch('pnforum_admin_managesubscriptions.html');
    } else {  // submit not empty
        list($pnuid, $allforums, $forum_ids, $alltopics, $topic_ids) = pnVarCleanFromInput('pnuid', 'allforum', 'forum_id', 'alltopic', 'topic_id');
        if($allforums == '1') {
            pnModAPIFunc('pnForum', 'user', 'unsubscribe_forum', array('user_id' => $pnuid));
        } elseif(count($forum_ids) > 0) {
            for($i=0; $i<count($forum_ids); $i++) {
                pnModAPIFunc('pnForum', 'user', 'unsubscribe_forum', array('user_id' => $pnuid, 'forum_id' => $forum_ids[$i]));
            }
        }

        if($alltopics == '1') {
            pnModAPIFunc('pnForum', 'user', 'unsubscribe_topic', array('user_id' => $pnuid));
        } elseif(count($topic_ids) > 0) {
            for($i=0; $i<count($topic_ids); $i++) {
                pnModAPIFunc('pnForum', 'user', 'unsubscribe_topic', array('user_id' => $pnuid, 'topic_id' => $topic_ids[$i]));
            }
        }
    }
    return pnRedirect(pnModURL('pnForum', 'admin', 'managesubscriptions', array('pnusername' => pnUserGetVar('uname', $pnuid))));
}

?>