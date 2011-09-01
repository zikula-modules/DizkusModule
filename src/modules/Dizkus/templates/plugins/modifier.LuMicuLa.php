<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
 
function smarty_modifier_LuMicuLa($string)
{
    if (ModUtil::available('LuMicuLa')) {
        $string = ModUtil::apiFunc('LuMicuLa', 'user', 'transform', array(
            'text'    => $string,
            'modname' => 'Dizkus'
        ));
    }
    return $string;        
                    
}
