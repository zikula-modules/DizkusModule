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
 * This class provides a handler to manage topic subscriptions.
 */
class TopicSubscriptions extends \Zikula_Form_AbstractHandler
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
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        if (!UserUtil::isLoggedIn()) {
            return ModUtil::func('Users', 'user', 'login', array('returnpage' => ModUtil::url($this->name, 'user', 'manageTopicSubscriptions')));
        }

        $subscriptions = ModUtil::apiFunc($this->name, 'topic', 'getSubscriptions');
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

        if (count($data['topicIds']) > 0) {
            foreach (array_keys($data['topicIds']) as $topicId) {
                if ($topicId) {
                    ModUtil::apiFunc($this->name, 'Topic', 'unsubscribe', array('topic' => $topicId));
                }
            }
        }

        $url = ModUtil::url($this->name, 'user', 'manageTopicSubscriptions');

        return $view->redirect($url);
    }

}
