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
class Dizkus_Form_Handler_Admin_DeleteForum extends Zikula_Form_AbstractHandler
{
    /**
     * category
     *
     * @var statement
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
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN) ) {
            return LogUtil::registerPermissionError();
        }
        
        $id = $this->request->query->get('id', null);
        
        if ($id) {
            $category = $this->entityManager->find('Dizkus_Entity_Forums', $id);
            if ($category) {
                $this->view->assign($category->toArray());
            } else {
                return LogUtil::registerArgsError($this->__f('Forum with id %s not found', $id));
            }
        } else {
            return LogUtil::registerArgsError();
        }

        $forums = ModUtil::apiFunc($this->name, 'Category', 'getForums', $id);
        $this->view->assign('forums', $forums);



        $actions = array();
        $otherCategories = ModUtil::apiFunc($this->name, 'Category', 'getAll', $id);
        foreach ($otherCategories as $otherCategory) {
            $actions[] = array(
                'text'  => $this->__f("Move to '%s'.", $otherCategory['cat_title']),
                'value' => $otherCategory['cat_id']
            );
        }

        $actions[] = array(
                    'text'  => $this->__('Remove them.'),
                    'value' => 'remove'
                   );
        $this->view->assign('actions', $actions);

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
        $url = ModUtil::url('Dizkus', 'admin', 'tree' );
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }
        
        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();


        return $view->redirect($url);
    }
}
