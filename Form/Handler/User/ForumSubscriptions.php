<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * This class provides a handler to manage topic subscriptions.
 */
class Dizkus_Form_Handler_User_ForumSubscriptions extends Zikula_Form_AbstractHandler
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
    function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ) || !UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        $subscriptions = ModUtil::apiFunc('Dizkus', 'Forum', 'getSubscriptions');
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
    function handleCommand(Zikula_Form_View $view, &$args)
    {
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();
        if (count($data['forumIds']) > 0) {
            foreach (array_keys($data['forumIds']) as $forumId) {
                if ($forumId) {
                    ModUtil::apiFunc('Dizkus', 'Forum', 'unsubscribe', array('forum' => $forumId));
                }
            }
        }
        $url = ModUtil::url($this->name, 'user', 'manageForumSubscriptions');

        return $view->redirect($url);
    }

}