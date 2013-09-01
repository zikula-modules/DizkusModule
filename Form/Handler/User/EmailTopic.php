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

use Dizkus\Manager\TopicManager;
/**
 * This class provides a handler to email a topic.
 */
class EmailTopic extends \Zikula_Form_AbstractHandler
{

    /**
     * topic id
     *
     * @var integer
     */
    private $topic_id;

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
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        $this->topic_id = (int)$this->request->query->get('topic');

        $managedTopic = new TopicManager($this->topic_id);

        $view->assign($managedTopic->get());
        $view->assign('emailsubject', $managedTopic->get()->getTitle());
        $view->assign('message', DataUtil::formatForDisplay($this->__('Hello! Please visit this link. I think it will be of interest to you.')) . "\n\n" . ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $this->topic_id), null, null, true));

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
        // rewrite to topic if cancel was pressed
        if ($args['commandName'] == 'cancel') {
            return $view->redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $this->topic_id)));
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();

        ModUtil::apiFunc('Dizkus', 'notify', 'email', array(
            'sendto_email' => $data['sendto_email'],
            'message' => $data['message'],
            'subject' => $data['emailsubject']
        ));
        $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $this->topic_id));

        return $view->redirect($url);
    }

}
