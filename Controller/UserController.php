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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface; // used in annotations - do not remove
use Symfony\Component\Security\Core\Exception\AccessDeniedException; // used in annotations - do not remove
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Entity\ForumUserEntity;
use Zikula\DizkusModule\Entity\TopicEntity;
use Zikula\DizkusModule\Controller\AbstractBaseController as AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\DizkusModule\Form\Type\UserPreferencesType;

class UserController extends AbstractController
{
    /**
     * @Route("/users", options={"expose"=true})
     * @Method("GET")
     *
     * Performs a user search based on the user name fragment entered so far.
     *
     * @param Request $request
     *                         fragment A partial user name entered by the user
     *
     * @throws AccessDeniedException
     *
     * @return string plainResponse with json_encoded object of users matching the criteria
     */
    public function getUsersAction(Request $request)
    {
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }
        $fragment = $request->query->get('fragment', null);
        $users = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumUserEntity')->getUsersByFragments(['fragments' => [$fragment]]);

        $reply = [];
        $reply['query'] = $fragment;
        $reply['suggestions'] = [];
        /** @var $user \Zikula\UsersModule\Entity\UserEntity */
        foreach ($users as $user) {
            $reply['suggestions'][] = [
                'value' => htmlentities(stripslashes($user->getUname())),
                'data' => $user->getUid(), ];
        }

        return new PlainResponse(json_encode($reply));
    }

    /**
     * @Route("/user/profile/{user}", defaults={"user"=null})
     *
     * prefs
     *
     * Interface for a user to manage general user preferences.
     *
     * @return string
     */
    public function profileAction(Request $request, ForumUserEntity $user = null)
    {
        $currentForumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if ($user) {
            $managedForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager($user->getUserId());
        } else {
            $managedForumUser = $currentForumUserManager;
        }
        //Anons are logged in but do not have prefs access
        if ((!$currentForumUserManager->isLoggedIn() || $currentForumUserManager->isAnonymous()) && is_null($user)) {
            throw new AccessDeniedException($this->__('Anonymos users do not have access to this part. Please log in.'));
        }

        return $this->render('@ZikulaDizkusModule/User/profile.html.twig', [
            'currentForumUser' => $currentForumUserManager,
            'managedForumUser' => $managedForumUser,
            'settings' => $this->getVars(),
        ]);
    }

    /**
     * @Route("/user/preferences/{user}", defaults={"user"=null})
     *
     * prefs
     *
     * Interface for a user to manage general user preferences.
     *
     * @return string
     */
    public function prefsAction(Request $request, ForumUserEntity $user = null)
    {
        $currentForumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if ($user) {
            $managedForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager($user->getUserId());
        } else {
            $managedForumUser = $currentForumUserManager;
        }

        if (!$currentForumUserManager->isLoggedIn()
            || $currentForumUserManager->isAnonymous()
        ) {
            throw new AccessDeniedException($this->__('Anonymos users do not have access to this page. Please log in.'));
        } elseif (!$currentForumUserManager->allowedToEdit($managedForumUser)) {
            throw new AccessDeniedException($this->__('Sorry, you have no permissions to access this page.'));
        }

        $form = $this->createForm(new UserPreferencesType(), $managedForumUser->toArray(), ['settings' => $this->getVars()]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                $managedForumUser
                    ->setPostOrder($data['postOrder']);

                if ($this->getVar('favorites_enabled')) {
                    $managedForumUser->setForumViewSettings((bool) $data['displayOnlyFavorites']);
                }

                if ($this->getVar('topic_subscriptions_enabled')) {
                    $managedForumUser->setAutosubscribe($data['autosubscribe']);
                }

                $managedForumUser->store();

                $this->addFlash('status', $this->__('Done! Updated configuration.'));
            }

            return $this->redirect($this->generateUrl('zikuladizkusmodule_user_prefs'));
        }

        return $this->render('@ZikulaDizkusModule/User/preferences.html.twig', [
            'form' => $form->createView(),
            'currentForumUser' => $currentForumUserManager,
            'managedForumUser' => $managedForumUser,
            'settings' => $this->getVars(),
        ]);
    }

    /**
     * @Route("/user/forum-subscriptions")
     *
     * Interface for a user to manage topic subscriptions
     *
     * @return string
     */
    public function manageForumSubscriptionsAction(Request $request)
    {
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        if ($request->query->has('unsubscribe')) {
            $unsubscribe = $request->query->get('unsubscribe');
            if ('all' == $unsubscribe) {
                $forumUserManager->get()->clearForumSubscriptions();
            } elseif (is_numeric($unsubscribe)) {
                $forumUserManager->unsubscribeFromForum($unsubscribe);
            }
            $forumUserManager->store();

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL));
        }

        return $this->render('@ZikulaDizkusModule/User/manageForumSubscriptions.html.twig', [
            'currentForumUser' => $forumUserManager,
            'settings' => $this->getVars(),
        ]);
    }

    /**
     * @Route("/user/forum-subscriptions/add/{forum}", requirements={"forum" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * Interface for a user to add forum subscription
     *
     * @return string
     */
    public function addForumSubscriptionAction(Request $request, ForumEntity $forum)
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

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        // forum subscriptions on off
        if ($this->getVar('forum_subscriptions_enabled')) {
            throw new AccessDeniedException($this->__('Error! Forum subscriptions have been disabled.'));
        }

        $managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($forum);
        if (!$managedForum->exists()) {
            throw new NotFoundHttpException($this->__('Error! Forum not found in \'Dizkus/UserController/addForumSubscriptionAction()\'.'));
        }

        if ($forumUserManager->subscribeForum($forum)->store()) {
            $status = $this->__('Forum was subscribed.');
        } else {
            $status = $this->__('Something went wrong.');
        }

        if ('json' == $format) {
        } elseif ('ajax.html' == $format) {
        } else {
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_viewforum', ['forum' => $managedForum->getId()], RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/user/forum-subscriptions/remove/{forum}", requirements={"forum" = "^[1-9]\d*$"}, options={"expose"=true})
     * @ Method("POST")
     * Interface for a user to add forum subscription
     *
     * @return string
     */
    public function removeForumSubscriptionAction(Request $request, ForumEntity $forum)
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

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        // forum subscriptions on/off add ! @todo
        if ($this->getVar('forum_subscriptions_enabled')) {
            throw new AccessDeniedException($this->__('Error! Subscriptions have been disabled.'));
            //return new BadDataResponse([], $this->__('Error! Favourites have been disabled.'));
        }

        $managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($forum);
        if (!$managedForum->exists()) {
            throw new NotFoundHttpException($this->__('Error! Forum not found in \'Dizkus/UserController/removeForumSubscriptionAction()\'.'));
        }

        if ($forumUserManager->unsubscribeForum($forum)->store()) {
            $status = $this->__('Forum was unsubscribed.');
        } else {
            $status = $this->__('Something went wrong.');
        }

        if ('json' == $format) {
        } elseif ('ajax.html' == $format) {
        } else {
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_viewforum', ['forum' => $managedForum->getId()], RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/user/topic-subscriptions")
     *
     * Interface for a user to manage topic subscriptions
     *
     * @return string
     */
    public function manageTopicSubscriptionsAction(Request $request)
    {
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_managetopicsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        if ($request->query->has('unsubscribe')) {
            $unsubscribe = $request->query->get('unsubscribe');
            if ('all' == $unsubscribe) {
                $forumUserManager->get()->clearTopicSubscriptions();
            } elseif (is_numeric($unsubscribe)) {
                $forumUserManager->unsubscribeFromTopic($unsubscribe);
            }
            $forumUserManager->store();

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL));
        }

        return $this->render('@ZikulaDizkusModule/User/manageTopicSubscriptions.html.twig', [
            'currentForumUser' => $forumUserManager,
            'settings' => $this->getVars(),
        ]);
    }

    /**
     * @Route("/user/topic-subscriptions/add/{topic}", requirements={"topic" = "^[1-9]\d*$"}, options={"expose"=true})
     * @ Method("POST")
     * Interface for a user to add topic subscription
     *
     * @return string
     */
    public function addTopicSubscriptionAction(Request $request, TopicEntity $topic)
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

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        // topic subscriptions on off
        if (!$this->getVar('topic_subscriptions_enabled')) {
            throw new AccessDeniedException($this->__('Error! Topic subscriptions have been disabled.'));
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager(null, $topic);
        if (!$managedTopic->exists()) {
            throw new NotFoundHttpException($this->__('Error! Topic not found in \'Dizkus/UserController/addTopicSubscriptionAction()\'.'));
        }

        if ($forumUserManager->subscribeTopic($topic)->store()) {
            $status = $this->__('Topic was subscribed.');
        } else {
            $status = $this->__('Something went wrong.');
        }

        if ('json' == $format) {
        } elseif ('ajax.html' == $format) {
        } else {
            $this->addFlash('status', $status);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/user/topic-subscriptions/remove/{topic}", requirements={"topic" = "^[1-9]\d*$"}, options={"expose"=true})
     * @ Method("POST")
     * Interface for a user to add forum subscription
     *
     * @return string
     */
    public function removeTopicSubscriptionAction(Request $request, TopicEntity $topic)
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

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        if ($this->getVar('forum_subscriptions_enabled')) {
            throw new AccessDeniedException($this->__('Error! Subscriptions have been disabled.'));
        }

        $managedTopic = $this->get('zikula_dizkus_module.topic_manager')->getManager(null, $topic);
        if (!$managedTopic->exists()) {
            throw new NotFoundHttpException($this->__('Error! Topic not found in \'Dizkus/UserController/removeTopicSubscriptionAction()\'.'));
        }

        if ($forumUserManager->unsubscribeFromTopic($topic)->store()) {
            $status = $this->__('Topic was unsubscribed.');
        } else {
            $status = $this->__('Something went wrong.');
        }

        if ('json' == $format) {
        } elseif ('ajax.html' == $format) {
        } else {
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_topic_viewtopic', ['topic' => $managedTopic->getId()], RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/user/favorite-forums")
     *
     * Interface for a user to manage topic subscriptions
     *
     * @return string
     */
    public function manageFavoriteForumsAction(Request $request)
    {
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_managetopicsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        if ($request->query->has('unsubscribe')) {
            $unsubscribe = $request->query->get('unsubscribe');
            if ('all' == $unsubscribe) {
                $forumUserManager->get()->clearForumFavorites();
            } elseif (is_numeric($unsubscribe)) {
                $forumUserManager->removeFavoriteForum($unsubscribe);
            }
            $forumUserManager->store();

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_managefavotites', [], RouterInterface::ABSOLUTE_URL));
        }

        return $this->render('@ZikulaDizkusModule/User/manageFavorites.html.twig', [
            'currentForumUser' => $forumUserManager,
            'settings' => $this->getVars(),
        ]);
    }

    /**
     * @Route("/user/favorite-forums/add/{forum}", requirements={"forum" = "^[1-9]\d*$"}, options={"expose"=true})
     * @ Method("POST")
     * Interface for a user to add forum subscription
     *
     * @return string
     */
    public function addFavoriteForumAction(Request $request, ForumEntity $forum)
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

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        // forum subscriptions on off
        if ($this->getVar('forum_subscriptions_enabled')) {
            throw new AccessDeniedException($this->__('Error! Subscriptions have been disabled.'));
            //return new BadDataResponse([], $this->__('Error! Favourites have been disabled.'));
        }

        if (empty($forum)) {
            //return new BadDataResponse([], $this->__('Error! No forum ID in \'Dizkus/Ajax/modifyForum()\'.'));
            throw new NotFoundHttpException($this->__('Error! Forum not found in \'Dizkus/UserController/addFavoriteForumAction()\'.'));
        }

        if ($forumUserManager->addFavoriteForum($forum)->store()) {
            return new PlainResponse('successful');
        } else {
            return new PlainResponse('something went wrong');
        }
    }

    /**
     * @Route("/user/favorite-forums/remove/{forum}", requirements={"forum" = "^[1-9]\d*$"}, options={"expose"=true})
     * @ Method("POST")
     * Interface for a user to add forum subscription
     *
     * @return string
     */
    public function removeFavoriteForumAction(Request $request, ForumEntity $forum)
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

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        // forum subscriptions on/off add ! @todo
        if ($this->getVar('forum_subscriptions_enabled')) {
            throw new AccessDeniedException($this->__('Error! Subscriptions have been disabled.'));
            //return new BadDataResponse([], $this->__('Error! Favourites have been disabled.'));
        }

        if (empty($forum)) {
            //return new BadDataResponse([], $this->__('Error! No forum ID in \'Dizkus/Ajax/modifyForum()\'.'));
            throw new NotFoundHttpException($this->__('Error! No forum ID in \'Dizkus/UserController/removeForumSubscriptionAction()\'.'));
        }

        if ($forumUserManager->removeFavoriteForum($forum)->store()) {
            return new PlainResponse('successful');
        } else {
            return new PlainResponse('something went wrong');
        }
    }

    /**
     * @Route("/user/view-{setting}", requirements={"setting"="all-forums|favorites"})
     * Show only favorite forums in index view instead of all forums.
     *
     * @param string $setting
     *
     * @throws AccessDeniedException if user not logged in
     *
     * @return RedirectResponse
     */
    public function changeViewAction($setting)
    {
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //Anons are logged in but do not have prefs access
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            throw new AccessDeniedException();
        }

        $forumUserManager->setForumViewSettings('favorities' == $setting ? true : false)->store();

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/user/sig")
     *
     * Interface for a user to manage signature
     *
     * @return string
     */
    public function signaturemanagementAction(Request $request)
    {
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_managetopicsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        //is feature enabled check
        if (!$this->getVar('signaturemanagement')) {
            throw new AccessDeniedException(); // @todo change to different exception
        }

        // Security check
        if (!$this->get('zikula_dizkus_module.security')->canWrite([])) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder(['signature' => $forumUserManager->getSignature()])
        ->add('signature', 'textarea', [
            'required' => false,
        ])
        ->add('save', 'submit', [
            'label' => 'Save',
        ])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                $forumUserManager->setSignature($data['signature']); //this stores itself automaticaly
                $this->addFlash('status', $this->__('Done! Signature has been updated.'));
            }

            return $this->redirect($this->generateUrl('zikuladizkusmodule_user_signaturemanagement'));
        }

        return $this->render('@ZikulaDizkusModule/User/manageSignature.html.twig', [
            'currentForumUser' => $forumUserManager,
            'form' => $form->createView(),
            'settings' => $this->getVars(),
        ]);
    }

    /**
     * @Route("/mine/posts/{start}", requirements={"start" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Display my posts or topics
     *
     * @param int    $start  pager offset
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function minePostsAction(Request $request, $start = 0)
    {
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_managetopicsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        list($posts, $pager) = $forumUserManager->getPosts($start);

        return $this->render('@ZikulaDizkusModule/User/my.posts.html.twig', [
            'currentForumUser' => $forumUserManager,
            'settings' => $this->getVars(),
            'posts' => $posts,
            'pager' => $pager
        ]);
    }

    /**
     * @Route("/mine/topics/{start}", requirements={"start" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Display my topics
     *
     * @param int    $start  pager offset
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function mineTopicsAction(Request $request, $start = 0)
    {
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();

        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_managetopicsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }

        list($topics, $pager) = $forumUserManager->getTopics($start);

        return $this->render('@ZikulaDizkusModule/User/my.topics.html.twig', [
            'currentForumUser' => $forumUserManager,
            'settings' => $this->getVars(),
            'topics' => $topics,
            'pager' => $pager
        ]);
    }
}
