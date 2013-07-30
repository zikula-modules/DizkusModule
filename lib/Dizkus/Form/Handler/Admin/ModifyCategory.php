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
 * This class provides a handler to edit categories.
 */
class Dizkus_Form_Handler_Admin_ModifyCategory extends Zikula_Form_AbstractHandler
{

    /**
     * category
     *
     * @var Dizkus_Entity_Forum
     */
    private $category;

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
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $id = $this->request->query->get('id', null);

        if ($id) {
            $view->assign('templatetitle', $this->__('Modify category'));
            $category = $this->entityManager->find('Dizkus_Entity_Forum', $id);
            if (!$category) {
                return LogUtil::registerError($this->__f('Category with id %s not found', $id));
            }
        } else {
            $category = new Dizkus_Entity_Forum();
            $forumRoot = $this->entityManager->getRepository('Dizkus_Entity_Forum')->findOneBy(array('name' => Dizkus_Entity_Forum::ROOTNAME));
            $category->setParent($forumRoot);
            $category->lock();
            $view->assign('templatetitle', $this->__('Create category'));
        }

        $this->view->assign($category->toArray());

        $this->view->caching = false;
        $this->category = $category;

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
        // check hooked modules for validation
        $hook = new Zikula_ValidationHook('dizkus.ui_hooks.forum.validate_edit', new Zikula_Hook_ValidationProviders());
        $hookvalidators = $this->notifyHooks($hook)->getValidators();
        if ($hookvalidators->hasErrors()) {
            return $this->view->registerError($this->__('Error! Hooked content does not validate.'));
        }

        $data = $view->getValues();

        $this->category->merge($data);
        $this->entityManager->persist($this->category);
        $this->entityManager->flush();
        
        $hookUrl = new Zikula_ModUrl($this->name, 'user', 'index', ZLanguage::getLanguageCode(), array('viewcat' => $this->category->getForum_id()));
        $this->notifyHooks(new Zikula_ProcessHook('dizkus.ui_hooks.forum.process_edit', $this->category->getForum_id(), $hookUrl));

        // redirect to the admin subforum overview
        return $view->redirect($url);
    }

}
