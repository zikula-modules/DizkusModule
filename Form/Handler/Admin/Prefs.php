<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Dizkus\Form\Handler\Admin;

class Prefs extends \Zikula_Form_AbstractHandler
{
    /**
     * These array keys are module vars that (for BC reasons) are stored
     * as text 'yes' or 'no' instead of boolean
     */
    private $YESNOS = array(
        'log_ip',
        'm2f_enabled',
        'rss2f_enabled',
        'favorites_enabled',
        'signaturemanagement',
        'removesignature',
        'striptags',
        'forum_enabled',
        'fulltextindex',
        'extendedsearch',
        'showtextinsearchresults'
    );

    public function initialize(Zikula_Form_View $view)
    {
        $this->view->caching = false;

        $this->view->assign('post_sort_order_options', array(array('text' => $this->__('Ascending'), 'value' => 'ASC'),
            array('text' => $this->__('Descending'), 'value' => 'DESC')));

        $this->view->assign('deletehook_options', array(array('text' => $this->__('Delete topic'), 'value' => 'remove'),
            array('text' => $this->__('Lock topic'), 'value' => 'lock')));

        $vars = $this->getVars();

        $adminGroup = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => 2));
        $admins = array(0 => array('text' => 'disable', 'value' => '-1'));
        foreach ($adminGroup['members'] as $admin) {
            $admins[] = array('text' => UserUtil::getVar('uname', $admin['uid']), 'value' => $admin['uid']);
        }
        $this->view->assign('admins', $admins);

        // convert yes/no to boolean
        foreach ($this->YESNOS as $value) {
            if (array_key_exists($value, $vars) and $vars[$value] == 'yes') {
                $vars[$value] = true;
            } else {
                $vars[$value] = false;
            }
        }

        $this->view->assign($vars);

        return true;
    }

    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError('index.php');
        }

        if ($args['commandName'] == 'submit') {

            // check for valid form
            if (!$view->isValid()) {
                return false;
            }

            $data = $view->getValues();

            // convert booleans to yes/no
            foreach ($this->YESNOS as $yesno) {
                $this->setVar($yesno, $data[$yesno] == 1 ? 'yes' : 'no');
                unset($data[$yesno]);
            }
            $this->setVar('fulltextindex', 'no'); // disable until technology catches up with InnoDB
            $this->setVar('extendedsearch', 'no'); // disable until technology catches up with InnoDB
            // set the rest from the array
            $this->setVars($data);
            LogUtil::registerStatus($this->__('Done! Updated configuration.'));
        } elseif ($args['commandName'] == 'restore') {
            $this->setVars(Dizkus_Installer::getDefaultVars());
            LogUtil::registerStatus($this->__('Done! Reset configuration to default values.'));
        }

        // redirect to compensate for trouble with `databound`
        $this->view->redirect(ModUtil::url('Dizkus', 'admin', 'tree'));

        return true;
    }

}
