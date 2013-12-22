<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Form\Handler\User;

use Zikula\Module\DizkusModule\Manager\PostManager;
use ModUtil;
use LogUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
     * @throws AccessDeniedHttpException If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
            throw new AccessDeniedHttpException(LogUtil::getErrorMsgPermission());
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
            $url = ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $this->post->getTopicId()));
            $response = new RedirectResponse(System::normalizeUrl($url));
            return $response;
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();

        $newtopic_id = ModUtil::apiFunc($this->name, 'topic', 'split', array('post' => $this->post, 'data' => $data));

        $url = ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $newtopic_id));

        $response = new RedirectResponse(System::normalizeUrl($url));
        return $response;
    }

}
