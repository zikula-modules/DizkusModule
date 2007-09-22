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
function pnForum_centerblock_init()
{
    pnSecAddSchema('pnForum_Centerblock::', 'Block title::');
}

/**
 * info
 *
 */
function pnForum_centerblock_info()
{
    return array( 'module' => 'pnForum',
                  'text_type' => 'pnForum_centerblock',
                  'text_type_long' => 'pnForum Centerblock',
                  'allow_multiple' => true,
                  'form_content' => false,
                  'form_refresh' => false,
                  'show_preview' => true);
}

/**
 * display the center block
 */
function pnForum_centerblock_display($row)
{
    if(!pnModAvailable('pnForum')) {
        return;
    }

    //check for Permission
	if (!pnSecAuthAction(0, 'pnForum_Centerblock::', "$row[title]::", ACCESS_READ)){
	    return;
	}

    pnModLangLoad('pnForum', 'user');
    // check if forum is turned off
    $disabled = pnf_available();
    if(!is_bool($disabled)) {
        $row['content'] = $disabled;
	    return themesideblock($row);
    }

    // return immediately if no post exist
    if(pnModAPIFunc('pnForum', 'user', 'boardstats', array('type' => 'all'))==0) {
        return;
    }

    if(pnModGetName() <> 'pnForum') {
        // add the pnForum stylesheet file to the page vars
        PageUtil::addVar('stylesheet', ThemeUtil::getModuleStylesheet('pnForum'));
    }

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);

    $pnr = new pnRender('pnForum');
    $pnr->add_core_data();
    $pnr->caching = false;

    // check if cb_template is set, if not, use the default centerblock template
    if(empty($vars['cb_template'])) {
        $vars['cb_template'] = "pnforum_centerblock_display.html";
    }
    if(empty($vars['cb_parameters'])) {
        $vars['cb_parameters'] = "maxposts=5";
    }
    $params = explode(",", $vars['cb_parameters']);

    if(is_array($params) &&(count($params)>0)) {
        foreach($params as $param) {
            $paramdata = explode("=", $param);
            $pnr->assign(trim($paramdata[0]), trim($paramdata[1]));
        }
    }

    $row['content'] = $pnr->fetch(trim($vars['cb_template']));
	return themesideblock($row);
}

/**
 * Update the block
 */
function pnForum_centerblock_update($row)
{
	if (!pnSecAuthAction(0, 'pnForum_Centerblock::', "$row[title]::", ACCESS_ADMIN)) {
	    return false;
	}
    list($cb_template,
         $cb_parameters) = pnVarCleanFromInput('cb_template',
                                               'cb_parameters');

    if(empty($cb_parameters)) { $cb_parameters = 'maxposts=5'; }
    if(empty($cb_template))   { $cb_template = 'pnforum_centerblock_display.html'; }

    $row['content'] = pnBlockVarsToContent(compact('cb_template', 'cb_parameters' ));
    return($row);
}


/**
 * Modify the block
 */
function pnForum_centerblock_modify($row)
{
	if (!pnSecAuthAction(0, 'pnForum_Centerblock::', "$row[title]::", ACCESS_ADMIN)) {
	    return false;
	}

    // Break out options from our content field
    $vars = pnBlockVarsFromContent($row['content']);

    if(!isset($vars['cb_parameters']) || empty($vars['cb_parameters'])) { $vars['cb_parameters'] = 'maxposts=5'; }
    if(!isset($vars['cb_template']) || empty($vars['cb_template']))   { $vars['cb_template']   = 'pnforum_centerblock_display.html'; }

    $pnRender = new pnRender('pnForum');
    $pnRender->caching = false;
    $pnRender->add_core_data();
    $pnRender->assign('vars', $vars);
    return $pnRender->fetch('pnforum_centerblock_config.html');
}
