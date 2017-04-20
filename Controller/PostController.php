<?php

/**
 * Dizkus
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
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\RouteUrl;
use Zikula\Core\Response\PlainResponse;
use Zikula\DizkusModule\Entity\PostEntity;
use Zikula\DizkusModule\Form\Type\Post\EditPostType;
use Zikula\DizkusModule\Form\Type\Post\DeletePostType;
use Zikula\DizkusModule\Entity\RankEntity;

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
     *
     * Edit a post
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
        $format = $this->decodeFormat($request);
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }
        $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post);
        if (!$managedPost->exists()){
            $error = $this->__f('Error! The post you selected (ID: %s) was not found. Please try again.', [$post]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error])); // add not found error code etc
            }

            $this->addFlash('error', $error);
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if(!$currentForumUser->allowedToEdit($managedPost)) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new EditPostType($currentForumUser->isLoggedIn()), $managedPost->get(), []);
        $form->handleRequest($request);

        // check hooked modules for validation for post
        $postHook = new ValidationHook(new ValidationProviders());
        /** @var $postHookValidators \Zikula\Core\Hook\ValidationProviders */
        $postHookValidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.validate_edit', $postHook)->getValidators();
        if ($postHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto edit_error;
        }

        if (!$form->isValid()) {
            goto edit_error;
        }

        if ($form->get('preview')->isClicked()) {
              $preview = $form->getData();
              $ranks = $this->get('zikula_dizkus_module.rank_helper')->getAll(['ranktype' => RankEntity::TYPE_POSTCOUNT]);
        }

        if ($form->get('save')->isClicked()) {
            $managedPost->update($form->getData());
            $url = new RouteUrl('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedPost->getManagedTopic()->getId()]);
            $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedPost->getId(), $url));

            // redirect to the topic
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedPost->getManagedTopic()->getId()], RouterInterface::ABSOLUTE_URL));
        }

        edit_error:

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Post/edit.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentTopic' => $managedPost->getManagedTopic(),
                    'form' => $form->createView(),
                    'managedPost' => $managedPost,
                    'ranks'       => isset($ranks) ? $ranks : false,
                    'preview'     => isset($preview) ? $preview : false,
                    'settings' => $this->getVars()
                ]);
        if ($format == 'ajax.html') {
            return new Response(json_encode(['html' => $contentHtml]));
        }
        // full html page
        return new Response($contentHtml);
    }

    /**
     * @Route("/post/{post}/delete", requirements={"post" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Delete a post
     *
     * @param Request $request
     * @param integer $post The post id to edit
     *
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException
     *
     * @return AjaxResponse
     */
    public function deleteAction(Request $request, $post)
    {
        $format = $this->decodeFormat($request);
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }
        $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post);
        if (!$managedPost->exists()){
            $error = $this->__f('Error! The post you selected (ID: %s) was not found. Please try again.', [$post]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error])); // add not found error code etc
            }

            $this->addFlash('error', $error);
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
//        if(!$currentForumUser->allowedToEdit($managedPost) || !$currentForumUser->allowedToModerate($managedPost)) {
//            throw new AccessDeniedException();
//        }

        $form = $this->createForm(new DeletePostType($currentForumUser->isLoggedIn()), $managedPost->get(), []);
        $form->handleRequest($request);

        // check hooked modules for validation for post
        $postHook = new ValidationHook(new ValidationProviders());
        /** @var $postHookValidators \Zikula\Core\Hook\ValidationProviders */
        $postHookValidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.validate_delete', $postHook)->getValidators();
        if ($postHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto delete_error;
        }

        if (!$form->isValid()) {
            goto delete_error;
        }

        if ($form->get('delete')->isClicked()) {
            $managedPost->update($form->getData());

            // @todo delete

            $url = new RouteUrl('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedPost->getManagedTopic()->getId()]);
            $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedPost->getId(), $url));

            // redirect to the topic
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedPost->getManagedTopic()->getId()], RouterInterface::ABSOLUTE_URL));
        }

        delete_error:

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Post/delete.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentTopic' => $managedPost->getManagedTopic(),
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
     * @Route("/post/{post}/move", requirements={"post" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * User interface to move a single post to another thread
     *
     * @return string
     */
    public function moveAction(Request $request, $post)
    {
        $format = $this->decodeFormat($request);
        if (!$this->get('zikula_dizkus_module.security')->canModerate([])) {
            throw new AccessDeniedException();
        }
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
