<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Block;

use SecurityUtil;
use ModUtil;
use BlockUtil;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RecentPostsBlock extends \Zikula_Controller_AbstractBlock
{

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema($this->name . '::RecentPostsBlock', 'Block ID::');
    }

    /**
     * info
     *
     * @return array
     */
    public function info()
    {
        return array(
            'module' => $this->name,
            'text_type' => $this->__('Forum recent'),
            'text_type_long' => $this->__('Recent forum posts'),
            'allow_multiple' => true,
            'form_content' => false,
            'form_refresh' => false,
            'show_preview' => true);
    }

    /**
     * Display the block
     *
     * @param array $blockInfo Blockinfo array.
     *
     * @throws AccessDeniedException on perm check failure
     *
     * @return array|boolean
     */
    public function display($blockInfo)
    {
        if (!ModUtil::available($this->name)) {
            return false;
        }
        // check for Permission
        if (!SecurityUtil::checkPermission($this->name . '::RecentPostsBlock', $blockInfo['bid'] . '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        // check if forum is turned off
        if ($this->getVar('forum_enabled') == 'no') {
            $blockInfo['content'] = $this->getVar('forum_disabled_info');

            return BlockUtil::themesideblock($blockInfo);
        }
        // return immediately if no posts exist
        if (ModUtil::apiFunc($this->name, 'user', 'countstats', array('type' => 'all')) == 0) {
            return false;
        }
        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        // check if template is set, if not, use the default block template
        if (empty($vars['template'])) {
            $vars['template'] = 'recentposts.tpl';
        }
        if (empty($vars['params'])) {
            $vars['params'] = 'maxposts=5';
        }
        // convert param string to php array
        $paramarray = array();
        $params = explode(',', $vars['params']);
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $param) {
                $paramdata = explode('=', $param);
                $paramarray[trim($paramdata[0])] = trim($paramdata[1]);
            }
        }
        $this->view->assign('lastposts', ModUtil::apiFunc($this->name, 'block', 'getLastPosts', $paramarray));

        $blockInfo['content'] = $this->view->fetch('Block/' . trim($vars['template']));

        return BlockUtil::themesideblock($blockInfo);
    }

    /**
     * Update the block
     *
     * @param array $blockInfo Blockinfo array.
     * 
     * @throws AccessDeniedException on perm check failure
     *
     * @return boolean|array
     */
    public function update($blockInfo)
    {
        if (!SecurityUtil::checkPermission($this->name . '::RecentPostsBlock', $blockInfo['bid'] . '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $params = $this->request->request->get('dizkus');
        $blockInfo['content'] = BlockUtil::varsToContent($params);

        return $blockInfo;
    }

    /**
     * Modify the block
     *
     * @param array $blockInfo Blockinfo array.
     *
     * @throws AccessDeniedException on perm check failure
     *
     * @return string|boolean
     */
    public function modify($blockInfo)
    {
        if (!SecurityUtil::checkPermission($this->name . '::RecentPostsBlock', $blockInfo['bid'] . '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        // ensure default values
        $vars['params'] = !empty($vars['params']) ? $vars['params'] : 'maxposts=5';
        $vars['template'] = !empty($vars['template']) ? $vars['template'] : 'recentposts.tpl';

        return $this->view->assign('vars', $vars)
            ->fetch('Block/recentposts_modify.tpl');
    }

}
