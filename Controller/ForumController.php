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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\RouteUrl;
use Zikula\DizkusModule\Entity\ForumEntity;
//use Zikula\DizkusModule\Form\Type\ModerateType;
use Zikula\ThemeModule\Engine\Annotation\Theme;

class ForumController extends AbstractController
{
    /**
     * @Route("")
     *
     * Show all forums a user may see
     *
     * @param Request $request
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request)
    {
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                        'forum_disabled_info' => $this->getVar('forum_disabled_info'),
            ]);
        }
        $indexTo = $this->getVar('indexTo');
        if (!empty($indexTo)) {
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_viewforum', ['forum' => (int) $indexTo], RouterInterface::ABSOLUTE_URL));
        }
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }
        // currentforumuser
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();

        $managedRootForum = $this->get('zikula_dizkus_module.forum_manager')->getManager(1);

//        // get the forums to display
//        $siteFavoritesAllowed = $this->getVar('favorites_enabled');
//        $qb = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->childrenQueryBuilder();
//        if (!$forumUserManager->isAnonymous() && $siteFavoritesAllowed && $forumUserManager->get()->getDisplayOnlyFavorites()) {
//            // display only favorite forums
//            $qb->join('node.favorites', 'fa');
//            $qb->andWhere('fa.forumUser = :uid');
//            $qb->setParameter('uid', $forumUserManager->getId());
//        } else {
//            //display an index of the level 1 forums
//            $qb->andWhere('node.lvl = 1');
//        }
//        $rawForums = $qb->getQuery()->getResult();
//        // filter the forum array by permissions
//        $forums = $this->get('zikula_dizkus_module.security')->filterForumArrayByPermission($rawForums);
//        // check to make sure there are forums to display
//        if (count($forums) < 1) {
//            if ($forumUserManager->get()->getDisplayOnlyFavorites()) {
//                $request->getSession()->getFlashBag()->add('error', $this->__('You have not selected any favorite forums. Please select some and try again.'));
//                $forumUserManager->setForumViewSettings(false);
//                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
//            } else {
//                $request->getSession()->getFlashBag()->add('error', $this->__('This site has not set up any forums or they are all private. Contact the administrator.'));
//            }
//        }

        return $this->render('@ZikulaDizkusModule/Forum/main.html.twig', [
                    'managedRootForum' => $managedRootForum,
                    'currentForumUser' => $forumUserManager,
                    'totalposts'      => $this->get('zikula_dizkus_module.count_helper')->getAllPostsCount(),
                    'settings'        => $this->getVars(),
        ]);
    }

    /**
     * @Route("/forum/{forum}/{start}", requirements={"forum" = "^[1-9]\d*$", "start" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * View forum by id
     * opens a forum and shows the last postings
     *
     * @param Request $request
     * @param int     $forum   the forum id
     * @param int     $start   the posting to start with if on page 1+
     *
     * @throws NotFoundHttpException if forumID <= 0
     * @throws AccessDeniedException if perm check fails
     *
     * @return Response|RedirectResponse
     */
    public function viewforumAction(Request $request, $forum, $start = 1)
    {
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                        'forum_disabled_info' => $this->getVar('forum_disabled_info'),
            ]);
        }
        // currentforumuser
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        // current forum
        $managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($forum); //new ForumManager($forum);
        if (!$managedForum->exists()) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! The forum you selected (ID: %s) was not found. Please try again.', [$forum]));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL));
        }
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead($managedForum->get())) {
            throw new AccessDeniedException();
        }

        $managedForum->getTopics($start);

        return $this->render('@ZikulaDizkusModule/Forum/view.html.twig', [
                    'currentForumUser' => $forumUserManager,
                    'currentForum' => $managedForum,
                     // filter the forum children by permissions
                    'forum'       => $this->get('zikula_dizkus_module.security')->filterForumChildrenByPermission($managedForum->get()),
                    'permissions' => $managedForum->getPermissions(),
                    'settings'    => $this->getVars(),
        ]);
    }

    /**
     * @Route("/forum/tree")
     *
     * @Theme("admin")
     *
     * Show the forum tree
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function treeAction()
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $this->getDoctrine()->getManager()->getConfiguration()->addCustomHydrationMode('tree', 'Gedmo\Tree\Hydrator\ORM\TreeObjectHydrator');
        $repo = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity');
        $tree = $repo->createQueryBuilder('node')->getQuery()
            ->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getResult('tree');

        return $this->render('@ZikulaDizkusModule/Forum/tree.html.twig', [
            'status'       => $repo->verify(),
            'tree'         => $tree,
            'importHelper' => $this->get('zikula_dizkus_module.import_helper')
        ]);
    }

    /**
     * @Route("/forum/tree/recover")
     *
     * @Theme("admin")
     *
     * Show the forum tree
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function treerecoverAction()
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
//        $this->getDoctrine()->getManager()->getConfiguration()->addCustomHydrationMode('tree', 'Gedmo\Tree\Hydrator\ORM\TreeObjectHydrator');
//        $repo = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity');
//        $repo->recover();
//        $this->getDoctrine()->getManager()->flush(); // important: flush recovered nodes

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_tree', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/forum/order/{action}/{forum}", requirements={"action" = "moveUp|moveDown", "forum" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Change forum order
     * Move up or down a forum in the tree
     *
     * @param string $action
     * @param ForumEntity $forum
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException on Perm check failure
     */
    public function changeForumOrderAction($action, ForumEntity $forum)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        /** @var $repo \Gedmo\Tree\Entity\Repository\NestedTreeRepository */
        $repo = $em->getRepository('Zikula\DizkusModule\Entity\ForumEntity');
        if ('moveUp' == $action) {
            $repo->moveUp($forum, true);
        } else {
            $repo->moveDown($forum, true);
        }
        $em->flush();

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_tree', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/forum/{forum}/{action}", requirements={"forum" = "^[1-9]\d*$", "action"="lock|unlock"})
     *
     * Lock forum
     *
     * User interface for forum locking
     *
     * @return string
     */
    public function lockAction(Request $request, $forum, $action)
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

        $managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($forum);
        if (!$managedForum->exists()) {
            throw new NotFoundHttpException($this->__('Error! Forum not found in \'Dizkus/ForumController/lockAction()\'.'));
        }

        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();
        //nicer redirect form of access denied
        if (!$forumUserManager->isLoggedIn() || $forumUserManager->isAnonymous()) {
            $path = [
                'returnpage' => $this->get('router')->generate('zikuladizkusmodule_forum_viewforum', ['forum' => $managedForum->getId()], RouterInterface::ABSOLUTE_URL),
                '_controller' => 'ZikulaUsersModule:User:login', ];

            $subRequest = $request->duplicate([], null, $path);
            $httpKernel = $this->get('http_kernel');
            $response = $httpKernel->handle(
            $subRequest, HttpKernelInterface::SUB_REQUEST
            );

            return $response;
        }

        if (!$forumUserManager->allowedToModerate($managedForum)) {
            throw new AccessDeniedException();
        }

        $managedForum->get()->{$action}();
        $managedForum->store();

        if (!$request->isXmlHttpRequest()) {
            // everything is good no ajax return to to topic view
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_viewforum', ['forum' => $managedForum->getId()], RouterInterface::ABSOLUTE_URL));
        }
    }

    /**
     * @Route("/forum/{forum}/modify", requirements={"forum" = "^[1-9]\d*$"})
     *
     * @Theme("admin")
     *
     * @return Response
     */
    public function modifyForumAction(Request $request, $forum = null)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // disallow editing of root forum
        if (1 == $id) {
            $this->addFlash('error', $this->__("Editing of root forum is disallowed", 403));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_tree', [], RouterInterface::ABSOLUTE_URL));
        }

        $managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($id);

        // count only in this forum or subforums as well?
        $topiccount = $this->get('zikula_dizkus_module.count_helper')->getForumTopicsCount($managedForum->getId());
        $postcount = $this->get('zikula_dizkus_module.count_helper')->getForumPostsCount($managedForum->getId());

        $form = $this->createForm('zikuladizkusmodule_admin_modify_forum', $managedForum->get(), []);

        $form->handleRequest($request);

        $hookvalidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.forum.validate_edit',
                new ValidationHook(
                    new ValidationProviders()
                )
            )->getValidators();

        if ($form->isValid() && !$hookvalidators->hasErrors()) {
            $managedForum->update($form->getData());
            $managedForum->store();
            // notify hooks
            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.forum.process_edit',
                    new ProcessHook($managedForum->getId(),
                        RouteUrl::createFromRoute('zikuladizkusmodule_user_viewforum', ['forum' => $managedForum->getId()])
                    )
                );

            if ($id) {
                $this->addFlash('status', $this->__('Forum successfully updated.'));
            } else {
                $this->addFlash('status', $this->__('Forum successfully created.'));
            }
        }

        return $this->render('@ZikulaDizkusModule/Forum/modifyforum.html.twig', [
            'topiccount' => $topiccount,
            'postcount' => $postcount,
            'forum' => $managedForum->get(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/forum/create")
     *
     * @Theme("admin")
     *
     * @return Response
     */
    public function createForumAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // disallow editing of root forum
        if (1 == $id) {
            $this->addFlash('error', $this->__("Editing of root forum is disallowed", 403));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_tree', [], RouterInterface::ABSOLUTE_URL));
        }

        $managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($id);

        // count only in this forum or subforums as well?
        $topiccount = $this->get('zikula_dizkus_module.count_helper')->getForumTopicsCount($managedForum->getId());
        $postcount = $this->get('zikula_dizkus_module.count_helper')->getForumPostsCount($managedForum->getId());

        $form = $this->createForm('zikuladizkusmodule_admin_modify_forum', $managedForum->get(), []);

        $form->handleRequest($request);

        $hookvalidators = $this->get('hook_dispatcher')
            ->dispatch('dizkus.ui_hooks.forum.validate_edit',
                new ValidationHook(
                    new ValidationProviders()
                )
            )->getValidators();

        if ($form->isValid() && !$hookvalidators->hasErrors()) {
            $managedForum->update($form->getData());
            $managedForum->store();
            // notify hooks
            $this->get('hook_dispatcher')
                ->dispatch('dizkus.ui_hooks.forum.process_edit',
                    new ProcessHook($managedForum->getId(),
                        RouteUrl::createFromRoute('zikuladizkusmodule_forum_viewforum', ['forum' => $managedForum->getId()])
                    )
                );

            if ($id) {
                $this->addFlash('status', $this->__('Forum successfully updated.'));
            } else {
                $this->addFlash('status', $this->__('Forum successfully created.'));
            }
        }

        return $this->render('@ZikulaDizkusModule/Forum/modifyforum.html.twig', [
            'topiccount' => $topiccount,
            'postcount' => $postcount,
            'forum' => $managedForum->get(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/forum/{forum}/moderate", requirements={"forum" = "^[1-9]\d*$"})
     *
     * Moderate forum
     *
     * User interface for moderation of multiple topics.
     *
     * @return string
     */
    public function moderateForumAction(Request $request, $forum)
    {
        // params check
        if (!isset($forum)) {
            throw new \InvalidArgumentException();
        }
        $forumUserManager = $this->get('zikula_dizkus_module.forum_user_manager')->getManager();

        // Get the Forum and Permission-Check
//        $this->_managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($forum);
//
//
//
//        if (!$this->_managedForum->isModerator()) {
//            // both zikula perms and Dizkus perms denied
//            throw new AccessDeniedException();
//        }
//
//        $form = $this->createForm(new ModerateType($this->get('translator'), $this->_managedForum), [], []);
//
//        $form->handleRequest($request);
//
//        if ($form->isValid()) {
//            $data = $form->getData();
//            $action = isset($data['action']) ? $data['action'] : '';
//            $shadow = $data['createshadowtopic'];
//            $moveto = isset($data['moveto']) ? $data['moveto'] : null;
//            $jointo = isset($data['jointo']) ? $data['jointo'] : null;
//            $jointo_select = isset($data['jointotopic']) ? $data['jointotopic'] : null;
//            // get this value by traditional method because checkboxen have values
//            $topic_ids = $request->request->get('topic_id', []);
//
//            if (count($topic_ids) != 0) {
//                switch ($action) {
//                    case 'del':
//                    case 'delete':
//                        foreach ($topic_ids as $topic_id) {
//                            // dump('delete topic'.$topic_id);
//                            $forum_id = $this->get('zikula_dizkus_module.topic_manager')->delete($topic_id);
//                        }
//
//                        break;
//
//                    case 'move':
//                        if (empty($moveto)) {
//                            $request->getSession()->getFlashBag()->add('error', $this->__('Error! You did not select a target forum for the move.'));
//                            // dump('move to forum'.$moveto); //
//                            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_moderateforum', ['forum' => $this->_managedForum->getId()], RouterInterface::ABSOLUTE_URL));
//                        }
//                        foreach ($topic_ids as $topic_id) {
//                            // dump('move topic #'.$topic_id.' to forum #'.$moveto);
//                            $this->get('zikula_dizkus_module.topic_manager')->move($topic_id, $moveto, $shadow);
//                          ModUtil::apiFunc($this->name, 'topic', 'move', ['topic_id' => $topic_id,
//                                'forum_id' => $moveto,
//                                'createshadowtopic' => $shadow]);
//                        }
//
//                        break;
//
//                    case 'lock':
//                    case 'unlock':
//                    case 'solve':
//                    case 'unsolve':
//                    case 'sticky':
//                    case 'unsticky':
//                        foreach ($topic_ids as $topic_id) {
//                            // dump($action.' '.$topic_id); // no post no title
//                            //$this->get('zikula_dizkus_module.topic_manager')->changeStatus($topic_id, $action, $post, $title);
//                            ModUtil::apiFunc($this->name, 'topic', 'changeStatus', [
//                                'topic' => $topic_id,
//                                'action' => $action]);
//                        }
//
//                        break;
//
//                    case 'join':
//                        if (empty($jointo) && empty($jointo_select)) {
//                            $request->getSession()->getFlashBag()->add('error', $this->__('Error! You did not select a target topic to join.'));
//
//                            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_moderateforum', ['forum' => $this->_managedForum->getId()], RouterInterface::ABSOLUTE_URL));
//                        }
//                        // text input overrides select box
//                        if (empty($jointo) && !empty($jointo_select)) {
//                            $jointo = $jointo_select;
//                        }
//                        if (in_array($jointo, $topic_ids)) {
//                            // jointo, the target topic, is part of the topics to join
//                            // we remove this to avoid a loop
//                            $fliparray = array_flip($topic_ids);
//                            unset($fliparray[$jointo]);
//                            $topic_ids = array_flip($fliparray);
//                        }
//                        foreach ($topic_ids as $from_topic_id) {
//                            //dump('join from'.$from_topic_id.' to '.$jointo); // @todo
//                            ModUtil::apiFunc($this->name, 'topic', 'join', ['from_topic_id' => $from_topic_id,
//                                'to_topic_id' => $jointo]);
//                        }
//
//                        break;
//
//                    default:
//                }
//            }
//
//            //return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_moderateforum', ['forum' => $this->_managedForum->getId()], RouterInterface::ABSOLUTE_URL));
//        }

        return $this->render('@ZikulaDizkusModule/Forum/moderate.html.twig', [
              'currentForumUser' => $forumUserManager,
//            'form'            => $form->createView(),
//            'forum'           => $this->_managedForum->get(),
//            'pager'           => $this->_managedForum->getPager(),
            'settings'        => $this->getVars(),
        ]);
    }

    /**
     * @Route("/forum/{forum}/delete", requirements={"forum" = "^[1-9]\d*$"})
     *
     * @Theme("admin")
     *
     * @return Response
     */
    public function deleteForumAction(Request $request, $forum = null)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        if ($forum) {
            $forum = $this->getDoctrine()->getManager()->find('Zikula\DizkusModule\Entity\ForumEntity', $forum);
            if ($forum) {
                //nothing to do here? @todo rearange this if
            } else {
                $this->addFlash('error', $this->__f('Forum with id %s not found', ['%s' => $id]), 403);

                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_tree', [], RouterInterface::ABSOLUTE_URL));
            }
        } else {
            $this->addFlash('error', $this->__('No forum id'), 403);

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_tree', [], RouterInterface::ABSOLUTE_URL));
        }

        $forumRoot = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->findOneBy(['name' => ForumEntity::ROOTNAME]);
        $destinations = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->getChildren($forumRoot);

        // @todo move to form handler
        $form = $this->createFormBuilder([])
        ->add('action', 'choice', [
            'choices' => ['0' => $this->__('Remove them'),
                '1' => $this->__('Move them to a new parent forum')],
            'multiple' => false,
            'expanded' => false,
            'required' => true])
        ->add('destination', 'choice', [
            'choices' => $destinations,
            'choice_value' => function ($destination) {
                //for some reason last element is null @FIXME
                return null === $destination ? null : $destination->getForum_id();
            },
            'choice_label' => function ($destination) use ($forum) {
                $isChild = $destination->getLft() > $forum->getLft() && $destination->getRgt() < $forum->getRgt() ? ' (' . $this->__("is child forum") . ')' : '';
                $current = $destination->getForum_id() === $forum->getForum_id() ? ' (' . $this->__("current") . ')' : '';
                $locked = $destination->isLocked() ? ' (' . $this->__("is locked") . ')' : '';

                return str_repeat("--", $destination->getLvl()) . $destination->getName() . $current . $locked . $isChild;
            },
            'choice_attr' => function ($destination) use ($forum) {
                $isChild = $destination->getLft() > $forum->getLft() && $destination->getRgt() < $forum->getRgt() ? true : false;

                return $destination->getForum_id() === $forum->getForum_id() || $destination->isLocked() || $isChild ? ['disabled' => 'disabled'] : [];
            },
            'choices_as_values' => true,
            'multiple' => false,
            'expanded' => false,
            'required' => true])
        ->add('remove', 'submit')
        ->getForm();

        $form->handleRequest($request);

        // check hooked modules for validation
        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.forum.validate_delete', $hook)->getValidators();

        if ($form->isValid() && !$hookvalidators->hasErrors()) {
            $data = $form->getData();
            if (1 == $data['action']) {
                // get the child forums and move them
                $children = $forum->getChildren();
                foreach ($children as $child) {
                    $child->setParent($data['destination']);
                }
                $forum->removeChildren();

                // get child topics and move them
                $topics = $forum->getTopics();
                foreach ($topics as $topic) {
                    $topic->setForum($data['destination']);
                    $forum->getTopics()->removeElement($topic);
                }
                $this->getDoctrine()->getManager()->flush();
            }
            // remove the forum
            $this->getDoctrine()->getManager()->remove($forum);
            $this->getDoctrine()->getManager()->flush();

            $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.forum.process_delete', new ProcessHook($forum->getForum_id()));

            if (isset($data['destination'])) {
                // sync last post in destination
                $this->get('zikula_dizkus_module.synchronization_helper')->forumLastPost($data['destination']);
            }

            // repair the tree
            $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->recover();
            $this->getDoctrine()->getManager()->clear();

            // resync all forums, topics & posters
            $this->get('zikula_dizkus_module.synchronization_helper')->all();
        }

        return $this->render('@ZikulaDizkusModule/Forum/deleteforum.html.twig', [
            'forum' => $forum,
            'form' => $form->createView(),
        ]);
    }
}
