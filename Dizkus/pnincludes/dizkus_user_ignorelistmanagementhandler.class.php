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

class Dizkus_user_ignorelistmanagementHandler
{
    function initialize(&$render)
    {   
        $dom = ZLanguage::getModuleDomain('Dizkus');
        
        // prepare list    
        $ignorelist_handling = pnModGetVar('Dizkus','ignorelist_handling');
        $ignorelist_options = array();
        switch ($ignorelist_handling) {
          case 'strict':
            $ignorelist_options[] = array('text' => __('strict', $dom), 'value' => 'strict');
          case 'medium':
            $ignorelist_options[] = array('text' => __('medium', $dom), 'value' => 'medium');
          default:
            $ignorelist_options[] = array('text' => __('none', $dom), 'value' => 'none');
        }
        // get user's configuration
        $render->caching = false;
        $render->add_core_data('PNConfig');
        // assign data
        $render->assign('ignorelist_options',    $ignorelist_options);
        $render->assign('ignorelist_myhandling', pnModAPIFunc('Dizkus','user','get_settings_ignorelist',array('uid' => pnUserGetVar('uid'))));
        return true;
    }
    function handleCommand(&$render, &$args)
    {
        $dom = ZLanguage::getModuleDomain('Dizkus');

        if ($args['commandName']=='update') {
            // Security check 
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT)) return LogUtil::registerPermissionError();

            // get the pnForm data and do a validation check
            $obj = $render->pnFormGetValues();          
            if (!$render->pnFormIsValid()) return false;

            // update user's attributes
            $uid = pnUserGetVar('uid'); 
            $user = DBUtil::selectObjectByID('users', $uid, 'uid', null, null, null, false);        $obj['uid'] = pnUserGetVar('uid');
            $user['__ATTRIBUTES__']['dzk_ignorelist_myhandling'] = $obj['ignorelist_myhandling']; 
            
            // store attributes 
            DBUtil::updateObject($user, 'users', '', 'uid');

            LogUtil::registerStatus(__('Done! Updated \'ignore list\' settings.', $dom));
            
            return $render->pnFormRedirect(pnModURL('Dizkus','user','prefs'));
        }
        return true;
    }
}
