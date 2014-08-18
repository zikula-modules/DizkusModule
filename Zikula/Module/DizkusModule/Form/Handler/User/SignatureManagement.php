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

use ModUtil;
use UserUtil;
use SecurityUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\ModUrl;
use ZLanguage;

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
     * @throws AccessDeniedException If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!UserUtil::isLoggedIn()) {
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_signaturemanagement');

            return ModUtil::func('Users', 'user', 'login', array('returnpage' => $url));
        }
        // Security check
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_COMMENT) || (!(ModUtil::getVar($this->name, 'signaturemanagement') == 'yes'))) {
            throw new AccessDeniedException();
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
            if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_COMMENT)) {
                throw new AccessDeniedException();
            }

            // get the Form data and do a validation check
            $obj = $view->getValues();
            if (!$view->isValid()) {
                return false;
            }

            UserUtil::setVar('signature', $obj['signature']);
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Signature has been updated.'));

            // redirect to user preferences page
            return $view->redirect(new ModUrl($this->name, 'user', 'prefs', ZLanguage::getLanguageCode()));
        }

        return true;
    }

}
