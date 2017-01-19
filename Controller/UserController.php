<?php

/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Controller;

use UserUtil;
use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface; // used in annotations - do not remove
use Symfony\Component\Security\Core\Exception\AccessDeniedException; // used in annotations - do not remove
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\DizkusModule\Entity\ForumUserEntity;
use Zikula\DizkusModule\Form\Type\UserPreferencesType;
use Zikula\DizkusModule\Manager\ForumUserManager;

class UserController extends AbstractController
{
    /**
     * @Route("/prefs")
     *
     * prefs
     *
     * Interface for a user to manage general user preferences.
     *
     * @return string
     */
    public function prefsAction(Request $request)
    {
        $uid = ($request->getSession()->get('uid') > 1) ? $request->getSession()->get('uid') : 1;
        $loggedIn = $uid > 1 ? true : false;
        if (!$loggedIn) {
            throw new AccessDeniedException();
        }

        $this->_forumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager(); //new ForumUserManager($uid);
        $form = $this->createForm(new UserPreferencesType(), $this->_forumUser->toArray(), ['favorites_enabled' => $this->getVar('favorites_enabled')]);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                $this->_forumUser->setPostOrder($data['postOrder']);
                if ($this->getVar('favorites_enabled')) {
                    $this->_forumUser->displayFavoriteForumsOnly((bool) $data['displayOnlyFavorites']);
                }
                $this->_forumUser->setAutosubscribe($data['autosubscribe']);

                $this->addFlash('status', $this->__('Done! Updated configuration.'));
            }

            return $this->redirect($this->generateUrl('zikuladizkusmodule_user_prefs'));
        }

        return $this->render('@ZikulaDizkusModule/User/preferences.html.twig', [
                    'form'     => $form->createView(),
                    'settings' => $this->getVars(),
        ]);
    }

    /**
     * @Route("/prefs/forum-subscriptions")
     *
     * Interface for a user to manage topic subscriptions
     *
     * @return string
     */
    public function manageForumSubscriptionsAction(Request $request)
    {
        $loggedIn = $this->get('zikula_users_module.current_user')->isLoggedIn();
        if (!$loggedIn) {
            $path = [
                'returnpage'  => $this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
                $subRequest,
                HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([]) || !$loggedIn) {
            throw new AccessDeniedException();
        }
        // @todo use service !
        $managedForumUser = new ForumUserManager($request->getSession()->get('uid'));

//        if (!$view->isValid()) {
//            return false;
//        }
//        $data = $view->getValues();
//        if (count($data['forumIds']) > 0) {
//            foreach ($data['forumIds'] as $forumId => $selected) {
//                if ($selected) {
//                    ModUtil::apiFunc($this->name, 'Forum', 'unsubscribe', array('forum' => $forumId));
//                }
//            }
//        }
//
//        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', array(), RouterInterface::ABSOLUTE_URL);
//        return $view->redirect($url);

        return $this->render('@ZikulaDizkusModule/User/manageForumSubscriptions.html.twig', [
                    'subscriptions' => $managedForumUser->get()->getForumSubscriptions(),
                    'settings'      => $this->getVars(),
        ]);
    }

    /**
     * @Route("/prefs/topic-subscriptions")
     *
     * Interface for a user to manage topic subscriptions
     *
     * @return string
     */
    public function manageTopicSubscriptionsAction(Request $request)
    {
        $loggedIn = $this->get('zikula_users_module.current_user')->isLoggedIn();
        if (!$loggedIn) {
            $path = [
                'returnpage'  => $this->get('router')->generate('zikuladizkusmodule_user_managetopicsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
                $subRequest,
                HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([]) || !$loggedIn) {
            throw new AccessDeniedException();
        }
        // @todo use service
        $managedForumUser = new ForumUserManager($request->getSession()->get('uid'));

//        $data = $view->getValues();
//
//        if (count($data['topicIds']) > 0) {
//            foreach ($data['topicIds'] as $topicId => $selected) {
//                if ($selected) {
//                    ModUtil::apiFunc($this->name, 'Topic', 'unsubscribe', array('topic' => $topicId));
//                }
//            }
//        }
//
//        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_managetopicsubscriptions', array(), RouterInterface::ABSOLUTE_URL);
//        return $view->redirect($url);

        return $this->render('@ZikulaDizkusModule/User/manageTopicSubscriptions.html.twig', [
                    'subscriptions' => $managedForumUser->get()->getTopicSubscriptions(),
                    'settings'      => $this->getVars(),
        ]);
    }

    /**
     * @Route("/prefs/view-all-forums")
     *
     * Show all forums in index view instead of only favorite forums
     *
     * @return RedirectResponse
     */
    public function showAllForumsAction()
    {
        return $this->changeViewSetting('all');
    }

    /**
     * @Route("/prefs/view-favs")
     *
     * Show only favorite forums in index view instead of all forums
     *
     * @return RedirectResponse
     */
    public function showFavoritesAction()
    {
        return $this->changeViewSetting('favorites');
    }

    /**
     * Show only favorite forums in index view instead of all forums.
     *
     * @param string $setting
     *
     * @throws AccessDeniedException if user not logged in
     *
     * @return RedirectResponse
     */
    private function changeViewSetting(Request $request, $setting)
    {
        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException();
        }
        $uid = $request->getSession()->get('uid');
        $forumUser = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumUserEntity', $uid);
        if (!$forumUser) {
            $forumUser = new ForumUserEntity($uid);
        }
        $method = $setting == 'favorites' ? 'showFavoritesOnly' : 'showAllForums';
        $forumUser->{$method}();
        $this->entityManager->persist($forumUser);
        $this->entityManager->flush();

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/prefs/sig")
     *
     * Interface for a user to manage signature
     *
     * @return string
     */
    public function signaturemanagementAction(Request $request)
    {
        $loggedIn = $this->get('zikula_users_module.current_user')->isLoggedIn();
        if (!$loggedIn) {
            $path = [
                'returnpage'  => $this->get('router')->generate('zikuladizkusmodule_user_signaturemanagement', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
                $subRequest,
                HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        // Security check
        if (!$this->get('zikula_dizkus_module.security')->canWrite([]) || !$this->getVar('signaturemanagement')) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder(['signature' => UserUtil::getVar('signature')])
                ->add('signature', 'textarea', [
                    'required' => false,
                ])
                ->add('cancel', 'submit', [
                    'label' => 'Restore defaults',
                ])
                ->add('save', 'submit', [
                    'label' => 'Save',
                ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();
                UserUtil::setVar('signature', $data['signature']);
                $this->addFlash('status', $this->__('Done! Signature has been updated.'));
            }

            return $this->redirect($this->generateUrl('zikuladizkusmodule_user_signaturemanagement'));
        }

        return $this->render('@ZikulaDizkusModule/User/manageSignature.html.twig', [
                    'form'     => $form->createView(),
                    'settings' => $this->getVars(),
        ]);
    }

    /**
     * @Route("/mine/{action}/{start}", requirements={"action" = "posts|topics", "start" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Display my posts or topics
     *
     * @param string $action = 'posts'|'topics'
     * @param int    $start  pager offset
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function mineAction($action = 'posts', $start = 0)
    {
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }
        if (ModUtil::apiFunc($this->name, 'user', 'useragentIsBot') === true) {
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
        }

        list($topics, $pager) = ModUtil::apiFunc($this->name, 'post', 'search', ['action' => $action, 'offset' => $start]);
        $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');
        $this->view->assign('topics', $topics)
            ->assign('pager', $pager)
            ->assign('action', $action)
            ->assign('last_visit_unix', $lastVisitUnix);

        return new Response($this->view->fetch('User/post/mine.tpl'));
    }

    /**
     * @Route("/getusers", options={"expose"=true})
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
        $users = $this->get('zikula_dizkus_module.forum_user_manager')->getUsersByFragments(['fragments' => [$fragment]]);
        $reply = [];
        $reply['query'] = $fragment;
        $reply['suggestions'] = [];
        /** @var $user \Zikula\UsersModule\Entity\UserEntity */
        foreach ($users as $user) {
            $reply['suggestions'][] = [
                'value' => htmlentities(stripslashes($user->getUname())),
                'data'  => $user->getUid(), ];
        }

        return new PlainResponse(json_encode($reply));
    }

    /**
     * @Route("/user/ip/{post}", requirements={"post" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * View the posters IP information
     *
     * @param int $post
     *
     * @throws AccessDeniedException     on failed perm check
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return Response
     */
    public function viewIpDataAction($post)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
            throw new AccessDeniedException();
        }
        $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post); //new PostManager();
        $pip = $managedPost->get()->getPoster_ip();
        $this->view->assign('viewip', ModUtil::apiFunc($this->name, 'user', 'get_viewip_data', ['pip' => $pip]))
            ->assign('topicId', $managedPost->getTopicId());

        return new Response($this->view->fetch('User/viewip.tpl'));
    }
}
