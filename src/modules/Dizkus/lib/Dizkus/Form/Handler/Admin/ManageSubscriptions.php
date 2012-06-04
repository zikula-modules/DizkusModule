<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Form_Handler_Admin_ManageSubscriptions extends Zikula_Form_AbstractHandler
{
    private $uid, $username;
    
    function initialize(Zikula_Form_View $view)
    {
        
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN) ) {
            return LogUtil::registerPermissionError();
        }
        
        $this->uid = 0;
        $topicsubscriptions = array();
        $forumsubscriptions = array();
        
        $this->username = FormUtil::getPassedValue('username', '');

        if (!empty($this->username)) {
            $this->uid = UserUtil::getIDFromName($this->username);
        }
        if (!empty($this->uid)) {
            $topicsubscriptions = ModUtil::apiFunc('Dizkus', 'user', 'getTopicSubscriptions', $this->uid);
            $forumsubscriptions = ModUtil::apiFunc('Dizkus', 'user', 'getForumSubscriptions', $this->uid);
        }
                
        
        $view->assign('username', $username);
        $view->assign('topicsubscriptions', $topicsubscriptions);
        $view->assign('forumsubscriptions', $forumsubscriptions);
        
        
        $this->view->caching = false;


        return true;
    }

    function handleCommand(Zikula_Form_View $view, &$args)
    {
        
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        
        $data = $view->getValues();
        
        foreach($data['forumsubscriptions'] as $id => $selected) {
            if($selected) {
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_forum_by_id', $id);
            }
        }
        
        foreach($data['topicsubscriptions'] as $id => $selected) {
            if($selected) {
                ModUtil::apiFunc('Dizkus', 'user', 'unsubscribe_topic_by_id', $id);
            }
        }
        
        
        $url = ModUtil::url('Dizkus', 'admin', 'managesubscriptions', array('username' => $this->username));
        return $view->redirect($url);

    }
}
