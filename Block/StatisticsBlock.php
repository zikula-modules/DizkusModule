<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Dizkus\Block;

use SecurityUtil;
use ModUtil;
use BlockUtil;

/**
 * This class provides the statistics block.
 */
class StatisticsBlock extends \Zikula_Controller_AbstractBlock
{
    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema('Dizkus::Statisticsblock', 'Block ID::');
    }

    /**
     * info
     *
     * @return array
     */
    public function info()
    {
        return array('module' => 'Dizkus', 'text_type' => $this->__('Dizkus statistic'), 'text_type_long' => $this->__('Dizkus Statistics Block'), 'allow_multiple' => true, 'form_content' => false, 'form_refresh' => false, 'show_preview' => true);
    }

    /**
     * display the statisticsblock
     *
     * @param array $blockinfo Blockinfo array.
     *
     * @return array|boolean
     */
    public function display($blockinfo)
    {
        if (!ModUtil::available('Dizkus')) {
            return false;
        }
        //check for Permission
        if (!SecurityUtil::checkPermission('Dizkus::Statisticsblock', "{$blockinfo['bid']}::", ACCESS_READ)) {
            return false;
        }
        // check if forum is turned off
        if ($this->getVar('forum_enabled') == 'no') {
            $blockinfo['content'] = $this->getVar('forum_disabled_info');

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
        $paramarray = array();
        $params = explode(',', $vars['sb_parameters']);
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $param) {
                $paramdata = explode('=', $param);
                $paramarray[trim($paramdata[0])] = trim($paramdata[1]);
            }
        }
        $this->view->assign('statparams', $paramarray);
        $blockinfo['content'] = $this->view->fetch(trim($vars['sb_template']));

        return BlockUtil::themesideblock($blockinfo);
    }

    /**
     * Update the block
     *
     * @param array $blockinfo Blockinfo array.
     *
     * @return boolean|array
     */
    public function update($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Dizkus::Statisticsblock:', "{$blockinfo['bid']}::", ACCESS_ADMIN)) {
            return false;
        }
        $sb_template = $this->request->request->get('sb_template', 'statisticsblock/display.tpl');
        $sb_parameters = $this->request->request->get('sb_parameters', 'maxposts=5');
        $blockinfo['content'] = BlockUtil::varsToContent(compact('sb_template', 'sb_parameters'));

        return $blockinfo;
    }

    /**
     * Modify the block
     *
     * @param array $blockinfo Blockinfo array.
     *
     * @return string|boolean
     */
    public function modify($blockinfo)
    {
        if (!SecurityUtil::checkPermission('Dizkus::Statisticsblock', "{$blockinfo['bid']}::", ACCESS_ADMIN)) {
            return false;
        }
        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockinfo['content']);
        if (!isset($vars['sb_parameters']) || empty($vars['sb_parameters'])) {
            $vars['sb_parameters'] = 'maxposts=5';
        }
        if (!isset($vars['sb_template']) || empty($vars['sb_template'])) {
            $vars['sb_template'] = 'statisticsblock/display.tpl';
        }
        //$render = Zikula_View::getInstance('Dizkus', false, null, true);
        return $this->view->assign('vars', $vars)->fetch('statisticsblock/config.tpl');
    }

}
