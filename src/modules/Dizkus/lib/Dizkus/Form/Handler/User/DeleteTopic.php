<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Form_Handler_User_DeleteTopic extends Zikula_Form_AbstractHandler
{
    private $topic_id;
    
    function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }
        
        $this->topic_id = (int)FormUtil::getPassedValue('topic');
        
        
        if (empty($this->topic_id)) {
            $post_id  = (int)FormUtil::getPassedValue('post');
            if (empty($post_id)) {
                return LogUtil::registerArgsError();
            }
            $this->topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_postid', array('post_id' => $post_id));
        }
    
        $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic', array(
            'topic_id' => $this->topic_id,
            'count'    => false)
        );
    
        if ($topic['access_moderate'] <> true) {
            return LogUtil::registerPermissionError();
        }
        
        $view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
        
        return true;
    }

    function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            return $view->redirect(ModUtil::url('Dizkus','user','viewtopic', array('topic' => $this->topic_id)));
        }

        $forum_id = ModUtil::apiFunc('Dizkus', 'user', 'deletetopic', array('topic_id' => $this->topic_id));
        
        return System::redirect(ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id)));        
    }
}