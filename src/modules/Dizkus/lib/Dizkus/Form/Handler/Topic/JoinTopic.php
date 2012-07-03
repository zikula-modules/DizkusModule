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
 * This class provides a handler to delete a topic.
 */
class Dizkus_Form_Handler_Topic_JoinTopic extends Zikula_Form_AbstractHandler
{

    /**
     * post data
     *
     * @var arrat
     */
    private $from_topic_id;
    private $to_topic_id;
    

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
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
        
        // get the input
        $this->from_topic_id = (int)$this->request->query->get('topic', (isset($args['topic'])) ? $args['topic'] : null);
    
        $this->view->assign('from_topic_id', $this->from_topic_id);
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
        // rewrite to topic if cancel was pressed
        if ($args['commandName'] == 'cancel') {
            return $view->redirect(ModUtil::url('Dizkus','topic','viewtopic', array('topic' => $this->from_topic_id)));
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();
        $this->to_topic_id = $data['to_topic_id'];
        
       
        $new_topic_id = ModUtil::apiFunc('Dizkus', 'topic', 'jointopics', array('from_topic_id' => (int)$this->from_topic_id,
                                                                       'to_topic_id'   => (int)$this->to_topic_id));

        $url = ModUtil::url('Dizkus', 'topic', 'viewtopic', array('topic' => $new_topic_id));
        return $view->redirect($url);
    }
}