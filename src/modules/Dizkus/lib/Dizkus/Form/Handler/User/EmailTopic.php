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
 * This class provides a handler to email a topic.
 */
class Dizkus_Form_Handler_User_EmailTopic extends Zikula_Form_AbstractHandler
{
    /**
     * topic id
     *
     * @var integer
     */
    private $topic_id;


    /**
     * topic poster uid
     *
     * @var integer
     */
    private $topic_poster;


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

        $this->topic_id = (int)$this->request->query->get('topic');

        $topic = ModUtil::apiFunc('Dizkus', 'Topic', 'read0', $this->topic_id);
        $emailsubject = $topic['topic_title'];

        $view->assign($topic);
        $view->assign('emailsubject', $emailsubject);
        $view->assign('message', DataUtil::formatForDisplay($this->__('Hello! Please visit this link. I think it will be of interest to you.')) ."\n\n" . ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic'=>$this->topic_id), null, null, true));
        $view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));

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
            return $view->redirect(ModUtil::url('Dizkus','user','viewtopic', array('topic' => $this->topic_id)));
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();

        ModUtil::apiFunc('Dizkus', 'user', 'emailtopic', array(
            'sendto_email' => $data['sendto_email'],
            'message'      => $data['message'],
            'subject'      => $data['emailsubject']
        ));
        $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $this->topic_id));
        return $view->redirect($url);

    }
}