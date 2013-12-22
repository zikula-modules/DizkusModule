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
use LogUtil;
use SecurityUtil;
use UserUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * @throws AccessDeniedHttpException If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!UserUtil::isLoggedIn()) {
            return ModUtil::func('Users', 'user', 'login', array('returnpage' => ModUtil::url($this->name, 'user', 'manageForumSubscriptions')));
        }

        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ) || !UserUtil::isLoggedIn()) {
            throw new AccessDeniedHttpException(LogUtil::getErrorMsgPermission());
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
        $url = ModUtil::url($this->name, 'user', 'manageForumSubscriptions');

        $response = new RedirectResponse(System::normalizeUrl($url));
        return $response;
    }

}
