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
use SecurityUtil;
use UserUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * This class provides a handler to manage topic subscriptions.
 */
class ForumSubscriptions extends \Zikula_Form_AbstractHandler
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
                'returnpage' => $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', array(), RouterInterface::ABSOLUTE_URL),
                '_controller' => 'zikulausersmodule_user_login');

            $subRequest = $view->getRequest()->duplicate(array(), null, $path);
            $httpKernel = $view->getContainer()->get('http_kernel');
            $response = $httpKernel->handle(
                $subRequest,
                HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ) || !UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $subscriptions = ModUtil::apiFunc($this->name, 'Forum', 'getSubscriptions');
        $view->assign('subscriptions', $subscriptions);

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
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();
        if (count($data['forumIds']) > 0) {
            foreach ($data['forumIds'] as $forumId => $selected) {
                if ($selected) {
                    ModUtil::apiFunc($this->name, 'Forum', 'unsubscribe', array('forum' => $forumId));
                }
            }
        }

        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', array(), RouterInterface::ABSOLUTE_URL);
        return $view->redirect($url);
    }

}
