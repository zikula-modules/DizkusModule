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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Response\PlainResponse;
use Zikula\Core\RouteUrl;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Form\Type\Topic\DeleteType;
use Zikula\DizkusModule\Form\Type\Topic\JoinMoveType;
use Zikula\DizkusModule\Form\Type\Topic\NewType;
use Zikula\DizkusModule\Form\Type\Topic\ReplyType;

class TopicController extends AbstractController
{
    /**
     * @Route("/topic/{topic}/{start}", requirements={"topic" = "^[1-9]\d*$", "start" = "^[1-9]\d*$"})
     *
     * viewtopic
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


//        dump($this->getDoctrine()->getRepository('Zikula\DizkusModule\Entity\PostEntity')->getLastPosts(5,3));
//        dump($this->get('zikula_dizkus_module.synchronization_helper')->posters());

        $currentTopic->loadPosts($start - 1);
        $currentTopic->incrementViewsCount();

        return $this->render('@ZikulaDizkusModule/Topic/view.html.twig', [
            'currentForumUser' => $currentForumUser,
            'currentTopic'    => $currentTopic,
            'start'           => $start,
            'preview'         => false,
            'settings'        => $this->getVars(),
            ]);
    }

    /**
     * @Route("/topic/new")
     *
     * Create new topic
     *
     * User interface to create a new topic
     *
     * @return string
     */
    public function newtopicAction(Request $request)
    {
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        // get the input
        $forum = (int) $request->query->get('forum');

        if (!isset($forum)) {
            $this->addFlash('error', $this->__('Error! Missing forum id.'));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($forum);

        if (!$managedForum->exists()){
            $this->addFlash('error', $this->__('Error! Missing forum id.'));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();

        if ($managedForum->get()->isLocked()) {
            $this->addFlash('error', $this->__('Error! This forum is locked. New topics cannot be created.'));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_viewforum', ['forum' => $forum], RouterInterface::ABSOLUTE_URL));
        }

        $form = $this->createForm(new NewType($forumUserManager->isLoggedIn()), [], []);
        $form->handleRequest($request);

        // check hooked modules for validation
        $hook = new ValidationHook(new ValidationProviders()); //
        $hookvalidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.forum.validate_edit', $hook)->getValidators();

        // check hooked modules for validation for post
        $postHook = new ValidationHook(new ValidationProviders());
        /** @var $postHookValidators \Zikula\Core\Hook\ValidationProviders */
        $postHookValidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.validate_edit', $postHook)->getValidators();
        if ($postHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $this->addFlash('error', "Error! $error");
            }
        }

        // check hooked modules for validation for topic
        $topicHook = new ValidationHook(new ValidationProviders());
        /** @var $topicHookValidators \Zikula\Core\Hook\ValidationProviders */
        $topicHookValidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.topic.validate_edit', $topicHook)->getValidators();
        if ($topicHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $this->addFlash('error', "Error! $error");
            }
        }

        if ($form->isValid() && !$hookvalidators->hasErrors() && !$postHookValidators->hasErrors() && !$topicHookValidators->hasErrors()) {
            $data = $form->getData();
            $data['forum_id'] = $forum;
            $data['attachSignature'] = isset($data['attachSignature']) ? $data['attachSignature'] : false;
            $data['subscribeTopic'] = isset($data['subscribeTopic']) ? $data['subscribeTopic'] : false;
            $data['isSupportQuestion'] = isset($data['isSupportQuestion']) ? $data['isSupportQuestion'] : false;

            $newManagedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager();
            $newManagedTopic->prepare($data);

            // @todo this maybe should be done by hooks?
//            if (ModUtil::apiFunc($this->name, 'user', 'isSpam', $newManagedTopic->getFirstPost())) {
//                $this->addFlash('error', $this->__('Error! Your post contains unacceptable content and has been rejected.'));
//                return false;
//            }

            if ($form->get('preview')->isClicked()) {
                $preview = true;
                $post = $newManagedTopic->getPreview()->toArray();
                $post['post_id'] = 0;
                $post['post_time'] = time();
                $post['topic_id'] = 0;
                $post['attachSignature'] = $data['attachSignature'];
                $post['subscribeTopic'] = $data['subscribeTopic'];
                $post['isSupportQuestion'] = $data['isSupportQuestion'];

                list(, $ranks) = $this->get('zikula_dizkus_module.rank_helper')->getAll(['ranktype' => RankEntity::TYPE_POSTCOUNT]);
            }

            if ($form->get('save')->isClicked()) {
                // store new topic
                $newManagedTopic->create();
                $url = new RouteUrl('zikuladizkusmodule_topic_viewtopic', ['topic' => $newManagedTopic->getId()]);
                // notify hooks for both POST and TOPIC
                $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.process_edit', new ProcessHook($newManagedTopic->getFirstPost()->getPost_id(), $url));
                $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.topic.process_edit', new ProcessHook($newManagedTopic->getId(), $url));

                // @todo notify topic & forum subscribers
//              ModUtil::apiFunc($this->name, 'notify', 'emailSubscribers', array('post' => $newManagedTopic->getFirstPost()));

                // redirect to the new topic
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $newManagedTopic->getId()], RouterInterface::ABSOLUTE_URL));
            }
        }

        return $this->render('@ZikulaDizkusModule/Topic/new.html.twig', [
            'last_visit_unix' => $forumUserManager->getLastVisit(),
            'currentForumUser' => $forumUserManager,
            'ranks'         => isset($ranks) ? $ranks : false,
            'form'          => $form->createView(),
            'breadcrumbs'   => $managedForum->getBreadcrumbs(false),
            'preview'       => isset($preview) ? $preview : false,
            'post'          => isset($post) ? $post : false,
            'forum'         => $managedForum->get(),
            'settings'      => $this->getVars(),
            ]);
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
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }


        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic);
        if (!$managedTopic->exists()) {
            $this->addFlash('error', $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();

        $template = $request->get('template') == 'quick.reply' || $request->isXmlHttpRequest() ? 'quick.reply' : 'reply';
        $action = $this->get('router')->generate('zikuladizkusmodule_topic_replytopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL);
        $form = $this->createForm(new ReplyType($forumUserManager->isLoggedIn()), [], ['action' => $action, 'topic' => $managedTopic->getId()]);
        $form->handleRequest($request);

        // process validation hooks
        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.validate_edit', $hook)->getValidators();
        /** @var $hookvalidators \Zikula\Core\Hook\ValidationProviders */
        if ($hookvalidators->hasErrors()) {
            foreach ($hookvalidators->getErrors() as $error) {
                // This need to be tested!!
                //$this->addFlash('error', "Error! $error");
                $form->get('message')->addError(new FormError($this->__($error)));
            }
        }

        //&& !$hookvalidators->hasErrors() is not needed because we attach any hook errors to message field
        // this might not be ok for some hooks - to chceck!
        if ($form->isValid()) {
            // everything is good at this point so we either show preview or save
            $data = $form->getData();
            $reply = [
                'topic_id'        => $topic,
                'post_text'       => $data['message'],
                'attachSignature' => $data['attachSignature'], ];

            $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager();
            $managedPost->create($reply);

            if ($form->get('preview')->isClicked()) {
                $preview = true;
                //$template = 'reply.preview';
                $post = $managedPost->toArray();
                $post['post_id'] = -1;
                $post['post_time'] = time();
                $post['topic_id'] = $topic;
                $post['attachSignature'] = $data['attachSignature'];
                $post['subscribeTopic'] = $data['subscribeTopic'];
            }

            if ($form->get('save')->isClicked()) {
                $managedPost->persist();
                $post = $managedPost->toArray();
                    //if not ajax redirect to topic page
                    if (!$request->isXmlHttpRequest()) {
                        // everything is good no ajax return to to topic view
                        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
                    }

                unset($form);
                $form = $this->createForm(new ReplyType($forumUserManager->isLoggedIn()), [], ['action' => $action, 'topic' => $managedTopic->getId()]);
            }
        } else {
            // error - no preview just form with error information!
        }

        return $this->render("@ZikulaDizkusModule/Topic/$template.html.twig", [
            'topic'     => $managedTopic->get(),
            'currentTopic' => $managedTopic,
            'form'      => $form->createView(),
            'currentForumUser' => $forumUserManager,
            'preview'   => isset($preview) ? $preview : false,
            'post'      => isset($post) ? $post : false,
            'start'     => isset($start) ? $start : 1,
            'settings'  => $this->getVars(),
            ], $request->isXmlHttpRequest() ? new PlainResponse() : null);
    }

    /**
     * @Route("/topic/{topic}/delete", requirements={"topic" = "^[1-9]\d*$"} )
     *
     * Delete topic
     *
     * User interface to delete a topic.
     *
     * @return string
     */
    public function deletetopicAction(Request $request, $topic)
    {
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic); //new TopicManager($topic);
        if (!$managedTopic->exists()) {
            $this->addFlash('error', $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $topic_poster = $managedTopic->get()->getPoster();
        $topicPerms = $managedTopic->getPermissions();

        if ($topicPerms['moderate'] != true) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new DeleteType($this->get('zikula_users_module.current_user')->isLoggedIn()), [], ['topic' => $managedTopic->getId()]);
        $form->handleRequest($request);

        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.topic.validate_delete', $hook)->getValidators();
        if ($hookvalidators->hasErrors()) {
            $this->addFlash('error', $this->__('Error! Hooked content does not validate.'));
        }

        if ($form->isValid() && !$hookvalidators->hasErrors()) {
            $data = $form->getData();

            if ($form->get('cancel')->isClicked()) {
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
            }

            if ($form->get('delete')->isClicked()) {
                $forum_id = $this->get('zikula_dizkus_module.topic_manager')->delete($managedTopic->get());
                $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.topic.process_delete', new ProcessHook($managedTopic->get()));

                //@todo send the poster a reason why his/her post was deleted
//                if ($data['sendReason'] && !empty($data['reason'])) {
//                    $poster = $topic_poster->getUser();
//                    ModUtil::apiFunc('Mailer', 'user', 'sendmessage', [
//                        'toaddress' => $poster['email'],
//                        'subject' => $this->__('Post deleted'),
//                        'body' => $data['reason'],
//                        'html' => true]
//                    );
//                    $this->request->getSession()->getFlashBag()->add('status', $this->__('Email sent!'));
//                }

                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_viewforum', ['forum' => $forum_id], RouterInterface::ABSOLUTE_URL));
            }
        }

        return $this->render('@ZikulaDizkusModule/Topic/delete.html.twig', [
            'topic'     => $managedTopic->get(),
            'form'      => $form->createView(),
            'settings'  => $this->getVars(),
            ]);
    }

    /**
     * @Route("/topic/{topic}/joinmove", requirements={"topic" = "^[1-9]\d*$"} )
     *
     * Move topic
     *
     * User interface to move a topic to another forum.
     *
     * @return string
     */
    public function joinmovetopicAction(Request $request, $topic)
    {
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic); //new TopicManager($topic);
        if (!$managedTopic->exists()) {
            $this->addFlash('error', $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $form = $this->createForm(
                    new JoinMoveType($this->get('translator'), $this->get('zikula_dizkus_module.forum_manager')->getAllChildren()),
                    [],
                    ['topic' => $managedTopic->getId(), 'forum' => $managedTopic->getForumId()]
        );

        $topicUrl = $this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            if ($form->get('cancel')->isClicked()) {
                return new RedirectResponse($topicUrl);
            }

            if ($form->get('move')->isClicked()) {
                // require perms for both subject topic and destination forum
                if (!$this->get('zikula_dizkus_module.security')->canModerate(['forum_id' => $managedTopic->getForumId()])
                        || !$this->get('zikula_dizkus_module.security')->canModerate(['forum_id' => $data['forum_id']])) {
                    throw new AccessDeniedException();
                }
                $this->get('zikula_dizkus_module.topic_manager')->move($managedTopic->getId(), $data['forum_id'], $data['createshadowtopic']);

                return new RedirectResponse($topicUrl);
            }

            if ($form->get('join')->isClicked()) {
                if (empty($data['to_topic_id'])) {
                    $this->addFlash('error', $this->__('Error! The topic ID cannot be empty.'));

                    return new RedirectResponse($topicUrl);
                }
                $managedDestinationTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($data['to_topic_id']);
                if (!$managedDestinationTopic->exists()) {
                    $this->addFlash('error', $this->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$data['to_topic_id']]));

                    return new RedirectResponse($topicUrl);
                }
                if (!$this->get('zikula_dizkus_module.security')->canModerate(['forum_id' => $managedTopic->getForumId()])
                        || !$this->get('zikula_dizkus_module.security')->canModerate(['forum_id' => $managedDestinationTopic->getForumId()])) {
                    throw new AccessDeniedException();
                }
                if ($managedDestinationTopic->getId() == $managedTopic->getId()) {
                    $this->addFlash('error', $this->__('Error! You cannot copy topic to itself.'));

                    return new RedirectResponse($topicUrl);
                }
                //@todo we asume everything will be ok
                $this->get('zikula_dizkus_module.topic_manager')->join($managedTopic, $managedDestinationTopic);

                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedDestinationTopic->getId()], RouterInterface::ABSOLUTE_URL));
            }
        }

        return $this->render('@ZikulaDizkusModule/Topic/move.html.twig', [
            'topic'     => $managedTopic->get(),
            'form'      => $form->createView(),
            'settings'  => $this->getVars(),
            ]);
    }

    /**
     * @Route("/topic/{topic}/split", requirements={"topic" = "^[1-9]\d*$"} )
     *
     * Split topic
     *
     * @return string
     */
    public function splittopicAction(Request $request)
    {
        //        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
//            throw new AccessDeniedException();
//        }
//
//        $postId = (int) $this->request->query->get('post');
//        $this->post = new PostManager($postId);
//
//        $this->view->assign($this->post->toArray());
//        $this->view->assign('newsubject', $this->__('Split') . ': ' . $this->post->get()->getTopic()->getTitle());
//
//        return true;
//
//        // rewrite to topic if cancel was pressed
//        if ($args['commandName'] == 'cancel') {
//            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->post->getTopicId()), RouterInterface::ABSOLUTE_URL);
//            return $view->redirect($url);
//        }
//
//        // check for valid form and get data
//        if (!$view->isValid()) {
//            return false;
//        }
//        $data = $view->getValues();
//
//        $newtopic_id = ModUtil::apiFunc($this->name, 'topic', 'split', array('post' => $this->post, 'data' => $data));
//
//        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $newtopic_id), RouterInterface::ABSOLUTE_URL);
//        return $view->redirect($url);

//        $form = FormUtil::newForm($this->name, $this);
//
//        return new Response($form->execute('User/topic/split.tpl', new SplitTopic()));

        return $this->render('@ZikulaDizkusModule/Topic/split.html.twig', [
//            'ranks' => isset($ranks) ? $ranks : false,
//            'lastVisitUnix' => $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit(),
//            'form' => $form->createView(),
//            'breadcrumbs' => $managedForum->getBreadcrumbs(false),
//            'preview'=> isset($preview) ? $preview : false,
//            'post' => isset($post) ? $post : false,
//            'forum' => $managedForum->get(),
            'settings' => $this->getVars(),
            ]);
    }

    /**
     * @Route("/topic/{topic}/mail", requirements={"topic" = "^[1-9]\d*$"} )
     *
     * User interface to email a topic to a arbitrary email-address
     *
     * @return string
     */
    public function emailtopicAction(Request $request)
    {
        //        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
//            throw new AccessDeniedException();
//        }
//
//        $this->topic_id = (int)$this->request->query->get('topic');
//
//        $managedTopic = new TopicManager($this->topic_id);
//
//        $view->assign($managedTopic->get()->toArray());
//        $view->assign('emailsubject', $managedTopic->get()->getTitle());
//        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->topic_id), RouterInterface::ABSOLUTE_URL);
//        $view->assign('message', DataUtil::formatForDisplay($this->__('Hello! Please visit this link. I think it will be of interest to you.')) . "\n\n" . $url);
//
//        return true;
//
//        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->topic_id), RouterInterface::ABSOLUTE_URL);
//        // rewrite to topic if cancel was pressed
//        if ($args['commandName'] == 'cancel') {
//            return $view->redirect($url);
//        }
//
//        // check for valid form and get data
//        if (!$view->isValid()) {
//            return false;
//        }
//        $data = $view->getValues();
//
//        ModUtil::apiFunc($this->name, 'notify', 'email', array(
//            'sendto_email' => $data['sendto_email'],
//            'message' => $data['message'],
//            'subject' => $data['emailsubject']
//        ));
//
//        return $view->redirect($url);

//        $form = FormUtil::newForm($this->name, $this);
//
//        return new Response($form->execute('User/topic/email.tpl', new EmailTopic()));

        return $this->render('@ZikulaDizkusModule/Topic/email.html.twig', [
//            'ranks' => isset($ranks) ? $ranks : false,
//            'lastVisitUnix' => $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit(),
//            'form' => $form->createView(),
//            'breadcrumbs' => $managedForum->getBreadcrumbs(false),
//            'preview'=> isset($preview) ? $preview : false,
//            'post' => isset($post) ? $post : false,
//            'forum' => $managedForum->get(),
            'settings' => $this->getVars(),
            ]);
    }

    /**
     * @Route("/topic/{topic}/{action}/{post}", requirements={
     *      "topic" = "^[1-9]\d*$",
     *      "action" = "sticky|unsticky|lock|unlock|solve|unsolve|setTitle",
     *      "post" = "^[1-9]\d*$"}, options={"expose"=true}
     *
     * )
     * @Method("GET")
     *
     * @param int    $topic
     * @param string $action
     * @param int    $post   (default = NULL)
     *
     * Change a param of a topic
     * WARNING: this method is overridden by an Ajax method
     *
     * @return RedirectResponse
     */
    public function changeTopicAction(Request $request, $topic, $action, $post = null)
    {


        dump($topic);
        dump($action);

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $topic], RouterInterface::ABSOLUTE_URL));
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

        $since = $request->query->get('since', null) == null ? null: (int)$request->query->get('since');
        $unanswered = $request->query->get('unanswered') == 'on' ? 1 : true ;
        $unsolved = $request->query->get('unsolved') == 'on' ? 1 : false ;
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 25);
            list($topics, $pager) = $this->getDoctrine()->getManager()
            ->getRepository('Zikula\DizkusModule\Entity\TopicEntity')
            ->getTopics($since, $unanswered, $unsolved, $page, $limit
            );

//        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
//            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
//        }

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
}
