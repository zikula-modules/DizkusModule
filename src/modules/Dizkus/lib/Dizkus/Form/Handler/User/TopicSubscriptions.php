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
class Dizkus_Form_Handler_User_TopicSubscriptions extends Zikula_Form_AbstractHandler
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
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        if (!UserUtil::isLoggedIn()) {
            return ModUtil::func('Users', 'user', 'loginscreen', array('redirecttype' => 1));
        }

        $subscriptions = ModUtil::apiFunc('Dizkus', 'user', 'get_topic_subscriptions');
        $view->assign('subscriptions', $subscriptions);
        $view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
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

        if (count($data['topicIds']) > 0) {
            foreach ($data['topicIds'] as $topicId) {
                if ($topicId) {
                    ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_topic', array('topic_id' => $topicId));
                }
            }
        }

        $url = ModUtil::url($this->name, 'User', 'topicsubscriptions');
        return $view->redirect($url);
    }
}