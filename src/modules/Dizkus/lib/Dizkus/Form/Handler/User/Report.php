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
 * This class provides a handler to report posts.
 */
class Dizkus_Form_Handler_User_Report extends Zikula_Form_AbstractHandler
{
    /**
     * post id
     *
     * @var integer
     */
    private $post_id;


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
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

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
        // get the input
        $post_id = (int)$this->request->query->get('post');
        $post = ModUtil::apiFunc('Dizkus', 'user', 'readpost', array('post_id' => $post_id));
        
        
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('Dizkus','user','viewtopic', array('topic' => $post['topic_id'], 'start' => '0')).'#pid'.$post['post_id'];
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        
        // some spam checks:
        // - remove html and compare with original comment
        // - use censor and compare with original comment
        // if only one of this comparisons fails -> trash it, it is spam.
        if (!UserUtil::isLoggedIn()) {
            if (strip_tags($data['comment']) <> $data['comment']) {
                // possibly spam, stop now
                // get the users ip address and store it in zTemp/Dizkus_spammers.txt
                dzk_blacklist();
                // set 403 header and stop
                header('HTTP/1.0 403 Forbidden');
                System::shutDown();
            }
        }
        
        

    
        ModUtil::apiFunc('Dizkus', 'user', 'notify_moderator',
                        array('post'    => $post,
                            'comment' => $data['comment']));
    
        $start = ModUtil::apiFunc('Dizkus', 'user', 'get_page_from_topic_replies',
                                  array('topic_replies' => $post['topic_replies']));
    
        $url = ModUtil::url('Dizkus', 'user', 'viewtopic',
                                       array('topic' => $post['topic_id'],
                                             'start' => $start));
        return $view->redirect($url);        
    }
}