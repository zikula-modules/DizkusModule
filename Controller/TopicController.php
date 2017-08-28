<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Controller;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; //unused at the moment
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\Hook\ValidationProviders;
use Zikula\Core\RouteUrl;
use Zikula\DizkusModule\Controller\AbstractBaseController as AbstractController;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Events\DizkusEvents;
use Zikula\DizkusModule\Form\Type\Topic\EditTopicType;
use Zikula\DizkusModule\Form\Type\Topic\EmailTopicType;
use Zikula\DizkusModule\Form\Type\Topic\DeleteTopicType;
use Zikula\DizkusModule\Form\Type\Topic\JoinMoveTopicType;
use Zikula\DizkusModule\Form\Type\Topic\NewTopicType;
use Zikula\DizkusModule\Form\Type\Topic\ReplyTopicType;
use Zikula\DizkusModule\Form\Type\Topic\SplitTopicType;

class TopicController extends AbstractController
{
    /**
     * @Route("/topic/{topic}/{start}", requirements={"topic" = "^[1-9]\d*$", "start" = "^[1-9]\d*$"})
     *
     * View topic
     *
     * @param Request $request
     * @param int     $topic   the topic ID
     * @param int     $start   pager value
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function viewtopicAction(Request $request, $topic, $start = 1)
    {
        //$format = $this->decodeFormat($request);

        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                        'forum_disabled_info' => $this->getVar('forum_disabled_info'),
            ]);
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        $currentTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$currentTopic->exists()) {
            $this->addFlash('error', $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead($currentTopic->get()->getForum())) {
            throw new AccessDeniedException();
        }

        $postOrder = $request->get('order', $currentForumUser->getPostOrder());

        $currentTopic->loadPosts($start - 1, $postOrder)
            ->incrementViewsCount()
            ->store();

        return $this->render('@ZikulaDizkusModule/Topic/view.html.twig', [
            'currentForumUser' => $currentForumUser,
            'currentTopic'    => $currentTopic,
            'start'           => $start,
            'order'           => $postOrder,
            'preview'         => false,
            'settings'        => $this->getVars(),
            ]);
    }

    /**
     * @Route("/topics/view-latest")
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
    public function viewLatestAction(Request $request)
    {
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();

        $since = $request->query->get('since', null) == null ? null : (int)$request->query->get('since');
        $unanswered = $request->query->get('unanswered') == 'on' ? 1 : true;
        $unsolved = $request->query->get('unsolved') == 'on' ? 1 : false;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 25);
        list($topics, $pager) = $this->getDoctrine()->getManager()
            ->getRepository('Zikula\DizkusModule\Entity\TopicEntity')
            ->getTopics($since, $unanswered, $unsolved, $page, $limit
            );

        return $this->render('@ZikulaDizkusModule/Topic/latest.html.twig', [
            'currentForumUser' => $forumUserManager,
            'topics' => $topics,
            'since' => $since,
            'unanswered' => $unanswered,
            'unsolved' => $unsolved,
            'page' => $page,
            'pager' => $pager,
            'settings' => $this->getVars(),
            ]);
    }

    /**
     * @Route("/forum/{forum}/topic/new", requirements={"forum" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Create new topic
     *
     * User interface to create a new topic
     *
     * @return string
     */
    public function newtopicAction(Request $request, $forum)
    {
        $format = $this->decodeFormat($request);
        $template = $this->decodeTemplate($request);
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }
        $error = false;
        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        $errorReturnUrl = $this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL);
        // get the forum
//        $forum = (int) $request->query->get('forum');
        if (!isset($forum)) {
            $error = $this->__('Error! Missing forum id.');

            goto error;
        }

        $managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($forum, null, false);
        if (!$managedForum->exists()) {
            $error = $this->__f('Error! Forum with id %s does not exist.' . $forum, [$forum]);

            goto error;
        }

        if ($managedForum->get()->isLocked()) {
            $error = $this->__('Error! This forum is locked. New topics cannot be created.');
            $errorReturnUrl = $this->get('router')->generate('zikuladizkusmodule_forum_viewforum', ['forum' => $forum], RouterInterface::ABSOLUTE_URL);

            goto error;
        }

        $newManagedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager();
        $managedFirstPost = $this->get('zikula_dizkus_module.post_manager')->getManager();

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
                new NewTopicType(),
                $newManagedTopic
                    ->get()
                    ->setTopic_time(new \DateTime())
                    ->setForum($managedForum->get())
                    ->setPoster($currentForumUser->get())
                    ->addPost($managedFirstPost
                                    ->get()
                                    ->setPost_time(new \DateTime())
                                    ->setTopic($newManagedTopic->get())
                                    ->setPoster($currentForumUser->get())
                                    ->setIsFirstPost()),
                ['loggedIn' => $currentForumUser->isLoggedIn(), 'settings' => $this->getVars()]
            );

        if ($format == 'html' || $template == 'comment') {
            $formBuilder->add('save', SubmitType::class)
                        ->add('preview', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($request->isMethod('GET')) {

            goto display;
        }

        $forumHookvalidators = $this
            ->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.forum.validate_edit',
                    new ValidationHook(
                        new ValidationProviders())
                )->getValidators();
        if ($forumHookvalidators->hasErrors()) {
            foreach ($forumHookvalidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto display;
        }

        $topicHookValidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.topic.validate_edit',
                new ValidationHook()
            )->getValidators();
        if ($topicHookValidators->hasErrors()) {
            foreach ($topicHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto display;
        }

        $postHookValidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.post.validate_edit',
                new ValidationHook()
            )->getValidators();
        if ($postHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto display;
        }

        if (!$form->isValid()) {
            goto display;
        }

        $newManagedTopic
            ->update($form->getData())
            ->get()
            ->setLast_post($newManagedTopic->getFirstPost());

        $this->get('event_dispatcher')
            ->dispatch(DizkusEvents::TOPIC_PREPARE,
                new GenericEvent($newManagedTopic->get()));

        $this->get('event_dispatcher')
            ->dispatch(DizkusEvents::POST_PREPARE,
                new GenericEvent($newManagedTopic->getFirstPost())
            );

        if ($form->get('preview')->isClicked()) {
            $preview = $managedFirstPost;
            $ranks = $this->get('zikula_dizkus_module.rank_helper')->getAll(['ranktype' => RankEntity::TYPE_POSTCOUNT]);
        }

        if ($form->get('save')->isClicked()) {
            $newManagedTopic->store();

            $managedForum
                    ->setLastPost($newManagedTopic->getFirstPost())
                    ->setParentsLastPost($newManagedTopic->getFirstPost())
                    ->store();

            $currentForumUser
                    ->subscribeTopic($form->has('subscribeTopic') && $form->get('subscribeTopic')->getData() ? $newManagedTopic->get() : null)
                    ->store();

            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.topic.process_edit',
                    new ProcessHook($newManagedTopic->getId(),
                        new RouteUrl('zikuladizkusmodule_topic_viewtopic',
                            ['topic' => $newManagedTopic->getId()]
                            )
                        )
                    );

            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.post.process_edit',
                    new ProcessHook($newManagedTopic->getFirstPost()->getId(),
                        new RouteUrl('zikuladizkusmodule_topic_viewtopic',
                            ['topic' => $newManagedTopic->getId()]
                            )
                        )
                    );

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::TOPIC_CREATE,
                    new GenericEvent($newManagedTopic->get()
                    )
                );

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::POST_CREATE,
                    new GenericEvent($newManagedTopic->getFirstPost()
                    )
                );

            // redirect to the new topic
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $newManagedTopic->getId()], RouterInterface::ABSOLUTE_URL));
        }

        display:

        if ($format == 'json') {

            return new Response(json_encode(['data' => 'no json support at the moment']));

        } else {
                $contentHtml = $this->renderView("@ZikulaDizkusModule/Topic/new.$template.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentForum'  => $managedForum,
                    'form'          => $form->createView(),
                    'ranks'         => isset($ranks) ? $ranks : false,
                    'preview'       => isset($preview) ? $preview : false,
                    'settings'      => $this->getVars(),
                ]);

            if ($template == 'ajax') {

                return new Response(json_encode(['html' => $contentHtml]));
            }

            return new Response($contentHtml);
        }

        error:

        if ($format == 'json') {

            return new Response(json_encode(['data' => $error]));

        } else {
                $contentHtml = $this->renderView("@ZikulaDizkusModule/Topic/error.new.$template.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'error'  => $error,
                    'settings'      => $this->getVars(),
                ]);

            if ($template == 'ajax') {

                return new Response(json_encode(['html' => $error]));
            }

            if ($template == 'default') {

                $this->addFlash('error', $error);

                return new RedirectResponse($errorReturnUrl);
            }

            return new Response($contentHtml);
        }
    }

    /**
     * @Route("/topic/{topic}/edit", requirements={"topic" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Edit topic
     *
     * @param int    $topic
     * @param string $action
     * @param int    $post   (default = NULL)
     *
     *
     * @return RedirectResponse
     */
    public function editTopicAction(Request $request, $topic)
    {
        $format = $this->decodeFormat($request);
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            $error = $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error]));
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if (!$currentForumUser->allowedToEdit($managedTopic) && !$currentForumUser->allowedToModerate($managedTopic)) {
            throw new AccessDeniedException();
        }

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
                new EditTopicType(),
                $managedTopic->get(),
                ['loggedIn' => $currentForumUser->isLoggedIn(), 'settings' => $this->getVars()]
            );

        if ($format == 'html') {
            $formBuilder->add('save', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($request->isMethod('GET')) {
            goto edit_error;
        }

        $topicHookValidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.topic.validate_edit',
                new ValidationHook()
            )->getValidators();
        if ($topicHookValidators->hasErrors()) {
            foreach ($topicHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto edit_error;
        }

        if (!$form->isValid()) {
            goto edit_error;
        }

        $managedTopic->update($form->getData());

        $this->get('event_dispatcher')
            ->dispatch(DizkusEvents::TOPIC_PREPARE,
                new GenericEvent($managedTopic->get()));

        if ($form->get('save')->isClicked()) {
            $managedTopic->store();

            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.topic.process_edit',
                    new ProcessHook($managedTopic->getId(),
                        new RouteUrl('zikuladizkusmodule_topic_viewtopic',
                            ['topic' => $managedTopic->getId()]
                            )
                        )
                    );

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::TOPIC_UPDATE,
                    new GenericEvent($managedTopic->get()
                    )
                );

            if ($format == 'json') {
            } elseif ($format == 'ajax.html') {
            } else {
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
            }
        }

        edit_error:

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Topic/edit.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentTopic' => $managedTopic,
                    'form' => $form->createView(),
                    'settings' => $this->getVars()
                ]);
        if ($format == 'ajax.html') {
            return new Response(json_encode(['html' => $contentHtml]));
        }
        // full html page
        return new Response($contentHtml);
    }

    /**
     * @Route("/topic/{topic}/reply", requirements={"topic" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Reply topic
     *
     * User interface to reply a topic.
     *
     * @return string
     */
    public function replytopicAction(Request $request, $topic)
    {
        $format = $this->decodeFormat($request);
        $template = $this->decodeTemplate($request);

        $error = false;
        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        $errorReturnUrl = $this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL);
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            $error = $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]);

            goto error;
        }

        $action = $this->get('router')->generate('zikuladizkusmodule_topic_replytopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL);
        $postManager = $this->get('zikula_dizkus_module.post_manager')->getManager();

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
            new ReplyTopicType(),
            $postManager
                ->get()
                    ->setPost_time(new \DateTime())
                    ->setTopic($managedTopic->get())
                    ->setPoster($currentForumUser->get()),
            ['loggedIn' => $currentForumUser->isLoggedIn(),
            'action' => $action,
            'settings' => $this->getVars()]
            );

        if ($format == 'html' || $template == 'comment') {
            $formBuilder->add('save', SubmitType::class)
                        ->add('preview', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $hookvalidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.post.validate_edit',
                new ValidationHook()
            )->getValidators();
        if ($hookvalidators->hasErrors()) {
            foreach ($hookvalidators->getErrors() as $error) {
                $form->get('post_text')->addError(new FormError($this->__($error)));
            }

            goto display;
        }

        if (!$form->isValid()) {

            goto display;
        }

        $postManager->update($form->getData());

        if ($form->get('preview')->isClicked()) {
            $preview = $postManager;
        }

        if ($form->get('save')->isClicked()) {
            $postManager->store();

            $currentForumUser
                    ->incrementPostCount()
                    ->subscribeTopic($form->has('subscribeTopic') && $form->get('subscribeTopic')->getData() ? $managedTopic->get() : null)
                    ->store();

            $managedTopic
                    ->incrementRepliesCount()
                    ->setLastPost($postManager->get())
                    ->store();

            $managedTopic
                    ->getManagedForum()
                        ->setLastPost($postManager->get())
                        ->setParentsLastPost($postManager->get())
                        ->incrementPostCount()
                        ->store();

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::TOPIC_REPLY,
                    new GenericEvent($managedTopic->get()));

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::POST_CREATE,
                    new GenericEvent($postManager->get()));

            if ($format == 'json') {

            } elseif ($format == 'ajax.html') {

            } else {
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
            }
        }

        display:

        if ($format == 'json') {

            return new Response(json_encode(['data' => 'no json support at the moment']));

        } else {
            $contentHtml = $this->renderView("@ZikulaDizkusModule/Topic/reply.$template.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentTopic' => $managedTopic,
                    'form'      => $form->createView(),
                    'preview'   => isset($preview) ? $preview : false,
                    'settings'  => $this->getVars(),
                    ]);
            if ($template == 'ajax') {

                return new Response(json_encode(['html' => $contentHtml]));
            }

            return new Response($contentHtml);
        }

        error:

        if ($format == 'json') {

            return new Response(json_encode(['data' => $error]));

        } else {
                $contentHtml = $this->renderView("@ZikulaDizkusModule/Topic/error.reply.$template.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'error'  => $error,
                    'settings'      => $this->getVars(),
                ]);

            if ($template == 'ajax') {

                return new Response(json_encode(['html' => $error]));
            }

            if ($template == 'default') {

                $this->addFlash('error', $error);

                return new RedirectResponse($errorReturnUrl);
            }

            return new Response($contentHtml);
        }
    }

    /**
     * @Route("/topic/{topic}/delete", requirements={"topic" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Delete topic
     *
     * User interface to delete a topic.
     *
     * @return string
     */
    public function deletetopicAction(Request $request, $topic)
    {
        $format = $this->decodeFormat($request);
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            $error = $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error]));
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if (!$currentForumUser->allowedToEdit($managedTopic) && !$currentForumUser->allowedToModerate($managedTopic)) {
            throw new AccessDeniedException();
        }

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
                new DeleteTopicType(),
                $managedTopic->get(),
                ['addReason'=>  $currentForumUser->getId() == $managedTopic->getManagedPoster()->getId() ? false : true,
                'settings' => $this->getVars()]
            );

        if ($format == 'html') {
            $formBuilder->add('delete', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($request->isMethod('GET')) {
            goto delete_error;
        }

        $topicHookValidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.topic.validate_edit',
                new ValidationHook()
            )->getValidators();
        if ($topicHookValidators->hasErrors()) {
            foreach ($topicHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto delete_error;
        }

        if (!$form->isValid()) {
            goto delete_error;
        }

        $managedTopic->update($form->getData());

        $this->get('event_dispatcher')
            ->dispatch(DizkusEvents::TOPIC_PREPARE,
                new GenericEvent($managedTopic->get()));

        if ($form->get('save')->isClicked()) {
            $managedTopic->store();

            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.topic.process_edit',
                    new ProcessHook($managedTopic->getId(),
                        new RouteUrl('zikuladizkusmodule_topic_viewtopic',
                            ['topic' => $managedTopic->getId()]
                            )
                        )
                    );

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::TOPIC_DELETE,
                    new GenericEvent($managedTopic->get()
                    )
                );

            if ($format == 'json') {
            } elseif ($format == 'ajax.html') {
            } else {
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
            }
        }

        delete_error:

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Topic/delete.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentTopic' => $managedTopic,
                    'form' => $form->createView(),
                    'settings' => $this->getVars()
                ]);
        if ($format == 'ajax.html') {
            return new Response(json_encode(['html' => $contentHtml]));
        }
        // full html page
        return new Response($contentHtml);
    }

    /**
     * @Route("/topic/{topic}/joinmove", requirements={"topic" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Move topic
     *
     * User interface to move a topic to another forum.
     *
     * @return string
     */
    public function joinmovetopicAction(Request $request, $topic)
    {
        $format = $this->decodeFormat($request);
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            $error = $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error]));
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if (!$currentForumUser->allowedToModerate($managedTopic)) {
            throw new AccessDeniedException();
        }

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
                new JoinMoveTopicType(),
                $managedTopic->get(),
                [
                'forum' => $managedTopic->getForumId(),
                'forums' => $this->get('zikula_dizkus_module.forum_manager')->getAllChildren(),
                'addReason' =>  $currentForumUser->getId() == $managedTopic->getManagedPoster()->getId() ? false : true,
                'translator' => $this->get('translator'),
                'settings' => $this->getVars()
                ]
            );

        if ($format == 'html') {
            $formBuilder->add('move', SubmitType::class);
            $formBuilder->add('join', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($request->isMethod('GET')) {
            goto delete_error;
        }

        $topicHookValidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.topic.validate_edit',
                new ValidationHook()
            )->getValidators();
        if ($topicHookValidators->hasErrors()) {
            foreach ($topicHookValidators->getErrors() as $error) {
                $form->addError(new FormError($this->__($error)));
            }
            goto delete_error;
        }

        if (!$form->isValid()) {
            goto delete_error;
        }

        $managedTopic->update($form->getData());

        $this->get('event_dispatcher')
            ->dispatch(DizkusEvents::TOPIC_PREPARE,
                new GenericEvent($managedTopic->get()));

        if ($form->get('move')->isClicked()) {
            //            require perms for both subject topic and destination forum
//            if (!$this->get('zikula_dizkus_module.security')->canModerate(['forum_id' => $managedTopic->getForumId()])
//                    || !$this->get('zikula_dizkus_module.security')->canModerate(['forum_id' => $data['forum_id']])) {
//                throw new AccessDeniedException();
//            }
//
//
//            $managedTopic->move($data['forum_id'], $data['createshadowtopic']);

            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.topic.process_edit',
                    new ProcessHook($managedTopic->getId(),
                        new RouteUrl('zikuladizkusmodule_topic_viewtopic',
                            ['topic' => $managedTopic->getId()]
                            )
                        )
                    );

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::TOPIC_MOVE,
                    new GenericEvent($managedTopic->get()
                    )
                );

            if ($format == 'json') {
            } elseif ($format == 'ajax.html') {
            } else {
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
            }
        }

        if ($form->get('join')->isClicked()) {
            //            if (empty($data['to_topic_id'])) {
//                $this->addFlash('error', $this->__('Error! The topic ID cannot be empty.'));
//
//                return new RedirectResponse($topicUrl);
//            }
//            $managedDestinationTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($data['to_topic_id']);
//            if (!$managedDestinationTopic->exists()) {
//                $this->addFlash('error', $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$data['to_topic_id']]));
//
//                return new RedirectResponse($topicUrl);
//            }
//            if (!$this->get('zikula_dizkus_module.security')->canModerate(['forum_id' => $managedTopic->getForumId()])
//                    || !$this->get('zikula_dizkus_module.security')->canModerate(['forum_id' => $managedDestinationTopic->getForumId()])) {
//                throw new AccessDeniedException();
//            }
//            if ($managedDestinationTopic->getId() == $managedTopic->getId()) {
//                $this->addFlash('error', $this->__('Error! You cannot copy topic to itself.'));
//
//                return new RedirectResponse($topicUrl);
//            }
            //@todo we asume everything will be ok
            //$this->get('zikula_dizkus_module.topic_manager')->join($managedTopic, $managedDestinationTopic);

            //return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedDestinationTopic->getId()], RouterInterface::ABSOLUTE_URL));

            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.topic.process_edit',
                    new ProcessHook($managedTopic->getId(),
                        new RouteUrl('zikuladizkusmodule_topic_viewtopic',
                            ['topic' => $managedTopic->getId()]
                            )
                        )
                    );

            $this->get('event_dispatcher')
                ->dispatch(DizkusEvents::TOPIC_JOIN,
                    new GenericEvent($managedTopic->get()
                    )
                );

            if ($format == 'json') {
            } elseif ($format == 'ajax.html') {
            } else {
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
            }
        }

        delete_error:

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Topic/joinmove.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentTopic' => $managedTopic,
                    'form' => $form->createView(),
                    'settings' => $this->getVars()
                ]);
        if ($format == 'ajax.html') {
            return new Response(json_encode(['html' => $contentHtml]));
        }
        // full html page
        return new Response($contentHtml);
    }

    /**
     * @Route("/topic/{topic}/split/{post}", requirements={"post" = "^[1-9]\d*$", "post" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Split topic
     *
     * @return string
     */
    public function splittopicAction(Request $request, $topic, $post)
    {
        $format = $this->decodeFormat($request);
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canModerate([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            $error = $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error]));
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
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

        if (!$managedTopic->get()->getPosts()->indexOf($managedPost->get())) {
            $error = $this->__f('Error! The post you selected (ID: %s) do not belong to selected topic (ID %t).', [$post, $topic]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error]));
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if (!$currentForumUser->allowedToModerate($managedTopic)) {
            throw new AccessDeniedException();
        }

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
                new SplitTopicType(),
                $managedTopic->get(),
                [
                'translator' => $this->get('translator'),
                'settings' => $this->getVars()
                ]
            );

        if ($format == 'html') {
            $formBuilder->add('split', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

//        $postId = (int) $this->request->query->get('post');
//        $this->post = new PostManager($postId);
//
//        $this->view->assign($this->post->toArray());
//        $this->view->assign('newsubject', $this->__('Split') . ': ' . $this->post->get()->getTopic()->getTitle());

        split_error:

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Topic/split.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentTopic' => $managedTopic,
                    'currentPost' => $managedPost,
                    'form' => $form->createView(),
                    'settings' => $this->getVars()
                ]);
        if ($format == 'ajax.html') {
            return new Response(json_encode(['html' => $contentHtml]));
        }
        // full html page
        return new Response($contentHtml);
    }

    /**
     * @Route("/topic/{topic}/mail", requirements={"topic" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * User interface to email a topic to a arbitrary email-address
     *
     * @return string
     */
    public function emailtopicAction(Request $request, $topic)
    {
        $format = $this->decodeFormat($request);
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            $error = $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]);
            if ($format == 'json' || $format == 'ajax.html') {
                return new Response(json_encode(['error' => $error]));
            }

            $this->addFlash('error', $error);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $currentForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
//        if(!$currentForumUser->allowedToModerate($managedTopic)) {
//            throw new AccessDeniedException();
//        }

        $formBuilder = $this->get('form.factory')
            ->createBuilder(
                new EmailTopicType(),
                $managedTopic->get(),
                [
                'translator' => $this->get('translator'),
                'settings' => $this->getVars()
                ]
            );

        if ($format == 'html') {
            $formBuilder->add('send', SubmitType::class);
        }

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

//        $view->assign($managedTopic->get()->toArray());
//        $view->assign('emailsubject', $managedTopic->get()->getTitle());
//        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->topic_id), RouterInterface::ABSOLUTE_URL);
//        $view->assign('message', DataUtil::formatForDisplay($this->__('Hello! Please visit this link. I think it will be of interest to you.')) . "\n\n" . $url);
//
//        ModUtil::apiFunc($this->name, 'notify', 'email', array(
//            'sendto_email' => $data['sendto_email'],
//            'message' => $data['message'],
//            'subject' => $data['emailsubject']
//        ));

        email_error:

        $contentHtml = $this->renderView("@ZikulaDizkusModule/Topic/email.$format.twig", [
                    'currentForumUser' => $currentForumUser,
                    'currentTopic' => $managedTopic,
                    'form' => $form->createView(),
                    'settings' => $this->getVars()
                ]);
        if ($format == 'ajax.html') {
            return new Response(json_encode(['html' => $contentHtml]));
        }
        // full html page
        return new Response($contentHtml);
    }

    /**
     * @Route("/topic/{topic}/{action}", requirements={"topic" = "^[1-9]\d*$", "action"="lock|unlock"}, options={"expose"=true})
     *
     * Lock forum
     *
     * User interface for forum locking
     *
     * @return string
     */
    public function lockAction(Request $request, $topic, $action)
    {
        $format = $this->decodeFormat($request);
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            if ($request->isXmlHttpRequest()) {
                return new UnavailableResponse([], strip_tags($this->getVar('forum_disabled_info')));
            } else {
                return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                    'forum_disabled_info' => $this->getVar('forum_disabled_info'),
                ]);
            }
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            throw new NotFoundHttpException($this->__('Error! Topic not found in \'Dizkus/TopicController/unlockAction()\'.'));
        }

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$forumUserManager->allowedToModerate($managedTopic)) {
            throw new AccessDeniedException();
        }

        $managedTopic->get()->{$action}();
        $managedTopic->store();

        if (!$request->isXmlHttpRequest()) {
            // everything is good no ajax return to to topic view
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmoduletopic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/topic/{topic}/{action}", requirements={"topic" = "^[1-9]\d*$", "action"="sticky|unsticky"}, options={"expose"=true})
     *
     * Lock forum
     *
     * User interface for forum locking
     *
     * @return string
     */
    public function stickyAction(Request $request, $topic, $action)
    {
        $format = $this->decodeFormat($request);
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            if ($request->isXmlHttpRequest()) {
                return new UnavailableResponse([], strip_tags($this->getVar('forum_disabled_info')));
            } else {
                return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                    'forum_disabled_info' => $this->getVar('forum_disabled_info'),
                ]);
            }
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            throw new NotFoundHttpException($this->__('Error! Topic not found in \'Dizkus/TopicController/unlockAction()\'.'));
        }

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$forumUserManager->allowedToModerate($managedTopic)) {
            throw new AccessDeniedException();
        }

        $managedTopic->get()->{$action}();
        $managedTopic->store();

        if (!$request->isXmlHttpRequest()) {
            // everything is good no ajax return to to topic view
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/topic/{topic}/{action}/{post}", requirements={"topic" = "^[1-9]\d*$", "action"="solve|unsolve"}, options={"expose"=true})
     *
     * Lock forum
     *
     * User interface for forum locking
     *
     * @return string
     */
    public function solveAction(Request $request, $topic, $action, $post = -1)
    {
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            if ($request->isXmlHttpRequest()) {
                return new UnavailableResponse([], strip_tags($this->getVar('forum_disabled_info')));
            } else {
                return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                    'forum_disabled_info' => $this->getVar('forum_disabled_info'),
                ]);
            }
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            throw new NotFoundHttpException($this->__('Error! Topic not found in \'Dizkus/TopicController/unlockAction()\'.'));
        }

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$forumUserManager->allowedToModerate($managedTopic)) {
            throw new AccessDeniedException();
        }

        $managedTopic->get()->{$action}($post);
        $managedTopic->store();

        if (!$request->isXmlHttpRequest()) {
            // everything is good no ajax return to to topic view
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmoduletopic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
        }
    }
}
