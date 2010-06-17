<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

include_once 'modules/Dizkus/common.php';

class Dizkus_Admin extends Zikula_Controller {
     
    /**
     * the main administration function
     *
     */
    public function main()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        $render = Renderer::getInstance('Dizkus', false, null, true);
    
        return $render->fetch('dizkus_admin_main.html');
    }
    
    /**
     * preferences
     *
     */
    public function preferences()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
    	  // Load handler class
        Loader::requireOnce('modules/Dizkus/includes/dizkus_admin_prefshandler.class.php');
    
        // Create output object
        $form = FormUtil::newForm('Dizkus');
    
        // Return the output that has been generated by this function
        return $form->execute('dizkus_admin_preferences.html', new dizkus_admin_prefshandler());
    }
    
    /**
     * syncforums
     */
    public function syncforums()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $silent = FormUtil::getPassedValue('silent', 0);
    
        $messages = array();
    
        ModUtil::apiFunc('Dizkus', 'admin', 'sync',
                     array('type' => 'all users'));
    
        $messages[] = DataUtil::formatForDisplay($this->__('Done! Synchronized Zikula users and Dizkus users.'));
    
        ModUtil::apiFunc('Dizkus', 'admin', 'sync',
                     array('type' => 'all forums'));
    
        $messages[] = DataUtil::formatForDisplay($this->__('Done! Synchronized forum index.'));
    
        ModUtil::apiFunc('Dizkus', 'admin', 'sync',
                     array('type' => 'all topics'));
    
        $messages[] = DataUtil::formatForDisplay($this->__('Done! Synchronized topics.'));
    
        ModUtil::apiFunc('Dizkus', 'admin', 'sync',
                     array('type' => 'all posts'));
    
        $messages[] = DataUtil::formatForDisplay($this->__('Done! Synchronized posts counter.'));
    
        if ($silent != 1) {
            LogUtil::registerStatus($messages);
        }
    
        return System::redirect(ModUtil::url('Dizkus', 'admin', 'main'));
    }
    
    /**
     * ranks
     */
    public function ranks()
    {
    
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        $submit   = FormUtil::getPassedValue('submit', null, 'GETPOST');
        $ranktype = (int)FormUtil::getPassedValue('ranktype', 0, 'GETPOST');
    
        if (!$submit) {
            list($rankimages, $ranks) = ModUtil::apiFunc('Dizkus', 'admin', 'readranks',
                                                      array('ranktype' => $ranktype));
    
            $render = Renderer::getInstance('Dizkus', false, null, true);
    
            $render->assign('ranks', $ranks);
            $render->assign('ranktype', $ranktype);
            $render->assign('rankimages', $rankimages);
    
            if ($ranktype == 0) {
                return $render->fetch('dizkus_admin_ranks.html');
            } else {
                return $render->fetch('dizkus_admin_honoraryranks.html');
            }
        } else {
            $ranks = FormUtil::getPassedValue('ranks');
            ModUtil::apiFunc('Dizkus', 'admin', 'saverank', array('ranks' => $ranks));
        }
    
        return System::redirect(ModUtil::url('Dizkus','admin', 'ranks', array('ranktype' => $ranktype)));
    }
    
    /**
     * ranks
     */
    public function assignranks()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        $submit     = FormUtil::getPassedValue('submit');
        $letter     = FormUtil::getPassedValue('letter');
        $lastletter = FormUtil::getPassedValue('lastletter');
        $page       = (int)FormUtil::getPassedValue('page', 1, 'GETPOST');
    
        // sync the current user, so that new users
        // get into the Dizkus database
        ModUtil::apiFunc('Dizkus', 'admin', 'sync', array('type' => 'all users')); 
    
        // check for a letter parameter
        if (!empty($lastletter)) {
            $letter = $lastletter;
        }
    
        // count users and forbid '*' if more than 1000 users are present
        $usercount = DBUtil::selectObjectCount('users');
        if (empty($letter) || strlen($letter) != 1 || (($usercount > 1000) && $letter == '*')) {
            $letter = 'a';
        }
        $letter = strtolower($letter);
    
        if (!$submit) {
            list($rankimages, $ranks) = ModUtil::apiFunc('Dizkus', 'admin', 'readranks',
                                                     array('ranktype' => 1));
    
            $tables = System::dbGetTables();
    
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
            foreach ($users as $user)
            {
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
            $rankpath = ModUtil::getVar('Dizkus', 'url_ranks_images') .'/';
            foreach ($ranks as $rank) {
                $inlinecss .= '#dizkus_admin option[value='.$rank['rank_id'].']:before { content:url("'.System::getBaseUrl() . $rankpath . $rank['rank_image'].'"); }' . "\n";
            }
            $inlinecss .= '</style>' . "\n";
            PageUtil::addVar('rawtext', $inlinecss);
    */        
            //usort($allusers, 'cmp_userorder');
    
            unset($users);
    
            $render = Renderer::getInstance('Dizkus', false, null, true);
    
            $render->assign('ranks', $ranks);
            $render->assign('rankimages', $rankimages);
            $render->assign('allusers', $allusers);
            $render->assign('letter', $letter);
            $render->assign('page', $page);
            $render->assign('perpage', $perpage);
            $render->assign('usercount', $usercount);
            $render->assign('allow_star', ($usercount < 1000));
    
            return $render->fetch('dizkus_admin_assignranks.html');
    
        } else {
            // avoid some vars in the url of the pager
            unset($_GET['submit']);
            unset($_POST['submit']);
            unset($_REQUEST['submit']);
            $setrank = FormUtil::getPassedValue('setrank');
            ModUtil::apiFunc('Dizkus', 'admin', 'assignranksave', 
                         array('setrank' => $setrank));
        }
    
        return System::redirect(ModUtil::url('Dizkus','admin', 'assignranks',
                                   array('letter' => $letter,
                                         'page'   => $page)));
    }
    
    /** 
     * reordertree
     *
     */
    public function reordertree()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        $categorytree = ModUtil::apiFunc('Dizkus', 'user', 'readcategorytree');
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
    
        $render = Renderer::getInstance('Dizkus', false, null, true);
    
        $render->assign('categorytree', $categorytree);
        $render->assign('catids', $catids);
        $render->assign('forumids', $forumids);
    
        return $render->fetch('dizkus_admin_reordertree.html');
    }
    
    /**
     * reordertreesave
     *
     * AJAX result function
     */
    public function reordertreesave()
    {
    
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            dzk_ajaxerror($this->__('Error! You do not have authorisation to administer this module.'));
        }
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!SecurityUtil::confirmAuthKey()) {
            //dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.');
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
                if (ModUtil::apiFunc('Dizkus', 'admin', 'updatecategory',
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
                            $newforum = array('forum_id'    => $forum_id,
                                              'cat_id'      => $cat_id,
                                              'forum_order' => $forumorder);
                            DBUtil::updateObject($newforum, 'dizkus_forums', null, 'forum_id');
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
     */
    public function editforum($args=array())
    {
    
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            dzk_ajaxerror($this->__('Error! You do not have authorisation to administer this module.'));
        }
    
        $forum_id   = isset($args['forum_id']) ? $args['forum_id'] : FormUtil::getPassedValue('forum_id', null, 'GETPOST');
        $returnhtml = isset($args['returnhtml']) ? $args['returnhtml'] : FormUtil::getPassedValue('returnhtml', null, 'GETPOST');
    
        if (!isset($forum_id)) {
            dzk_ajaxerror(_MODARGSERROR . ': forum_id ' . DataUtil::formatForDisplay($forum_id) . ' in Dizkus_admin_editforum()');
        }
    
        if ($forum_id == -1) {
            // create a new forum
            $new = true;
            $cat_id = FormUtil::getPassedValue('cat');
            $forum = array('forum_name'       => $this->__('-- Create new forum --', $dom),
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
            $moderators = array();
        } else {
            // we are editing
            $new = false;
            $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                  array('forum_id'  => $forum_id,
                                        'permcheck' => ACCESS_ADMIN));
            $moderators = ModUtil::apiFunc('Dizkus', 'admin', 'readmoderators',
                                        array('forum_id' => $forum['forum_id']));
    
    
        }
    
        $externalsourceoptions = array( 0 => array('checked'  => '',
                                                   'name'     => $this->__('No external source', $dom),
                                                   'ok'       => '',
                                                   'extended' => false),   // none
                                        1 => array('checked'  => '',
                                                   'name'     => $this->__('Mail2Forum', $dom),
                                                   'ok'       => '',
                                                   'extended' => true),  // mail
                                        2 => array('checked'  => '',
                                                   'name'     => $this->__('RSS2Forum', $dom),
                                                   'ok'       => (ModUtil::available('Feeds') == true) ? '' : $this->__("<span style=\"color: red;\">'Feeds' module is not available.</span>", $dom),
                                                   'extended' => true)); // rss
    
        $externalsourceoptions[$forum['pop3_active']]['checked'] = ' checked="checked"';
    
        $hooked_modules_raw = ModUtil::apiFunc('modules', 'admin', 'gethookedmodules',
                                           array('hookmodname' => 'Dizkus'));
    
        $hooked_modules = array(array('name' => $this->__('No hooked module found.', $dom),
                                      'id'   => 0));
    
        $foundsel = false;
        foreach ($hooked_modules_raw as $hookmod => $dummy) {
            $hookmodid = ModUtil::getIDFromName($hookmod);
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
        if (ModUtil::available('Feeds')) {
            $rssfeeds = ModUtil::apiFunc('Feeds', 'user', 'getall');
        }
    
        $render = Renderer::getInstance('Dizkus', false, null, true);
    
        $render->assign('hooked_modules', $hooked_modules);
        $render->assign('rssfeeds', $rssfeeds);
        $render->assign('externalsourceoptions', $externalsourceoptions);
    
        Loader::loadClass('CategoryUtil');
        $cats        = CategoryUtil::getSubCategories (1, true, true, true, true, true);
        $catselector = CategoryUtil::getSelector_Categories($cats, 'id', $forum['forum_pntopic'], 'pncategory');
        $render->assign('categoryselector', $catselector);
    
        $render->assign('moderators', $moderators);
        $hideusers = ModUtil::getVar('Dizkus', 'hideusers');
        if ($hideusers == 'no') {
            $users = ModUtil::apiFunc('Dizkus', 'admin', 'readusers',
                                  array('moderators' => $moderators));
        } else {
            $users = array();
        }
        $render->assign('users', $users);
        $render->assign('groups', ModUtil::apiFunc('Dizkus', 'admin', 'readgroups',
                                            array('moderators' => $moderators)));
        $render->assign('forum', $forum);
        $render->assign('newforum', $new);
    
        $html = $render->fetch('dizkus_ajax_editforum.html');
    
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
     */
    public function editcategory($args=array())
    {
    
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            dzk_ajaxerror($this->__('Error! You do not have authorisation to administer this module.'));
        }
    
        $cat_id   = FormUtil::getPassedValue('cat', (isset($args['cat'])) ? $args['cat'] : '', 'GETPOST');
    
        if ($cat_id == 'new') {
            $new = true;
            $category = array('cat_title'    => $this->__('-- Create new category --', $dom),
                              'cat_id'       => time(),
                              'forum_count'  => 0);
            // we add a new category
        } else {
            $new = false;
            $category = ModUtil::apiFunc('Dizkus', 'admin', 'readcategories',
                                     array( 'cat_id' => $cat_id ));
            $forums = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                   array('cat_id'    => $cat_id,
                                         'permcheck' => 'nocheck'));
            $category['forum_count'] = count($forums);
        }
    
        $render = Renderer::getInstance('Dizkus', false, null, true);
    
        $render->assign('category', $category );
        $render->assign('newcategory', $new);
    
        dzk_jsonizeoutput(array('data'     => $render->fetch('dizkus_ajax_editcategory.html'),
                                'cat_id'   => $category['cat_id'],
                                'new'      => $new),
                          false,
                          true);
    }
    
    /**
     * storecategory
     *
     * AJAX function
     */
    public function storecategory()
    {
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            dzk_ajaxerror($this->__('Error! You do not have authorisation to administer this module.'));
        }
    
        if (!SecurityUtil::confirmAuthKey()) {
            dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
        }
    
        $cat_id    = FormUtil::getPassedValue('cat_id');
        $cat_title = FormUtil::getPassedValue('cat_title');
        $add       = FormUtil::getPassedValue('add');
        $delete    = FormUtil::getPassedValue('delete');
    
        if (!empty($delete)) {
            $forums = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                   array('cat_id'    => $cat_id,
                                         'permcheck' => 'nocheck'));
            if (count($forums) > 0) {
                $category = ModUtil::apiFunc('Dizkus', 'admin', 'readcategories',
                                         array( 'cat_id' => $cat_id ));
                dzk_ajaxerror('Error! The \'' . $category['cat_title'] . '\' category contains ' . count($forums) . ' forums.');
            }
            $res = ModUtil::apiFunc('Dizkus', 'admin', 'deletecategory',
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
            $cat_id = ModUtil::apiFunc('Dizkus', 'admin', 'addcategory',
                                   array('cat_title' => $cat_title));
            if (!is_bool($cat_id)) {
                $category = ModUtil::apiFunc('Dizkus', 'admin', 'readcategories',
                                         array( 'cat_id' => $cat_id ));
                $render = Renderer::getInstance('Dizkus', false, null, true);
                $render->assign('category', $category );
                $render->assign('newcategory', false);
                dzk_jsonizeoutput(array('cat_id'      => $cat_id,
                                        'old_id'      => $original_catid,
                                        'cat_title'   => $cat_title,
                                        'action'      => 'add',
                                        'edithtml'    => $render->fetch('dizkus_ajax_editcategory.html'),
                                        'cat_linkurl' => ModUtil::url('Dizkus', 'user', 'main', array('viewcat' => $cat_id))),
                                  true,
                                  false); 
            } else {
                dzk_ajaxerror('error creating category "' . DataUtil::formatForDisplay($cat_title) . '"');
            }
    
        } else {
            if (ModUtil::apiFunc('Dizkus', 'admin', 'updatecategory',
                             array('cat_title' => $cat_title,
                                   'cat_id'    => $cat_id)) == true) {
                dzk_jsonizeoutput(array('cat_id'      => $cat_id,
                                        'old_id'      => $cat_id,
                                        'cat_title'   => $cat_title,
                                        'action'      => 'update',
                                        'cat_linkurl' => ModUtil::url('Dizkus', 'user', 'main', array('viewcat' => $cat_id))),
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
    public function storeforum()
    {
    
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            dzk_ajaxerror($this->__('Error! You do not have authorisation to administer this module.'));
        }
    
        if (!SecurityUtil::confirmAuthKey()) {
            dzk_ajaxerror($this->__('Error! Invalid authorisation key (\'authkey\'). This is probably either because you pressed the \'Back\' button to return to a page which does not allow that, or else because the page\'s authorisation key expired due to prolonged inactivity. Please refresh the page and try again.'));
        }
    
        SessionUtil::setVar('zk_ajax_call', 'ajax');
    
        $forum_name           = FormUtil::getPassedValue('forum_name');
        $forum_id             = FormUtil::getPassedValue('forum_id');
        $cat_id               = FormUtil::getPassedValue('cat_id');
        $desc                 = FormUtil::getPassedValue('desc');
        $mods                 = FormUtil::getPassedValue('mods');
        $rem_mods             = FormUtil::getPassedValue('rem_mods');
        $extsource            = FormUtil::getPassedValue('extsource');
        $rssfeed              = FormUtil::getPassedValue('rssfeed');
        $pop3_server          = FormUtil::getPassedValue('pop3_server');
        $pop3_port            = FormUtil::getPassedValue('pop3_port');
        $pop3_login           = FormUtil::getPassedValue('pop3_login');
        $pop3_password        = FormUtil::getPassedValue('pop3_password');
        $pop3_passwordconfirm = FormUtil::getPassedValue('pop3_passwordconfirm');
        $pop3_interval        = FormUtil::getPassedValue('pop3_interval');
        $pop3_matchstring     = FormUtil::getPassedValue('pop3_matchstring');
        $pnuser               = FormUtil::getPassedValue('pnuser');
        $pnpassword           = FormUtil::getPassedValue('pnpassword');
        $pnpasswordconfirm    = FormUtil::getPassedValue('pnpasswordconfirm');
        $moduleref            = FormUtil::getPassedValue('moduleref');
        $pop3_test            = FormUtil::getPassedValue('pop3_test');
        $add                  = FormUtil::getPassedValue('add');
        $delete               = FormUtil::getPassedValue('delete');
    
        $pntopic              = (int)FormUtil::getpassedValue('pncategory', 0);
    
        $pop3testresulthtml = '';
        if (!empty($delete)) {
            $action = 'delete';
            $newforum = array();
            $forumtitle = '';
            $editforumhtml = '';
            $old_id = $forum_id;
            $cat_id = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_category',
                                   array('forum_id' => $forum_id)); 
            // no security check!!!
            ModUtil::apiFunc('Dizkus', 'admin', 'deleteforum',
                         array('forum_id'   => $forum_id));
        } else {
            // add or update - the next steps are the same for both
            if ($extsource == 2) {
                // store the rss feed in the pop3_server field
                $pop3_server = $rssfeed;
            }
    
            if ($pop3_password <> $pop3_passwordconfirm) {
                dzk_ajaxerror($this->__('Error! The two passwords you entered do not match. Please correct your entries and try again.'));
            }
            if ($pnpassword <> $pnpasswordconfirm) {
                dzk_ajaxerror($this->__('Error! The two passwords you entered do not match. Please correct your entries and try again.'));
            }
    
            if (!empty($add)) {
                $action = 'add';
                $old_id = $forum_id;
                $pop3_password = base64_encode($pop3_password);
                $pnpassword = base64_encode($pnpassword);
                $forum_id = ModUtil::apiFunc('Dizkus', 'admin', 'addforum',
                                         array('forum_name'             => $forum_name,
                                               'cat_id'                 => $cat_id,
                                               'forum_desc'             => $desc,
                                               'mods'                   => $mods,
                                               'forum_pop3_active'      => $extsource,
                                               'forum_pop3_server'      => $pop3_server,
                                               'forum_pop3_port'        => $pop3_port,
                                               'forum_pop3_login'       => $pop3_login,
                                               'forum_pop3_password'    => $pop3_password,
                                               'forum_pop3_interval'    => $pop3_interval,
                                               'forum_pop3_pnuser'      => $pnuser,
                                               'forum_pop3_pnpassword'  => $pnpassword,
                                               'forum_pop3_matchstring' => $pop3_matchstring,
                                               'forum_moduleref'        => $moduleref,
                                               'forum_pntopic'          => $pntopic));
            } else {
                $action = 'update';
                $old_id = '';
                $forum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                      array('forum_id' => $forum_id));
    
                // check if user has changed the password
                if ($forum['pop3_password'] == $pop3_password) {
                    // no change necessary
                    $pop3_password = "";
                } else {
                    $pop3_password = base64_encode($pop3_password);
                }
    
                // check if user has changed the password
                if ($forum['pop3_pnpassword'] == $pnpassword) {
                    // no change necessary
                    $pnpassword = '';
                } else {
                    $pnpassword = base64_encode($pnpassword);
                }
    
                ModUtil::apiFunc('Dizkus', 'admin', 'editforum',
                             array('forum_name'             => $forum_name,
                                   'forum_id'               => $forum_id,
                                   'cat_id'                 => $cat_id,
                                   'forum_desc'             => $desc,
                                   'mods'                   => $mods,
                                   'rem_mods'               => $rem_mods,
                                   'forum_pop3_active'      => $extsource,
                                   'forum_pop3_server'      => $pop3_server,
                                   'forum_pop3_port'        => $pop3_port,
                                   'forum_pop3_login'       => $pop3_login,
                                   'forum_pop3_password'    => $pop3_password,
                                   'forum_pop3_interval'    => $pop3_interval,
                                   'forum_pop3_pnuser'      => $pnuser,
                                   'forum_pop3_pnpassword'  => $pnpassword,
                                   'forum_pop3_matchstring' => $pop3_matchstring,
                                   'forum_moduleref'        => $moduleref,
                                   'forum_pntopic'          => $pntopic));
            }
            $editforumhtml = Dizkus_admin_editforum(array('forum_id'   => $forum_id,
                                                          'returnhtml' => true));
    
            $forumtitle = '<a href="' . ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id)) .'">' . $forum_name . '</a> (' . $forum_id . ')';
    
            // re-read forum data 
            $newforum = ModUtil::apiFunc('Dizkus', 'admin', 'readforums',
                                  array('forum_id'  => $forum_id,
                                        'permcheck' => 'nocheck'));
    
            if ($pop3_test == 1) {
                $pop3testresult = ModUtil::apiFunc('Dizkus', 'user', 'testpop3connection',
                                               array('forum_id' => $forum_id));
    
                $render = Renderer::getInstance('Dizkus', false, null, true);
    
                $render->assign('messages', $pop3testresult);
                $render->assign('forum_id', $forum_id);
    
                $pop3testresulthtml = $render->fetch('dizkus_admin_pop3test.html');
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
    public function managesubscriptions()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
    
        $submit     = FormUtil::getPassedValue('submit');
        $pnusername = FormUtil::getPassedValue('pnusername');
    
        $pnuid = 0;
        $topicsubscriptions = array();
        $forumsubscriptions = array();
    
        if (!empty($pnusername)) {
            $pnuid = UserUtil::getIDFromName($pnusername);
        }
        if (!empty($pnuid)) {
            $topicsubscriptions = ModUtil::apiFunc('Dizkus', 'user', 'get_topic_subscriptions', array('user_id' => $pnuid));
            $forumsubscriptions = ModUtil::apiFunc('Dizkus', 'user', 'get_forum_subscriptions', array('user_id' => $pnuid));
        }
    
        if (!$submit) {
            // submit is empty
            $render = Renderer::getInstance('Dizkus', false, null, true);
    
            $render->assign('pnusername', $pnusername);
            $render->assign('pnuid', $pnuid = UserUtil::getIDFromName($pnusername));
            $render->assign('topicsubscriptions', $topicsubscriptions);
            $render->assign('forumsubscriptions', $forumsubscriptions);
    
            return $render->fetch('dizkus_admin_managesubscriptions.html');
    
        } else {  // submit not empty
            $pnuid      = FormUtil::getPassedValue('pnuid');
            $allforums  = FormUtil::getPassedValue('allforum');
            $forum_ids  = FormUtil::getPassedValue('forum_id');
            $alltopics  = FormUtil::getPassedValue('alltopic');
            $topic_ids  = FormUtil::getPassedValue('topic_id');
    
            if ($allforums == '1') {
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_forum', array('user_id' => $pnuid));
            } elseif (count($forum_ids) > 0) {
                for($i = 0; $i < count($forum_ids); $i++) {
                    ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_forum', array('user_id' => $pnuid, 'forum_id' => $forum_ids[$i]));
                }
            }
    
            if ($alltopics == '1') {
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_topic', array('user_id' => $pnuid));
            } elseif (count($topic_ids) > 0) {
                for($i = 0; $i < count($topic_ids); $i++) {
                    ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_topic', array('user_id' => $pnuid, 'topic_id' => $topic_ids[$i]));
                }
            }
        }
    
        return System::redirect(ModUtil::url('Dizkus', 'admin', 'managesubscriptions', array('pnusername' => UserUtil::getVar('uname', $pnuid))));
    }

}