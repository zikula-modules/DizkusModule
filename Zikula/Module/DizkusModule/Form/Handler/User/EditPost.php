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
use System;
use Zikula_Form_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\ModUrl;
use ZLanguage;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;

/**
 * This class provides a handler to create a new topic.
 */
class EditPost extends \Zikula_Form_AbstractHandler
{

    /**
     * PostManager
     *
     * @var integer
     */
    private $_post;

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
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new AccessDeniedException();
        }

        // get the input
        $id = (int)$this->request->query->get('post');

        if (!isset($id)) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Missing post id.'));
            return $view->redirect(new ModUrl($this->name, 'user', 'index', ZLanguage::getLanguageCode()));
        }

        $this->_post = new PostManager($id);
        $view->assign($this->_post->toArray());
        $view->assign('preview', false);

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
        $data = $view->getValues();
        $deleting = (isset($data['delete']) && $data['delete'] === true);
        $fragment = $deleting ? null : 'pid' . $this->_post->getId();
        $url = new ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $this->_post->getTopicId()), $fragment);

        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        // check hooked modules for validation
        $hookarea = $deleting ? 'dizkus.ui_hooks.post.validate_delete' : 'dizkus.ui_hooks.post.validate_edit';
        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->dispatchHooks($hookarea, $hook)->getValidators();
        if ($hookvalidators->hasErrors()) {
            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
        }

        // check to see if the post contains spam
        if (ModUtil::apiFunc($this->name, 'user', 'isSpam', $this->_post->get())) {
            return $this->view->registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
        }

        if ($deleting) {
            if ($this->_post->get()->isFirst()) {
                return $this->view->registerError($this->__('Error! Cannot delete the first post in a topic. Delete the topic instead.'));
            }
            $this->_post->delete();
            $this->dispatchHooks('dizkus.ui_hooks.post.process_delete', new ProcessHook($this->_post->getId()));

            return $view->redirect($url);
        }
        unset($data['delete']);

        $data['post_text'] = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $data['post_text']);

        $this->_post->prepare($data);

        // show preview
        if ($args['commandName'] == 'preview') {
            $view->assign('preview', true);
            $view->assign('post', $this->_post->toArray());
            $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
            $view->assign('last_visit_unix', $lastVisitUnix);
            $view->assign('data', $data);

            return true;
        }

        // store post
        $this->_post->update();
        $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($this->_post->getId(), $url));

        // redirect to the new topic
        return $view->redirect($url);
    }

}
