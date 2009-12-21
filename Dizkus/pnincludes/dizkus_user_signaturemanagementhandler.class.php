<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_user_signaturemanagementHandler
{
    function initialize(&$render)
    {       
        $render->assign('signature', pnUserGetVar('_SIGNATURE'));
        $render->caching = false;
        $render->add_core_data('PNConfig');

        return true;
    }

    function handleCommand(&$render, $args)
    {
        $dom = ZLanguage::getModuleDomain('Dizkus');

        if ($args['commandName'] == 'update') {
            // Security check 
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT)) {
                return LogUtil::registerPermissionError();
            }

            // get the pnForm data and do a validation check
            $obj = $render->pnFormGetValues();          
            if (!$render->pnFormIsValid()) {
                return false;
            }

            pnUserSetVar('_SIGNATURE',$obj['signature']);
            LogUtil::registerStatus(__('Done! Signature has been updated.', $dom));

            return $render->pnFormRedirect(pnModURL('Dizkus','user','prefs'));
        }

        return true;
    }
}
