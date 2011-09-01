<?php
/**
 * Wikula
 *
 * @copyright  (c) Wikula Development Team
 * @link       http://code.zikula.org/wikula/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * category    Zikula_3rdParty_Modules
 * @subpackage Wiki
 * @subpackage Wikula
 */

function smarty_function_editor($params, &$smarty)
{
    if(array_key_exists('textfield', $params) ) {
        $textfield = $params['textfield'];
    } else { 
        $textfield = 'message';
    }
    
    if (ModUtil::available('LuMicuLa'))  {
        $args = array(
            'textfield' => $textfield,
            'mode' => 'forum'
        );
        return ModUtil::apiFunc('LuMicuLa', 'plugin', 'editor', $args);
    }
}
