<?php
/**
 * Dizkus.
 *
 * @copyright (c) Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\RouteUrl;
use Zikula\Core\Response\PlainResponse;
use Zikula\DizkusModule\Entity\PostEntity;



class PostController extends AbstractController
{
    /**
     * @Route("/posts/view-latest")
     *
     * View latest topics
     *
     * @param Request $request
     *                         string 'selorder'
     *                         integer 'nohours'
     *                         integer 'unanswered'
     *                         integer 'last_visit_unix'
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function viewlatestAction(Request $request)
    {
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        $since = $request->query->get('since', null) == null ? null: (int)$request->query->get('since');

        $posts = [];
//        // get the input
//        $params = [];
//        $params['selorder'] = $request->get('selorder', 1);
//        $params['nohours'] = (int)$request->request->get('nohours', 24);
//        $params['unanswered'] = (int)$request->query->get('unanswered', 0);
//        $params['amount'] = (int)$request->query->get('amount', null);

//        getLastPosts($params)

        // Permission check
//        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
//            throw new AccessDeniedException();
//        }

//        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
//            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
//        }



//        list($topics, $text, $pager) = ModUtil::apiFunc($this->name, 'post', 'getLatest', $params);
//        $this->view->assign('topics', $topics);
//        $this->view->assign('text', $text);
//        $this->view->assign('pager', $pager);

        return $this->render('@ZikulaDizkusModule/Post/latest.html.twig', [
            'currentForumUser' => $forumUserManager,
            'since' => $since,
            'latestPosts' => $posts,
//            'posts' => $posts,
//            'pager', $pager,
            'settings' => $this->getVars(),
            ]);
    }

    /**
     * @Route("/post/{post}/edit", requirements={"post" = "^[1-9]\d*$"}, options={"expose"=true})
     * @Method("GET")
     *
     * Edit a post.
     *
     * @param Request $request
     * @param integer $post The post id to edit
     *
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException
     *
     * @return AjaxResponse
     */
    public function editAction(Request $request, $post)
    {
        //        $this->errorIfForumDisabled();
//        $this->checkAjaxToken();
//        $post_id = $request->request->get('post', null);
//        $currentUserId = UserUtil::getVar('uid');
//        if (!empty($post_id)) {
//            $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post_id); // new PostManager($post_id);
//            $forum = $managedPost->get()->getTopic()->getForum();
//            $managedForum = new ForumManager(null, $forum);
//            if ($managedPost->get()->getPoster()->getUser_id() == $currentUserId || $managedForum->isModerator()) {
//                $this->view->setCaching(false);
//                $this->view->assign('post', $managedPost->get());
//                // simplify our live
//                $this->view->assign('postingtextareaid', 'postingtext_' . $managedPost->getId() . '_edit');
//                $this->view->assign('isFirstPost', $managedPost->get()->isFirst());
//
//                return new AjaxResponse($this->view->fetch('Ajax/editpost.tpl'));
//            } else {
//                throw new AccessDeniedException();
//            }
//        }
//        throw new \InvalidArgumentException($this->__f('Error! No post ID in %s.', '\'Dizkus/Ajax/editpost()\''));
    }

    /**
     * @Route("/post/preview", options={"expose"=true})
     * @Method("POST")
     *
     * Edit a post.
     *
     * @param Request $request
     * @param integer $post The post id to edit
     *
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException
     *
     * @return AjaxResponse
     */
    public function previewAction(Request $request)
    {
        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager();

        $managedPost->getPreview($request->get('topic_reply_form'));

        return $this->render("@ZikulaDizkusModule/Post/preview.html.twig", [
            'currentForumUser' => $currentForumUser,
            'postManager' => $managedPost,
            'settings' => $this->getVars(),
        ], $request->isXmlHttpRequest() ? new PlainResponse() : null);
    }

    /**
     * @Route("/post/{post}/update", requirements={"post" = "^[1-9]\d*$"}, options={"expose"=true})
     * @Method("POST")
     *
     * Update a post.
     *
     * @param Request $request
     *                         postId           The post id to update.
     *                         title
     *                         message          The new post message.
     *                         delete_post      Delete this post?
     *                         attach_signature Attach signature?
     *
     * RETURN: array($action The executed action.
     *               $newText The new post text (can be empty).
     *               $redirect The page to redirect to (can be empty).
     *              )
     *
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException     if the user tries to delete the only post of a topic
     *
     * @return AjaxResponse
     */
    public function updateAction(Request $request, $post)
    {
        //        $this->errorIfForumDisabled();
//        $this->checkAjaxToken();
//        $post_id = $request->request->get('postId', '');
//        $title = $request->request->get('title', '');
//        $message = $request->request->get('message', '');
//        $delete = $request->request->get('delete_post', 0) == '1' ? true : false;
//        $attach_signature = $request->request->get('attach_signature', 0) == '1' ? true : false;
//        if (!empty($post_id)) {
//            $message = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $message);
//            $this->checkMessageLength($message);
//            $managedOriginalPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post_id); //new PostManager($post_id);
//            if ($delete) {
//                if ($managedOriginalPost->get()->isFirst()) {
//                    throw new AccessDeniedException($this->__('Error! Cannot delete the first post in a topic. Delete the topic instead.'));
//                } else {
//                    $response = ['action' => 'deleted'];
//                }
//                $managedOriginalPost->delete();
//                $this->dispatchHooks('dizkus.ui_hooks.post.process_delete', new ProcessHook($managedOriginalPost->getId()));
//            } else {
//                $data = [
//                    'title' => $title,
//                    'post_text' => $message,
//                    'attachSignature' => $attach_signature,];
//                $managedOriginalPost->update($data);
//                $url = RouteUrl::createFromRoute('zikuladizkusmodule_user_viewtopic', ['topic' => $managedOriginalPost->getTopicId()], 'pid' . $managedOriginalPost->getId());
//                $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedOriginalPost->getId(), $url));
//                if ($attach_signature && !$this->getVar('removesignature')) {
//                    // include signature in response text
//                    $sig = UserUtil::getVar('signature', $managedOriginalPost->get()->getPoster_id());
//                    $message .= !empty($sig) ? "<div class='dzk_postSignature'>{$this->getVar('signature_start')}<br />{$sig}<br />{$this->getVar('signature_end')}</div>" : '';
//                }
//                // must dzkVarPrepHTMLDisplay the message content here because the template modifies cannot be run in ajax
//                $newText = ModUtil::apiFunc($this->name, 'user', 'dzkVarPrepHTMLDisplay', $message);
//                // process hooks
//                $newText = $this->dispatchHooks('dizkus.filter_hooks.post.filter', new FilterHook($newText))->getData();
//                // process internal quotes/hooks
//                $newText = ModUtil::apiFunc($this->name, 'ParseTags', 'transform', ['message' => $newText]);
//                $response = [
//                    'action' => 'updated',
//                    'newText' => $newText,];
//            }
//
//            return new AjaxResponse($response);
//        }
//        throw new \InvalidArgumentException($this->__f('Error! No post_id in %s.', '\'Dizkus/Ajax/updatepost()\''));
    }

    /**
     * @Route("/post/{post}/move", requirements={"post" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * User interface to move a single post to another thread
     *
     * @return string
     */
    public function moveAction(Request $request, $post)
    {
        //        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
//            throw new AccessDeniedException();
//        }
//
//        // get the input
//        $id = (int) $this->request->query->get('post');
//
//        $this->post_id = $id;
//
//        $managedPost = new PostManager($id);
//
//        $this->old_topic_id = $managedPost->getTopicId();
//
//        if ($managedPost->get()->isFirst()) {
//            $this->request->getSession()->getFlashBag()->add('error', 'You can not move the first post of a topic!');
//            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $managedPost->getTopicId()], RouterInterface::ABSOLUTE_URL);
//
//            return $view->redirect($url);
//        }
//
//        return true;
//        if ($args['commandName'] == 'cancel') {
//            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $this->old_topic_id, 'start' => 1], RouterInterface::ABSOLUTE_URL) . '#pid' . $this->post_id;
//
//            return $view->redirect($url);
//        }
//
//        // check for valid form
//        if (!$view->isValid()) {
//            return false;
//        }
//
//        $data = $view->getValues();
//        $data['old_topic_id'] = $this->old_topic_id;
//        $data['post_id'] = $this->post_id;
//
//        $newTopicPostCount = ModUtil::apiFunc($this->name, 'post', 'move', $data);
//        $start = $newTopicPostCount - $newTopicPostCount % ModUtil::getVar($this->name, 'posts_per_page', 15);
//
//        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $data['to_topic_id'], 'start' => $start], RouterInterface::ABSOLUTE_URL) . '#pid' . $this->post_id;
//
//        return $view->redirect($url);
//        $form = FormUtil::newForm($this->name, $this);
//
//        return new Response($form->execute('User/post/move.tpl', new MovePost()));
    }

    /**
     * @Route("/post/{post}/report", requirements={"post" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Report
     *
     * User interface to notify a moderator about a (bad) posting.
     *
     * @return string
     */
    public function reportAction(Request $request, $post)
    {
        $format = $this->decodeFormat($request);

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();

        $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post);
        if (!$managedPost->exists()){
            $error = $this->__f('Error! The post you selected (ID: %s) was not found. Please try again.', [$post]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error])); //add not found error code etc
            }

            $this->addFlash('error', $error);
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        // return json response
        if ($format == 'json') {
            if ($request->isMethod('POST')) {

                //get message
                //
//        // some spam checks:
//        // - remove html and compare with original comment
//        // - use censor and compare with original comment
//        // if only one of this comparisons fails -> trash it, it is spam.
//        if (!UserUtil::isLoggedIn()) {
//            if (strip_tags($data['comment']) != $data['comment']) {
//                // possibly spam, stop now
//                // get the users ip address and store it in zTemp/Dizkus_spammers.txt
//                $this->dzk_blacklist();
//                // set 403 header and stop
//                header('HTTP/1.0 403 Forbidden');
//                System::shutDown();
//            }
//        }
//
//        ModUtil::apiFunc($this->name, 'notify', 'notify_moderator', ['post' => $this->_post->get(),
//            'comment' => $data['comment'],]);


            $status = $this->__('Done! Moderators will be notified.');
                    return new Response(json_encode(['status' => $status]));
            }
            return new Response(json_encode(['currentForumUser' => $currentForumUser->toArray(),
                    'managedPost' => $managedPost->toArray(),
                    'settings' => $this->getVars()
                                                ]));
        }

        //html only part
        $form = $this->createFormBuilder(['message' => ''])
        ->add('message', 'textarea', [
            'required' => false,
        ])
        ->add('send', 'submit')
        ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('send')->isClicked()) {
                $data = $form->getData();


//        // some spam checks:
//        // - remove html and compare with original comment
//        // - use censor and compare with original comment
//        // if only one of this comparisons fails -> trash it, it is spam.
//        if (!UserUtil::isLoggedIn()) {
//            if (strip_tags($data['comment']) != $data['comment']) {
//                // possibly spam, stop now
//                // get the users ip address and store it in zTemp/Dizkus_spammers.txt
//                $this->dzk_blacklist();
//                // set 403 header and stop
//                header('HTTP/1.0 403 Forbidden');
//                System::shutDown();
//            }
//        }
//
//        ModUtil::apiFunc($this->name, 'notify', 'notify_moderator', ['post' => $this->_post->get(),
//            'comment' => $data['comment'],]);



                $status = $this->__('Done! Moderators will be notified.');
                if ($format == 'ajax.html') {
                    return new Response(json_encode(['html' => $status]));
                }
                $this->addFlash('status', $status);
            }

            $start =  $managedPost->getManagedTopic()->getTopicPage($managedPost->get()->getTopic()->getReplyCount());
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedPost->getTopicId(), 'start' => $start], RouterInterface::ABSOLUTE_URL));
        }

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Post/report.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'form' => $form->createView(),
                    'managedPost' => $managedPost,
                    'settings' => $this->getVars()
                ]);
        if ($format == 'ajax.html') {
            return new Response(json_encode(['html' => $contentHtml]));
        }
        // full html page
        return new Response($contentHtml);
    }

    /**
     * @Route("/post/{post}/poster", requirements={"post" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Get poster data as forum user and send it as json/html or redirect
     *
     * @param int $post
     *
     * @throws AccessDeniedException     on failed perm check
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return Response
     */
    public function posterAction(Request $request, $post)
    {
        $format = $this->decodeFormat($request);

        $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post);
        if (!$managedPost->exists()){
            $error = $this->__f('Error! The post you selected (ID: %s) was not found. Please try again.', [$post]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error])); //add not found error code etc
            }

            $this->addFlash('error', $error);
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //$managedTopic = $managedPost->getManagedTopic();
        $managedPoster = $managedPost->getManagedPoster();


        // return json response
        if ($format == 'json') {
            if ($request->isMethod('POST')) {



            }
        }

        if ($format == 'ajax.html') {
            $contentHtml = $this->renderView("@ZikulaDizkusModule/Post/preview.html.twig", [
                    'currentForumUser' => $currentForumUser,
                    'postManager' => $managedPost,
                    'settings' => $this->getVars(),
                ]);

            return new Response(json_encode(['html' => $contentHtml]));
        }

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_profile', ['user' => $managedPoster->getUserId()], RouterInterface::ABSOLUTE_URL));
    }


    private function decodeFormat(Request $request) {

        if (0 === strpos($request->headers->get('Accept'), 'application/json')) {
            $format = 'json';
        } elseif ($request->isXmlHttpRequest()) {
            $format = 'ajax.html';
        } else {
            $format = 'html';
        }

        return $format;
    }
}
