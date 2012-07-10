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
 * This class provides a handler to move a post.
 */
class Dizkus_Form_Handler_User_MoveTopic extends Zikula_Form_AbstractHandler
{

    /**
     * old post id
     *
     * @var integer
     */
    private $old_topic_id;


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
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }


        $this->old_topic_id = (int)$this->request->query->get('topic', null);
        $view->assign('topic', $this->old_topic_id);
        $view->assign('forums', ModUtil::apiFunc($this->name, 'Forum', 'getTreeAsDropdownList', false));
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


        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('Dizkus','user','viewtopic', array('topic' => $this->old_topic_id));
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();


        if ($args['commandName'] == 'move') {

            list($f_id, $c_id) = ModUtil::apiFunc($this->name, 'user', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $this->old_topic_id));
            if ($data['forum_id'] == $f_id) {
                return LogUtil::registerError($this->__('Error! The original forum cannot be the same as the target forum.'));
            }
            if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                return LogUtil::registerPermissionError();
            }
            $data['topic_id'] = $this->old_topic_id;

            ModUtil::apiFunc('Dizkus', 'user', 'movetopic', $data);

            $url = ModUtil::url('Dizkus','user','viewtopic', array('topic' => $this->old_topic_id));
            return $view->redirect($url);
        }


        if ($args['commandName'] == 'join') {
            list($f_id, $c_id) = ModUtil::apiFunc($this->name, 'user', 'get_forumid_and_categoryid_from_topicid', array('topic_id' => $this->old_topic_id));
            if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
                return LogUtil::registerPermissionError();
            }

            if (!empty($data['to_topic_id']) && ($data['to_topic_id'] == $this->old_topic_id)) {
                // user wants to copy topic to itself
                return LogUtil::registerError($this->__('Error! The original topic cannot be the same as the target topic.'), null, ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $f_id)));
            }

            $data['from_topic_id'] = $this->old_topic_id;

            ModUtil::apiFunc('Dizkus', 'user', 'jointopics', $data);

            $url = ModUtil::url('Dizkus','user','viewtopic', array('topic' => $data['to_topic_id']));
            return $view->redirect($url);

        }

        return true;
    }
}