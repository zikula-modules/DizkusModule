<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

Loader::includeOnce('modules/Dizkus/common.php');

/**
 * the main administration function
 *
 */
function Dizkus_admin_main()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');
    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }
    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    return $pnr->fetch('dizkus_admin_main.html');
}

/**
 * preferences
 *
 */
function Dizkus_admin_preferences()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

	  // Load handler class
    Loader::requireOnce('modules/Dizkus/pnincludes/dizkus_admin_prefshandler.class.php');

    // Create output object
    $pnf = FormUtil::newpnForm('Dizkus');

    // Return the output that has been generated by this function
    return $pnf->pnFormExecute('dizkus_admin_preferences.html', new dizkus_admin_prefshandler());
}

/**
 * syncforums
 *
 */
function Dizkus_admin_syncforums()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }
    $silent = FormUtil::getPassedValue('silent', 0);

    pnModAPIFunc('Dizkus', 'admin', 'sync',
                 array('type' => 'all users'));
    $message = DataUtil::formatForDisplay(__('Done! Synchronised Zikula and Dizkus users.', $dom)) . '<br />';

    pnModAPIFunc('Dizkus', 'admin', 'sync',
                 array('type' => 'all forums'));
    $message .= DataUtil::formatForDisplay(__('Done! Synchronised forum index.', $dom)) . '<br />';

    pnModAPIFunc('Dizkus', 'admin', 'sync',
                 array('type' => 'all topics'));
    $message .= DataUtil::formatForDisplay(__('Done! Synchronised topics.', $dom)) . '<br />';

    pnModAPIFunc('Dizkus', 'admin', 'sync',
                 array('type' => 'all posts'));
    $message .= DataUtil::formatForDisplay(__('Done! Synchronised posts counter.', $dom)) . '<br />';

    if ($silent != 1) {
        LogUtil::registerStatus($message);
    }

    return pnRedirect(pnModURL('Dizkus', 'admin', 'main'));
}

/**
 * ranks
 *
 */
function Dizkus_admin_ranks()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $submit   = FormUtil::getPassedValue('submit', null, 'GETPOST');
    $ranktype = (int)FormUtil::getPassedValue('ranktype', 0, 'GETPOST');

    if (!$submit) {
        list($rankimages, $ranks) = pnModAPIFunc('Dizkus', 'admin', 'readranks',
                                                  array('ranktype' => $ranktype));

        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('ranks', $ranks);
        $pnr->assign('ranktype', $ranktype);
        $pnr->assign('rankimages', $rankimages);
        if ($ranktype == 0) {
            return $pnr->fetch('dizkus_admin_ranks.html');
        } else {
            return $pnr->fetch('dizkus_admin_honoraryranks.html');
        }
    } else {
        $ranks = FormUtil::getPassedValue('ranks');
        pnModAPIFunc('Dizkus', 'admin', 'saverank', array('ranks' => $ranks));
    }
    return pnRedirect(pnModURL('Dizkus','admin', 'ranks', array('ranktype' => $ranktype)));
}

/**
 * ranks
 *
 */
function Dizkus_admin_assignranks()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $submit     = FormUtil::getPassedValue('submit');
    $letter     = FormUtil::getPassedValue('letter');
    $lastletter = FormUtil::getPassedValue('lastletter');
    $page       = (int)FormUtil::getPassedValue('page', 1, 'GETPOST');

    // sync the current user, so that new users
    // get into the Dizkus database
    pnModAPIFunc('Dizkus', 'admin', 'sync', array('type' => 'all users')); 

    // check for a letter parameter
    if(!empty($lastletter)) {
        $letter = $lastletter;
    }

    // count users and forbid '*' if more than 1000 users are present
    $usercount = DBUtil::selectObjectCount('users');
    if (empty($letter) || strlen($letter) != 1 || (($usercount > 1000) && $letter == '*')) {
        $letter = 'a';
    }
    $letter = strtolower($letter);

    if (!$submit) {

        list($rankimages, $ranks) = pnModAPIFunc('Dizkus', 'admin', 'readranks',
                                                 array('ranktype' => 1));

        $tables = pnDBGetTables();

        $userscol  = $tables['users_column'];
        $where     = 'LEFT('.$userscol['uname'].',1) LIKE \''.DataUtil::formatForStore($letter).'%\'';
        $orderby   = $userscol['uname'].' ASC';
        $usercount = DBUtil::selectObjectCount('users', $where);

        $perpage = 50;
        if ($page <> -1 && $perpage <> -1) {
            $start = ($page-1) * $perpage;
            $users = DBUtil::selectObjectArray('users', $where, $orderby, $start, $perpage);
        }

        $allusers = array();
        foreach ($users as $user) {
            if ($user['uid'] == 1)  continue;

            $alias = '';
            if (!empty($user['name'])) {
                $alias = ' (' . $user['name'] . ')';
            }

            $user['name'] = $user['uname'] . $alias;

            $user['rank_id'] = 0;
            for ($cnt = 0; $cnt < count($ranks); $cnt++) {
                if (in_array($user['uid'], $ranks[$cnt]['users'])) {
                    $user['rank_id'] = $ranks[$cnt]['rank_id'];
                }
            }
            array_push($allusers, $user);
        }
/*
        $inlinecss = '<style type="text/css">' ."\n";
        $rankpath = pnModGetVar('Dizkus', 'url_ranks_images') .'/';
        foreach ($ranks as $rank) {
            $inlinecss .= '#dizkus_admin option[value='.$rank['rank_id'].']:before { content:url("'.pnGetBaseURL() . $rankpath . $rank['rank_image'].'"); }' . "\n";
        }
        $inlinecss .= '</style>' . "\n";
        PageUtil::addVar('rawtext', $inlinecss);
*/        
        //usort($allusers, 'cmp_userorder');

        unset($users);

        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('ranks', $ranks);
        $pnr->assign('rankimages', $rankimages);
        $pnr->assign('allusers', $allusers);
        $pnr->assign('letter', $letter);
        $pnr->assign('page', $page);
        $pnr->assign('perpage', $perpage);
        $pnr->assign('usercount', $usercount);
        $pnr->assign('allow_star', ($usercount < 1000));
        return $pnr->fetch('dizkus_admin_assignranks.html');

    } else {
        // avoid some vars in the url of the pager
        unset($_GET['submit']);
        unset($_POST['submit']);
        unset($_REQUEST['submit']);
        $setrank = FormUtil::getPassedValue('setrank');
        pnModAPIFunc('Dizkus', 'admin', 'assignranksave', 
                     array('setrank' => $setrank));
    }

    return pnRedirect(pnModURL('Dizkus','admin', 'assignranks',
                               array('letter' => $letter,
                                     'page'   => $page)));
}

/** 
 * reordertree
 *
 */
function Dizkus_admin_reordertree()
{
     $dom = ZLanguage::getModuleDomain('Dizkus');

   if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $categorytree = pnModAPIFunc('Dizkus', 'user', 'readcategorytree');
    $catids = array();
    $forumids = array();
    if (is_array($categorytree) && count($categorytree) > 0) {
        foreach ($categorytree as $category) {
            $catids[] = $category['cat_id'];
            if (is_array($category['forums']) && count($category['forums']) > 0) {
                foreach ($category['forums'] as $forum) {
                    $forumids[] = $forum['forum_id'];
                }
            }
        }
    }
    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign('categorytree', $categorytree);
    $pnr->assign('catids', $catids);
    $pnr->assign('forumids', $forumids);
    return $pnr->fetch('dizkus_admin_reordertree.html');
}

/**
 * reordertreesave
 *
 * AJAX result function
 *
 */
function Dizkus_admin_reordertreesave()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        dzk_ajaxerror(__('Sorry! You have not been granted authorisation for this module.', $dom));
    }

    SessionUtil::setVar('pn_ajax_call', 'ajax');

    if (!SecurityUtil::confirmAuthKey()) {
//        dzk_ajaxerror(__('Sorry! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.', $dom));
    }

    $categoryarray = FormUtil::getPassedValue('category');
    // the last entry in the $category is the placeholder for a new
    // category, we need to remove this
    // not used any longer: array_pop($categoryarray);
    if (is_array($categoryarray) && count($categoryarray) > 0) {
        foreach ($categoryarray as $catorder => $cat_id) {
            // array key = catorder starts with 0, but we need 1, so we increase the order
            // value
            $catorder++;
            if (pnModAPIFunc('Dizkus', 'admin', 'updatecategory',
                             array('cat_id'    => $cat_id,
                                   'cat_order' => $catorder)) == false) {
                dzk_ajaxerror('updatecategory(): cannot reorder category ' . $cat_id . ' (' . $catorder . ')');
            }

            $forumsincategoryarray = FormUtil::getPassedValue('cid_' . $cat_id);
            // last two item in the array or for internal purposes in the template
            // we do not need them, in fact they lead to errors when we
            // do not remove them
            array_pop($forumsincategoryarray);
            array_pop($forumsincategoryarray);

            if (is_array($forumsincategoryarray) && count($forumsincategoryarray) > 0) {
                foreach ($forumsincategoryarray as $forumorder => $forum_id) {
                    if (!empty($forum_id) && is_numeric($forum_id)) {
                        // array key start with 0, but we need 1, so we increase the order
                        // value
                        $forumorder++;
                        if (pnModAPIFunc('Dizkus', 'admin', 'storenewforumorder',
                                         array('forum_id' => $forum_id,
                                               'cat_id'   => $cat_id,
                                               'order'    => $forumorder)) == false) {
                            dzk_ajaxerror('Error! \'storenewforumorder()\' cannot re-order the ' . $forum_id . ' forum in the ' . $cat_id . ' category (' . $forumorder . ').');
                        }
                    }
                }
            }
        }
    }
    dzk_jsonizeoutput('', true, true);

}

/**
 * editforum
 *
 * AJAX function
 *
 */
function Dizkus_admin_editforum($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        dzk_ajaxerror(__('Sorry! You do not have authorisation to administer this module.', $dom));
    }

    if (count($args) > 0) {
        extract($args);
        // forum_id, returnhtml
    } else {
        $forum_id = (int)FormUtil::getPassedValue('forum', null, 'GETPOST');
    }

    if (!isset($forum_id)) {
        dzk_ajaxerror(_MODARGSERROR . ': forum_id ' . DataUtil::formatForDisplay($forum_id) . ' in Dizkus_admin_editforum()');
    }

    if ($forum_id == -1) {
        // create a new forum
        $new = true;
        $cat_id = FormUtil::getPassedValue('cat');
        $forum = array('forum_name'       => __('-- Create new forum --', $dom),
                       'forum_id'         => time(), /* for new forums only! */
                       'forum_desc'       => '',
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
                       'forum_pntopic'    => 0,
                       'externalsource'   => 0);
    } else {
        // we are editing
        $new = false;
        $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                              array('forum_id'  => $forum_id,
                                    'permcheck' => ACCESS_ADMIN));

    }
    $externalsourceoptions = array( 0 => array('checked'  => '',
                                               'name'     => __('No external source', $dom),
                                               'ok'       => '',
                                               'extended' => false),   // none
                                    1 => array('checked'  => '',
                                               'name'     => __('Mail2Forum', $dom),
                                               'ok'       => '',
                                               'extended' => true),  // mail
                                    2 => array('checked'  => '',
                                               'name'     => __('RSS2Forum', $dom),
                                               'ok'       => (pnModAvailable('Feeds') == true) ? '' : __('<span style="color: red;">Feeds module not available.</span>', $dom),
                                               'extended' => true)); // rss

    $externalsourceoptions[$forum['pop3_active']]['checked'] = ' checked="checked"';

    $hooked_modules_raw = pnModAPIFunc('modules', 'admin', 'gethookedmodules',
                                       array('hookmodname' => 'Dizkus'));

    $hooked_modules = array(array('name' => __('No hooked module found.', $dom),
                                  'id'   => 0));

    $foundsel = false;
    foreach ($hooked_modules_raw as $hookmod => $dummy) {
        $hookmodid = pnModGetIDFromName($hookmod);
        $sel = false;
        if ($forum['forum_moduleref'] == $hookmodid) {
            $sel = true;
            $foundsel = true;
        }
        $hooked_modules[] = array('name' => $hookmod,
                                  'id'   => $hookmodid,
                                  'sel'  => $sel);
    }

    if ($foundsel == false) {
        $hooked_modules[0]['sel'] = true;
    }

    // read all RSS feeds
    $rssfeeds = array();
    if (pnModAvailable('Feeds')) {
        $rssfeeds = pnModAPIFunc('Feeds', 'user', 'getall');
    }

    $moderators = pnModAPIFunc('Dizkus', 'admin', 'readmoderators',
                                array('forum_id' => $forum['forum_id']));

    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign('hooked_modules', $hooked_modules);
    $pnr->assign('rssfeeds', $rssfeeds);
    $pnr->assign('externalsourceoptions', $externalsourceoptions);

    Loader::loadClass('CategoryUtil');
    $cats        = CategoryUtil::getSubCategories (1, true, true, true, true, true);
    $catselector = CategoryUtil::getSelector_Categories($cats, 'id', $forum['forum_pntopic'], 'pncategory');
    $pnr->assign('categoryselector', $catselector);

    $pnr->assign('moderators', $moderators);
    $hideusers = pnModGetVar('Dizkus', 'hideusers');
    if ($hideusers == 'no') {
        $users = pnModAPIFunc('Dizkus', 'admin', 'readusers',
                              array('moderators' => $moderators));
    } else {
        $users = array();
    }
    $pnr->assign('users', $users);
    $pnr->assign('groups', pnModAPIFunc('Dizkus', 'admin', 'readgroups',
                                        array('moderators' => $moderators)));
    $pnr->assign('forum', $forum);
    $pnr->assign('newforum', $new);
    $html = $pnr->fetch('dizkus_ajax_editforum.html');
    if (!isset($returnhtml)) {
        dzk_jsonizeoutput(array('forum_id' => $forum['forum_id'],
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
function Dizkus_admin_editcategory($args=array())
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        dzk_ajaxerror(__('Sorry! You do not have authorisation to administer this module.', $dom));
    }

    if (!empty($args)) {
        extract($args);
        $cat_id = $cat;
    } else {
        $cat_id = FormUtil::getPassedValue('cat');
    }
    if ($cat_id == 'new') {
        $new = true;
        $category = array('cat_title'    => __('-- Create new category --', $dom),
                          'cat_id'       => time(),
                          'forum_count'  => 0);
        // we add a new category
    } else {
        $new = false;
        $category = pnModAPIFunc('Dizkus', 'admin', 'readcategories',
                                 array( 'cat_id' => $cat_id ));
        $forums = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                               array('cat_id'    => $cat_id,
                                     'permcheck' => 'nocheck'));
        $category['forum_count'] = count($forums);
    }
    $pnr = pnRender::getInstance('Dizkus', false, null, true);
    $pnr->assign('category', $category );
    $pnr->assign('newcategory', $new);
    dzk_jsonizeoutput(array('data'     => $pnr->fetch('dizkus_ajax_editcategory.html'),
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
function Dizkus_admin_storecategory()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    SessionUtil::setVar('pn_ajax_call', 'ajax');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        dzk_ajaxerror(__('Sorry! You do not have authorisation to administer this module.', $dom));
    }

    if (!SecurityUtil::confirmAuthKey()) {
        dzk_ajaxerror(__('Sorry! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.', $dom));
    }

    $cat_id    = FormUtil::getPassedValue('cat_id');
    $cat_title = FormUtil::getPassedValue('cat_title');
    $add       = FormUtil::getPassedValue('add');
    $delete    = FormUtil::getPassedValue('delete');

    $cat_title = DataUtil::convertFromUTF8($cat_title);
    if (!empty($delete)) {
        $forums = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                               array('cat_id'    => $cat_id,
                                     'permcheck' => 'nocheck'));
        if (count($forums) > 0) {
            $category = pnModAPIFunc('Dizkus', 'admin', 'readcategories',
                                     array( 'cat_id' => $cat_id ));
            dzk_ajaxerror('Error! The \'' . $category['cat_title'] . '\' category contains ' . count($forums) . ' forums.');
        }
        $res = pnModAPIFunc('Dizkus', 'admin', 'deletecategory',
                            array('cat_id' => $cat_id));
        if ($res == true) {
            dzk_jsonizeoutput(array('cat_id' => $cat_id,
                                    'old_id' => $cat_id,
                                    'action' => 'delete'),
                              true,
                              false); 
        } else {
            dzk_ajaxerror('Error! Could not delete the \'' . DataUtil::formatForDisplay($cat_id) . '\' category.');
        }

    } elseif (!empty($add)) {
        $original_catid = $cat_id;
        $cat_id = pnModAPIFunc('Dizkus', 'admin', 'addcategory',
                               array('cat_title' => $cat_title));
        if (!is_bool($cat_id)) {
            $category = pnModAPIFunc('Dizkus', 'admin', 'readcategories',
                                     array( 'cat_id' => $cat_id ));
            $pnr = pnRender::getInstance('Dizkus', false, null, true);
            $pnr->assign('category', $category );
            $pnr->assign('newcategory', false);
            dzk_jsonizeoutput(array('cat_id'      => $cat_id,
                                    'old_id'      => $original_catid,
                                    'cat_title'   => $cat_title,
                                    'action'      => 'add',
                                    'edithtml'    => $pnr->fetch('dizkus_ajax_editcategory.html'),
                                    'cat_linkurl' => pnModURL('Dizkus', 'user', 'main', array('viewcat' => $cat_id))),
                              true,
                              false); 
        } else {
            dzk_ajaxerror('error creating category "' . DataUtil::formatForDisplay($cat_title) . '"');
        }

    } else {
        if (pnModAPIFunc('Dizkus', 'admin', 'updatecategory',
                         array('cat_title' => $cat_title,
                               'cat_id'    => $cat_id)) == true) {
            dzk_jsonizeoutput(array('cat_id'      => $cat_id,
                                    'old_id'      => $cat_id,
                                    'cat_title'   => $cat_title,
                                    'action'      => 'update',
                                    'cat_linkurl' => pnModURL('Dizkus', 'user', 'main', array('viewcat' => $cat_id))),
                              true,
                              false); 
        } else {
            dzk_ajaxerror('Error! Could not update \'cat_id\' ' . DataUtil::formatForDisplay($cat_id) . ' with title \'' . DataUtil::formatForDisplay($cat_title) . '\'.');
        }
    }
}

/**
 * storeforum
 *
 * AJAX function
 */
function Dizkus_admin_storeforum()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        dzk_ajaxerror(__('Sorry! You do not have authorisation to administer this module', $dom));
    }

    if (!SecurityUtil::confirmAuthKey()) {
        dzk_ajaxerror(__('Sorry! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.', $dom));
    }

    SessionUtil::setVar('pn_ajax_call', 'ajax');

    $forum_name    = FormUtil::getPassedValue('forum_name');
    $forum_id    = FormUtil::getPassedValue('forum_id');
    $cat_id    = FormUtil::getPassedValue('cat_id');
    $desc    = FormUtil::getPassedValue('desc');
    $mods    = FormUtil::getPassedValue('mods');
    $rem_mods    = FormUtil::getPassedValue('rem_mods');
    $extsource    = FormUtil::getPassedValue('extsource');
    $rssfeed    = FormUtil::getPassedValue('rssfeed');
    $pop3_server    = FormUtil::getPassedValue('pop3_server');
    $pop3_port    = FormUtil::getPassedValue('pop3_port');
    $pop3_login    = FormUtil::getPassedValue('pop3_login');
    $pop3_password    = FormUtil::getPassedValue('pop3_password');
    $pop3_passwordconfirm    = FormUtil::getPassedValue('pop3_passwordconfirm');
    $pop3_interval    = FormUtil::getPassedValue('pop3_interval');
    $pop3_matchstring    = FormUtil::getPassedValue('pop3_matchstring');
    $pnuser    = FormUtil::getPassedValue('pnuser');
    $pnpassword    = FormUtil::getPassedValue('pnpassword');
    $pnpasswordconfirm    = FormUtil::getPassedValue('pnpasswordconfirm');
    $moduleref    = FormUtil::getPassedValue('moduleref');
    $pop3_test    = FormUtil::getPassedValue('pop3_test');
    $add    = FormUtil::getPassedValue('add');
    $delete    = FormUtil::getPassedValue('delete');

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
    if (!empty($delete)) {
        $action = 'delete';
        $newforum = array();
        $forumtitle = '';
        $editforumhtml = '';
        $old_id = $forum_id;
        $cat_id = pnModAPIFunc('Dizkus', 'user', 'get_forum_category',
                               array('forum_id' => $forum_id)); 
        // no security check!!!
        pnModAPIFunc('Dizkus', 'admin', 'deleteforum',
                     array('forum_id'   => $forum_id));
    } else {
        // add or update - the next steps are the same for both
        if($extsource == 2) {
            // store the rss feed in the pop3_server field
            $pop3_server = $rssfeed;
        }

        if ($pop3_password <> $pop3_passwordconfirm) {
            dzk_ajaxerror(__('Sorry! The two passwords you entered do not match. Please correct your entries and try again.', $dom));
        }
        if ($pnpassword <> $pnpasswordconfirm) {
            dzk_ajaxerror(__('Sorry! The two passwords you entered do not match. Please correct your entries and try again.', $dom));
        }

        if (!empty($add)) {
            $action = 'add';
            $old_id = $forum_id;
            $pop3_password = base64_encode($pop3_password);
            $pnpassword = base64_encode($pnpassword);
            $forum_id = pnModAPIFunc('Dizkus', 'admin', 'addforum',
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
            $forum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                                  array('forum_id' => $forum_id));

            // check if user has changed the password
            if ($forum['pop3_password'] == $pop3_password) {
                // no change necessary
                $pop3_password = "";
            } else {
                $pop3_password = base64_encode($pop3_password);
            }

            // check if user has changed the password
            if($forum['pop3_pnpassword'] == $pnpassword) {
                // no change necessary
                $pnpassword = '';
            } else {
                $pnpassword = base64_encode($pnpassword);
            }

            pnModAPIFunc('Dizkus', 'admin', 'editforum',
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
        $editforumhtml = Dizkus_admin_editforum(array('forum_id'   => $forum_id,
                                                       'returnhtml' => true));

        $forumtitle = '<a href="' . pnModURL('Dizkus', 'user', 'viewforum', array('forum' => $forum_id)) .'">' . $forum_name . '</a> (' . $forum_id . ')';

        // re-read forum data 
        $newforum = pnModAPIFunc('Dizkus', 'admin', 'readforums',
                              array('forum_id'  => $forum_id,
                                    'permcheck' => 'nocheck'));

        if ($pop3_test==1) {
            $pop3testresult = pnModAPIFunc('Dizkus', 'user', 'testpop3connection',
                                           array('forum_id' => $forum_id));

            $pnr = pnRender::getInstance('Dizkus', false, null, true);
            $pnr->assign('messages', $pop3testresult);
            $pnr->assign('forum_id', $forum_id);
            $pop3testresulthtml = $pnr->fetch('dizkus_admin_pop3test.html');
        }
    } 

    dzk_jsonizeoutput(array('action'         => $action,
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
function Dizkus_admin_managesubscriptions()
{
     $dom = ZLanguage::getModuleDomain('Dizkus');

   if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        return LogUtil::registerPermissionError();
    }

    $submit     = FormUtil::getPassedValue('submit');
    $pnusername = FormUtil::getPassedValue('pnusername');

    $pnuid = 0;
    $topicsubscriptions = array();
    $forumsubscriptions = array();

    if (!empty($pnusername)) {
        $pnuid = pnUserGetIDFromName($pnusername);
    }
    if (!empty($pnuid)) {
        $topicsubscriptions = pnModAPIFunc('Dizkus', 'user', 'get_topic_subscriptions', array('user_id' => $pnuid));
        $forumsubscriptions = pnModAPIFunc('Dizkus', 'user', 'get_forum_subscriptions', array('user_id' => $pnuid));
    }

    if (!$submit) {
        // submit is empty
        $pnr = pnRender::getInstance('Dizkus', false, null, true);
        $pnr->assign('pnusername', $pnusername);
        $pnr->assign('pnuid', $pnuid = pnUserGetIDFromName($pnusername));
        $pnr->assign('topicsubscriptions', $topicsubscriptions);
        $pnr->assign('forumsubscriptions', $forumsubscriptions);

        return $pnr->fetch('dizkus_admin_managesubscriptions.html');
    } else {  // submit not empty
        $pnuid      = FormUtil::getPassedValue('pnuid');
        $allforums  = FormUtil::getPassedValue('allforum');
        $forum_ids  = FormUtil::getPassedValue('forum_id');
        $alltopics  = FormUtil::getPassedValue('alltopic');
        $topic_ids  = FormUtil::getPassedValue('topic_id');
        if ($allforums == '1') {
            pnModAPIFunc('Dizkus', 'user', 'unsubscribe_forum', array('user_id' => $pnuid));
        } elseif (count($forum_ids) > 0) {
            for($i = 0; $i < count($forum_ids); $i++) {
                pnModAPIFunc('Dizkus', 'user', 'unsubscribe_forum', array('user_id' => $pnuid, 'forum_id' => $forum_ids[$i]));
            }
        }

        if ($alltopics == '1') {
            pnModAPIFunc('Dizkus', 'user', 'unsubscribe_topic', array('user_id' => $pnuid));
        } elseif (count($topic_ids) > 0) {
            for($i = 0; $i < count($topic_ids); $i++) {
                pnModAPIFunc('Dizkus', 'user', 'unsubscribe_topic', array('user_id' => $pnuid, 'topic_id' => $topic_ids[$i]));
            }
        }
    }
    return pnRedirect(pnModURL('Dizkus', 'admin', 'managesubscriptions', array('pnusername' => pnUserGetVar('uname', $pnuid))));
}
