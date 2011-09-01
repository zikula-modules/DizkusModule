<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Form_Handler_Admin_ModifySubForum extends Zikula_Form_AbstractHandler
{
    private $_id;
    
    function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN) ) {
            return LogUtil::registerPermissionError();
        }
        
        $id = FormUtil::getPassedValue('id');
        
        if ($id) {
            $view->assign('templatetitle', $this->__('Modify subforum'));
            $subforum   = DBUtil::selectObjectByID ('dizkus_forums', $id, 'forum_id');
            if ($subforum) {
                $this->_id = $id;
               $this->view->assign($subforum);
            } else {
                return LogUtil::registerError($this->__f('Article with id %s not found', $id));
            }
        } else {
            $view->assign('templatetitle', $this->__('Create subforum'));
        } 
        
        
        $mainforums0 = DBUtil::selectObjectArray('dizkus_forums', $where = 'WHERE is_subforum = 0');
        $mainforums  = array();
        foreach ($mainforums0 as $mainforum) {
            $mainforums[] = array(
                'value' => $mainforum['forum_id'],
                'text' => $mainforum['forum_name']
            );
        }
        $this->view->assign('mainforums', $mainforums);
                
        $this->view->caching = false;

        return true;
    }

    function handleCommand(Zikula_Form_View $view, &$args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError('index.php');
        }

        $url = ModUtil::url('Dizkus', 'admin', 'subforums' );
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }
        
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        
        $data = $view->getValues();

        
        // switch between edit and create mode
        if ($this->_id) {
            $data['forum_id'] = $this->_id;
            $mainforum   = DBUtil::selectObjectByID ('dizkus_forums', $data['is_subforum'], 'forum_id');
            $data['cat_id'] = $mainforum['cat_id'];
            DBUtil::updateObject($data, 'dizkus_forums', null, 'forum_id');
        } else {
            $mainforum   = DBUtil::selectObjectByID ('dizkus_forums', $data['is_subforum'], 'forum_id');
            $data['cat_id'] = $mainforum['cat_id'];
            DBUtil::insertObject($data, 'dizkus_forums','forum_id');
        }

        return $view->redirect($url);
    }
}
