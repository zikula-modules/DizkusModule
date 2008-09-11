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

Loader::includeOnce('modules/Dizkus/common.php');

/**
 * init
 *
 */
function Dizkus_statisticsblock_init()
{
    SecurityUtil::registerPermissionSchema('Dizkus_Statisticsblock::', 'Block title::');
}

/**
 * info
 *
 */
function Dizkus_statisticsblock_info()
{
    return array( 'module' => 'Dizkus',
                  'text_type' => 'Dizkus_statisticsblock',
                  'text_type_long' => 'Dizkus Statistics',
                  'allow_multiple' => true,
                  'form_content' => false,
                  'form_refresh' => false,
                  'show_preview' => true);
}

/**
 * display the statisticsblock
 */
function Dizkus_statisticsblock_display($row)
{
    if(!pnModAvailable('Dizkus')) {
        return;
    }
    
    //check for Permission
    if (!SecurityUtil::checkPermission('Dizkus_Statisticsblock::', $row['title'] . '::', ACCESS_READ)){ 
        return; 
    }

    pnModLangLoad('Dizkus', 'common');
    // check if forum is turned off
    $disabled = dzk_available();
    if(!is_bool($disabled)) {
        $row['content'] = $disabled;
	    return themesideblock($row);
    }

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);

    // check if cb_template is set, if not, use the default centerblock template
    if(empty($vars['sb_template'])) {
        $vars['sb_template'] = "dizkus_statisticsblock_display.html";
    }
    if(empty($vars['sb_parameters'])) {
        $vars['sb_parameters'] = "maxposts=5";
    }

    $pnr = pnRender::getInstance('Dizkus', false, null, true);

    $params = explode(',', $vars['sb_parameters']);

    if(is_array($params) && count($params)>0) {
        foreach($params as $param) {
            $paramdata = explode("=", $param);
            $pnr->assign(trim($paramdata[0]), trim($paramdata[1]));
        }
    }
    $row['content'] = $pnr->fetch(trim($vars['sb_template']));
	return themesideblock($row);
}

/**
 * Update the block
 */
function Dizkus_statisticsblock_update($row)
{
	if (!SecurityUtil::checkPermission('Dizkus_Statisticsblock::', "$row[title]::", ACCESS_ADMIN)) {
	    return false;
	}

	$sb_template   = FormUtil::getPassedValue('sb_template', 'dizkus_statisticsblock_display.html', 'POST');
	$sb_parameters = FormUtil::getPassedValue('sb_parameters', 'maxposts=5', 'POST');

    $row['content'] = pnBlockVarsToContent(compact('sb_template', 'sb_parameters' ));
    return($row);
}

/**
 * Modify the block
 */
function Dizkus_statisticsblock_modify($row)
{
	if (!SecurityUtil::checkPermission('Dizkus_Statisticsblock::', $row['title'] . '::', ACCESS_ADMIN)) {
	    return false;
	}

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);

    if(!isset($vars['sb_parameters']) || empty($vars['sb_parameters'])) { $vars['sb_parameters'] = 'maxposts=5'; }
    if(!isset($vars['sb_template']) || empty($vars['sb_template']))   { $vars['sb_template']   = 'dizkus_statisticsblock_display.html'; }

    $pnRender = pnRender::getInstance('Dizkus', false, null, true);
    $pnRender->assign('vars', $vars);
    return $pnRender->fetch('dizkus_statisticsblock_config.html');
}
