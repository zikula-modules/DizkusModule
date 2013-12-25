<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Form\Handler\Admin;

use ModUtil;
use SecurityUtil;
use UserUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This class provides a handler to manage subscriptions.
 */
class ManageSubscriptions extends \Zikula_Form_AbstractHandler
{

    /**
     * user id
     *
     * @var integer
     */
    private $_uid;

    /**
     * username
     *
     * @var string
     */
    private $_username;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $topicsubscriptions = array();
        $forumsubscriptions = array();
        $this->_uid = $this->request->query->get('uid', 0);

        if (!empty($this->_uid)) {
            $this->_username = UserUtil::getVar('uname', $this->_uid);
        }

        if (!empty($this->_uid)) {
            $params = array('uid' => $this->_uid);
            $topicsubscriptions = ModUtil::apiFunc($this->name, 'Topic', 'getSubscriptions', $params);
            $forumsubscriptions = ModUtil::apiFunc($this->name, 'Forum', 'getSubscriptions', $params);
        }

        $view->assign('username', $this->_username);
        $view->assign('topicsubscriptions', $topicsubscriptions);
        $view->assign('forumsubscriptions', $forumsubscriptions);
        $view->caching = false;

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

        if (!empty($data)) {
            foreach ($data['forumsubscriptions'] as $id => $selected) {
                if ($selected) {
                    ModUtil::apiFunc($this->name, 'user', 'unsubscribe_forum_by_id', $id);
                }
            }

            foreach ($data['topicsubscriptions'] as $id => $selected) {
                if ($selected) {
                    ModUtil::apiFunc($this->name, 'user', 'unsubscribe_TopicById', $id);
                }
            }

            $url = ModUtil::url($this->name, 'admin', 'managesubscriptions', array('uid' => $this->_uid));
        } else {
            $this->_username = $this->request->request->get('username', '');
            $this->_uid = UserUtil::getIdFromName($this->_username);
            if ($this->_uid) {
                $url = ModUtil::url($this->name, 'admin', 'managesubscriptions', array('uid' => $this->_uid));
            } else {
                $this->request->getSession()->getFlashBag()->add('error', $this->__f('Could not find username "%s". Please try again.', $this->_username));
                $url = ModUtil::url($this->name, 'admin', 'managesubscriptions');
            }
        }
        return new RedirectResponse(System::normalizeUrl($url));
    }

}
