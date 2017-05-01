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
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\RouteUrl;
use Zikula\DizkusModule\Controller\AbstractBaseController as AbstractController;
use Zikula\DizkusModule\Events\DizkusEvents;
use Zikula\DizkusModule\Form\Type\Post\EditPostType;
use Zikula\DizkusModule\Form\Type\Post\DeletePostType;
use Zikula\DizkusModule\Form\Type\Post\MovePostType;
use Zikula\DizkusModule\Entity\RankEntity;

/**
 * PostController class
 */
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
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        $since = $request->query->get('since', null) == null ? null : (int)$request->query->get('since');
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 25);

        list($posts, $pager) = $this->getDoctrine()->getManager()
            ->getRepository('Zikula\DizkusModule\Entity\PostEntity')
            //->setManager($this->get('zikula_dizkus_module.post_manager'))
            ->getPosts($since, $page, $limit);

        $managedPosts = [];
        foreach ($posts as $post) {
            $managedPosts[] = $this->get('zikula_dizkus_module.post_manager')->getManager(null, $post);
        }

//        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
//            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
//        }

        return $this->render('@ZikulaDizkusModule/Post/latest.html.twig', [
            'currentForumUser' => $forumUserManager,
            'since' => $since,
            'latestPosts' => $managedPosts,
            'pager'=> $pager,
            'page' => $page,
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
        if (!$managedPost->exists()) {
            $error = $this->__f('Error! The post you selected (ID: %s) was not found. Please try again.', [$post]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error]));
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if (!$currentForumUser->allowedToEdit($managedPost) && !$currentForumUser->allowedToModerate($managedPost)) {
            throw new AccessDeniedException();
        }

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
                new EditPostType(),
                $managedPost->get(),
                ['addReason'=>  $currentForumUser->getId() == $managedPost->getManagedPoster()->getId() ? false : true,
                 'loggedIn' => $currentForumUser->isLoggedIn(),
                 'settings' => $this->getVars()
                ]
            );

        if ($format == 'html') {
            $formBuilder->add('save', SubmitType::class)
                        ->add('preview', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($request->isMethod('GET')) {
            goto edit_error;
        }

        $postHookValidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.validate_edit', new ValidationHook())->getValidators();
        if ($postHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto edit_error;
        }

        if (!$form->isValid()) {
            goto edit_error;
        }

        $managedPost->update($form->getData());

        $this->get('event_dispatcher')
            ->dispatch(DizkusEvents::POST_PREPARE,
                new GenericEvent($managedPost->get())
            );

        if ($form->get('preview')->isClicked()) {
            $preview = $managedPost;
            $ranks = $this->get('zikula_dizkus_module.rank_helper')->getAll(['ranktype' => RankEntity::TYPE_POSTCOUNT]);
        }

        if ($form->get('save')->isClicked()) {
            $managedPost->store();

            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.post.process_edit',
                    new ProcessHook($managedPost->getId(),
                        new RouteUrl('zikuladizkusmodule_topic_viewtopic',
                            ['topic' => $managedPost->getManagedTopic()->getId()]
                        )
                    )
                );

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::POST_UPDATE,
                    new GenericEvent($managedPost->get(),
                    ['reason' => $form->has('reason') ? $form->get('reason')->getData() : null,
                     'notifier' => $currentForumUser]
                    )
                );

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
        if (!$managedPost->exists()) {
            $error = $this->__f('Error! The post you selected (ID: %s) was not found. Please try again.', [$post]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error])); // add not found error code etc
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if (!$currentForumUser->allowedToEdit($managedPost) && !$currentForumUser->allowedToModerate($managedPost)) {
            throw new AccessDeniedException();
        }

        // pre delete (event hook)

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
                new DeletePostType(),
                $managedPost->get(),
                ['addReason'=>  $currentForumUser->getId() == $managedPost->getManagedPoster()->getId() ? false : true]
            );

        if ($format == 'html') {
            $formBuilder->add('delete', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $postHookValidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.post.validate_delete',
                new ValidationHook(
                    new ValidationProviders()
                )
            )->getValidators();
        if ($postHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto delete_error;
        }

        if (!$form->isValid()) {
            goto delete_error;
        }
        // we need to simulate delete button in ajax forms both json and html
        if ($form->get('delete')->isClicked()) {
            $managedPost->update($form->getData());

            // id is null beyond this point
            // post is deleted from db
            // PostEntity and all other associated Entities are still here
            // we can still use this manager to retrive post delete data
            $managedPost->delete();

            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.post.process_delete',
                    new ProcessHook($managedPost->getManagedTopic()->getId(),
                        new RouteUrl('zikuladizkusmodule_topic_viewtopic',
                            ['topic' => $managedPost->getManagedTopic()->getId()]
                        )
                    )
                );

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::POST_DELETE,
                    new GenericEvent($managedPost->get(),
                    ['reason' => $form->has('reason') ? $form->get('reason')->getData() : null,
                     'notifier' => $currentForumUser]
                    )
                );

             //redirect to the topic
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
        // light html
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

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post);
        if (!$managedPost->exists()) {
            $error = $this->__f('Error! The post you selected (ID: %s) was not found. Please try again.', [$post]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error])); // add not found error code etc
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if (!$currentForumUser->allowedToModerate($managedPost)) {
            throw new AccessDeniedException();
        }

        // pre move (event hook)

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
                new MovePostType(),
                $managedPost->get(),
                ['addReason'=>  $currentForumUser->getId() == $managedPost->getManagedPoster()->getId() ? false : true]
            );

        if ($format == 'html') {
            $formBuilder->add('move', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $postHookValidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.post.validate_edit',
                new ValidationHook(
                    new ValidationProviders()
                )
            )->getValidators();
        if ($postHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto delete_error;
        }

        if (!$form->isValid()) {
            goto delete_error;
        }
        // we need to simulate delete button in ajax forms both json and html
        if ($form->get('move')->isClicked()) {
            $managedOriginTopic = $managedPost->getManagedTopic();

            $managedPost
                ->update($form->getData()) // set destination topic
                ->store() // save
                ->getManagedTopic() // destination topic management
                    ->incrementRepliesCount()
                    ->store()
                    ->resetLastPost(true)
                    ->getManagedForum()
                        ->resetLastPost(true);

            $managedOriginTopic->incrementRepliesCount()
                                ->store()
                                ->resetLastPost(true)
                                ->getManagedForum()
                                    ->resetLastPost(true);

            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.post.process_edit',
                    new ProcessHook($managedPost->getManagedTopic()->getId(),
                        new RouteUrl('zikuladizkusmodule_topic_viewtopic',
                            ['topic' => $managedPost->getManagedTopic()->getId()]
                        )
                    )
                );

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::POST_MOVE,
                    new GenericEvent($managedPost->get(),
                    ['reason' => $form->has('reason') ? $form->get('reason')->getData() : null,
                     'original_topic' => $managedOriginTopic->get(),
                     'notifier' => $currentForumUser]
                    )
                );

            if ($format == 'json') {
                // other formats
            }

            //redirect to original topic
            //return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedOriginTopic->getId()], RouterInterface::ABSOLUTE_URL));

            //redirect to destination topic default
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedPost->getManagedTopic()->getId()], RouterInterface::ABSOLUTE_URL));
        }

        delete_error:

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Post/move.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentTopic' => $managedPost->getManagedTopic(),
                    'form' => $form->createView(),
                    'managedPost' => $managedPost,
                    'settings' => $this->getVars()
                ]);
        // light html
        if ($format == 'ajax.html') {
            return new Response(json_encode(['html' => $contentHtml]));
        }
        // full html page
        return new Response($contentHtml);
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
        if (!$managedPost->exists()) {
            $error = $this->__f('Error! The post you selected (ID: %s) was not found. Please try again.', [$post]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error])); //add not found error code etc
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $form = $this->createFormBuilder(['message' => ''])
                ->add('message', 'textarea', ['required' => false])
                ->add('send', 'submit')
                ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('send')->isClicked()) {
                $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::POST_NOTIFY_MODERATOR,
                    new GenericEvent($managedPost->get(),
                    ['message' => $form->get('message')->getData(),
                    'notifier' => $currentForumUser]
                    )
                );

                $status = $this->__('Done! Moderators will be notified.');
                if ($format == 'json') {
                } elseif ($format == 'ajax.html') {
                    return new Response(json_encode(['html' => $status]));
                }

                $this->addFlash('status', $status);
            }

            $start =  $managedPost->getManagedTopic()->getTopicPage($managedPost->get()->getTopic()->getReplyCount());

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedPost->getTopicId(), 'start' => $start], RouterInterface::ABSOLUTE_URL));
        }

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Post/report.$format.twig", [
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
        if (!$managedPost->exists()) {
            $error = $this->__f('Error! The post you selected (ID: %s) was not found. Please try again.', [$post]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error])); //add not found error code etc
            }

            $this->addFlash('error', $error);
            
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
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
}

////        // some spam checks:
////        // - remove html and compare with original comment
////        // - use censor and compare with original comment
////        // if only one of this comparisons fails -> trash it, it is spam.
////        if (!UserUtil::isLoggedIn()) {
////            if (strip_tags($data['comment']) != $data['comment']) {
////                // possibly spam, stop now
////                // get the users ip address and store it in zTemp/Dizkus_spammers.txt
////                $this->dzk_blacklist();
////                // set 403 header and stop
////                header('HTTP/1.0 403 Forbidden');
////                System::shutDown();
////            }
////        }
