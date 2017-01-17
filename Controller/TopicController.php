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
use Zikula\Core\Response\PlainResponse;

use Zikula\DizkusModule\Form\Type\Topic\NewType;
use Zikula\DizkusModule\Form\Type\Topic\ReplyType;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Manager\TopicManager;

use Zikula\Common\Translator\TranslatorInterface;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\FormError;
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
class TopicController extends AbstractController
{
    /**
     * @Route("/topic/{topic}/{start}", requirements={"topic" = "^[1-9]\d*$", "start" = "^[1-9]\d*$"})
     *
     * viewtopic
     *
     * @param Request $request
     * @param integer $topic the topic ID
     * @param integer $start pager value
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function viewtopicAction(Request $request, $topic, $start = 1)
    {
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                        'forum_disabled_info' => $this->getVar('forum_disabled_info')
            ]); 
        }
        
        $lastVisitUnix = $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit();
 
        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic); //new TopicManager($topic);
        if (!$managedTopic->exists()) {
            $request->getSession()->getFlashBag()->add('error', $this->translator->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]));
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead($managedTopic->get()->getForum())) {
            throw new AccessDeniedException();
        }     
                
        // @todo rank helper    
        list(, $ranks) = $this->get('zikula_dizkus_module.rank_helper')->getAll(['ranktype' => RankEntity::TYPE_POSTCOUNT]); //ModUtil::apiFunc($this->name, 'Rank', 'getAll', ['ranktype' => RankEntity::TYPE_POSTCOUNT]);
        
        $managedTopic->incrementViewsCount();
         
        return $this->render('@ZikulaDizkusModule/Topic/view.html.twig', [
            'ranks' => $ranks,
            'start' => $start,
            'topic' => $managedTopic->get(),
            'posts' => $managedTopic->getPosts(--$start),
            'pager' => $managedTopic->getPager(),
            'permissions' => $this->get('zikula_dizkus_module.security')->get($managedTopic->get()->getForum()),
            'isModerator' => $managedTopic->getManagedForum()->isModerator(),
            'breadcrumbs' => $managedTopic->getBreadcrumbs(),
            'isSubscribed' => $managedTopic->isSubscribed(),
            'nextTopic' => $managedTopic->getNext(),
            'previousTopic' => $managedTopic->getPrevious(),
            'last_visit_unix' => $lastVisitUnix,
            'preview'=> false,
            'settings' => $this->getVars()
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
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! Missing forum id.'));
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }

        $managedForum =  $this->get('zikula_dizkus_module.forum_manager')->getManager($forum);    //new ForumManager($this->_forumId);
        if ($managedForum->get()->isLocked()) {
            // it should be impossible for a user to get here, but this is just a sanity check
            $request->getSession()->getFlashBag()->add('error', $this->__('Error! This forum is locked. New topics cannot be created.'));
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_viewforum', ['forum' => $forum], RouterInterface::ABSOLUTE_URL));
        }
         
        $form = $this->createForm(new NewType($this->get('zikula_users_module.current_user')->isLoggedIn()), [], []);        
        $form->handleRequest($request);
        
        // check hooked modules for validation
        $hook = new ValidationHook(new ValidationProviders()); //   
        $hookvalidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.forum.validate_edit', $hook)->getValidators();        
        
        // check hooked modules for validation for POST
        $postHook = new ValidationHook(new ValidationProviders());
        /** @var $postHookValidators \Zikula\Core\Hook\ValidationProviders */
        $postHookValidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.validate_edit', $postHook)->getValidators(); 
        if ($postHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $request->getSession()->getFlashBag()->add('error', "Error! $error");
            }
        }
        
        // check hooked modules for validation for TOPIC
        $topicHook = new ValidationHook(new ValidationProviders());
        /** @var $topicHookValidators \Zikula\Core\Hook\ValidationProviders */
        $topicHookValidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.topic.validate_edit', $topicHook)->getValidators();
        if ($topicHookValidators->hasErrors()) {
            foreach ($postHookValidators->getErrors() as $error) {
                $request->getSession()->getFlashBag()->add('error', "Error! $error");
            }
        } 
        
        if ($form->isValid() && !$hookvalidators->hasErrors() && !$postHookValidators->hasErrors() && !$topicHookValidators->hasErrors()) {

            $data = $form->getData();
            $data['forum_id'] = $forum;
            $data['attachSignature'] = isset($data['attachSignature']) ? $data['attachSignature'] : false ;
            $data['subscribeTopic'] = isset($data['subscribeTopic']) ? $data['subscribeTopic'] : false ;
            $data['isSupportQuestion'] = isset($data['isSupportQuestion']) ? $data['isSupportQuestion'] : false ;
            
            
            $newManagedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager();
            $newManagedTopic->prepare($data);
                       
            // @todo this maybe should be done by hooks?
//            if (ModUtil::apiFunc($this->name, 'user', 'isSpam', $newManagedTopic->getFirstPost())) {
//                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Your post contains unacceptable content and has been rejected.'));
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
            'ranks' => isset($ranks) ? $ranks : false,
            'lastVisitUnix' => $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit(),
            'form' => $form->createView(),
            'breadcrumbs' => $managedForum->getBreadcrumbs(false),
            'preview'=> isset($preview) ? $preview : false,
            'post' => isset($post) ? $post : false,
            'forum' => $managedForum->get(),
            'settings' => $this->getVars()
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
            
        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager($topic); //new TopicManager($topic);
        if (!$managedTopic->exists()) {
            $request->getSession()->getFlashBag()->add('error', $this->translator->__f('Error! The topic you selected (ID: %s) was not found. Please try again.', [$topic]));
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        } 
        
        $template = $request->get('template') == 'quick.reply' || $request->isXmlHttpRequest() ? 'quick.reply' : 'reply';
        $action  = $this->get('router')->generate('zikuladizkusmodule_topic_replytopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL);         
        $form = $this->createForm(new ReplyType($this->get('zikula_users_module.current_user')->isLoggedIn()), [], ['action' => $action, 'topic' => $managedTopic->getId()]);        
        $form->handleRequest($request);
        
        // process validation hooks
        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.post.validate_edit', $hook)->getValidators();
        /** @var $hookvalidators \Zikula\Core\Hook\ValidationProviders */
        if ($hookvalidators->hasErrors()) {
            foreach ($hookvalidators->getErrors() as $error) {
                // This need to be tested!!
                //$request->getSession()->getFlashBag()->add('error', "Error! $error");
                $form->get('message')->addError(new FormError($this->__($error)));
            }
        }
        
        //&& !$hookvalidators->hasErrors() is not needed because we attach any hook errors to message field 
        // this might not be ok for some hooks - to chceck!
        if($form->isValid()){
            
            //return new PlainResponse(dump($form));
            
            // everything is good at this point so we either show preview or save 
            $data = $form->getData();
            $reply = [
                'topic_id' => $topic,
                'post_text' => $data['message'],
                'attachSignature' => $data['attachSignature']];            
            
            $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager();
            $managedPost->create($reply);            
       
            list(, $ranks) = $this->get('zikula_dizkus_module.rank_helper')->getAll(['ranktype' => RankEntity::TYPE_POSTCOUNT]);  
            
                if ($form->get('preview')->isClicked()) {
                    $preview = true;
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
                    if(!$request->isXmlHttpRequest()){
                        // everything is good no ajax return to to topic view
                        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));                        
                    }
                    
                    unset($form);
                    $form = $this->createForm(new ReplyType($this->get('zikula_users_module.current_user')->isLoggedIn()), [], ['action' => $action, 'topic' => $managedTopic->getId()]); 
                    
                }                
              
        }else {
           // error - no preview just form with error information!
        }
                 
        // 
        return $this->render("@ZikulaDizkusModule/Topic/$template.html.twig", [
            'topic' => $managedTopic->get(),
            'ranks' => isset($ranks) ? $ranks : false,
//            'lastVisitUnix' => $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit(),
            'form' => $form->createView(),
//            'breadcrumbs' => $managedForum->getBreadcrumbs(false),
            'preview'=> isset($preview) ? $preview : false,
            'post' => isset($post) ? $post : false,
//            'forum' => $managedForum->get(),
            'start' => isset($start) ? $start : 1,
            'settings' => $this->getVars()
            ], $request->isXmlHttpRequest() ? new PlainResponse() : null );  
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
    public function deletetopicAction(Request $request)
    {
//        
//        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
//            throw new AccessDeniedException();
//        }
//
//        $this->topic_id = (int)$this->request->query->get('topic');
//
//        if (empty($this->topic_id)) {
//            $post_id = (int)$this->request->query->get('post');
//            if (empty($post_id)) {
//                throw new \InvalidArgumentException();
//            }
//            $managedPost = new PostManager($post_id);
//            $this->topic_id = $managedPost->getTopicId();
//        }
//
//        $managedTopic = new TopicManager($this->topic_id);
//
//        $this->topic_poster = $managedTopic->get()->getPoster();
//        $topicPerms = $managedTopic->getPermissions();
//
//        if ($topicPerms['moderate'] <> true) {
//            throw new AccessDeniedException();
//        }
//
//        $view->assign($managedTopic->toArray());
//
//        return true;
//        
//        
//       // rewrite to topic if cancel was pressed
//        if ($args['commandName'] == 'cancel') {
//            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->topic_id), RouterInterface::ABSOLUTE_URL);
//            return $view->redirect($url);
//        }
//
//        // check for valid form and get data
//        if (!$view->isValid()) {
//            return false;
//        }
//        $hook = new ValidationHook(new ValidationProviders());
//        $hookvalidators = $this->dispatchHooks('dizkus.ui_hooks.topic.validate_delete', $hook)->getValidators();
//        if ($hookvalidators->hasErrors()) {
//            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
//        }
//
//        $forum_id = ModUtil::apiFunc($this->name, 'topic', 'delete', array('topic' => $this->topic_id));
//        $this->dispatchHooks('dizkus.ui_hooks.topic.process_delete', new ProcessHook($this->topic_id));
//
//        $data = $view->getValues();
//
//        // send the poster a reason why his/her post was deleted
//        if ($data['sendReason'] && !empty($data['reason'])) {
//            $poster = $this->topic_poster->getUser();
//            ModUtil::apiFunc('Mailer', 'user', 'sendmessage', array(
//                'toaddress' => $poster['email'],
//                'subject' => $this->__('Post deleted'),
//                'body' => $data['reason'],
//                'html' => true)
//            );
//            $this->request->getSession()->getFlashBag()->add('status', $this->__('Email sent!'));
//        }
//
//        // redirect to the forum of the deleted topic
//        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewforum', array('forum' => $forum_id), RouterInterface::ABSOLUTE_URL);
//        return $view->redirect($url);
        
        
        
//        $form = FormUtil::newForm($this->name, $this);
//
//        return new Response($form->execute('User/topic/delete.tpl', new DeleteTopic()));
        
        return $this->render('@ZikulaDizkusModule/Topic/delete.html.twig', [
//            'ranks' => isset($ranks) ? $ranks : false,
//            'lastVisitUnix' => $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit(),
//            'form' => $form->createView(),
//            'breadcrumbs' => $managedForum->getBreadcrumbs(false),
//            'preview'=> isset($preview) ? $preview : false,
//            'post' => isset($post) ? $post : false,
//            'forum' => $managedForum->get(),
            'settings' => $this->getVars()
            ]); 
    }

    /**
     * @Route("/topic/{topic}/move", requirements={"topic" = "^[1-9]\d*$"} )
     *
     * Move topic
     *
     * User interface to move a topic to another forum.
     *
     * @return string
     */
    public function movetopicAction(Request $request)
    {
        
//        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
//            throw new AccessDeniedException();
//        }
//
//        $this->topic_id = (int) $this->request->query->get('topic', null);
//        $managedTopic = new TopicManager($this->topic_id);
//        $this->topic = $managedTopic->get();
//        $view->assign('topic', $this->topic_id);
//        $view->assign('forums', ModUtil::apiFunc($this->name, 'Forum', 'getAllChildren'));
//        
//        
//        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $this->topic_id), RouterInterface::ABSOLUTE_URL);
//        if ($args['commandName'] == 'cancel') {
//            return $view->redirect($url);
//        }
//
//        // check for valid form
//        if (!$view->isValid()) {
//            return false;
//        }
//
//        $data = $view->getValues();
//
//        if ($args['commandName'] == 'move') {
//            // require perms for both subject topic and destination forum
//            if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $this->topic->getForum())
//                    || !ModUtil::apiFunc($this->name, 'Permission', 'canModerate', array('forum_id' => $data['forum_id']))) {
//                throw new AccessDeniedException();
//            }
//
//            if ($data['forum_id'] == $this->topic->getForum()->getForum_id()) {
//                $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! The original forum cannot be the same as the target forum.'));
//                return false;
//            }
//            $data['topicObj'] = $this->topic;
//
//            ModUtil::apiFunc($this->name, 'topic', 'move', $data);
//
//            return $view->redirect($url);
//        }
//
//        if ($args['commandName'] == 'join') {
//            $managedDestinationTopic = new TopicManager($data['to_topic_id']);
//            // require perms for both subject topic and destination topic
//            if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $this->topic->getForum())
//                    || !ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $managedDestinationTopic->get()->getForum())) {
//                throw new AccessDeniedException();
//            }
//
//            if (!empty($data['to_topic_id']) && ($data['to_topic_id'] == $this->topic_id)) {
//                // user wants to copy topic to itself
//                return $view->redirect($url);
//            }
//
//            $data['from_topic_id'] = $this->topic_id;
//            $data['topicObj'] = $this->topic;
//
//            ModUtil::apiFunc($this->name, 'topic', 'join', $data);
//
//            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $data['to_topic_id']), RouterInterface::ABSOLUTE_URL);
//            return $view->redirect($url);
//        }
//
//        return true;
        
//        $form = FormUtil::newForm($this->name, $this);
//
//        return new Response($form->execute('User/topic/move.tpl', new MoveTopic()));
        
        return $this->render('@ZikulaDizkusModule/Topic/move.html.twig', [
//            'ranks' => isset($ranks) ? $ranks : false,
//            'lastVisitUnix' => $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit(),
//            'form' => $form->createView(),
//            'breadcrumbs' => $managedForum->getBreadcrumbs(false),
//            'preview'=> isset($preview) ? $preview : false,
//            'post' => isset($post) ? $post : false,
//            'forum' => $managedForum->get(),
            'settings' => $this->getVars()
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
            'settings' => $this->getVars()
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
            'settings' => $this->getVars()
            ]); 
    } 

   /**
     * @Route("/topic/{topic}/{action}/{post}", requirements={
     *      "topic" = "^[1-9]\d*$",
     *      "action" = "subscribe|unsubscribe|sticky|unsticky|lock|unlock|solve|unsolve|setTitle",
     *      "post" = "^[1-9]\d*$"}, options={"expose"=true}
     *      
     * )
     * @Method("GET")
     *
     * @param integer $topic
     * @param string $action
     * @param integer $post (default = NULL)
     *
     * Change a param of a topic
     * WARNING: this method is overridden by an Ajax method
     *
     * @return RedirectResponse
     */
    public function changeTopicStatusAction(Request $request, $topic, $action, $post = null)
    {
//        $params = array(
//            'action' => $action,
//            'topic' => $topic,
//            'post' => $post);
//        // perm check in API
//        ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);
//
//       
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
     *  string 'selorder'
     *  integer 'nohours'
     *  integer 'unanswered'
     *  integer 'last_visit_unix'
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function viewlatestAction(Request $request)
    {
//        // Permission check
//        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
//            throw new AccessDeniedException();
//        }
//        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
//            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
//        }
//        // get the input
//        $params = [];
//        $params['selorder'] = $request->get('selorder', 1);
//        $params['nohours'] = (int)$request->request->get('nohours', 24);
//        $params['unanswered'] = (int)$request->query->get('unanswered', 0);
//        $params['amount'] = (int)$request->query->get('amount', null);
//        $params['last_visit_unix'] = (int)$request->query->get('last_visit_unix', time());
//        $this->view->assign($params);
//        list($topics, $text, $pager) = ModUtil::apiFunc($this->name, 'post', 'getLatest', $params);
//        $this->view->assign('topics', $topics);
//        $this->view->assign('text', $text);
//        $this->view->assign('pager', $pager);
//        $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
//        $this->view->assign('last_visit_unix', $lastVisitUnix);
//
//        return new Response($this->view->fetch('User/topic/latest.tpl'));
        
        return $this->render('@ZikulaDizkusModule/Topic/latest.html.twig', [
//            'ranks' => isset($ranks) ? $ranks : false,
//            'lastVisitUnix' => $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit(),
//            'form' => $form->createView(),
//            'breadcrumbs' => $managedForum->getBreadcrumbs(false),
//            'preview'=> isset($preview) ? $preview : false,
//            'post' => isset($post) ? $post : false,
//            'forum' => $managedForum->get(),
            'settings' => $this->getVars()
            ]); 
    }  
}



//    /**
//     * @Route("/reply")
//     * @Method("POST")
//     *
//     * reply to a post
//     *
//     * @param Request $request
//     *  integer 'forum' the forum ID
//     *  integer 'topic' the topic ID
//     *  integer 'post' the post ID
//     *  string 'returnurl' encoded url string
//     *  string 'message' the content of the post
//     *  integer 'attach_signature'
//     *  integer 'subscribe_topic'
//     *  string 'preview' submit button converted to boolean
//     *  string 'submit' submit button converted to boolean
//     *  string 'cancel' submit button converted to boolean
//     *
//     * @throws AccessDeniedException on failed perm check
//     *
//     * @return Response|RedirectResponse
//     */
//    public function replyAction(Request $request)
//    {
//        // Comment Permission check
//        $forum_id = (int) $request->request->get('forum', null);
//        if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', ['forum_id' => $forum_id])) {
//            throw new AccessDeniedException();
//        }
//        $this->checkCsrfToken();
//        // get the input
//        $topic_id = (int)$request->request->get('topic', null);
//        $post_id = (int)$request->request->get('post', null);
//        $returnUrl = $request->request->get('returnUrl', '');
//        $message = $request->request->get('message', '');
//        $attach_signature = (int)$request->request->get('attach_signature', 0);
//        $subscribe_topic = (int)$request->request->get('subscribe_topic', 0);
//        // convert form submit buttons to boolean
//        $isPreview = $request->request->get('preview', null);
//        $isPreview = isset($isPreview) ? true : false;
//        $submit = $request->request->get('submit', null);
//        $submit = isset($submit) ? true : false;
//        $cancel = $request->request->get('cancel', null);
//        $cancel = isset($cancel) ? true : false;
//        /**
//         * if cancel is submitted move to topic-view
//         */
//        if ($cancel) {
//            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $topic_id], RouterInterface::ABSOLUTE_URL));
//        }
//        $message = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $message);
//        // check for maximum message size
//        if (strlen($message) + strlen('[addsig]') > 65535) {
//            $request->getSession()->getFlashBag()->add('status', $this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
//            // switch to preview mode
//            $isPreview = true;
//        }
//        if (empty($message)) {
//            $request->getSession()->getFlashBag()->add('status', $this->__('Error! The message is empty. Please add some text.'));
//            // switch to preview mode
//            $isPreview = true;
//        }
//        // check hooked modules for validation
//        if ($submit) {
//            $hook = new ValidationHook(new ValidationProviders());
//            $hookvalidators = $this->dispatchHooks('dizkus.ui_hooks.post.validate_edit', $hook)->getValidators();
//            if ($hookvalidators->hasErrors()) {
//                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Hooked content does not validate.'));
//                $isPreview = true;
//            }
//        }
//        if ($submit && !$isPreview) {
//            $data = [
//                'topic_id' => $topic_id,
//                'post_text' => $message,
//                'attachSignature' => $attach_signature];
//            $managedPost = $this->get('zikula_dizkus_module.post_manager')->manage();
//            $managedPost->create($data);
//            // check to see if the post contains spam
//            if (ModUtil::apiFunc($this->name, 'user', 'isSpam', $managedPost->get())) {
//                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Your post contains unacceptable content and has been rejected.'));
//                return new Response('', Response::HTTP_NOT_ACCEPTABLE);
//            }
//            $managedPost->persist();
//            // handle subscription
//            if ($subscribe_topic) {
//                ModUtil::apiFunc($this->name, 'topic', 'subscribe', ['topic' => $topic_id]);
//            } else {
//                ModUtil::apiFunc($this->name, 'topic', 'unsubscribe', ['topic' => $topic_id]);
//            }
//            $start = ModUtil::apiFunc($this->name, 'user', 'getTopicPage', ['replyCount' => $managedPost->get()->getTopic()->getReplyCount()]);
//            $params = [
//                'topic' => $topic_id,
//                'start' => $start];
//            $url = RouteUrl::createFromRoute('zikuladizkusmodule_user_viewtopic', $params, "pid{$managedPost->getId()}");
//            $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedPost->getId(), $url));
//            // notify topic & forum subscribers
////            $notified = ModUtil::apiFunc($this->name, 'notify', 'emailSubscribers', array('post' => $managedPost->get()));
//            // if viewed in hooked state, compute redirectUrl to go back to hook subscriber
//            if (!empty($returnUrl)) {
//                $urlParams = json_decode(htmlspecialchars_decode($returnUrl), true);
//                $urlParams['args']['start'] = $start;
//                if (isset($urlParams['route'])) { // array generated from RouteUrl::toArray() or from Request Obj
//                    $route = $urlParams['route'];
//                    unset($urlParams['route']);
//                    $url = RouteUrl::createFromRoute($route, $urlParams['args'], "pid{$managedPost->getId()}");
//                } else {
//                    if (isset($urlParams['application'])) { // array generated from ModUrl::toArray()
//                        $mod = $urlParams['application'];
//                        unset($urlParams['application']);
//                        $type = $urlParams['controller'];
//                        unset($urlParams['controller']);
//                        $func = $urlParams['action'];
//                        unset($urlParams['action']);
//                    } else { // array generated only from URI
//                        $mod = $urlParams['module'];
//                        unset($urlParams['module']);
//                        $type = $urlParams['type'];
//                        unset($urlParams['type']);
//                        $func = $urlParams['func'];
//                        unset($urlParams['func']);
//                    }
//                    $url = new ModUrl($mod, $type, $func, ZLanguage::getLanguageCode(), $urlParams, 'pid' . $managedPost->getId());
//                }
//            }
//
//            return new RedirectResponse(System::normalizeUrl($url->getUrl()));
//        } else {
//            $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
//            $managedTopic = new TopicManager($topic_id);
//            $managedPoster = new ForumUserManager();
//            $reply = [
//                'topic_id' => $topic_id,
//                'post_id' => $post_id,
//                'attach_signature' => $attach_signature,
//                'subscribe_topic' => $subscribe_topic,
//                'topic' => $managedTopic->toArray(),
//                'message' => $message];
//            $post = [
//                'post_id' => 0,
//                'topic_id' => $topic_id,
//                'poster' => $managedPoster->toArray(),
//                'post_time' => time(),
//                'attachSignature' => $attach_signature,
//                'post_text' => $message,
//                'userAllowedToEdit' => false];
//            // Do not show edit link
//            $permissions = [];
//            list(, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', ['ranktype' => RankEntity::TYPE_POSTCOUNT]);
//            $this->view->assign('ranks', $ranks);
//            $this->view->assign('post', $post);
//            $this->view->assign('reply', $reply);
//            $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
//            $this->view->assign('preview', $isPreview);
//            $this->view->assign('last_visit_unix', $lastVisitUnix);
//            $this->view->assign('permissions', $permissions);
//
//            return new Response($this->view->fetch('User/topic/reply.tpl'));
//        }
//    }

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
//    public function replyAction(Request $request)
//    {
//        $this->errorIfForumDisabled();
//        $this->checkAjaxToken();
//        $topic_id = $request->request->get('topic', null);
//        $message = $request->request->get('message', '');
//        $attach_signature = $request->request->get('attach_signature', 0) == '1' ? true : false;
//        $subscribe_topic = $request->request->get('subscribe_topic', 0) == '1' ? true : false;
//        $preview = $request->request->get('preview', 0) == '1' ? true : false;
//        $message = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $message);
//        $managedTopic = new TopicManager($topic_id);
//        $start = 1;
//        $this->checkMessageLength($message);
//        $data = array(
//            'topic_id' => $topic_id,
//            'post_text' => $message,
//            'attachSignature' => $attach_signature);
//        $managedPost = new PostManager();
//        $managedPost->create($data);
//        // process validation hooks
//        $hook = new ValidationHook(new ValidationProviders());
//        $hookvalidators = $this->dispatchHooks('dizkus.ui_hooks.post.validate_edit', $hook)->getValidators();
//        /** @var $hookvalidators \Zikula\Core\Hook\ValidationProviders */
//        if ($hookvalidators->hasErrors()) {
//            foreach ($hookvalidators->getErrors() as $error) {
//                $request->getSession()->getFlashBag()->add('error', "Error! $error");
//            }
//            $preview = true;
//        }
//        // check to see if the post contains spam
//        if (ModUtil::apiFunc($this->name, 'user', 'isSpam', $managedPost->get())) {
//            $request->getSession()->getFlashBag()->add('error', $this->__('Error! Your post contains unacceptable content and has been rejected.'));
//            $preview = true;
//        }
//        if ($preview == false) {
//            $managedPost->persist();
//            if ($subscribe_topic) {
//                ModUtil::apiFunc($this->name, 'topic', 'subscribe', array('topic' => $topic_id));
//            } else {
//                ModUtil::apiFunc($this->name, 'topic', 'unsubscribe', array('topic' => $topic_id));
//            }
//            $start = ModUtil::apiFunc($this->name, 'user', 'getTopicPage', array('replyCount' => $managedPost->get()->getTopic()->getReplyCount()));
//            $params = array('topic' => $topic_id, 'start' => $start);
//            $url = RouteUrl::createFromRoute('zikuladizkusmodule_user_viewtopic', $params, 'pid' . $managedPost->getId());
//            $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedPost->getId(), $url));
//            // notify topic & forum subscribers
////            ModUtil::apiFunc($this->name, 'notify', 'emailSubscribers', array('post' => $managedPost->get()));
//            $post = $managedPost->get()->toArray();
//            $permissions = ModUtil::apiFunc($this->name, 'permission', 'get', array('forum_id' => $managedPost->get()->getTopic()->getForum()->getForum_id()));
//        } else {
//            // preview == true, create fake post
//            $managedPoster = new ForumUserManager();
//            $post = array(
//                'post_id' => 99999999999,
//                'topic_id' => $topic_id,
//                'poster' => $managedPoster->toArray(),
//                'post_time' => time(),
//                'attachSignature' => $attach_signature,
//                'post_text' => $message,
//                'subscribe_topic' => $subscribe_topic,
//                'userAllowedToEdit' => false);
//            // Do not show edit link
//            $permissions = array();
//        }
//        $this->view->setCaching(false);
//        $this->view->assign('topic', $managedTopic->get());
//        $this->view->assign('post', $post);
//        $this->view->assign('start', $start);
//        $this->view->assign('preview', $preview);
//        $this->view->assign('permissions', $permissions);
//        list(, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => RankEntity::TYPE_POSTCOUNT));
//        $this->view->assign('ranks', $ranks);
//
//        if ($request->getSession()->getFlashBag()->has('error')) {
//            $errors = implode('\n', $request->getSession()->getFlashBag()->get('error'));
//            return new Response($errors, 500);
//        } else {
//            return new AjaxResponse(array(
//                'data' => $this->view->fetch('User/post/single.tpl'),
//                'post_id' => $post['post_id']));
//        }
//    }    
//    
//       
 


    /**
     * @Route("/topic/change-status", options={"expose"=true})
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
//    public function changeTopicStatusAction(Request $request)
//    {
//        // Check if forum is disabled
//        if (!$this->getVar('forum_enabled')) {
//            return new UnavailableResponse([], strip_tags($this->getVar('forum_disabled_info')));
//        }
//        // Get common parameters
//        $params = [];
//        $params['topic'] = $request->request->get('topic', '');
//        $params['post'] = $request->request->get('post', null);
//        $params['action'] = $request->request->get('action', '');
//
//        // Check if topic is is set
//        if (empty($params['topic'])) {
//            return new BadDataResponse([], $this->__('Error! No topic ID in \'Dizkus/Ajax/changeTopicStatus()\'.'));
//        }
//        // Check if action is legal
//        $allowedActions = ['lock', 'unlock', 'sticky', 'unsticky', 'subscribe', 'unsubscribe', 'solve', 'unsolve', 'setTitle'];
//        if (empty($params['action']) || !in_array($params['action'], $allowedActions)) {
//            return new BadDataResponse([], $this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus/Ajax/changeTopicStatus()\'.', DataUtil::formatForDisplay($params['action'])));
//        }
//        // Get title parameter if action == setTitle
//        if ($params['action'] == 'setTitle') {
//            $params['title'] = trim($request->request->get('title', ''));
//            if (empty($params['title'])) {
//                return new BadDataResponse([], $this->__('Error! The post has no subject line.'));
//            }
//        }
//        // perm check in API
//        ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);
//
//        return new PlainResponse('successful');
//    }    
