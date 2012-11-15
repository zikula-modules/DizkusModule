<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * This class provides a handler to edit forums.
 */
class Dizkus_Form_Handler_Admin_ModifyForum extends Zikula_Form_AbstractHandler
{
    /**
     * forum
     *
     * @var statement
     */
    private $_forum;


    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN) ) {
            return LogUtil::registerPermissionError();
        }
        
        $id = $this->request->query->get('id', null);
        if ($id) {
            $view->assign('templatetitle', $this->__('Modify forum'));
        } else {
            $view->assign('templatetitle', $this->__('Create forum'));
        }

        $forum = new Dizkus_ContentType_Forum($id);

        $url = ModUtil::url($this->name, 'admin', 'tree');
        if (!$forum->exists()) {
            return LogUtil::registerError($this->__f('Item with id %s not found', $id), null, $url);
        }
        if (!$forum->isCategory()) {
            return LogUtil::registerError($this->__f('Item with id %s is a category', $id), null, $url);

        }



            //$forum_mods = ModUtil::apiFunc('Dizkus', 'admin', 'readforummods', $forum->getforum_id());
            //$view->assign('forum_mods', $forum_mods);


        $t = $forum->toArray();
        unset($t['parent']);


        if ($forum->get()->getforum_pop3_active()) {
            $this->view->assign('extsource', 'mail2forum');
        } else {
            $this->view->assign('extsource', 'noexternal');
        }



        $view->assign($t);


        // assign all users and groups
        $users  = UserUtil::getAll();
        $groups = UserUtil::getGroups();
        $usersAndGroups = array();
        foreach ($users as $value) {
            $usersAndGroups[] = array(
                'value' => $value['uid'],
                'text'  => $value['uname'],
            );
        }
        foreach ($groups as $value) {
            $usersAndGroups[] = array(
                'value' => '100000'.$value['gid'],
                'text'  => $value['name'].' ('.$this->__('Group').')',
            );
        }
        $view->assign('usersAndGroups', $usersAndGroups);


        $this->view->assign('parents', ModUtil::apiFunc($this->name, 'Forum', 'getParents'));


        $this->view->caching = Zikula_View::CACHE_DISABLED;


        $this->_forum = $forum;

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
    function handleCommand(Zikula_Form_View $view, &$args)
    {
        $url = ModUtil::url('Dizkus', 'admin', 'tree');
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }


        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();

        $data['parent'] = $this->entityManager->find('Dizkus_Entity_Forums', $data['parent']);

        if ($data['extsource'] == 'mail2forum' && $data['pop3_passwordconfirm'] != $data['pop3_password']) {
            return LogUtil::registerError('Passwords are not matching!');
        } else {
            unset($data['pop3_passwordconfirm']);
        }


        if ($data['extsource'] == 'mail2forum' && $data['pnpasswordconfirm'] != $data['pnpassword']) {
            return LogUtil::registerError('Passwords are not matching!');
        } else {
            unset($data['pnpasswordconfirm']);
        }

        //$forum_mods = $data['forum_mods'];
        unset($data['forum_mods']);


        //$forum_id = $forum->getforum_id();
        
        
        $this->_forum->store($data);


        // ToDo: add status messages
        /*if ($edit) {
            LogUtil::registerStatus(__('Forum successfully updated.'));
        } else {
            LogUtil::registerStatus(__('Forum successfully created.'));
        }*/

        /*if ($forum) {
            $moderators = $this->entityManager->getRepository('Dizkus_Entity_Moderators')
                ->findBy(array('forum_id' => $forum_id));

            // remove deselected moderators
            foreach ($moderators as $moderator) {
                $key = array_search($moderator->getuser_id(), $forum_mods);
                if ($key) {
                    unset($forum_mods[$key]);
                } else {
                    $this->entityManager->remove($moderator);
                }
            }
        }


        // insert added moderators
        foreach ($forum_mods as $forum_mod) {
            $newModerator = new Dizkus_Entity_Moderators2();
            $newModerator->setForum_id($forum_id);
            $newModerator->setUser_id($forum_mod);
            $this->entityManager->persist($newModerator);
        }*/

        //$this->entityManager->flush();

        // redirect to the admin forum overview
        return $view->redirect($url);
    }
}
