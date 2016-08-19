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

use Zikula\Module\DizkusModule\Manager\PostManager;
use ModUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\RouterInterface;

/**
 * This class provides a handler to delete a topic.
 */
class SplitTopic extends \Zikula_Form_AbstractHandler
{

    /**
     * post data
     *
     * @var PostManager
     */
    private $post;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws AccessDeniedException If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
            throw new AccessDeniedException();
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
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->post->getTopicId()), RouterInterface::ABSOLUTE_URL);
            return $view->redirect($url);
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();

        $newtopic_id = ModUtil::apiFunc($this->name, 'topic', 'split', array('post' => $this->post, 'data' => $data));

        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $newtopic_id), RouterInterface::ABSOLUTE_URL);
        return $view->redirect($url);
    }

}
