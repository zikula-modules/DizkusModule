<?php
// $Id$
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------

class Dizkus_user_signaturemanagementHandler
{
    function initialize(&$render)
    {       
        $render->assign('signature', pnUserGetVar('_SIGNATURE'));
        $render->caching = false;
        $render->add_core_data('PNConfig');
        return true;
    }
    function handleCommand(&$render, &$args)
    {
        if ($args['commandName']=='update') {
            // Security check 
            if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_COMMENT)) return LogUtil::registerPermissionError();

            // get the pnForm data and do a validation check
            $obj = $render->pnFormGetValues();          
            if (!$render->pnFormIsValid()) return false;

            pnUserSetVar('_SIGNATURE',$obj['signature']);
            LogUtil::registerStatus(_DZK_SIGNATUREUPDATED);
            
            return $render->pnFormRedirect(pnModURL('Dizkus','user','prefs'));
        }
        return true;
    }
}
