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
 * This class provides a handler to move a post.
 */
class Dizkus_Form_Handler_Post_MovePost extends Zikula_Form_AbstractHandler
{
    /**
     * post id
     *
     * @var integer
     */
    private $post_id;


    /**
     * old post id
     *
     * @var integer
     */
    private $old_topic_id;


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

        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $this->post_id = (int)$this->request->query->get('post');
        $post = ModUtil::apiFunc('Dizkus', 'post', 'readpost', array('post_id' => $this->post_id));
        $this->old_topic_id = $post['topic_id'];
    
        if (!allowedtomoderatecategoryandforum($post['cat_id'], $post['forum_id'])) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
        
        $view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
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
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('Dizkus','user','viewtopic', array('topic' => $this->old_topic_id, 'start' => '0#pid'.$this->post_id));
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();
        $data['old_topic_id'] = $this->old_topic_id;
        $data['post_id']      = $this->post_id;
    
        $start = ModUtil::apiFunc('Dizkus', 'post', 'movepost', $data);
        $start = $start - $start%ModUtil::getVar('Dizkus', 'posts_per_page', 15);
        
        
        $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $data['to_topic_id'], 'start' => $start)).'#pid'.$this->post_id;
        return $view->redirect($url);        
    }
}