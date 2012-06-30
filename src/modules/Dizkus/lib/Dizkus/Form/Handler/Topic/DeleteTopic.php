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
 * This class provides a handler to delete a topic.
 */
class Dizkus_Form_Handler_Topic_DeleteTopic extends Zikula_Form_AbstractHandler
{
    /**
     * topic id
     *
     * @var integer
     */
    private $topic_id;


    /**
     * topic poster uid
     *
     * @var integer
     */
    private $topic_poster;


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

        $this->topic_id = (int)$this->request->query->get('topic');
        
        
        if (empty($this->topic_id)) {
            $post_id  = (int)$this->request->query->get('post');
            if (empty($post_id)) {
                return LogUtil::registerArgsError();
            }
            $this->topic_id = ModUtil::apiFunc('Dizkus', 'topic', 'get_topicid_by_postid', array('post_id' => $post_id));
        }

        $topic = ModUtil::apiFunc('Dizkus', 'topic', 'readtopic', array(
            'topic_id' => $this->topic_id,
            'count'    => false)
        );
    
        $this->topic_poster = $topic['topic_poster'];
        
      
        $view->assign('topicTitle', $topic['topic_title']);
        
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
        // rewrite to topic if cancel was pressed
        if ($args['commandName'] == 'cancel') {
            return $view->redirect(ModUtil::url('Dizkus','user','viewtopic', array('topic' => $this->topic_id)));
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();
        

        // send the poster a reason why his/her post was deleted
        if ($data['sendReason'] && !empty($data['reason'])) {
            $toaddress = UserUtil::getVar('email', $this->topic_poster);
            ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array(
                    'toaddress' => $toaddress,
                    'subject'   => $this->__('Post deleted'),
                    'body'      => $data['reason'],
                    'html'      => true
                )
            );
            LogUtil::registerStatus($this->__('Email sended!'));
        }

        // redirect to the forum of the deleted topic
        $forum_id = ModUtil::apiFunc('Dizkus', 'topic', 'delete', $this->topic_id);
        $url = ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $forum_id));
        return $view->redirect($url);
    }
}