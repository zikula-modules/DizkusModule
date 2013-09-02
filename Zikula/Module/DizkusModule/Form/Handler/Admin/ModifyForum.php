<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Form\Handler\Admin;

use Zikula\Module\DizkusModule\Manager\ForumManager;
use Zikula\Module\DizkusModule\Connection\Pop3Connection;

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
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $id = $this->request->query->get('id', null);
        // disallow editing of root forum
        if ($id == 1) {
            LogUtil::registerError($this->__("Editing of root forum is disallowed", 403));
            System::redirect(ModUtil::url($this->name, 'admin', 'tree'));
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

        $url = ModUtil::url($this->name, 'admin', 'tree');
        if (!$this->_forum->exists()) {
            return LogUtil::registerError($this->__f('Item with id %s not found', $id), null, $url);
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
        $url = ModUtil::url($this->name, 'admin', 'tree');
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        // check hooked modules for validation
        $hook = new Zikula_ValidationHook('dizkus.ui_hooks.forum.validate_edit', new Zikula_Hook_ValidationProviders());
        $hookvalidators = $this->notifyHooks($hook)->getValidators();
        if ($hookvalidators->hasErrors()) {
            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
        }

        $data = $view->getValues();

        // convert parent id to object
        $data['parent'] = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\ForumEntity', $data['parent']);

        if ($data['extsource'] == 'mail2forum') {
            if ($data['passwordconfirm'] != $data['password']) {
                return LogUtil::registerError('Pop3 passwords are not matching!');
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
                $connectionData['coreUser'] = $this->entityManager->find('Zikula\Module\UsersModule\Entity\UserEntity', $data['coreUser']);
                $connection = new Pop3Connection($connectionData);
                $this->_forum->get()->setPop3Connection($connection);

                if ($data['pop3_test']) {
                    // @todo perform test
                    LogUtil::registerStatus($this->__("Pop3 test successful."));
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
        $hookUrl = new Zikula_ModUrl($this->name, 'user', 'viewforum', ZLanguage::getLanguageCode(), array('forum' => $this->_forum->getId()));
        $this->notifyHooks(new Zikula_ProcessHook('dizkus.ui_hooks.forum.process_edit', $this->_forum->getId(), $hookUrl));

        if ($this->_action == 'e') {
            LogUtil::registerStatus($this->__('Forum successfully updated.'));
        } else {
            LogUtil::registerStatus($this->__('Forum successfully created.'));
        }

        // redirect to the admin forum overview
        return $view->redirect($url);
    }

}
