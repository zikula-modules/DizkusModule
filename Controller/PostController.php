<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Controller;

use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\RouteUrl;

use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\ForumManager;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove

/**
 * 
 */
class PostController extends AbstractController
{


    /**
     * @Route("/post/edit")
     *
     * Edit post
     *
     * User interface to edit a new post
     *
     * @return string
     */
//    public function editpostAction()
//    {
//        $form = FormUtil::newForm($this->name, $this);
//
//        return new Response($form->execute('User/post/edit.tpl', new EditPost()));
//    }


    /**
     * @Route("/post/edit", options={"expose"=true})
     * @Method("POST")
     *
     * Edit a post.
     *
     * @param Request $request
     *  post The post id to edit.
     *
     * RETURN: The edit post form.
     *
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException
     *
     * @return AjaxResponse
     */
    public function editpostAction(Request $request)
    {
        $this->errorIfForumDisabled();
        $this->checkAjaxToken();
        $post_id = $request->request->get('post', null);
        $currentUserId = UserUtil::getVar('uid');
        if (!empty($post_id)) {
            $managedPost = new PostManager($post_id);
            $forum = $managedPost->get()->getTopic()->getForum();
            $managedForum = new ForumManager(null, $forum);
            if ($managedPost->get()->getPoster()->getUser_id() == $currentUserId || $managedForum->isModerator()) {
                $this->view->setCaching(false);
                $this->view->assign('post', $managedPost->get());
                // simplify our live
                $this->view->assign('postingtextareaid', 'postingtext_' . $managedPost->getId() . '_edit');
                $this->view->assign('isFirstPost', $managedPost->get()->isFirst());

                return new AjaxResponse($this->view->fetch('Ajax/editpost.tpl'));
            } else {
                throw new AccessDeniedException();
            }
        }
        throw new \InvalidArgumentException($this->__f('Error! No post ID in %s.', '\'Dizkus/Ajax/editpost()\''));
    }    
    
    /**
     * @Route("/post/update", options={"expose"=true})
     * @Method("POST")
     *
     * Update a post.
     *
     * @param Request $request
     *  postId           The post id to update.
     *  title
     *  message          The new post message.
     *  delete_post      Delete this post?
     *  attach_signature Attach signature?
     *
     * RETURN: array($action The executed action.
     *               $newText The new post text (can be empty).
     *               $redirect The page to redirect to (can be empty).
     *              )
     *
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException If the user tries to delete the only post of a topic.
     *
     * @return AjaxResponse
     */
    public function updatepostAction(Request $request)
    {
        $this->errorIfForumDisabled();
        $this->checkAjaxToken();
        $post_id = $request->request->get('postId', '');
        $title = $request->request->get('title', '');
        $message = $request->request->get('message', '');
        $delete = $request->request->get('delete_post', 0) == '1' ? true : false;
        $attach_signature = $request->request->get('attach_signature', 0) == '1' ? true : false;
        if (!empty($post_id)) {
            $message = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $message);
            $this->checkMessageLength($message);
            $managedOriginalPost = new PostManager($post_id);
            if ($delete) {
                if ($managedOriginalPost->get()->isFirst()) {
                    throw new AccessDeniedException($this->__('Error! Cannot delete the first post in a topic. Delete the topic instead.'));
                } else {
                    $response = array('action' => 'deleted');
                }
                $managedOriginalPost->delete();
                $this->dispatchHooks('dizkus.ui_hooks.post.process_delete', new ProcessHook($managedOriginalPost->getId()));
            } else {
                $data = array(
                    'title' => $title,
                    'post_text' => $message,
                    'attachSignature' => $attach_signature);
                $managedOriginalPost->update($data);
                $url = RouteUrl::createFromRoute('zikuladizkusmodule_user_viewtopic', array('topic' => $managedOriginalPost->getTopicId()), 'pid' . $managedOriginalPost->getId());
                $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedOriginalPost->getId(), $url));
                if ($attach_signature && !$this->getVar('removesignature')) {
                    // include signature in response text
                    $sig = UserUtil::getVar('signature', $managedOriginalPost->get()->getPoster_id());
                    $message .=!empty($sig) ? "<div class='dzk_postSignature'>{$this->getVar('signature_start')}<br />{$sig}<br />{$this->getVar('signature_end')}</div>" : '';
                }
                // must dzkVarPrepHTMLDisplay the message content here because the template modifies cannot be run in ajax
                $newText = ModUtil::apiFunc($this->name, 'user', 'dzkVarPrepHTMLDisplay', $message);
                // process hooks
                $newText = $this->dispatchHooks('dizkus.filter_hooks.post.filter', new FilterHook($newText))->getData();
                // process internal quotes/hooks
                $newText = ModUtil::apiFunc($this->name, 'ParseTags', 'transform', array('message' => $newText));
                $response = array(
                    'action' => 'updated',
                    'newText' => $newText);
            }

            return new AjaxResponse($response);
        }
        throw new \InvalidArgumentException($this->__f('Error! No post_id in %s.', '\'Dizkus/Ajax/updatepost()\''));
    }
    
    
    /**
     * @Route("/post/move")
     *
     * User interface to move a single post to another thread
     *
     * @return string
     */
    public function movepostAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('User/post/move.tpl', new MovePost()));
    }
    
    /**
     * @Route("/post/report")
     *
     * Report
     *
     * User interface to notify a moderator about a (bad) posting.
     *
     * @return string
     */
    public function reportAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('User/notifymod.tpl', new Report()));
    }
    
    /**
     * Checks if a message is shorter than 65535 - 8 characters.
     *
     * @param string $message The message to check.
     *
     * @throws \LengthException
     *
     * @return void
     */
    private function checkMessageLength($message)
    {
        if (!ModUtil::apiFunc($this->name, 'post', 'checkMessageLength', array('message' => $message))) {
            throw new \LengthException($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
        }
    }   
}