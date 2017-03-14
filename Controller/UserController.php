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
use Zikula\DizkusModule\Form\Type\UserPreferencesType;

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

        $this->_forumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
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
            'form' => $form->createView(),
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
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([]) || !$loggedIn) {
            throw new AccessDeniedException();
        }

        $managedForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();

        if ($request->query->has('unsubscribe')) {
            $unsubscribe = $request->query->get('unsubscribe');
            if ($unsubscribe == 'all') {
                $managedForumUser->get()->clearForumSubscriptions();
            } elseif (is_numeric($unsubscribe)) {
                $managedForumUser->unsubscribeFromForum($unsubscribe);
            }

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL));
        }

        return $this->render('@ZikulaDizkusModule/User/manageForumSubscriptions.html.twig', [
            'subscriptions' => $managedForumUser->get()->getForumSubscriptions(),
            'settings' => $this->getVars(),
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
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_managetopicsubscriptions', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$this->get('zikula_dizkus_module.security')->canRead([]) || !$loggedIn) {
            throw new AccessDeniedException();
        }

        $managedForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();

        if ($request->query->has('unsubscribe')) {
            $unsubscribe = $request->query->get('unsubscribe');
            if ($unsubscribe == 'all') {
                $managedForumUser->get()->clearTopicSubscriptions();
            } elseif (is_numeric($unsubscribe)) {
                $managedForumUser->unsubscribeFromTopic($unsubscribe);
            }

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_manageforumsubscriptions', [], RouterInterface::ABSOLUTE_URL));
        }

        return $this->render('@ZikulaDizkusModule/User/manageTopicSubscriptions.html.twig', [
            'subscriptions' => $managedForumUser->get()->getTopicSubscriptions(),
            'settings' => $this->getVars(),
        ]);
    }

    /**
     * @Route("/prefs/view-{setting}", requirements={"setting"="all-forums|favorites"})
     * Show only favorite forums in index view instead of all forums.
     *
     * @param string $setting
     *
     * @throws AccessDeniedException if user not logged in
     *
     * @return RedirectResponse
     */
    public function changeViewAction(Request $request, $setting)
    {
        if (!$this->get('zikula_users_module.current_user')->isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $forumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager()->get();
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
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_user_signaturemanagement', [], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        // Security check
        if (!$this->get('zikula_dizkus_module.security')->canWrite([]) || !$this->getVar('signaturemanagement')) {
            throw new AccessDeniedException();
        }

        $managedForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        $form = $this->createFormBuilder(['signature' => $managedForumUser->getSignature()])
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
                $managedForumUser->setSignature($data['signature']);
                $this->addFlash('status', $this->__('Done! Signature has been updated.'));
            }

            return $this->redirect($this->generateUrl('zikuladizkusmodule_user_signaturemanagement'));
        }

        return $this->render('@ZikulaDizkusModule/User/manageSignature.html.twig', [
            'form' => $form->createView(),
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
        // Permission check - should't this be write?
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }
        // @todo check if user agent is bot

        list($topics, $pager) = $this->get('zikula_dizkus_module.post_manager')->search($action, $start);

        return $this->render('@ZikulaDizkusModule/User/myposts.html.twig', [
            'settings' => $this->getVars(),
            'action' => $action,
            'topics' => $topics,
            'pager' => $pager,
            'last_visit_unix' => $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit()
        ]);
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
                'data' => $user->getUid(), ];
        }

        return new PlainResponse(json_encode($reply));
    }
}
