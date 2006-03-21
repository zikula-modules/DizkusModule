<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the PN_phpBB14  Module Development Team   *
 * http://www.pnforum.de/                                            *
 ************************************************************************
 * Modified version of: *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
 ************************************************************************
 * License *
 ************************************************************************
 * This program is free software; you can redistribute it and/or modify *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation; either version 2 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * This program is distributed in the hope that it will be useful,      *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with this program; if not, write to the Free Software          *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 *
 * USA                                                                  *
 ************************************************************************
 *
 * statisticsblock
 * @version $Id$
 * @author Andreas Krapohl, Frank Schummertz
 * @copyright 2003 by Andreas Krapohl, Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

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
    if (!pnSecAuthAction(0, 'pnForum_Statisticsblock::', "$row[title]::", ACCESS_READ)){ 
        return; 
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
        // add the pnForum stylesheet file to the addtional_header array
        pnf_add_stylesheet_header();
    }

    $pnr =& new pnRender('pnForum');
    $pnr->add_core_data();
    $pnr->caching = false;

    $params = explode(",", $vars['sb_parameters']);

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
	if (!pnSecAuthAction(0, 'pnForum_Statisticsblock::', "$row[title]::", ACCESS_ADMIN)) {
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
	if (!pnSecAuthAction(0, 'pnForum_Statisticsblock::', "$row[title]::", ACCESS_ADMIN)) {
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

?>