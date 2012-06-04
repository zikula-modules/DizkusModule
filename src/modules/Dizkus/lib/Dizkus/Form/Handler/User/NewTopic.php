<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Form_Handler_User_NewTopic extends Zikula_Form_AbstractHandler
{
    private $forum_id;
    private $topic_poster;
    
    function initialize(Zikula_Form_View $view)
    {
        // Permission check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }
        
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $this->forum_id = (int)FormUtil::getPassedValue('forum');
        if (!isset($this->forum_id)) {
            return LogUtil::registerError($this->_('Error! Missing forum id.'), null, ModUtil::url('Dizkus','user', 'main'));
        }
        
        $view->assign('preview', false);
        
        return true;
    }

    function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('Dizkus','user', 'viewforum', array('forum' => $this->forum_id));
            return $view->redirect($url);
        }
    
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();
        
        
        // check for maximum message size
        if ((strlen($data['message']) +  strlen('[addsig]')) > 65535) {
            LogUtil::registerStatus($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
            // switch to preview mode
        }
        
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
        
        
        $newtopic = ModUtil::apiFunc('Dizkus', 'user', 'preparenewtopic', $data);
        
        
        
        if ($args['commandName'] == 'preview') {
            $view->assign('preview', true);
            $view->assign('newtopic', $newtopic);
            $view->assign('last_visit', $last_visit);
            $view->assign('last_visit_unix', $last_visit_unix);
            $view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
            return true;
        }

        
        $data['forum_id'] = $this->forum_id;
            
        $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic', $data);        
        

        $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $topic_id));
        return $view->redirect($url);
        
    }
}