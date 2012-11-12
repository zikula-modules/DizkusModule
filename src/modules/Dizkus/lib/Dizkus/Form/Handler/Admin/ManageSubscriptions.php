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
 * This class provides a handler to manage subscriptions.
 */
class Dizkus_Form_Handler_Admin_ManageSubscriptions extends Zikula_Form_AbstractHandler
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
    function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN) ) {
            return LogUtil::registerPermissionError();
        }

        $this->_uid = 0;
        $topicsubscriptions = array();
        $forumsubscriptions = array();
        $this->_username = $this->request->query->get('username', '');

        if (!empty($this->_username)) {
            $this->_uid = UserUtil::getIDFromName($this->_username);
        }

        if (!empty($this->_uid)) {
            $topicsubscriptions = ModUtil::apiFunc('Dizkus', 'user', 'getTopicSubscriptions', $this->_uid);
            $forumsubscriptions = ModUtil::apiFunc('Dizkus', 'user', 'getForumSubscriptions', $this->_uid);
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
    function handleCommand(Zikula_Form_View $view, &$args)
    {
        
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();
        
        foreach ($data['forumsubscriptions'] as $id => $selected) {
            if ($selected) {
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_forum_by_id', $id);
            }
        }

        foreach ($data['topicsubscriptions'] as $id => $selected) {
            if ($selected) {
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_TopicById', $id);
            }
        }

        $url = ModUtil::url('Dizkus', 'admin', 'managesubscriptions', array('username' => $this->_username ));
        return $view->redirect($url);
    }
}
