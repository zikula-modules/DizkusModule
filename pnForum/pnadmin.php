<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

Loader::includeOnce('modules/pnForum/common.php');

/**
 * the main administration function
 *
 */
function pnForum_admin_main()
{
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return LogUtil::registerPermissionError();
    }
    $pnr = pnRender::getInstance('pnForum', false, null, true);
    return $pnr->fetch("pnforum_admin_main.html");
}

/**
 * preferences
 *
 */
function pnForum_admin_preferences()
{
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return LogUtil::registerPermissionError();
    }

    $submit = FormUtil::getPassedValue('submit', null, 'GETPOST');

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

        if (pnModGetVar('pnForum', 'forum_enabled') == "yes") {
        	$forumenabled_onchecked = $checked;
        	$forumenabled_offchecked = " ";
        } else {
        	$forumenabled_onchecked = " ";
        	$forumenabled_offchecked = $checked;
        }

        $pnr = pnRender::getInstance('pnForum', false, null, true);
        $pnr->assign('autosubscribe', $autosubscribechecked);
        $pnr->assign('signature_start', stripslashes(pnModGetVar('pnForum', 'signature_start')));
        $pnr->assign('signature_end', stripslashes(pnModGetVar('pnForum', 'signature_end')));
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
        $pnr->assign('forumenabled_onchecked',  $forumenabled_onchecked);
        $pnr->assign('forumenabled_offchecked', $forumenabled_offchecked);
        return $pnr->fetch( "pnforum_admin_preferences.html");
    } else { // submit is set
        $actiontype = FormUtil::getPassedValue('actiontype', 'Save', 'GETPOST');
        if($actiontype=="Save") {
            pnModSetVar('pnForum', 'forum_enabled', DataUtil::formatForStore(FormUtil::getPassedValue('forum_enabled')));
            pnModSetVar('pnForum', 'forum_disabled_info', DataUtil::formatForStore(FormUtil::getPassedValue('forum_disabled_info')));
            pnModSetVar('pnForum', 'newtopicconfirmation', DataUtil::formatForStore(FormUtil::getPassedValue('newtopicconfirmation')));
            pnModSetVar('pnForum', 'rss2f_enabled', DataUtil::formatForStore(FormUtil::getPassedValue('rss2f_enabled')));
            pnModSetVar('pnForum', 'deletehookaction', DataUtil::formatForStore(FormUtil::getPassedValue('deletehookaction')));
            pnModSetVar('pnForum', 'striptags', DataUtil::formatForStore(FormUtil::getPassedValue('striptags')));
            pnModSetVar('pnForum', 'removesignature', DataUtil::formatForStore(FormUtil::getPassedValue('removesignature')));
            pnModSetVar('pnForum', 'hideusers', DataUtil::formatForStore(FormUtil::getPassedValue('hideusers')));
            pnModSetVar('pnForum', 'favorites_enabled', DataUtil::formatForStore(FormUtil::getPassedValue('favorites_enabled')));
            pnModSetVar('pnForum', 'm2f_enabled', DataUtil::formatForStore(FormUtil::getPassedValue('m2f_enabled')));
            pnModSetVar('pnForum', 'autosubscribe', DataUtil::formatForStore(FormUtil::getPassedValue('autosubscribe')));
            pnModSetVar('pnForum', 'signature_start', DataUtil::formatForStore(FormUtil::getPassedValue('signature_start')));
            pnModSetVar('pnForum', 'signature_end', DataUtil::formatForStore(FormUtil::getPassedValue('signature_end')));

            $topics_per_page = (int)FormUtil::getPassedValue('topics_per_page');
            if(empty($topics_per_page) || $topics_per_page<0) {
                $topics_per_page = 15;
            }
            pnModSetVar('pnForum', 'topics_per_page', DataUtil::formatForStore($topics_per_page));
            
            $posts_per_page = (int)FormUtil::getPassedValue('posts_per_page');
            if(empty($posts_per_page) || $posts_per_page<0) {
                $posts_per_page = 15;
            }
            pnModSetVar('pnForum', 'posts_per_page', DataUtil::formatForStore($posts_per_page));

            $hot_threshold = (int)FormUtil::getPassedValue('hot_threshold');
            if(empty($hot_threshold) || $hot_threshold<0) {
                $hot_threshold = 20;
            }
            pnModSetVar('pnForum', 'hot_threshold', DataUtil::formatForStore($hot_threshold));
            
            $email_from = FormUtil::getPassedValue('email_from');
            if(empty($email_from) || !pnVarValidate($email_from, 'email')) {
                $email_from = pnConfigGetVar('adminmail');
            }
            pnModSetVar('pnForum', 'email_from', DataUtil::formatForStore($email_from));
            
            $default_lang = FormUtil::getPassedValue('default_lang');
            if(empty($default_lang)) {
                $default_lang = _CHARSET;
            }
            pnModSetVar('pnForum', 'default_lang', DataUtil::formatForStore($default_lang));

            pnModSetVar('pnForum', 'url_ranks_images', DataUtil::formatForStore(FormUtil::getPassedValue('url_ranks_images')));
            pnModSetVar('pnForum', 'posticon', DataUtil::formatForStore(FormUtil::getPassedValue('posticon')));
            pnModSetVar('pnForum', 'firstnew_image', DataUtil::formatForStore(FormUtil::getPassedValue('firstnew_image')));
            pnModSetVar('pnForum', 'post_sort_order', DataUtil::formatForStore(FormUtil::getPassedValue('post_sort_order')));
            pnModSetVar('pnForum', 'log_ip', DataUtil::formatForStore(FormUtil::getPassedValue('log_ip')));
            pnModSetVar('pnForum', 'slimforum', DataUtil::formatForStore(FormUtil::getPassedValue('slimforum')));
            $timespanforchanges = (int)FormUtil::getPassedValue('timespanforchanges');
            if(empty($timespanforchanges) || $timespanforchanges<0) {
                $timespanforchanges = 24;
            }
            pnModSetVar('pnForum', 'timespanforchanges', DataUtil::formatForStore($timespanforchanges));
        }
        if($actiontype=="RestoreDefaults")  {
            pnModSetVar('pnForum', 'forum_enabled', 'yes');
            pnModSetVar('pnForum', 'forum_disabled_info', _PNFORUM_DISABLED_INFO);
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
		    pnModSetVar('pnForum', 'timespanforchanges', 12);
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
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return LogUtil::registerPermissionError();
    }

    $submit = FormUtil::getPassedValue('submit');

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
        $pnr = pnRender::getInstance('pnForum', false, null, true);
        $pnr->assign('dbversion', $dbversion);
        $pnr->assign('dbtype', $dbconn->databaseType);
        $pnr->assign('dbname', $dbconn->databaseName);
        $pnr->assign('fulltextindex_checked', $fulltextindex_checked);
        $pnr->assign('extendedsearch_checked', $extendedsearch_checked);
        return $pnr->fetch( "pnforum_admin_advancedpreferences.html");
    } else { // submit is set
        pnModSetVar('pnForum', 'fulltextindex', DataUtil::formatForStore(FormUtil::getPassedValue('fulltextindex')));
        pnModSetVar('pnForum', 'extendedsearch', DataUtil::formatForStore(FormUtil::getPassedValue('extendedsearch')));
    }
    return pnRedirect(pnModURL('pnForum', 'admin', 'main'));
}

/**
 * syncforums
 *
 */
function pnForum_admin_syncforums()
{
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return LogUtil::registerPermissionError();
    }
    $silent = FormUtil::getPassedValue('silent');

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "users"));
	$message = DataUtil::formatForDisplay(_PNFORUM_SYNC_USERS) . "<br />";

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "all forums"));
	$message .= DataUtil::formatForDisplay(_PNFORUM_SYNC_FORUMINDEX) . "<br />";

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "all topics"));
	$message .= DataUtil::formatForDisplay(_PNFORUM_SYNC_TOPICS) . "<br />";

	pnModAPIFunc('pnForum', 'admin', 'sync',
	             array( 'id'   => NULL,
	                    'type' => "all posts"));
	$message .= DataUtil::formatForDisplay(_PNFORUM_SYNC_POSTSCOUNT) . "<br />";

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
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return LogUtil::registerPermissionError();
    }

    list($submit, $ranktype) = pnVarCleanFromInput('submit', 'ranktype');

    if(!is_numeric($ranktype)) {
        return _MODARGSERROR;
    }

    list($rankimages, $ranks) = pnModAPIFunc('pnForum', 'admin', 'readranks',
                                             array('ranktype' => $ranktype));

    if(!$submit) {
        $pnr = pnRender::getInstance('pnForum', false, null, true);
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
                                                            'rank_special' => $ranktype,
                                                            'rank_id'      => $rank_id,
                                                            'rank_title'   => $title,
                                                            'rank_min'     => $min_posts,
                                                            'rank_max'     => $max_posts,
                                                            'rank_image'   => $image));
    }
    return pnRedirect(pnModURL('pnForum','admin', 'ranks', array('ranktype' => $ranktype)));
}

/**
 * ranks
 *
 */
function pnForum_admin_assignranks()
{
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return LogUtil::registerPermissionError();
    }

    list($submit, $letter, $lastletter) = pnVarCleanFromInput('submit', 'letter', 'lastletter');
    // check for a letter parameter
    if(!empty($lastletter)) {
        $letter = $lastletter;
    }
    if (empty($letter) || strlen($letter) != 1) {
        $letter = 'A';
    }

    if(!$submit) {
        // sync the users, so that new pn users get into the pnForum
        // database
        pnModAPIFunc('pnForum', 'user', 'usersync');
    
        list($rankimages, $ranks) = pnModAPIFunc('pnForum', 'admin', 'readranks',
                                                 array('ranktype' => 1));

        // remove the first rank, its used for adding new ranks only
        array_splice($ranks, 0, 1);
    
        switch($letter) {
            case '?':
                // read usernames beginning with special chars
                $regexpfield = 'uname';
                $regexpression = '^[[:punct:][:digit:]]';
                break;
            case '*':
                // read allusers
                $regexpfield = '';
                $regexpression = '';
                break;
            default:
                $regexpfield = 'uname';
                $regexpression = '^' . $letter;
        }
        $users = pnUserGetAll('uname', 'ASC', 0, 1, '', $regexpfield, $regexpression);
    
        $allusers = array();
        foreach ($users as $user) {
            if ($user['uname'] == 'Anonymous')  continue;
            
            $alias = '';
            if (!empty($user['name'])) {
                $alias = ' (' . $user['name'] . ')';
            }
            
            $user['name'] = $user['uname'] . $alias;
    
            $user['rank_id'] = 0;
            for($cnt=0; $cnt<count($ranks); $cnt++) {
                if(in_array($user['uid'], $ranks[$cnt]['users'])) {
                    $user['rank_id'] = $ranks[$cnt]['rank_id'];
                }
            }
            array_push($allusers, $user);
        }
        usort($allusers, 'cmp_userorder');
        
        unset($users);

        $pnr = pnRender::getInstance('pnForum', false, null, true);
        $pnr->assign('ranks', $ranks);
        $pnr->assign('rankimages', $rankimages);
        $pnr->assign('allusers', $allusers);
        $pnr->assign('letter', $letter);
        return $pnr->fetch("pnforum_admin_assignranks.html");
    } else {
        $setrank = FormUtil::getPassedValue('setrank');
        pnModAPIFunc('pnForum', 'admin', 'assignranksave', 
                     array('setrank' => $setrank));
    }
    return pnRedirect(pnModURL('pnForum','admin', 'assignranks',
                               array('letter' => $letter)));
}


/** 
 * reordertree
 *
 */
function pnForum_admin_reordertree()
{
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return LogUtil::registerPermissionError();
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
    $pnr = pnRender::getInstance('pnForum', false, null, true);
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
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
        pnf_ajaxerror(_PNFORUM_NOAUTH_TOADMIN);
    }

    SessionUtil::setVar('pn_ajax_call', 'ajax');
    
    if(!SecurityUtil::confirmAuthKey()) {
//        pnf_ajaxerror(_BADAUTHKEY);
    }
    
    $categoryarray = FormUtil::getPassedValue('category');
    // the last entry in the $category is the placeholder for a new
    // category, we need to remove this
    // not used any longer: array_pop($categoryarray);
    if(is_array($categoryarray) && count($categoryarray) > 0) {
        foreach($categoryarray as $catorder => $cat_id) {
            // array key = catorder starts with 0, but we need 1, so we increase the order
            // value
            $catorder++;
            if(pnModAPIFunc('pnForum', 'admin', 'updatecategory',
                                              array('cat_id'    => $cat_id,
                                                    'cat_order' => $catorder)) == false) {
                pnf_ajaxerror('updatecategory(): cannot reorder category ' . $cat_id . ' (' . $catorder . ')');
            }

            $forumsincategoryarray = FormUtil::getPassedValue('cid_' . $cat_id);
            // last two item in the array or for internal purposes in the template
            // we do not need them, in fact they lead to errors when we
            // do not remove them
            array_pop($forumsincategoryarray);
            array_pop($forumsincategoryarray);

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
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
        pnf_ajaxerror(_PNFORUM_NOAUTH_TOADMIN);
    }
    
    if(count($args)>0) {
        extract($args);
        // forum_id, returnhtml
    } else {
        $forum_id = (int)FormUtil::getPassedValue('forum', null, 'GETPOST');
    }
     
    if(!isset($forum_id)) {
        pnf_ajaxerror(_MODARGSERROR . ': forum_id ' . DataUtil::formatForDisplay($forum_id) . ' in pnForum_admin_editforum()');
    }
    
    if($forum_id == -1) {
        // create a new forum 
        $new = true;
        $cat_id = FormUtil::getPassedValue('cat');
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
                                    'permcheck' => ACCESS_ADMIN));

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
                                               'ok'       => (pnModAvailable('Feeds')==true) ? '' : _PNFORUM_RSSMODULENOTAVAILABLE,
                                               'extended' => true)); // rss
    $externalsourceoptions[$forum['forum_pop3_active']]['checked'] = ' checked="checked"';
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
    if(pnModAvailable('Feeds')) {
        $rssfeeds = pnModAPIFunc('Feeds', 'user', 'getall');
    }

    $moderators = pnModAPIFunc('pnForum', 'admin', 'readmoderators',
                                    array('forum_id' => $forum['forum_id']));


    $pnr = pnRender::getInstance('pnForum', false, null, true);
    $pnr->assign('hooked_modules', $hooked_modules);
    $pnr->assign('rssfeeds', $rssfeeds);
    $pnr->assign('externalsourceoptions', $externalsourceoptions);
    
    Loader::loadClass('CategoryUtil');
    $cats        = CategoryUtil::getSubCategories (1, true, true, true, true, true);
    $catselector = CategoryUtil::getSelector_Categories($cats, $forum['forum_pntopic'], 'pncategory');
    $pnr->assign('categoryselector', $catselector);        
    
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
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	pnf_ajaxerror(_PNFORUM_NOAUTH_TOADMIN);
    }

    if(!empty($args)) {
        extract($args);
        $cat_id = $cat;
    } else {
        $cat_id = FormUtil::getPassedValue('cat');
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
    $pnr = pnRender::getInstance('pnForum', false, null, true);
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
    pnSessionSetVar('pn_ajax_call', 'ajax');

    if (!SecurityUtil::checkPermission('pnForum::', '::', ACCESS_ADMIN)) {
    	pnf_ajaxerror(_PNFORUM_NOAUTH_TOADMIN);
    }
    
    if(!SecurityUtil::confirmAuthKey()) {
        pnf_ajaxerror(_BADAUTHKEY);
    }

    list($cat_id, 
         $cat_title, 
         $add,
         $delete) = pnVarCleanFromInput('cat_id', 
                                        'cat_title', 
                                        'add',
                                        'delete');

    $cat_title = DataUtil::convertFromUTF8($cat_title);
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
                              false); 
        } else {
            pnf_ajaxerror('error deleting category ' . DataUtil::formatForDisplay($cat_id));
        }
        
    } else if(!empty($add)) {
        $original_catid = $cat_id;
        $cat_id = pnModAPIFunc('pnForum', 'admin', 'addcategory',
                               array('cat_title' => $cat_title));
        if(!is_bool($cat_id)) {
            $category = pnModAPIFunc('pnForum', 'admin', 'readcategories',
                                     array( 'cat_id' => $cat_id ));
            $pnr = pnRender::getInstance('pnForum', false, null, true);
            $pnr->assign('category', $category );
            $pnr->assign('newcategory', false);
            pnf_jsonizeoutput(array('cat_id'      => $cat_id,
                                    'old_id'      => $original_catid,
                                    'cat_title'   => $cat_title,
                                    'action'      => 'add',
                                    'edithtml'    => $pnr->fetch('pnforum_ajax_editcategory.html'),
                                    'cat_linkurl' => pnModURL('pnForum', 'user', 'main', array('viewcat' => $cat_id))),
                              true,
                              false); 
        } else {
            pnf_ajaxerror('error creating category "' . DataUtil::formatForDisplay($cat_title) . '"');
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
                              false); 
        } else {
            pnf_ajaxerror('error updating cat_id ' . DataUtil::formatForDisplay($cat_id) . ' with title "' . DataUtil::formatForDisplay($cat_title) . '"');
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
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	pnf_ajaxerror(_PNFORUM_NOAUTH_TOADMIN);
    }

    if(!SecurityUtil::confirmAuthKey()) {
        pnf_ajaxerror(_BADAUTHKEY);
    }

    SessionUtil::setVar('pn_ajax_call', 'ajax');
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

    $pntopic = (int)FormUtil::getpassedValue('pncategory', 0);

    $forum_name           = DataUtil::convertFromUTF8($forum_name);           
    $desc                 = DataUtil::convertFromUTF8($desc);                 
    $pop3_server          = DataUtil::convertFromUTF8($pop3_server);          
    $pop3_login           = DataUtil::convertFromUTF8($pop3_login);           
    $pop3_password        = DataUtil::convertFromUTF8($pop3_password);        
    $pop3_passwordconfirm = DataUtil::convertFromUTF8($pop3_passwordconfirm); 
    $pop3_matchstring     = DataUtil::convertFromUTF8($pop3_matchstring);     
    $pnuser               = DataUtil::convertFromUTF8($pnuser);               
    $pnpassword           = DataUtil::convertFromUTF8($pnpassword);           
    $pnpasswordconfirm    = DataUtil::convertFromUTF8($pnpasswordconfirm);    

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
                     array('forum_id'   => $forum_id));
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
            $pnr = pnRender::getInstance('pnForum', false, null, true);
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
    if (!SecurityUtil::checkPermission('pnForum::', "::", ACCESS_ADMIN)) {
    	return LogUtil::registerPermissionError();
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
        $pnr = pnRender::getInstance('pnForum', false, null, true);
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
