<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Controller;

use ModUtil;
use UserUtil;
use DataUtil;
use SessionUtil;
use SecurityUtil;
use System;
use ZLanguage;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\Core\Response\Ajax\UnavailableResponse;
use Zikula\Core\Response\Ajax\BadDataResponse;
use Zikula\Core\Response\PlainResponse;
use Zikula\Core\ModUrl;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\Hook\FilterHook;
use Zikula\Module\DizkusModule\Entity\RankEntity;
use Zikula\Module\DizkusModule\Manager\TopicManager;
use Zikula\Module\DizkusModule\Manager\PostManager;
use Zikula\Module\DizkusModule\Manager\ForumUserManager;
use Zikula\Module\DizkusModule\Manager\ForumManager;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/ajax")
 */
class AjaxController extends \Zikula_Controller_AbstractAjax
{

    /**
     * Checks if the forum is disabled.
     *
     * @throws AccessDeniedException
     *
     * @return void
     */
    private function errorIfForumDisabled()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            throw new AccessDeniedException(strip_tags($this->getVar('forum_disabled_info')));
        }
    }

    /**
     * Checks if a message is shorter than 65535 - 8 characters.
     *
     * @param string $message The message to check.
     *
     * @throws FatalErrorException
     *
     * @return void
     */
    private function checkMessageLength($message)
    {
        if (!ModUtil::apiFunc($this->name, 'post', 'checkMessageLength', array('message' => $message))) {
            throw new FatalErrorException($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
        }
    }

    /**
     * Create and configure the view for the controller.
     *
     * @return void
     *
     * @note This is necessary because the Zikula_Controller_AbstractAjax overrides this method located in Zikula_AbstractController.
     */
    protected function configureView()
    {
        $this->setView();
        $this->view->setController($this);
        $this->view->assign('controller', $this);
    }

    /**
     * @Route("/reply", options={"expose"=true})
     * @Method("POST")
     *
     * Reply to a topic (or just preview).
     *
     * @param Request $request
     *  topic            The topic id to reply to.
     *  message          The post message.
     *  attach_signature Attach signature?
     *  subscribe_topic  Subscribe to topic.
     *  preview          Is this a preview only?
     *
     * RETURN: array($data The rendered post.
     *               $post_id The post id.
     *              )
     *
     * @return Response|AjaxResponse
     */
    public function replyAction(Request $request)
    {
        $this->errorIfForumDisabled();
        $this->checkAjaxToken();
        $topic_id = $request->request->get('topic', null);
        $message = $request->request->get('message', '');
        $attach_signature = $request->request->get('attach_signature', 0) == '1' ? true : false;
        $subscribe_topic = $request->request->get('subscribe_topic', 0) == '1' ? true : false;
        $preview = $request->request->get('preview', 0) == '1' ? true : false;
        $message = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $message);
        $managedTopic = new TopicManager($topic_id);
        $start = 1;
        $this->checkMessageLength($message);
        $data = array(
            'topic_id' => $topic_id,
            'post_text' => $message,
            'attachSignature' => $attach_signature);
        $managedPost = new PostManager();
        $managedPost->create($data);
        // process validation hooks
        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->dispatchHooks('dizkus.ui_hooks.post.validate_edit', $hook)->getValidators();
        /** @var $hookvalidators \Zikula\Core\Hook\ValidationProviders */
        if ($hookvalidators->hasErrors()) {
            foreach ($hookvalidators->getErrors() as $error) {
                $this->request->getSession()->getFlashBag()->add('error', "Error! $error");
            }
            $preview = true;
        }
        // check to see if the post contains spam
        if (ModUtil::apiFunc($this->name, 'user', 'isSpam', $managedPost->get())) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Your post contains unacceptable content and has been rejected.'));
            $preview = true;
        }
        if ($preview == false) {
            $managedPost->persist();
            if ($subscribe_topic) {
                ModUtil::apiFunc($this->name, 'topic', 'subscribe', array('topic' => $topic_id));
            } else {
                ModUtil::apiFunc($this->name, 'topic', 'unsubscribe', array('topic' => $topic_id));
            }
            $start = ModUtil::apiFunc($this->name, 'user', 'getTopicPage', array('replyCount' => $managedPost->get()->getTopic()->getReplyCount()));
            $params = array('topic' => $topic_id, 'start' => $start);
            $url = new ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), $params, 'pid' . $managedPost->getId());
            $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedPost->getId(), $url));
            // notify topic & forum subscribers
            ModUtil::apiFunc($this->name, 'notify', 'emailSubscribers', array('post' => $managedPost->get()));
            $post = $managedPost->get()->toArray();
            $permissions = ModUtil::apiFunc($this->name, 'permission', 'get', array('forum_id' => $managedPost->get()->getTopic()->getForum()->getForum_id()));
        } else {
            // preview == true, create fake post
            $managedPoster = new ForumUserManager();
            $post = array(
                'post_id' => 99999999999,
                'topic_id' => $topic_id,
                'poster' => $managedPoster->toArray(),
                'post_time' => time(),
                'attachSignature' => $attach_signature,
                'post_text' => $message,
                'subscribe_topic' => $subscribe_topic,
                'userAllowedToEdit' => false);
            // Do not show edit link
            $permissions = array();
        }
        $this->view->setCaching(false);
        $this->view->assign('topic', $managedTopic->get());
        $this->view->assign('post', $post);
        $this->view->assign('start', $start);
        $this->view->assign('preview', $preview);
        $this->view->assign('permissions', $permissions);
        list(, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => RankEntity::TYPE_POSTCOUNT));
        $this->view->assign('ranks', $ranks);

        if ($this->request->getSession()->getFlashBag()->has('error')) {
            $errors = implode('\n', $this->request->getSession()->getFlashBag()->get('error'));
            return new Response($errors, 500);
        } else {
            return new AjaxResponse(array(
                'data' => $this->view->fetch('User/post/single.tpl'),
                'post_id' => $post['post_id']));
        }
    }

    /**
     * @Route("/editpost", options={"expose"=true})
     * @Method("POST")
     *
     * Edit a post.
     *
     * @param Request $request
     *  post The post id to edit.
     *
     * RETURN: The edit post form.
     *
     * @throws FatalErrorException
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
        throw new FatalErrorException($this->__('Error! No post ID in \'Dizkus/Ajax/editpost()\'.'));
    }

    /**
     * @Route("/updatepost", options={"expose"=true})
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
     * @throws FatalErrorException
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
                $url = new ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array(
                    'topic' => $managedOriginalPost->getTopicId()), 'pid' . $managedOriginalPost->getId());
                $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedOriginalPost->getId(), $url));
                if ($attach_signature && $this->getVar('removesignature') == 'no') {
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
        throw new FatalErrorException($this->__('Error! No post_id in \'Dizkus/Ajax/updatepost()\'.'));
    }

    /**
     * @Route("/changetopicstatus", options={"expose"=true})
     * @Method("POST")
     *
     * changeTopicStatus
     *
     * @param Request $request
     *  topic
     *  post
     *  action
     *  userAllowedToEdit
     *  title
     *
     * @throws AccessDeniedException If the current user does not have adequate permissions to perform this function.
     *
     * @return UnavailableResponse|BadDataResponse|PlainResponse
     */
    public function changeTopicStatusAction(Request $request)
    {
        // Check if forum is disabled
        if ($this->getVar('forum_enabled') == 'no') {
            return new UnavailableResponse(array(), strip_tags($this->getVar('forum_disabled_info')));
        }
        // Get common parameters
        $params = array();
        $params['topic_id'] = $request->request->get('topic', '');
        $params['post_id'] = $request->request->get('post', null);
        $params['action'] = $request->request->get('action', '');
        $userAllowedToEdit = $request->request->get('userAllowedToEdit', 0);
        // certain actions a user is always allowed
        $userAllowedToEdit = in_array($params['action'], array('subscribe', 'unsubscribe', 'solve', 'unsolve')) ? 1 : $userAllowedToEdit;
        // Check if topic is is set
        if (empty($params['topic_id'])) {
            return new BadDataResponse(array(), $this->__('Error! No topic ID in \'Dizkus/Ajax/changeTopicStatus()\'.'));
        }
        // Check if action is legal
        $allowedActions = array('lock', 'unlock', 'sticky', 'unsticky', 'subscribe', 'unsubscribe', 'solve', 'unsolve', 'setTitle');
        if (empty($params['action']) || !in_array($params['action'], $allowedActions)) {
            return new BadDataResponse(array(), $this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus/Ajax/changeTopicStatus()\'.', DataUtil::formatForDisplay($params['action'])));
        }
        // Get title parameter if action == setTitle
        if ($params['action'] == 'setTitle') {
            $params['title'] = trim($request->request->get('title', ''));
            if (empty($params['title'])) {
                return new BadDataResponse(array(), $this->__('Error! The post has no subject line.'));
            }
        }
        SessionUtil::setVar('zk_ajax_call', 'ajax');
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate') && !($userAllowedToEdit == 1)) {
            throw new AccessDeniedException();
        }
        ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);

        return new PlainResponse('successful');
    }

    /**
     * @Route("/getusers", options={"expose"=true})
     * @Method("GET")
     *
     * Performs a user search based on the user name fragment entered so far.
     *
     * @param Request $request
     *  fragment A partial user name entered by the user.
     *
     * @throws AccessDeniedException
     *
     * @return string PlainResponse with json_encoded object of users matching the criteria.
     */
    public function getUsersAction(Request $request)
    {
        $this->checkAjaxToken();
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $fragment = $request->query->get('fragment', null);
        $users = ModUtil::apiFunc($this->name, 'user', 'getUsersByFragments', array('fragments' => array($fragment)));
        $reply = array();
        $reply['query'] = $fragment;
        $reply['suggestions'] = array();
        /** @var $user \Zikula\Module\UsersModule\Entity\UserEntity */
        foreach ($users as $user) {
            $reply['suggestions'][] = array(
                'value' => htmlentities(stripslashes($user->getUname())),
                'data' => $user->getUid());
        }

        return new PlainResponse(json_encode($reply));
    }

    /**
     * @Route("/modforum", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request
     *  forum
     *  action
     *
     * @return string PlainResponse|BadDataResponse|UnavailableResponse
     *
     * @throws AccessDeniedException on failed perm check
     */
    public function modifyForumAction(Request $request)
    {
        $this->checkAjaxToken();
        if ($this->getVar('forum_enabled') == 'no') {
            return new UnavailableResponse(array(), strip_tags($this->getVar('forum_disabled_info')));
        }
        if ($this->getVar('favorites_enabled') == 'no') {
            return new BadDataResponse(array(), $this->__('Error! Favourites have been disabled.'));
        }
        $params = array(
            'forum_id' => $request->request->get('forum'),
            'action' => $request->request->get('action'));
        if (empty($params['forum_id'])) {
            return new BadDataResponse(array(), $this->__('Error! No forum ID in \'Dizkus/Ajax/modifyForum()\'.'));
        }
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            // only need read perms to make a favorite
            throw new AccessDeniedException();
        }
        SessionUtil::setVar('zk_ajax_call', 'ajax');
        ModUtil::apiFunc($this->name, 'Forum', 'modify', $params);

        return new PlainResponse('successful');
    }

    /**
     * @Route("/forumusers", options={"expose"=true})
     *
     * update the "users online" section in the footer
     *
     * used in User/footer_with_ajax.tpl
     *
     * @return UnavailableResponse
     */
    public function forumusersAction()
    {
        if ($this->getVar('forum_enabled') == 'no') {
            return new UnavailableResponse(array(), strip_tags($this->getVar('forum_disabled_info')));
        }
        $this->view->setCaching(false);
        if (System::getVar('shorturls')) {
            $this->view->_get_plugin_filepath('outputfilter', 'shorturls');
            $this->view->register_outputfilter('smarty_outputfilter_shorturls');
        }
        $this->view->display('Ajax/forumusers.tpl');
        System::shutDown();
    }

}