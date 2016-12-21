<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Form\Handler\User;

use Zikula\DizkusModule\Manager\TopicManager;
use ModUtil;
use DataUtil;
use Zikula_Form_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\RouterInterface;

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
     * @param Zikula_Form_View $view current Zikula_Form_View instance
     *
     * @return boolean
     *
     * @throws AccessDeniedException if the current user does not have adequate permissions to perform this function
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new AccessDeniedException();
        }

        $this->topic_id = (int)$this->request->query->get('topic');

        $managedTopic = new TopicManager($this->topic_id);

        $view->assign($managedTopic->get()->toArray());
        $view->assign('emailsubject', $managedTopic->get()->getTitle());
        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->topic_id), RouterInterface::ABSOLUTE_URL);
        $view->assign('message', DataUtil::formatForDisplay($this->__('Hello! Please visit this link. I think it will be of interest to you.')) . "\n\n" . $url);

        return true;
    }

    /**
     * Handle form submission.
     *
     * @param Zikula_Form_View $view  current Zikula_Form_View instance
     * @param array            &$args Arguments
     *
     * @return bool|void
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->topic_id), RouterInterface::ABSOLUTE_URL);
        // rewrite to topic if cancel was pressed
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();

        ModUtil::apiFunc($this->name, 'notify', 'email', array(
            'sendto_email' => $data['sendto_email'],
            'message' => $data['message'],
            'subject' => $data['emailsubject']
        ));

        return $view->redirect($url);
    }
}
