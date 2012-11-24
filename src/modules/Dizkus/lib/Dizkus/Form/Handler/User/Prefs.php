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
class Dizkus_Form_Handler_User_Prefs extends Zikula_Form_AbstractHandler
{
    /**
     * forum id
     *
     * @var integer
     */
    private $_posterData;


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

        if (!UserUtil::isLoggedIn()) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }
    
        // get the input
        $this->_posterData = new Dizkus_ContentType_PosterData(UserUtil::getVar('uid'));


        $view->assign($this->_posterData->toArray());
        $orders = array(
            0 => array(
                'text' => 'newest submissions at top',
                'value' => 0
            ),
            1 => array(
                'text' => 'oldest submissions at top',
                'value' => 1
            )
        );
        $view->assign('orders', $orders);


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
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('Dizkus', 'user', 'prefs');
            return $view->redirect($url);
        }
    
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();
        $this->_posterData->store($data);

        return true;
    }
}