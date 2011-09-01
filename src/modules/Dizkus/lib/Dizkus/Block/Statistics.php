<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Block_Statistics extends Zikula_Controller_AbstractBlock
{
	/**
	 * init
	 */
	public function init()
	{
	    SecurityUtil::registerPermissionSchema('Dizkus_Statisticsblock::', 'Block ID::');
	}
	
	/**
	 * info
	 */
	public function info()
	{
	    return array('module'         => 'Dizkus',
	                 'text_type'      => $this->__('Dizkus statistic'),
	                 'text_type_long' => $this->__('Dizkus Statistics Block'),
	                 'allow_multiple' => true,
	                 'form_content'   => false,
	                 'form_refresh'   => false,
	                 'show_preview'   => true);
	}
	
	/**
	 * display the statisticsblock
	 */
	public function display($blockinfo)
	{
	    if (!ModUtil::available('Dizkus')) {
	        return;
	    }
	
	    //check for Permission
	    if (!SecurityUtil::checkPermission('Dizkus_Statisticsblock::', "$blockinfo[bid]::", ACCESS_READ)){ 
	        return; 
	    }
	
	    // check if forum is turned off
	    $disabled = dzk_available();
	    if (!is_bool($disabled)) {
	        $blockinfo['content'] = $disabled;
	        return BlockUtil::themesideblock($blockinfo);
	    }
	
	    // break out options from our content field
	    $vars = BlockUtil::varsFromContent($blockinfo['content']);
	
	    // check if cb_template is set, if not, use the default centerblock template
	    if (empty($vars['sb_template'])) {
	        $vars['sb_template'] = 'statisticsblock/display.tpl';
	    }
	    if (empty($vars['sb_parameters'])) {
	        $vars['sb_parameters'] = 'maxposts=5';
	    }
	
	    $params = explode(',', $vars['sb_parameters']);
	
	    if (is_array($params) && count($params) > 0) {
	        foreach ($params as $param) {
	            $paramdata = explode('=', $param);
	            $this->view->assign(trim($paramdata[0]), trim($paramdata[1]));
	        }
	    }
	
	    $blockinfo['content'] = $this->view->fetch(trim($vars['sb_template']));
	
	    return BlockUtil::themesideblock($blockinfo);
	}
	
	/**
	 * Update the block
	 */
	public function update($blockinfo)
	{
	    if (!SecurityUtil::checkPermission('Dizkus_Statisticsblock::', "$blockinfo[bid]::", ACCESS_ADMIN)) {
	        return false;
	    }
	
	    $sb_template   = FormUtil::getPassedValue('sb_template', 'statisticsblock/display.tpl', 'POST');
	    $sb_parameters = FormUtil::getPassedValue('sb_parameters', 'maxposts=5', 'POST');
	
	    $blockinfo['content'] = BlockUtil::varsToContent(compact('sb_template', 'sb_parameters'));
	
	    return($blockinfo);
	}
	
	/**
	 * Modify the block
	 */
	public function modify($blockinfo)
	{
	    if (!SecurityUtil::checkPermission('Dizkus_Statisticsblock::', "$blockinfo[bid]::", ACCESS_ADMIN)) {
	        return false;
	    }
	
	    // Break out options from our content field
	    $vars = BlockUtil::varsFromContent($blockinfo['content']);
	
	    if (!isset($vars['sb_parameters']) || empty($vars['sb_parameters'])) {
	        $vars['sb_parameters'] = 'maxposts=5';
	    }
	    if (!isset($vars['sb_template']) || empty($vars['sb_template'])) {
	        $vars['sb_template']   = 'statisticsblock/display.tpl';
	    }
	
	    //$render = Zikula_View::getInstance('Dizkus', false, null, true);
	
	    $this->view->assign('vars', $vars);
	
	    return $this->view->fetch('statisticsblock/config.tpl');
	}

}
