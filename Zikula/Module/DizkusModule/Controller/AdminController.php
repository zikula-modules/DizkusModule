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
use LogUtil;
use SecurityUtil;
use FormUtil;
use Zikula\Module\DizkusModule\Entity\RankEntity;
use Zikula\Module\DizkusModule\Form\Handler\Admin\Prefs;
use Zikula\Module\DizkusModule\Form\Handler\Admin\AssignRanks;
use Zikula\Module\DizkusModule\Form\Handler\Admin\ModifyForum;
use Zikula\Module\DizkusModule\Form\Handler\Admin\DeleteForum;
use Zikula\Module\DizkusModule\Form\Handler\Admin\ManageSubscriptions;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminController extends \Zikula_AbstractController
{

    public function postInitialize()
    {
        $this->view->setCaching(false);
    }

    /**
     * The main administration function.
     *
     * @return string HTML output string.
     * @deprecated
     */
    public function mainAction()
    {
        $url = ModUtil::url($this->name, 'admin', 'tree');
        $response = new RedirectResponse(System::normalizeUrl($url));
        $response->send();
        exit;
    }

    /**
     * the main administration function
     *
     */
    public function indexAction()
    {
        $url = ModUtil::url($this->name, 'admin', 'tree');
        $response = new RedirectResponse(System::normalizeUrl($url));
        $response->send();
        exit;
    }

    /**
     * Change forum order
     *
     * Move up or down a forum in the tree
     *
     * @return boolean
     */
    public function changeForumOrderAction()
    {
        $action = $this->request->query->get('action', 'moveUp');
        $forumId = $this->request->query->get('forum', null);
        if (empty($forumId)) {
            return LogUtil::registerArgsError();
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

        $response = new RedirectResponse(System::normalizeUrl($url));
        $response->send();
        exit;
    }

    /**
     * preferences
     *
     */
    public function preferencesAction()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        // Create output object
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('admin/preferences.tpl', new Prefs());
    }

    /**
     * syncforums
     */
    public function syncforumsAction()
    {
        $showstatus = !$this->request->request->get('silent', 0);
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $succesful = ModUtil::apiFunc($this->name, 'Sync', 'forums');
        if ($showstatus && $succesful) {
            LogUtil::registerStatus($this->__('Done! Synchronized forum index.'));
        } else {
            return LogUtil::registerError($this->__('Error synchronizing forum index'));
        }
        $succesful = ModUtil::apiFunc($this->name, 'Sync', 'topics');
        if ($showstatus && $succesful) {
            LogUtil::registerStatus($this->__('Done! Synchronized topics.'));
        } else {
            return LogUtil::registerError($this->__('Error synchronizing topics.'));
        }
        $succesful = ModUtil::apiFunc($this->name, 'Sync', 'posters');
        if ($showstatus && $succesful) {
            LogUtil::registerStatus($this->__('Done! Synchronized posts counter.'));
        } else {
            return LogUtil::registerError($this->__('Error synchronizing posts counter.'));
        }

        $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'tree')));
        $response->send();
        exit;
    }

    /**
     * ranks
     */
    public function ranksAction()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $submit = $this->request->request->get('submit', 2);
        $ranktype = (int) $this->request->query->get('ranktype', RankEntity::TYPE_POSTCOUNT);
        if ($submit == 2) {
            list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => $ranktype));
            $this->view->assign('ranks', $ranks);
            $this->view->assign('ranktype', $ranktype);
            $this->view->assign('rankimages', $rankimages);
            if ($ranktype == 0) {
                return $this->response($this->view->fetch('admin/ranks.tpl'));
            } else {
                return $this->response($this->view->fetch('admin/honoraryranks.tpl'));
            }
        } else {
            $ranks = $this->request->getPost()->filter('ranks', '', FILTER_SANITIZE_STRING);
            ModUtil::apiFunc($this->name, 'Rank', 'save', array('ranks' => $ranks));
        }

        $response = new RedirectResponse(System::normalizeUrl(ModUtil::url($this->name, 'admin', 'ranks', array('ranktype' => $ranktype))));
        $response->send();
        exit;
    }

    /**
     * ranks
     */
    public function assignranksAction()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('admin/assignranks.tpl', new AssignRanks());
    }

    /**
     * tree
     *
     * Show the forum tree.
     *
     * @return string
     */
    public function treeAction()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $tree = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->childrenHierarchy(null, false);

        return $this->response($this->view->assign('tree', $tree)->fetch('admin/tree.tpl'));
    }

    /**
     *
     */
    public function modifyForumAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('admin/modifyforum.tpl', new ModifyForum());
    }

    /**
     *
     */
    public function deleteforumAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('admin/deleteforum.tpl', new DeleteForum());
    }

    /**
     * managesubscriptions
     *
     */
    public function manageSubscriptionsAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return $form->execute('admin/managesubscriptions.tpl', new ManageSubscriptions());
    }

}