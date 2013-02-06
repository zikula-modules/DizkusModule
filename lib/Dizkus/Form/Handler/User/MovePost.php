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
class Dizkus_Form_Handler_User_MovePost extends Zikula_Form_AbstractHandler
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
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        // get the input
        $id = (int)$this->request->query->get('post');
        
        $this->post_id = $id;

        $post = new Dizkus_Manager_Post($id);
        
        $this->old_topic_id = $post->getTopicId();

        if ($post->isFirst()) {
            LogUtil::registerError('You can not move the first post of a topic!');
            $url = ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $post->getTopicId()));
            return System::redirect($url);
        }

        return true;

        //if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $post)) {
        //    // user is not allowed to moderate this forum
        //    return LogUtil::registerPermissionError();
        //}
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
            $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $this->old_topic_id, 'start' => '0#pid' . $this->post_id));
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();
        $data['old_topic_id'] = $this->old_topic_id;
        $data['post_id'] = $this->post_id;

        $newTopicPostCount = ModUtil::apiFunc('Dizkus', 'user', 'movepost', $data);
        $start = $newTopicPostCount - $newTopicPostCount % ModUtil::getVar('Dizkus', 'posts_per_page', 15);

        $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $data['to_topic_id'], 'start' => $start)) . '#pid' . $this->post_id;
        return $view->redirect($url);
    }

}