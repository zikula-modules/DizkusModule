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
    
    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 

    $categories = pnModAPIFunc('pnForum', 'admin', 'readcategories');
    $forums = pnModAPIFunc('pnForum', 'admin', 'readforums');
    $pnr =& new pnRender("pnForum");
    $pnr->caching = false;
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

    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
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
        if (pnModGetVar('pnForum', 'show_html') == "yes") {
        	$htmlonchecked = $checked;
        	$htmloffchecked = " ";
        } else {
        	$htmlonchecked = " ";
        	$htmloffchecked = $checked;
        }
        if (pnModGetVar('pnForum', 'show_bbcode') == "yes") {
        	$bbcodeonchecked = $checked;
        	$bbcodeoffchecked = " ";
        } else {
        	$bbcodeonchecked = " ";
        	$bbcodeoffchecked = $checked;
        }
        if (pnModGetVar('pnForum', 'show_smile') == "yes") {
        	$smileonchecked = $checked;
        	$smileoffchecked = " ";
        } else {
        	$smileonchecked = " ";
        	$smileoffchecked = $checked;
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
        $pnr =& new pnRender("pnForum");
        $pnr->cachung = false;
        $pnr->assign('signature_start', stripslashes(pnModGetVar('pnForum', 'signature_start')));
        $pnr->assign('signature_end', stripslashes(pnModGetVar('pnForum', 'signature_end')));
	    $pnr->assign('min_postings_for_anchor', pnModGetVar('pnForum', 'min_postings_for_anchor'));
	    $pnr->assign('topics_per_page', pnModGetVar('pnForum', 'topics_per_page'));
	    $pnr->assign('posts_per_page', pnModGetVar('pnForum', 'posts_per_page'));
	    $pnr->assign('hot_threshold', pnModGetVar('pnForum', 'hot_threshold'));
	    $pnr->assign('email_from', pnModGetVar('pnForum', 'email_from'));
	    $pnr->assign('default_lang', pnModGetVar('pnForum', 'default_lang'));
        $pnr->assign('url_smiles', pnModGetVar('pnForum', 'url_smiles'));
        $pnr->assign('url_ranks_images', pnModGetVar('pnForum', 'url_ranks_images'));
        $pnr->assign('posticon', pnModGetVar('pnForum', 'posticon'));
        $pnr->assign('firstnew_image', pnModGetVar('pnForum', 'firstnew_image'));
        $pnr->assign('folder_image', pnModGetVar('pnForum', 'folder_image'));
        $pnr->assign('hot_folder_image', pnModGetVar('pnForum', 'hot_folder_image'));
        $pnr->assign('newposts_image', pnModGetVar('pnForum', 'newposts_image'));
        $pnr->assign('hot_newposts_image', pnModGetVar('pnForum', 'hot_newposts_image'));
        $pnr->assign('locked_image', pnModGetVar('pnForum', 'locked_image'));
        $pnr->assign('post_sort_order_ascchecked', $post_sort_order_ascchecked);
        $pnr->assign('post_sort_order_descchecked', $post_sort_order_descchecked);
        $pnr->assign('htmlonchecked', $htmlonchecked);
        $pnr->assign('htmloffchecked', $htmloffchecked);
        $pnr->assign('bbcodeonchecked', $bbcodeonchecked);
        $pnr->assign('bbcodeoffchecked', $bbcodeoffchecked);
        $pnr->assign('smileonchecked', $smileonchecked);
        $pnr->assign('smileoffchecked', $smileoffchecked);
        $pnr->assign('logiponchecked', $logiponchecked);
        $pnr->assign('logipoffchecked', $logipoffchecked);
        $pnr->assign('slimforumonchecked', $slimforumonchecked);
        $pnr->assign('slimforumoffchecked', $slimforumoffchecked);
        return $pnr->fetch( "pnforum_admin_preferences.html");
    } else { // submit is set
        $actiontype = pnVarCleanfromInput('actiontype');
        if($actiontype=="Save") {
            pnModSetVar('pnForum', 'signature_start', pnVarPrepForStore(pnVarCleanFromInput('signature_start')));
            pnModSetVar('pnForum', 'signature_end', pnVarPrepForStore(pnVarCleanFromInput('signature_end')));
            pnModSetVar('pnForum', 'min_postings_for_anchor', pnVarPrepForStore(pnVarCleanFromInput('min_postings_for_anchor')));
            pnModSetVar('pnForum', 'topics_per_page', pnVarPrepForStore(pnVarCleanFromInput('topics_per_page')));
            pnModSetVar('pnForum', 'posts_per_page', pnVarPrepForStore(pnVarCleanFromInput('posts_per_page')));
            pnModSetVar('pnForum', 'hot_threshold', pnVarPrepForStore(pnVarCleanFromInput('hot_threshold')));
            pnModSetVar('pnForum', 'email_from', pnVarPrepForStore(pnVarCleanFromInput('email_from')));
            pnModSetVar('pnForum', 'default_lang', pnVarPrepForStore(pnVarCleanFromInput('default_lang')));
            pnModSetVar('pnForum', 'url_smiles', pnVarPrepForStore(pnVarCleanFromInput('url_smiles')));
            pnModSetVar('pnForum', 'url_ranks_images', pnVarPrepForStore(pnVarCleanFromInput('url_ranks_images')));
            pnModSetVar('pnForum', 'posticon', pnVarPrepForStore(pnVarCleanFromInput('posticon')));
            pnModSetVar('pnForum', 'firstnew_image', pnVarPrepForStore(pnVarCleanFromInput('firstnew_image')));
            pnModSetVar('pnForum', 'folder_image', pnVarPrepForStore(pnVarCleanFromInput('folder_image')));
            pnModSetVar('pnForum', 'hot_folder_image', pnVarPrepForStore(pnVarCleanFromInput('hot_folder_image')));
            pnModSetVar('pnForum', 'newposts_image', pnVarPrepForStore(pnVarCleanFromInput('newposts_image')));
            pnModSetVar('pnForum', 'hot_newposts_image', pnVarPrepForStore(pnVarCleanFromInput('hot_newposts_image')));
            pnModSetVar('pnForum', 'locked_image', pnVarPrepForStore(pnVarCleanFromInput('locked_image')));
            pnModSetVar('pnForum', 'post_sort_order', pnVarPrepForStore(pnVarCleanFromInput('post_sort_order')));
            pnModSetVar('pnForum', 'show_html', pnVarPrepForStore(pnVarCleanFromInput('show_html')));
            pnModSetVar('pnForum', 'show_bbcode', pnVarPrepForStore(pnVarCleanFromInput('show_bbcode')));
            pnModSetVar('pnForum', 'show_smile', pnVarPrepForStore(pnVarCleanFromInput('show_smile')));
            pnModSetVar('pnForum', 'log_ip', pnVarPrepForStore(pnVarCleanFromInput('log_ip')));
            pnModSetVar('pnForum', 'slimforum', pnVarPrepForStore(pnVarCleanFromInput('slimforum')));
        } 
        if($actiontype=="RestoreDefaults")  {
            pnModSetVar('pnForum', 'signature_start', '<div style="border: 1px solid black;">');
            pnModSetVar('pnForum', 'signature_end', '</div>');
		    pnModSetVar('pnForum', 'min_postings_for_anchor', 2);
		    pnModSetVar('pnForum', 'posts_per_page', 15);
		    pnModSetVar('pnForum', 'topics_per_page', 15);
		    pnModSetVar('pnForum', 'hot_threshold', 20);
		    pnModSetVar('pnForum', 'email_from', pnConfigGetVar('adminmail'));
		    pnModSetVar('pnForum', 'default_lang', 'iso-8859-1');
		    pnModSetVar('pnForum', 'url_smiles', "modules/pnForum/pnimages/smiles");
		    pnModSetVar('pnForum', 'url_ranks_images', "modules/pnForum/pnimages/ranks");
		    pnModSetVar('pnForum', 'folder_image', "modules/pnForum/pnimages/folder.gif");
		    pnModSetVar('pnForum', 'hot_folder_image', "modules/pnForum/pnimages/hot_folder.gif");
		    pnModSetVar('pnForum', 'newposts_image', "modules/pnForum/pnimages/red_folder.gif");
		    pnModSetVar('pnForum', 'hot_newposts_image', "modules/pnForum/pnimages/hot_red_folder.gif");
		    pnModSetVar('pnForum', 'posticon', "modules/pnForum/pnimages/posticon.gif");
		    pnModSetVar('pnForum', 'profile_image', "modules/pnForum/pnimages/profile.gif");
		    pnModSetVar('pnForum', 'locked_image', "modules/pnForum/pnimages/lock.gif");
		    pnModSetVar('pnForum', 'firstnew_image', "modules/pnForum/pnimages/firstnew.gif");
		    pnModSetVar('pnForum', 'post_sort_order', "ASC");
		    pnModSetVar('pnForum', 'show_html', "yes");
		    pnModSetVar('pnForum', 'show_bbcode', "yes");
		    pnModSetVar('pnForum', 'show_smile', "yes");
		    pnModSetVar('pnForum', 'log_ip', "yes");
		    pnModSetVar('pnForum', 'slimforum', "no");
        }
    }
    pnRedirect(pnModURL('pnForum', 'admin', 'main'));
}

/**
 * syncforums
 * 
 */
function pnForum_admin_syncforums()
{

    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 

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
    pnRedirect(pnModURL('pnForum', 'admin', 'main'));
    return true;
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
    
    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 
    if(!pnModAPILoad('pnForum', 'user')) {
        return showforumerror("loading userapi failed", __FILE__, __LINE__);
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
        pnRedirect(pnModUrl('pnForum', 'admin', 'main'));
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
    
    if(!pnModAPILoad('pnForum', 'admin')) {
        return showforumerror("loading adminapi failed", __FILE__, __LINE__);
    } 

    list($submit, $forum_id) = pnVarCleanFromInput('submit', 'forum_id');
    
    if(!$submit) {
        //
        if($forum_id==-1) {
            $forum = array('forum_name'  => "",
                           'forum_id'    => -1,
                           'forum_desc'  => "",
                           'forum_access'=> -1,
                           'forum_type'  => -1,
                           'forum_order' => -1,
                           'cat_title'   => "",
                           'cat_id'      => -1 );
        } else {
            $forum = pnModAPIFunc('pnForum', 'admin', 'readforums',
                                  array('forum_id' => $forum_id));
        }
        $moderators = pnModAPIFunc('pnForum', 'admin', 'readmoderators',
                                    array('forum_id' => $forum['forum_id']));
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->assign('forum', $forum);
        $pnr->assign('categories', pnModAPIFunc('pnForum', 'admin', 'readcategories'));
        $pnr->assign('moderators', $moderators);
        $pnr->assign('users', pnModAPIFunc('pnForum', 'admin', 'readusers',
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
             $actiontype ) = pnVarCleanFromInput('forum_name',
                                                 'forum_id',
                                                 'cat_id',
                                                 'desc',
                                                 'mods',
                                                 'rem_mods',
                                                 'actiontype'); 
/*
pnfdebug("name", $forum_name);
pnfdebug("desc", $desc);
pnfdebug("fid", $forum_id);
pnfdebug("cid", $cat_id);
pnfdebug("action", $actiontype);
pnfdebug("rem_mods", $rem_mods);
pnfdebug("mods", $mods, true);
*/
        switch($actiontype) {
            case "Add":
                pnModAPIFunc('pnForum', 'admin', 'addforum',
                             array('forum_name' => $forum_name,
                                   'cat_id'     => $cat_id,
                                   'desc'       => $desc,
                                   'mods'       => $mods));
                break;
            case "Edit":
                pnModAPIFunc('pnForum', 'admin', 'editforum',
                             array('forum_name' => $forum_name,
                                   'forum_id'   => $forum_id,
                                   'cat_id'     => $cat_id,
                                   'desc'       => $desc,
                                   'mods'       => $mods,
                                   'rem_mods'   => $rem_mods));
                break;
            case "Delete":
                // no security check!!!
                pnModAPIFunc('pnForum', 'admin', 'deleteforum',
                             array('forum_id'   => $forum_id,
                                   'ok'         => 1 )); 
                break;    
            default:
        }
    }
    pnRedirect(pnModURL('pnForum', 'admin', 'main'));
    return true;
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
    
    if(!pnModAPILoad('pnForum', 'admin')) {
        return "loading adminapi failed";
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
        $pnr->assign('url_ranks_images', pnModGetVar('pnForum', 'url_ranks_images'));
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
    pnRedirect(pnModURL('pnForum','admin', 'ranks', array('ranktype' => $ranktype)));
    return true;
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
    
    if(!pnModAPILoad('pnForum', 'admin')) {
        return "loading adminapi failed";
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
        $pnr->assign('url_ranks_images', pnModGetVar('pnForum', 'url_ranks_images'));
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
    pnRedirect(pnModURL('pnForum','admin', 'assignranks'));
    return true;
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
    
    if(!pnModAPILoad('pnForum', 'admin')) {
        return "loading adminapi failed";
    } 
    list($direction) = pnVarCleanFromInput('direction');

    $categories = pnModAPIFunc('pnForum', 'admin', 'readcategories');

    if(!$direction) {
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
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
    pnRedirect(pnModURL('pnForum', 'admin', 'reordercategories'));
    return true;
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
    
    if(!pnModAPILoad('pnForum', 'admin')) {
        return "loading adminapi failed";
    } 
    list($direction, $cat_id) = pnVarCleanFromInput('direction', 'cat_id');

    if(!empty($cat_id) && is_numeric($cat_id)) {
        $forums = pnModAPIFunc('pnForum', 'admin', 'readforums',
                               array('cat_id' => $cat_id));
        $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                                 array('cat_id' => $cat_id));
    }
    if(!$direction) {
        $pnr =& new pnRender("pnForum");
        $pnr->caching = false;
        $pnr->assign('forums', $forums);
        $pnr->assign('total_forums', count($forums));
        $pnr->assign('category', $category);
        return $pnr->fetch("pnforum_admin_reorderforums.html");
    } else {
        list( $forum_order,
              $forum_id,
              $direction ) = pnVarCleanFromInput('forum_order',
                                                 'forum_id',
                                                 'direction');
        pnModAPIFunc('pnForum', 'admin', 'reorderforumssave',
                     array('cat_id'      => $cat_id,
                           'forum_order' => $forum_order,
                           'forum_id'    => $forum_id,
                           'direction'   => $direction));
    }
    pnRedirect(pnModURL('pnForum', 'admin', 'reorderforums',
                        array('cat_id' => $cat_id)));
    return true;
}

?>