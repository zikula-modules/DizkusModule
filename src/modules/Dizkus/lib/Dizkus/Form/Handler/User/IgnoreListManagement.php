<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Form_Handler_User_IgnoreListManagement extends Zikula_Form_AbstractHandler
{
    function initialize(&$render)
    {   
        // prepare list    
        $ignorelist_handling = ModUtil::getVar('Dizkus','ignorelist_handling');
        $ignorelist_options = array();
        switch ($ignorelist_handling)
        {
            case 'strict':
                $ignorelist_options[] = array('text' => $this->__('Strict'), 'value' => 'strict');

            case 'medium':
                $ignorelist_options[] = array('text' => $this->__('Medium'), 'value' => 'medium');

            default:
                $ignorelist_options[] = array('text' => $this->__('None'), 'value' => 'none');
        }

        // get user's configuration
        $render->caching = false;
        $render->add_core_data(CONFIG_MODULE);

        // assign data
        $render->assign('ignorelist_options',    $ignorelist_options);
        $render->assign('ignorelist_myhandling', ModUtil::apiFunc('Dizkus','user','get_settings_ignorelist',array('uid' => UserUtil::getVar('uid'))));
        return true;
    }

    function handleCommand(&$render, $args)
    {
        if ($args['commandName'] == 'update') {
            // Security check 
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT)) {
                return LogUtil::registerPermissionError();
            }

            // get the Form data and do a validation check
            $obj = $render->getValues();          
            if (!$render->isValid()) {
                return false;
            }

            // update user's attributes
            $uid = UserUtil::getVar('uid'); 
            $user = DBUtil::selectObjectByID('users', $uid, 'uid', null, null, null, false);        $obj['uid'] = UserUtil::getVar('uid');
            $user['__ATTRIBUTES__']['dzk_ignorelist_myhandling'] = $obj['ignorelist_myhandling']; 

            // store attributes 
            DBUtil::updateObject($user, 'users', '', 'uid');

            LogUtil::registerStatus($this->__('Done! Updated the \'Ignore list\' settings.'));

            return $render->redirect(ModUtil::url('Dizkus','user','prefs'));
        }

        return true;
    }
}
