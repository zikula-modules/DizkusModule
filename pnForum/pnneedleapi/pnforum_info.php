<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id: pnuser.php 804 2007-09-14 18:00:46Z landseer $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

/**
 * pnforum needle info
 * @param none
 * @return string with short usage description
 */
function pnForum_needleapi_pnforum_info()
{
    $info = array('module'  => 'pnForum', // module name
                  'info'    => 'PNFORUM{F-forumid|T-topicid}',   // possible needles  
                  'inspect' => true);     //reverse lookpup possible, needs MultiHook_needleapi_pnforum_inspect() function
    return $info;
}
