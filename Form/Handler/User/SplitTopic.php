<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Form\Handler\User;

use Dizkus\Manager\PostManager;
/**
 * This class provides a handler to delete a topic.
 */
class SplitTopic extends \Zikula_Form_AbstractHandler
{

    /**
     * post data
     *
     * @var arrat
     */
    private $post;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        $postId = (int) $this->request->query->get('post');
        $this->post = new PostManager($postId);

        $this->view->assign($this->post->toArray());
        $this->view->assign('newsubject', $this->__('Split') . ': ' . $this->post->get()->getTopic()->getTitle());

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
        // rewrite to topic if cancel was pressed
        if ($args['commandName'] == 'cancel') {
            return $view->redirect(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $this->topic_id)));
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();

        $newtopic_id = ModUtil::apiFunc('Dizkus', 'topic', 'split', array('post' => $this->post, 'data' => $data));

        $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $newtopic_id));

        return $view->redirect($url);
    }

}
