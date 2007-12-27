<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

Loader::includeOnce('modules/pnForum/common.php');

/**
 * init
 *
 */
function pnForum_statisticsblock_init()
{
    pnSecAddSchema('pnForum_Statisticsblock::', 'Block title::');
}

/**
 * info
 *
 */
function pnForum_statisticsblock_info()
{
    return array( 'module' => 'pnForum',
                  'text_type' => 'pnForum_statisticsblock',
                  'text_type_long' => 'pnForum Statistics',
                  'allow_multiple' => true,
                  'form_content' => false,
                  'form_refresh' => false,
                  'show_preview' => true);
}

/**
 * display the statisticsblock
 */
function pnForum_statisticsblock_display($row)
{
    if(!pnModAvailable('pnForum')) {
        return;
    }
    
    //check for Permission
    if (!SecurityUtil::checkPermission('pnForum_Statisticsblock::', $row['title'] . '::', ACCESS_READ)){ 
        return; 
    }

    pnModLangLoad('pnForum', 'user');
    // check if forum is turned off
    $disabled = pnf_available();
    if(!is_bool($disabled)) {
        $row['content'] = $disabled;
	    return themesideblock($row);
    }

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);

    // check if cb_template is set, if not, use the default centerblock template
    if(empty($vars['sb_template'])) {
        $vars['sb_template'] = "pnforum_statisticsblock_display.html";
    }
    if(empty($vars['sb_parameters'])) {
        $vars['sb_parameters'] = "maxposts=5";
    }

    if(pnModGetName() <> 'pnForum') {
        // add the pnForum stylesheet file to the page vars
        PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('pnForum'));
    }

    $pnr = new pnRender('pnForum');
    $pnr->add_core_data();
    $pnr->caching = false;

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
function pnForum_statisticsblock_update($row)
{
	if (!SecurityUtil::checkPermission('pnForum_Statisticsblock::', "$row[title]::", ACCESS_ADMIN)) {
	    return false;
	}
    list($sb_template,
         $sb_parameters) = pnVarCleanFromInput('sb_template',
                                               'sb_parameters');

    if(empty($sb_parameters)) { $sb_parameters = 'maxposts=5'; }
    if(empty($sb_template))   { $sb_template = 'pnforum_statisticsblock_display.html'; }

    $row['content'] = pnBlockVarsToContent(compact('sb_template', 'sb_parameters' ));
    return($row);
}

/**
 * Modify the block
 */
function pnForum_statisticsblock_modify($row)
{
	if (!SecurityUtil::checkPermission('pnForum_Statisticsblock::', $row['title'] . '::', ACCESS_ADMIN)) {
	    return false;
	}

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);

    if(!isset($vars['sb_parameters']) || empty($vars['sb_parameters'])) { $vars['sb_parameters'] = 'maxposts=5'; }
    if(!isset($vars['sb_template']) || empty($vars['sb_template']))   { $vars['sb_template']   = 'pnforum_statisticsblock_display.html'; }

    $pnRender = new pnRender('pnForum');
    $pnRender->caching = false;
    $pnRender->add_core_data();
    $pnRender->assign('vars', $vars);
    return $pnRender->fetch('pnforum_statisticsblock_config.html');
}
