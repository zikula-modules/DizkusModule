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
 * This class provides a handler to create a new topic.
 */
class Dizkus_Form_Handler_User_QuickReply extends Zikula_Form_AbstractHandler
{
    /**
     * forum id
     *
     * @var integer
     */
    private $_post;


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

        // get the input
        $id = (int)$this->request->query->get('post');

        if (!isset($id)) {
            return LogUtil::registerError(
                $this->__('Error! Missing post id.'),
                null,
                ModUtil::url('Dizkus', 'user', 'main')
            );
        }

        $this->_post = new Dizkus_EntityAccess_Post($id);
        $view->assign($this->_post->toArray());
        $view->assign('preview', false);


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
        $url = ModUtil::url($this->name, 'user', 'viewtopic', array('topic' => $this->_post->getTopicId()));
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {

            return false;
        }

        $data = $view->getValues();

        /*if ($this->isSpam($args['message'])) {
            return LogUtil::registerError($this->__('Error! Your post contains unacceptable content and has been rejected.'));
        }*/





        if (isset($data['delete']) && $data['delete'] === true) {
            $this->_post->delete();
            return $view->redirect($url);
        }
        unset($data['delete']);



        $this->_post->prepare($data);

        // show preview
        if ($args['commandName'] == 'preview') {
            $view->assign('preview', true);
            $view->assign('post', $this->_post->toArray());
            list($lastVisit, $lastVisitUnix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
            $view->assign('last_visit', $lastVisit);
            $view->assign('last_visit_unix', $lastVisitUnix);
            $view->assign('data', $data);
            return true;
        }

        // store post
        $this->_post->update();

        // redirect to the new topic
        return $view->redirect($url);

    }
}