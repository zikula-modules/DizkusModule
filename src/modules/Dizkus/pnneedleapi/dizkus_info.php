<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * Dizkus needle info
 * @param none
 * @return string with short usage description
 */
function Dizkus_needleapi_dizkus_info()
{
    $info = array('module'  => 'Dizkus', // module name
                  'info'    => 'DIZKUS{F-forumid|T-topicid}',   // possible needles  
                  'inspect' => true);     //reverse lookpup possible, needs MultiHook_needleapi_dizkus_inspect() function

    return $info;
}
