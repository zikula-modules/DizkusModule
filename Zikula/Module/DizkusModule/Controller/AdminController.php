<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Controller;

use ModUtil;
use System;
use SecurityUtil;
use FormUtil;
use Zikula\Module\DizkusModule\Entity\RankEntity;
use Zikula\Module\DizkusModule\Form\Handler\Admin\Prefs;
use Zikula\Module\DizkusModule\Form\Handler\Admin\AssignRanks;
use Zikula\Module\DizkusModule\Form\Handler\Admin\ModifyForum;
use Zikula\Module\DizkusModule\Form\Handler\Admin\DeleteForum;
use Zikula\Module\DizkusModule\Form\Handler\Admin\ManageSubscriptions;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class AdminController extends \Zikula_AbstractController
{

    public function postInitialize()
    {
        $this->view->setCaching(false);
    }

    /**
     * @Route("/main")
     *
     * The main administration function.
     *
     * @return RedirectResponse
     * @deprecated
     */
    public function mainAction()
    {
        $url = ModUtil::url($this->name, 'admin', 'tree');
        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("")
     *
     * the main administration function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        $url = ModUtil::url($this->name, 'admin', 'tree');
        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/order")
     * @Method("GET")
     *
     * Change forum order
     * Move up or down a forum in the tree
     * 
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function changeForumOrderAction(Request $request)
    {
        $action = $request->query->get('action', 'moveUp');
        $forumId = $request->query->get('forum', null);
        if (empty($forumId)) {
            throw new \InvalidArgumentException();
        }
        $repo = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity');
        $forum = $repo->find($forumId);
        if ($action == 'moveUp') {
            $repo->moveUp($forum, true);
        } else {
            $repo->moveDown($forum, true);
        }
        $this->entityManager->flush();
        $url = ModUtil::url($this->name, 'admin', 'tree');

        return new RedirectResponse(System::normalizeUrl($url));
    }

    /**
     * @Route("/prefs")
     *
     * preferences
     *
     */
    public function preferencesAction()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        // Create output object
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('Admin/preferences.tpl', new Prefs());
    }

    /**
     * @Route("/sync")
     * @Method("POST")
     *
     * syncforums
     * 
     * @param Request $request
     */
    public function syncforumsAction(Request $request)
    {
        $showstatus = !$request->request->get('silent', 0);
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $succesful = ModUtil::apiFunc($this->name, 'Sync', 'forums');
        if ($showstatus && $succesful) {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Synchronized forum index.'));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error synchronizing forum index'));
        }
        $succesful = ModUtil::apiFunc($this->name, 'Sync', 'topics');
        if ($showstatus && $succesful) {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Synchronized topics.'));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error synchronizing topics.'));
        }
        $succesful = ModUtil::apiFunc($this->name, 'Sync', 'posters');
        if ($showstatus && $succesful) {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Done! Synchronized posts counter.'));
        } else {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error synchronizing posts counter.'));
        }

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'tree')));
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
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $submit = $request->request->get('submit', 2);
        $ranktype = (int) $request->query->get('ranktype', RankEntity::TYPE_POSTCOUNT);
        if ($submit == 2) {
            list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => $ranktype));
            $this->view->assign('ranks', $ranks);
            $this->view->assign('ranktype', $ranktype);
            $this->view->assign('rankimages', $rankimages);
            if ($ranktype == 0) {
                return new Response($this->view->fetch('Admin/ranks.tpl'));
            } else {
                return new Response($this->view->fetch('Admin/honoraryranks.tpl'));
            }
        } else {
            $ranks = $request->request->filter('ranks', '', FILTER_SANITIZE_STRING);
            ModUtil::apiFunc($this->name, 'Rank', 'save', array('ranks' => $ranks));
        }

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'ranks', array('ranktype' => $ranktype))));
    }

    /**
     * @Route("/ranks-assign")
     *
     * ranks
     */
    public function assignranksAction()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('Admin/assignranks.tpl', new AssignRanks());
    }

    /**
     * @Route("/tree")
     *
     * Show the forum tree.
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function treeAction()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $tree = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->childrenHierarchy(null, false);

        return new Response($this->view->assign('tree', $tree)->fetch('Admin/tree.tpl'));
    }

    /**
     * @Route("/modify")
     *
     */
    public function modifyForumAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('Admin/modifyforum.tpl', new ModifyForum());
    }

    /**
     * @Route("/delete")
     *
     */
    public function deleteforumAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('Admin/deleteforum.tpl', new DeleteForum());
    }

    /**
     * @Route("/subscriptions")
     *
     */
    public function manageSubscriptionsAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('Admin/managesubscriptions.tpl', new ManageSubscriptions());
    }

}