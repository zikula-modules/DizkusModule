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

use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Manager\TopicManager;

use Zikula\Common\Translator\TranslatorInterface;

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
class TopicController extends AbstractController
{
    /**
     * @Route("/topic/{topic}/{start}", requirements={"topic" = "^[1-9]\d*$", "start" = "^[1-9]\d*$"})
     * @Method("GET")
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
     * @Route("/reply")
     * @Method("POST")
     *
     * reply to a post
     *
     * @param Request $request
     *  integer 'forum' the forum ID
     *  integer 'topic' the topic ID
     *  integer 'post' the post ID
     *  string 'returnurl' encoded url string
     *  string 'message' the content of the post
     *  integer 'attach_signature'
     *  integer 'subscribe_topic'
     *  string 'preview' submit button converted to boolean
     *  string 'submit' submit button converted to boolean
     *  string 'cancel' submit button converted to boolean
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function replyAction(Request $request)
    {
        // Comment Permission check
        $forum_id = (int) $request->request->get('forum', null);
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canWrite', ['forum_id' => $forum_id])) {
            throw new AccessDeniedException();
        }
        $this->checkCsrfToken();
        // get the input
        $topic_id = (int)$request->request->get('topic', null);
        $post_id = (int)$request->request->get('post', null);
        $returnUrl = $request->request->get('returnUrl', '');
        $message = $request->request->get('message', '');
        $attach_signature = (int)$request->request->get('attach_signature', 0);
        $subscribe_topic = (int)$request->request->get('subscribe_topic', 0);
        // convert form submit buttons to boolean
        $isPreview = $request->request->get('preview', null);
        $isPreview = isset($isPreview) ? true : false;
        $submit = $request->request->get('submit', null);
        $submit = isset($submit) ? true : false;
        $cancel = $request->request->get('cancel', null);
        $cancel = isset($cancel) ? true : false;
        /**
         * if cancel is submitted move to topic-view
         */
        if ($cancel) {
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $topic_id], RouterInterface::ABSOLUTE_URL));
        }
        $message = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $message);
        // check for maximum message size
        if (strlen($message) + strlen('[addsig]') > 65535) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
            // switch to preview mode
            $isPreview = true;
        }
        if (empty($message)) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Error! The message is empty. Please add some text.'));
            // switch to preview mode
            $isPreview = true;
        }
        // check hooked modules for validation
        if ($submit) {
            $hook = new ValidationHook(new ValidationProviders());
            $hookvalidators = $this->dispatchHooks('dizkus.ui_hooks.post.validate_edit', $hook)->getValidators();
            if ($hookvalidators->hasErrors()) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Hooked content does not validate.'));
                $isPreview = true;
            }
        }
        if ($submit && !$isPreview) {
            $data = [
                'topic_id' => $topic_id,
                'post_text' => $message,
                'attachSignature' => $attach_signature];
            $managedPost = $this->get('zikula_dizkus_module.post_manager')->manage();
            $managedPost->create($data);
            // check to see if the post contains spam
            if (ModUtil::apiFunc($this->name, 'user', 'isSpam', $managedPost->get())) {
                $request->getSession()->getFlashBag()->add('error', $this->__('Error! Your post contains unacceptable content and has been rejected.'));
                return new Response('', Response::HTTP_NOT_ACCEPTABLE);
            }
            $managedPost->persist();
            // handle subscription
            if ($subscribe_topic) {
                ModUtil::apiFunc($this->name, 'topic', 'subscribe', ['topic' => $topic_id]);
            } else {
                ModUtil::apiFunc($this->name, 'topic', 'unsubscribe', ['topic' => $topic_id]);
            }
            $start = ModUtil::apiFunc($this->name, 'user', 'getTopicPage', ['replyCount' => $managedPost->get()->getTopic()->getReplyCount()]);
            $params = [
                'topic' => $topic_id,
                'start' => $start];
            $url = RouteUrl::createFromRoute('zikuladizkusmodule_user_viewtopic', $params, "pid{$managedPost->getId()}");
            $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedPost->getId(), $url));
            // notify topic & forum subscribers
//            $notified = ModUtil::apiFunc($this->name, 'notify', 'emailSubscribers', array('post' => $managedPost->get()));
            // if viewed in hooked state, compute redirectUrl to go back to hook subscriber
            if (!empty($returnUrl)) {
                $urlParams = json_decode(htmlspecialchars_decode($returnUrl), true);
                $urlParams['args']['start'] = $start;
                if (isset($urlParams['route'])) { // array generated from RouteUrl::toArray() or from Request Obj
                    $route = $urlParams['route'];
                    unset($urlParams['route']);
                    $url = RouteUrl::createFromRoute($route, $urlParams['args'], "pid{$managedPost->getId()}");
                } else {
                    if (isset($urlParams['application'])) { // array generated from ModUrl::toArray()
                        $mod = $urlParams['application'];
                        unset($urlParams['application']);
                        $type = $urlParams['controller'];
                        unset($urlParams['controller']);
                        $func = $urlParams['action'];
                        unset($urlParams['action']);
                    } else { // array generated only from URI
                        $mod = $urlParams['module'];
                        unset($urlParams['module']);
                        $type = $urlParams['type'];
                        unset($urlParams['type']);
                        $func = $urlParams['func'];
                        unset($urlParams['func']);
                    }
                    $url = new ModUrl($mod, $type, $func, ZLanguage::getLanguageCode(), $urlParams, 'pid' . $managedPost->getId());
                }
            }

            return new RedirectResponse(System::normalizeUrl($url->getUrl()));
        } else {
            $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
            $managedTopic = new TopicManager($topic_id);
            $managedPoster = new ForumUserManager();
            $reply = [
                'topic_id' => $topic_id,
                'post_id' => $post_id,
                'attach_signature' => $attach_signature,
                'subscribe_topic' => $subscribe_topic,
                'topic' => $managedTopic->toArray(),
                'message' => $message];
            $post = [
                'post_id' => 0,
                'topic_id' => $topic_id,
                'poster' => $managedPoster->toArray(),
                'post_time' => time(),
                'attachSignature' => $attach_signature,
                'post_text' => $message,
                'userAllowedToEdit' => false];
            // Do not show edit link
            $permissions = [];
            list(, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', ['ranktype' => RankEntity::TYPE_POSTCOUNT]);
            $this->view->assign('ranks', $ranks);
            $this->view->assign('post', $post);
            $this->view->assign('reply', $reply);
            $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
            $this->view->assign('preview', $isPreview);
            $this->view->assign('last_visit_unix', $lastVisitUnix);
            $this->view->assign('permissions', $permissions);

            return new Response($this->view->fetch('User/topic/reply.tpl'));
        }
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
    
    /**
     * @Route("/topic/new")
     *
     * Create new topic
     *
     * User interface to create a new topic
     *
     * @return string
     */
    public function newtopicAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('User/topic/new.tpl', new NewTopic()));
    }    

    /**
     * @Route("/topic/delete")
     *
     * Delete topic
     *
     * User interface to delete a post.
     *
     * @return string
     */
    public function deletetopicAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('User/topic/delete.tpl', new DeleteTopic()));
    }

    /**
     * @Route("/topic/move")
     *
     * Move topic
     *
     * User interface to move a topic to another forum.
     *
     * @return string
     */
    public function movetopicAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('User/topic/move.tpl', new MoveTopic()));
    }
    

    /**
     * @Route("/topic/change-status/{topic}/{action}/{post}", requirements={
     *      "topic" = "^[1-9]\d*$",
     *      "action" = "subscribe|unsubscribe|sticky|unsticky|lock|unlock|solve|unsolve|setTitle",
     *      "post" = "^[1-9]\d*$"})
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
//    public function changeTopicStatusAction($topic, $action, $post = null)
//    {
//        $params = array(
//            'action' => $action,
//            'topic' => $topic,
//            'post' => $post);
//        // perm check in API
//        ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);
//
//        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $topic), RouterInterface::ABSOLUTE_URL));
//    }


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
    public function changeTopicStatusAction(Request $request)
    {
        // Check if forum is disabled
        if (!$this->getVar('forum_enabled')) {
            return new UnavailableResponse([], strip_tags($this->getVar('forum_disabled_info')));
        }
        // Get common parameters
        $params = [];
        $params['topic'] = $request->request->get('topic', '');
        $params['post'] = $request->request->get('post', null);
        $params['action'] = $request->request->get('action', '');

        // Check if topic is is set
        if (empty($params['topic'])) {
            return new BadDataResponse([], $this->__('Error! No topic ID in \'Dizkus/Ajax/changeTopicStatus()\'.'));
        }
        // Check if action is legal
        $allowedActions = ['lock', 'unlock', 'sticky', 'unsticky', 'subscribe', 'unsubscribe', 'solve', 'unsolve', 'setTitle'];
        if (empty($params['action']) || !in_array($params['action'], $allowedActions)) {
            return new BadDataResponse([], $this->__f('Error! No mode or illegal mode parameter (%s) in \'Dizkus/Ajax/changeTopicStatus()\'.', DataUtil::formatForDisplay($params['action'])));
        }
        // Get title parameter if action == setTitle
        if ($params['action'] == 'setTitle') {
            $params['title'] = trim($request->request->get('title', ''));
            if (empty($params['title'])) {
                return new BadDataResponse([], $this->__('Error! The post has no subject line.'));
            }
        }
        // perm check in API
        ModUtil::apiFunc($this->name, 'Topic', 'changeStatus', $params);

        return new PlainResponse('successful');
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
        // Permission check
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new AccessDeniedException();
        }
        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
        }
        // get the input
        $params = [];
        $params['selorder'] = $request->get('selorder', 1);
        $params['nohours'] = (int)$request->request->get('nohours', 24);
        $params['unanswered'] = (int)$request->query->get('unanswered', 0);
        $params['amount'] = (int)$request->query->get('amount', null);
        $params['last_visit_unix'] = (int)$request->query->get('last_visit_unix', time());
        $this->view->assign($params);
        list($topics, $text, $pager) = ModUtil::apiFunc($this->name, 'post', 'getLatest', $params);
        $this->view->assign('topics', $topics);
        $this->view->assign('text', $text);
        $this->view->assign('pager', $pager);
        $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
        $this->view->assign('last_visit_unix', $lastVisitUnix);

        return new Response($this->view->fetch('User/topic/latest.tpl'));
    }

    /**
     * @Route("/topic/split")
     *
     * Split topic
     *
     * @return string
     */
    public function splittopicAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('User/topic/split.tpl', new SplitTopic()));
    }    
 
    /**
     * @Route("/topic/mail")
     *
     * User interface to email a topic to a arbitrary email-address
     *
     * @return string
     */
    public function emailtopicAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('User/topic/email.tpl', new EmailTopic()));
    }   
    
}