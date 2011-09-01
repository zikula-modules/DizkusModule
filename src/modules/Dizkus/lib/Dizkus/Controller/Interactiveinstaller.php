<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

Class Dizkus_Controller_Interactiveinstaller extends Zikula_Controller_AbstractInteractiveInstaller
{
	
    /**
     * upgrade

     */
	
    //public function upgrade($oldversion)
    //{
    //}
    
    /**
     * interactiveupgrade
     *
     *
     */
    public function upgrade($args)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Error! No permission for this action.'));
        }
    
        $oldversion = FormUtil::getPassedValue('oldversion', isset($args['oldversion']) ? $args['oldversion'] : 0, 'GETPOST');
    
        $authid = SecurityUtil::generateAuthKey('Modules');
        switch ($oldversion)
        {
            case '2.7.1':
                $templatefile = 'upgrade/30.tpl';
                break;
    
            case '3.0':
                $templatefile = 'upgrade/31.tpl';
                break;
    
            case '3.1':
                $templatefile = 'upgrade/320.tpl';
                break;
                  
            default:
                // no interactive upgrade for version < 2.7
                // or latest step reached
                $this->view->clear_compiled();
                $this->view->clear_cache();
                return System::redirect(ModUtil::url('Modules', 'admin', 'upgrade', array('authid' => $authid)));
        }
    
        $this->view->setCaching(false)->add_core_data();
    
        $this->view->assign('oldversion', $oldversion);
        $this->view->assign('authid', $authid);
    
        return $this->view->fetch($templatefile);
    }
    
    /**
     * interactiveupgrade_to_3_0
     *
     */
    public function interactiveupgrade_to_3_0()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Error! No permission for this action.'));
        }
    
        $submit = FormUtil::getPassedValue('submit', null, 'GETPOST');
    
        if (!empty($submit)) {
            $result = $this->upgrade_to_3_0();
            if ($result<>true) {
                return LogUtil::registerError(_('Error! The upgrade to Dizkus 3.0 failed.'));
            }
            return System::redirect(ModUtil::url('Dizkus', 'interactiveinstaller', 'upgrade', array('oldversion' => '3.0' )));
        }
    
        return System::redirect(ModUtil::url('Modules', 'admin', 'view'));
    }
    
    /**
     * upgrade to 3.0
     *
     */
    public function upgrade_to_3_0()
    {        
        // rename the old pnForum tablenames to Dizkus tablenames
        $tables = array('pnforum_categories'         => 'dizkus_categories',
                        'pnforum_forum_mods'         => 'dizkus_forum_mods',
                        'pnforum_forums'             => 'dizkus_forums',
                        'pnforum_posts'              => 'dizkus_posts',
                        'pnforum_posts_text'         => 'dizkus_posts_text',
                        'pnforum_ranks'              => 'dizkus_ranks',
                        'pnforum_subscription'       => 'dizkus_subscription',
                        'pnforum_topics'             => 'dizkus_topics',
                        'pnforum_users'              => 'dizkus_users',
                        'pnforum_topic_subscription' => 'dizkus_topic_subscription',
                        'pnforum_forum_favorites'    => 'dizkus_forum_favorites');
    
        $dbconn = DBConnectionStack::getConnection();
        $dict   = NewDataDictionary($dbconn);
        $prefix = System::getVar('prefix');
        foreach($tables as $oldtable => $newtable) {
            $sqlarray = $dict->RenameTableSQL($prefix.'_'.$oldtable, $prefix.'_'.$newtable);
            $result   = $dict->ExecuteSQLArray($sqlarray);
            $success  = ($result==2);
            if (!$success) {
                $dberrmsg = $dbconn->ErrorNo().' - '.$dbconn->ErrorMSg();
                LogUtil::registerError ($this->__("Error! The renaming of table '%1$s' to '%2$s' failed: %3$s.", array($oldtable, $$newtable, $dberrmsg)));
            }
        }
    
        // add some columns to the post table - with DBUtil this is a one-liner, you just have to
        // define the new columns in the pntables array, see pntables.php
        DBUtil::changeTable('dizkus_posts');
    
        // remove obsolete module vars
        $this->delVar('pnForum', 'posticon');
        $this->delVar('pnForum', 'firstnew_image');
    
        $oldvars = ModUtil::getVar('pnForum');
        foreach ($oldvars as $varname => $oldvar) {
            // update path to rank images - simply replace pnForum with Dizkus
            if ($varname == 'url_ranks_images') {
                $oldvar = str_replace('pnForum', 'Dizkus', $oldvar);
            }
            ModUtil::setVar('Dizkus', $varname, $oldvar);
        }
        ModUtil::delVar('pnForum');
    
        // update hooks
        $ztables    = DBUtil::getTables();
        $hookstable  = $ztables['hooks'];
        $hookscolumn = $ztables['hooks_column'];
    
        $sql = 'UPDATE ' . $hookstable . ' SET ' . $hookscolumn['smodule'] . '=\'Dizkus\' WHERE ' . $hookscolumn['smodule'] . '=\'pnForum\'';
        $res = DBUtil::executeSQL ($sql);
        if ($res === false) {
            return LogUtil::registerError($this->__("Error! A problem was encountered while upgrading the source module for hooks ('smodule')."));
        }
    
        $sql = 'UPDATE ' . $hookstable . ' SET ' . $hookscolumn['tmodule'] . '=\'Dizkus\' WHERE ' . $hookscolumn['tmodule'] . '=\'pnForum\'';
        $res = DBUtil::executeSQL ($sql);
        if ($res === false) {
            return LogUtil::registerError($this->__("Error! A problem was encountered while upgrading the target module for hooks ('tmodule')."));
        }
    
        // introduce new module variable
        $this->setVar('signaturemanagement', 'no'); 
        $this->setVar('sendemailswithsqlerrors', 'no');
        $this->setVar('showtextinsearchresults', 'no');
        $this->setVar('minsearchlength', 3);
        $this->setVar('maxsearchlength', 30);
    
        $this->setVar('ignorelist_handling', 'medium');
        return true;
    }
    
    /**
     * interactiveupgrade_to_3_1
     */
    public function interactiveupgrade_to_3_1()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Error! No permission for this action.'));
        }
    
        $submit = FormUtil::getPassedValue('submit', null, 'GETPOST');
    
        if (!empty($submit)) {
            $result = $this->upgrade_to_3_1();
            if ($result<>true) {
                return LogUtil::registerError($this->__('Error! Could not upgrade to Dizkus 3.1.'));
            }
            return System::redirect(ModUtil::url('Dizkus', 'interactiveinstaller', 'upgrade', array('oldversion' => '3.1' )));
        }
    
        return System::redirect(ModUtil::url('Modules', 'admin', 'view'));
    }
    
    /**
     * upgrade to 3.1
     */
    public function upgrade_to_3_1()
    {
        // merge posts and posts_text table
        ModUtil::dbInfoLoad('Dizkus');
    
        $ztable = DBUtil::getTables();
    
        $poststable      = $ztable['dizkus_posts'];
        $postscolumn     = $ztable['dizkus_posts_column'];
        $poststexttable  = $ztable['dizkus_posts_text'];
        $poststextcolumn = $ztable['dizkus_posts_text_column'];
    
        // change table structures
        DBUtil::changeTable('dizkus_posts');
        DBUtil::changeTable('dizkus_ranks');
    
        DBUtil::dropColumn('dizkus_topics', 'topic_notify');
        DBUtil::dropColumn('dizkus_topics', 'sticky_label');
        DBUtil::dropColumn('dizkus_topics', 'poll_id');
        DBUtil::dropColumn('dizkus_forums', 'forum_access');
        DBUtil::dropColumn('dizkus_forums', 'forum_type');
        DBUtil::dropColumn('dizkus_topic_subscription', 'forum_id');
    
        // add some missing index fields, all named 'id' if not existing
        DBUtil::executeSQL('ALTER TABLE '. $ztable['dizkus_topic_subscription'] .' ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        DBUtil::executeSQL('ALTER TABLE '. $ztable['dizkus_forum_mods'] .' ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        
        // due to a bug in 3.0 no primary key has been added to the dizkus_users table upon creation, we will add this now 
        $res = DBUtil::executeSQL('SHOW COLUMNS FROM '. $ztable['dizkus_users']);
        $id_exists = false;
        foreach($res as $resline) {
            //(array) 0:
            //   1. (string) 0 = id
            //   2. (string) 1 = int(11)
            //   3. (string) 2 = NO
            //   4. (string) 3 = PRI
            //   5. (NULL) 4 = (none)
            //   6. (string) 5 = auto_increment
            if($resline[0] == 'user_id' && $resline[3] == 'PRI') {
                // found id
                $id_exists = true;
                break;
            }
        }
        if (!$id_exists) {
            DBUtil::executeSQL('ALTER TABLE '. $ztable['dizkus_users'] .' ADD PRIMARY KEY(user_id)');
        }
    
        // move all posting text from post_text to posts table and remove the post_text table - never knew why this has been split
        $sql = 'UPDATE ' . $poststable . ' AS p  
                SET p.' . $postscolumn['post_text'] . '= ( 
                    SELECT pt1.' . $poststextcolumn['post_text'] . ' 
                    FROM ' . $poststexttable . ' AS pt1
                    WHERE pt1.' . $poststextcolumn['post_id'] . '=p.' . $poststextcolumn['post_id'] .')
                WHERE EXISTS (
                    SELECT pt.' . $poststextcolumn['post_text'] . ' 
                    FROM ' . $poststexttable . ' AS pt 
                    WHERE pt.' . $poststextcolumn['post_id'] . '=p.' . $poststextcolumn['post_id'] .')';
    
        if (DBUtil::executeSQL($sql) != true) {
            LogUtil::registerError ($this->__("Error! Could not upgrade the table '%s'.", 'dizkus_posts'));
        }
    
        // remove obsolete table
        DBUtil::dropTable('dizkus_posts_text');
    
        // remove obsolete module variables
        $this->delVar('sendemailswithsqlerrors');
        $this->delVar('default_lang');
        
        // $this->migratecategories();
    
        // drop old tables
        //
        // this will be done when the upgrade is finished and working - just before the release
        //
        // DBUtil::dropTable('dizkus_categories');
        // DBUtil::dropTable('dizkus_forums');
    
        // introduce new module variable
        $this->setVar('allowgravatars', 1);
        $this->setVar('gravatarimage', 'gravatar.gif');
    
        return true;
    }
    
    /**
     * interactiveupgrade_to_3_2
     */
    public function interactiveupgrade_to_3_2_0()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', "::", ACCESS_ADMIN)) {
            return LogUtil::registerError($this->__('Error! No permission for this action.'));
        }
    
        $submit = FormUtil::getPassedValue('submit', null, 'GETPOST');
    
        if (!empty($submit)) {
            $result = $this->upgrade_to_3_2_0();
            if ($result<>true) {
                return LogUtil::registerError($this->__('Error! Could not upgrade to Dizkus 3.2.0'));
            }
            return System::redirect(ModUtil::url('Dizkus', 'interactiveinstaller', 'upgrade', array('oldversion' => '3.2.0' )));
        }
    
        return System::redirect(ModUtil::url('Modules', 'admin', 'view'));
    }
    
    /**
     * upgrade to 3.2
     */
    public function upgrade_to_3_2_0()
    {
        // remove pn from images/rank folder
        $this->setVar('url_ranks_images', "modules/Dizkus/images/ranks");
        
        ModUtil::dbInfoLoad('Settings');
        $tables = DBUtil::getTables();
        
        $objtable   = $tables['objectdata_attributes'];
        $objcolumn  = $tables['objectdata_attributes_column'];
        $userstable  = $tables['dizkus_users'];
        $userscolumn = $tables['dizkus_users_column'];
            
        // One sql per user property to move all data from user_data table to the attributes table
        // This is the most efficient way to do this. During a test upgrade this took less than 0.3 secs for 6700
        // users and >15K of properties.
        foreach ($userscolumn as $uc) {
            if ($uc <> 'user_id') {
                $uc = DataUtil::formatforStore($uc); 
                // Set cr_date and lu_date to now, cr_uid and lu_uid will be the uid of the user the attributes belong to
                $timestring = date('Y-m-d H:i:s');
                $sql = "INSERT INTO " . $objtable . " (" . $objcolumn['attribute_name'] . ",
                                                       " . $objcolumn['object_type'] . ",
                                                       " . $objcolumn['object_id'] . ",
                                                       " . $objcolumn['value'] . ",
                                                       " . $objcolumn['cr_date'] . ",
                                                       " . $objcolumn['cr_uid'] . ",
                                                       " . $objcolumn['lu_date'] . ",
                                                       " . $objcolumn['lu_uid'] . ")
                        SELECT 'dizkus_" . $uc . "',
                               'users',
                               " . $userscolumn['user_id'] . ",
                               " . $userscolumn[$uc] . ",
                               '" . $timestring . "',
                               " . $userscolumn['user_id'] . ",
                               '" . $timestring . "',
                               " . $userscolumn['user_id'] . "
                        FROM " . $userstable;
                DBUtil::executeSQL($sql);
            }
        }
    
        // done - now drop the dizkus_users table
        DBUtil::dropTable('dizkus_users');
    
        $this->delVar('autosubscribe');
        $this->delVar('allowgravatars');
        $this->delVar('gravatarimage');
        return true;
    }

}
