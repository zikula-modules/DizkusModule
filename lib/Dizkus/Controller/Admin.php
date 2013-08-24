<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
class Dizkus_Controller_Admin extends Zikula_AbstractController
{

    public function postInitialize()
    {
        $this->view->setCaching(false)->add_core_data();
    }

    /**
     * the main administration function
     *
     */
    public function main()
    {
        $url = ModUtil::url($this->name, 'admin', 'tree');

        return System::redirect($url);
    }

    /**
     * Change forum order
     *
     * Move up or down a forum in the tree
     *
     * @return boolean
     */
    public function changeForumOrder()
    {
        $action = $this->request->query->get('action', 'moveUp');
        $forumId = $this->request->query->get('forum', null);
        if (empty($forumId)) {
            return LogUtil::registerArgsError();
        }
        $repo = $this->entityManager->getRepository('Dizkus_Entity_Forum');
        $forum = $repo->find($forumId);
        if ($action == 'moveUp') {
            $repo->moveUp($forum, true);
        } else {
            $repo->moveDown($forum, true);
        }
        $this->entityManager->flush();
        $url = ModUtil::url($this->name, 'admin', 'tree');

        return System::redirect($url);
    }

    /**
     * preferences
     *
     */
    public function preferences()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $form = FormUtil::newForm('Dizkus', $this);

        // Return the output that has been generated by this function
        return $form->execute('admin/preferences.tpl', new Dizkus_Form_Handler_Admin_Prefs());
    }

    /**
     * syncforums
     */
    public function syncforums()
    {
        $showstatus = !($this->request->request->get('silent', 0));

        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $succesful = ModUtil::apiFunc('Dizkus', 'Sync', 'forums');
        if ($showstatus && $succesful) {
            LogUtil::registerStatus($this->__('Done! Synchronized forum index.'));
        } else {
            return LogUtil::registerError($this->__("Error synchronizing forum index"));
        }

        $succesful = ModUtil::apiFunc('Dizkus', 'Sync', 'topics');
        if ($showstatus && $succesful) {
            LogUtil::registerStatus($this->__('Done! Synchronized topics.'));
        } else {
            return LogUtil::registerError($this->__("Error synchronizing topics."));
        }

        $succesful = ModUtil::apiFunc('Dizkus', 'Sync', 'posters');
        if ($showstatus && $succesful) {
            LogUtil::registerStatus($this->__('Done! Synchronized posts counter.'));
        } else {
            return LogUtil::registerError($this->__("Error synchronizing posts counter."));
        }

        return System::redirect(ModUtil::url('Dizkus', 'admin', 'main'));
    }

    /**
     * ranks
     */
    public function ranks()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $submit = $this->request->getPost()->filter('submit', 2);
        $ranktype = $this->request->getGet()->filter('ranktype', Dizkus_Entity_Rank::TYPE_POSTCOUNT, FILTER_SANITIZE_NUMBER_INT);

        if ($submit == 2) {
            list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => $ranktype));

            $this->view->assign('ranks', $ranks);
            $this->view->assign('ranktype', $ranktype);
            $this->view->assign('rankimages', $rankimages);

            if ($ranktype == 0) {
                return $this->view->fetch('admin/ranks.tpl');
            } else {
                return $this->view->fetch('admin/honoraryranks.tpl');
            }
        } else {
            $ranks = $this->request->getPost()->filter('ranks', '', FILTER_SANITIZE_STRING);
            ModUtil::apiFunc($this->name, 'Rank', 'save', array('ranks' => $ranks));
        }

        return System::redirect(ModUtil::url($this->name, 'admin', 'ranks', array('ranktype' => $ranktype)));
    }

    /**
     * ranks
     */
    public function assignranks()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        $form = FormUtil::newForm('Dizkus', $this);

        return $form->execute('admin/assignranks.tpl', new Dizkus_Form_Handler_Admin_AssignRanks());
    }

    /**
     * tree
     *
     * Show the forum tree.
     *
     * @return string
     */
    public function tree()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $tree = $this->entityManager->getRepository('Dizkus_Entity_Forum')->childrenHierarchy(null, false);

        return $this->view->assign('tree', $tree)
                        ->fetch('admin/tree.tpl');
    }

    /**
     *
     */
    public function modifyForum()
    {
        $form = FormUtil::newForm('Dizkus', $this);

        return $form->execute('admin/modifyforum.tpl', new Dizkus_Form_Handler_Admin_ModifyForum());
    }

    /**
     *
     */
    public function deleteforum()
    {
        $form = FormUtil::newForm('Dizkus', $this);

        return $form->execute('admin/deleteforum.tpl', new Dizkus_Form_Handler_Admin_DeleteForum());
    }

    /**
     * managesubscriptions
     *
     */
    public function manageSubscriptions()
    {
        $form = FormUtil::newForm('Dizkus', $this);

        return $form->execute('admin/managesubscriptions.tpl', new Dizkus_Form_Handler_Admin_ManageSubscriptions());
    }

}