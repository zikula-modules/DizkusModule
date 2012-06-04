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
    private $uid;


    /**
     * username
     *
     * @var string
     */
    private $username;


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
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN) ) {
            return LogUtil::registerPermissionError();
        }

        $this->uid = 0;
        $topicsubscriptions = array();
        $forumsubscriptions = array();
        $this->username = $this->request->query->get('username', '');

        if (!empty($this->username)) {
            $this->uid = UserUtil::getIDFromName($this->username);
        }
        if (!empty($this->uid)) {
            $topicsubscriptions = ModUtil::apiFunc('Dizkus', 'user', 'getTopicSubscriptions', $this->uid);
            $forumsubscriptions = ModUtil::apiFunc('Dizkus', 'user', 'getForumSubscriptions', $this->uid);
        }

        $view->assign('username', $this->username);
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
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_topic_by_id', $id);
            }
        }

        $url = ModUtil::url('Dizkus', 'admin', 'managesubscriptions', array('username' => $this->username));
        return $view->redirect($url);
    }
}
