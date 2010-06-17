<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

include_once 'modules/Dizkus/common.php';

/**
 * init
 */
function Dizkus_centerblock_init()
{
    SecurityUtil::registerPermissionSchema('Dizkus_Centerblock::', 'Block ID::');
}

/**
 * info
 */
function Dizkus_centerblock_info()
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

    return array('module'         => 'Dizkus',
                 'text_type'      => __('Dizkus recent', $dom),
                 'text_type_long' => __('Dizkus recent posts', $dom),
                 'allow_multiple' => true,
                 'form_content'   => false,
                 'form_refresh'   => false,
                 'show_preview'   => true);
}

/**
 * display the center block
 */
function Dizkus_centerblock_display($blockinfo)
{
    if (!ModUtil::available('Dizkus')) {
        return;
    }

    // check for Permission
    if (!SecurityUtil::checkPermission('Dizkus_Centerblock::', "$blockinfo[bid]::", ACCESS_READ)){
        return;
    }

    // check if forum is turned off
    $disabled = dzk_available();
    if (!is_bool($disabled)) {
        $blockinfo['content'] = $disabled;
        return themesideblock($blockinfo);
    }

    // return immediately if no post exist
    if (ModUtil::apiFunc('Dizkus', 'user', 'boardstats', array('type' => 'all')) == 0) {
        return;
    }

    // Break out options from our content field
    $vars = BlockUtil::varsFromContent($blockinfo['content']);

    $render = Renderer::getInstance('Dizkus', false, null, true);

    // check if cb_template is set, if not, use the default centerblock template
    if (empty($vars['cb_template'])) {
        $vars['cb_template'] = 'dizkus_centerblock_display.html';
    }
    if (empty($vars['cb_parameters'])) {
        $vars['cb_parameters'] = 'maxposts=5';
    }
    $params = explode(',', $vars['cb_parameters']);

    if (is_array($params) &&(count($params) > 0)) {
        foreach($params as $param)
        {
            $paramdata = explode('=', $param);
            $render->assign(trim($paramdata[0]), trim($paramdata[1]));
        }
    }

    $blockinfo['content'] = $render->fetch(trim($vars['cb_template']));

    return themesideblock($blockinfo);
}

/**
 * Update the block
 */
function Dizkus_centerblock_update($blockinfo)
{
    if (!SecurityUtil::checkPermission('Dizkus_Centerblock::', "$blockinfo[bid]::", ACCESS_ADMIN)) {
        return false;
    }
    
    $cb_template   = FormUtil::getPassedValue('cb_template', 'dizkus_centerblock_display.html', 'POST');
    $cb_parameters = FormUtil::getPassedValue('cb_parameters', 'maxposts=5', 'POST');

    $blockinfo['content'] = BlockUtil::varsToContent(compact('cb_template', 'cb_parameters'));

    return($blockinfo);
}

/**
 * Modify the block
 */
function Dizkus_centerblock_modify($blockinfo)
{
    if (!SecurityUtil::checkPermission('Dizkus_Centerblock::', "$blockinfo[bid]::", ACCESS_ADMIN)) {
        return false;
    }
    
    // Break out options from our content field
    $vars = BlockUtil::varsFromContent($blockinfo['content']);

    if (!isset($vars['cb_parameters']) || empty($vars['cb_parameters'])) {
        $vars['cb_parameters'] = 'maxposts=5';
    }
    if (!isset($vars['cb_template']) || empty($vars['cb_template'])) {
        $vars['cb_template']   = 'dizkus_centerblock_display.html';
    }

    $render = Renderer::getInstance('Dizkus', false, null, true);
    $render->assign('vars', $vars);

    return $render->fetch('dizkus_centerblock_config.html');
}
