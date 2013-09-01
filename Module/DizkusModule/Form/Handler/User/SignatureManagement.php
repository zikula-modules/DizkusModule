<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Form\Handler\User;

/**
 * This class provides a handler for the signature management.
 */
class SignatureManagement extends \Zikula_Form_AbstractHandler
{

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!UserUtil::isLoggedIn()) {
            return ModUtil::func('Users', 'user', 'login', array('returnpage' => ModUtil::url($this->name, 'user', 'signaturemanagement')));
        }
        // Security check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT) || (!(ModUtil::getVar('Dizkus', 'signaturemanagement') == 'yes'))) {
            return LogUtil::registerPermissionError();
        }

        $view->assign('signature', UserUtil::getVar('signature'));
        $view->caching = false;
        $view->add_core_data();

        return true;
    }

    /**
     * Handle form submission.
     *
     * @param Zikula_Form_View $view  Current Zikula_Form_View instance.
     * @param array            &$args Arguments.
     *
     * @return bool|void
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'update') {
            // Security check
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT)) {
                return LogUtil::registerPermissionError();
            }

            // get the Form data and do a validation check
            $obj = $view->getValues();
            if (!$view->isValid()) {
                return false;
            }

            UserUtil::setVar('signature', $obj['signature']);
            LogUtil::registerStatus($this->__('Done! Signature has been updated.'));

            // redirect to user preferences page
            $url = ModUtil::url('Dizkus', 'user', 'prefs');

            return $view->redirect($url);
        }

        return true;
    }

}
