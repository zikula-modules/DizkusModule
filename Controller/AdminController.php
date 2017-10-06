<?php

/*
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Controller;

use Zikula\Core\Controller\AbstractController;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Form\Type\DizkusSettingsType;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     *
     * the main administration function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_tree', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/prefs")
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function preferencesAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $settingsManager = $this->get('zikula_dizkus_module.settings_manager');
        $form = $this->createForm(new DizkusSettingsType(), $settingsManager->getSettingsForForm(), ['settingsManager' => $settingsManager]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                if (!$settingsManager->setSettings($request->request->get($form->getName()))) {
                    $this->addFlash('error', $this->__('Error! Settings not set! Please try again'));
                } else {
                    $this->addFlash('status', $this->__('Settings set.'));
                    if (!$settingsManager->saveSettings()) {
                        $this->addFlash('error', $this->__('Error! Settings not saved! Please try again'));
                    } else {
                        $this->addFlash('status', $this->__('Settings saved.'));
                    }
                }
            }
            if ($form->get('restore')->isClicked()) {
                if (!$settingsManager->restoreSettings()) {
                    $this->addFlash('error', $this->__('Error! Settings not set! Please try again'));
                } else {
                    $this->addFlash('error', $this->__('Error! Settings not restored! Please try again'));
                }
            }

            return $this->redirect($this->generateUrl('zikuladizkusmodule_admin_preferences'));
        }

        return $this->render('@ZikulaDizkusModule/Admin/settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ranks")
     *
     * ranks
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse
     *
     * @throws AccessDeniedException
     */
    public function ranksAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $submit = $request->request->get('submit', 2);
        $ranktype = (int) $request->query->get('ranktype', RankEntity::TYPE_POSTCOUNT);
        if ($submit == 2) {
            $ranks = $this->get('zikula_dizkus_module.rank_helper')->getAll(['ranktype' => RankEntity::TYPE_POSTCOUNT]);
            $template = 'honoraryranks';
            if ($ranktype == 0) {
                $template = 'ranks';
            }

            return $this->render("@ZikulaDizkusModule/Admin/$template.html.twig", [
                'ranks' => $ranks,
                'ranktype' => $ranktype,
                'rankimages' => $this->get('zikula_dizkus_module.rank_helper')->getAllRankImages(),
                'settings' => $this->getVars()
            ]);
        } else {
            $ranks = $request->request->filter('ranks', '', FILTER_SANITIZE_STRING);
            $this->get('zikula_dizkus_module.rank_helper')->save(['ranks' => $ranks]);
        }

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_ranks', ['ranktype' => $ranktype], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/assignranks")
     *
     * ranks
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function assignranksAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $page = (int) $request->query->get('page', 1);
        $letter = $request->query->get('letter');

        if ($request->getMethod() == 'POST') {
            $letter = $request->request->get('letter');
            $page = (int) $request->request->get('page', 1);

            $setrank = $request->request->get('setrank');

            $this->get('zikula_dizkus_module.rank_helper')->assign(['setrank' => $setrank]);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_assignranks', ['page' => $page, 'letter' => $letter], RouterInterface::ABSOLUTE_URL));
        }

        $letter = (empty($letter) || strlen($letter) != 1) ? '*' : $letter;
        $perpage = 20;
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('u')
        ->from('Zikula\UsersModule\Entity\UserEntity', 'u')
        ->orderBy('u.uname', 'ASC');
        if ($letter != '*') {
            $qb->andWhere('u.uname LIKE :letter')
            ->setParameter('letter', strtolower($letter) . '%');
        }
        $query = $qb->getQuery();
        $query->setFirstResult(($page - 1) * $perpage)
        ->setMaxResults($perpage);

        // Paginator
        $allusers = new Paginator($query);
        $count = $allusers->count();

        // recreate the array of users as ForumUserEntities
        $userArray = [];
        /** @var $user \Zikula\UsersModule\Entity\UserEntity */
        foreach ($allusers as $user) {
            $managedForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager($user->getUid(), false);
            $forumUser = $managedForumUser->get();
            if (isset($forumUser)) {
                $userArray[$user->getUid()] = $forumUser;
            } else {
                $count--;
            }
        }

        $ranks = $this->get('zikula_dizkus_module.rank_helper')->getAll(['ranktype' => RankEntity::TYPE_HONORARY]);

        return $this->render('@ZikulaDizkusModule/Admin/assignranks.html.twig', [
            'ranks' => $ranks,
            'rankimages' => $this->get('zikula_dizkus_module.rank_helper')->getAllRankImages(),
            'allusers' => $userArray,
            'letter' => $letter,
            'page' => $page,
            'perpage' => $perpage,
            'usercount' => $count
        ]);
    }

    /**
     * @Route("/subscriptions/{uid}", options={"expose"=true})
     *
     * @return Response
     */
    public function manageSubscriptionsAction(Request $request, $uid = null)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if ($request->isMethod('POST') && $request->request->get('username', false)) {
            $managedForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManagedByUserName($request->request->get('username'), false);
        } else {
            $managedForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager($uid, false);
        }

        if ($request->isMethod('POST')) {
            if ($managedForumUser->exists()) {
                $forumSub = $request->request->get('forumsubscriptions', []);
                foreach ($forumSub as $id => $selected) {
                    if ($selected) {
                        $managedForumUser->unsubscribeForum($id);
                    }
                }

                $topicSub = $request->request->get('topicsubscriptions', []);
                foreach ($topicSub as $id => $selected) {
                    if ($selected) {
                        $managedForumUser->unsubscribeFromTopic($id);
                    }
                }
            }
        }

        return $this->render('@ZikulaDizkusModule/Admin/managesubscriptions.html.twig', [
            'managedForumUser' => $managedForumUser
        ]);
    }
}
