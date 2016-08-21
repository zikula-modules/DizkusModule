<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Form\Handler\User;

use ModUtil;
use UserUtil;
use SecurityUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
            $path = array(
                'returnpage' => $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_signaturemanagement', array(), RouterInterface::ABSOLUTE_URL),
                '_controller' => 'zikulausersmodule_user_login');

            $subRequest = $view->getRequest()->duplicate(array(), null, $path);
            $httpKernel = $view->getContainer()->get('http_kernel');
            $response = $httpKernel->handle(
                $subRequest,
                HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }
        // Security check
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_COMMENT) || !ModUtil::getVar($this->name, 'signaturemanagement')) {
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
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_prefs', array(), RouterInterface::ABSOLUTE_URL);
            return $view->redirect($url);
        }

        return true;
    }

}
