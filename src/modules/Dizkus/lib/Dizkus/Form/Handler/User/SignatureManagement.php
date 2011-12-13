<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Form_Handler_User_SignatureManagement extends Zikula_Form_AbstractHandler
{
    function initialize(&$render)
    {       
        $render->assign('signature', UserUtil::getVar('signature'));
        $render->caching = false;
        $render->add_core_data(CONFIG_MODULE);

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

            UserUtil::setVar('signature',$obj['signature']);
            LogUtil::registerStatus($this->__('Done! Signature has been updated.'));

            return $render->redirect(ModUtil::url('Dizkus','user','prefs'));
        }

        return true;
    }
}
