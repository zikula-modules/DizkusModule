<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Form\Handler\Admin;

use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Connection\Pop3Connection;
use ModUtil;
use SecurityUtil;
use UserUtil;
use Zikula_Form_View;
use System;
use Zikula_View;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\RouteUrl;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This class provides a handler to edit forums.
 */
class ModifyForum extends \Zikula_Form_AbstractHandler
{

    /**
     * forum
     *
     * @var ForumManager
     */
    private $_forum;

    /**
     * action [e]dit/[c]reate
     *
     * @var string
     */
    private $_action;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $id = $this->request->query->get('id', null);
        // disallow editing of root forum
        if ($id == 1) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__("Editing of root forum is disallowed", 403));
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_admin_tree', array(), RouterInterface::ABSOLUTE_URL);
            return $view->redirect($url);
        }
        if ($id > 1) {
            $view->assign('templatetitle', $this->__('Modify forum'));
            $this->_action = 'e';
        } else {
            $id = null;
            $view->assign('templatetitle', $this->__('Create forum'));
            $this->_action = 'c';
        }

        $this->_forum = new ForumManager($id);

        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_admin_tree');
        if (!$this->_forum->exists()) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__f('Item with id %s not found', $id), null, $url);
            return false;
        }

        $t = $this->_forum->toArray();
        unset($t['parent']);
        $view->assign($t);
        $parent = $this->_forum->get()->getParent();
        if (isset($parent)) {
            $this->view->assign('parent', $parent->getForum_id());
        }

        $connection = $this->_forum->get()->getPop3Connection();
        if (isset($connection)) {
            $this->view->assign('extsource', 'mail2forum');
            $connectionData = $connection->getConnection();
            $this->view->assign($connectionData);
        } else {
            $this->view->assign('extsource', 'noexternal');
        }

        $view->assign('moderatorUsers', $this->_forum->get()->getModeratorUsersAsIdArray());
        $view->assign('moderatorGroups', $this->_forum->get()->getModeratorGroupsAsIdArray());

        // assign all users for the moderator selection
        $allUsers = UserUtil::getAll();
        $allUsersAsDrowpdownList = array();
        foreach ($allUsers as $user) {
            $allUsersAsDrowpdownList[] = array(
                'value' => $user['uid'],
                'text' => $user['uname'],
            );
        }
        $view->assign('allUsers', $allUsersAsDrowpdownList);

        // assign all groups for the moderator selection
        $groups = UserUtil::getGroups();
        $allGroupsAsDrowpdownList = array();
        foreach ($groups as $value) {
            $allGroupsAsDrowpdownList[] = array(
                'value' => $value['gid'],
                'text' => $value['name'] . ' (' . $this->__('Group') . ')',
            );
        }
        $view->assign('allGroups', $allGroupsAsDrowpdownList);

        $this->view->assign('parents', ModUtil::apiFunc($this->name, 'Forum', 'getParents'));
        $this->view->caching = Zikula_View::CACHE_DISABLED;

        return true;
    }

    /**
     * Handle form submission.
     *
     * @param Zikula_Form_View $view  Current Zikula_Form_View instance.
     * @param array            &$args Arguments.
     *
     * @return bool|void
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_admin_tree', array(), RouterInterface::ABSOLUTE_URL);
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        // check hooked modules for validation
        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->dispatchHooks('dizkus.ui_hooks.forum.validate_edit', $hook)->getValidators();
        if ($hookvalidators->hasErrors()) {
            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
        }

        $data = $view->getValues();

        // convert parent id to object
        $data['parent'] = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $data['parent']);

        if ($data['extsource'] == 'mail2forum') {
            if ($data['passwordconfirm'] != $data['password']) {
                $this->request->getSession()->getFlashBag()->add('error', 'Pop3 passwords are not matching!');
                return false;
            } else {
                //create connection object
                $connectionData = array(
                    'active' => true,
                    'server' => $data['server'],
                    'port' => $data['port'],
                    'login' => $data['login'],
                    'password' => $data['password'],
                    'matchstring' => $data['matchstring'],
                );
                $connectionData['coreUser'] = $this->entityManager->getReference('Zikula\UsersModule\Entity\UserEntity', $data['coreUser']);
                $connection = new Pop3Connection($connectionData);
                $this->_forum->get()->setPop3Connection($connection);

                if ($data['pop3_test']) {
                    // @todo perform test
                    $this->request->getSession()->getFlashBag()->add('status', $this->__("Pop3 test successful."));
                }
            }
        } elseif ($data['extsource'] == 'rss2forum') {
            // @todo temporary value until known what to do
            $this->_forum->get()->setPop3Connection(null);
        } else {
            $this->_forum->get()->setPop3Connection(null);
        }

        // unset extra data before merge
        unset($data['pop3_test'], $data['extsource'], $data['passwordconfirm'], $data['server'], $data['port'], $data['login'], $data['password'], $data['matchstring'], $data['coreUser']);
        // add the rest of the form data
        $this->_forum->store($data);

        // notify hooks
        $hookUrl = RouteUrl::createFromRoute('zikuladizkusmodule_user_viewforum', array('forum' => $this->_forum->getId()));
        $this->dispatchHooks('dizkus.ui_hooks.forum.process_edit', new ProcessHook($this->_forum->getId(), $hookUrl));

        if ($this->_action == 'e') {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Forum successfully updated.'));
        } else {
            $this->request->getSession()->getFlashBag()->add('status', $this->__('Forum successfully created.'));
        }

        // redirect to the admin forum overview
        return $view->redirect($url);
    }

}
