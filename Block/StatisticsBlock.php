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

class StatisticsBlock extends \Zikula_Controller_AbstractBlock
{

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
        SecurityUtil::registerPermissionSchema($this->name . '::StatisticsBlock', 'Block ID::');
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
            'text_type' => $this->__('Forum statistics'),
            'text_type_long' => $this->__('Forum Statistics Block'),
            'allow_multiple' => true,
            'form_content' => false,
            'form_refresh' => false,
            'show_preview' => true);
    }

    /**
     * display the block
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
        //check for Permission
        if (!SecurityUtil::checkPermission($this->name . '::StatisticsBlock', $blockInfo['bid'] . '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }
        // check if forum is turned off
        if ($this->getVar('forum_enabled') == 'no') {
            $blockInfo['content'] = $this->getVar('forum_disabled_info');

            return BlockUtil::themesideblock($blockInfo);
        }
        // break out options from our content field
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        // check if template is set, if not, use the default template
        if (empty($vars['template'])) {
            $vars['template'] = 'statisticsblock.tpl';
        }
        if (empty($vars['params'])) {
            $vars['params'] = 'maxposts=5';
        }
        // convert param string to php array
        $paramArray = array();
        $params = explode(',', $vars['params']);
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $param) {
                $paramData = explode('=', $param);
                $paramArray[trim($paramData[0])] = trim($paramData[1]);
            }
        }
        // must set this default
        $paramArray['months'] = !empty($paramArray['months']) ? $paramArray['months'] : 6;

        $topForums = ModUtil::apiFunc($this->name, 'block', 'getTopForums', $paramArray);
        $this->view->assign('topforums', $topForums)
            ->assign('topforumscount', count($topForums));
        $lastPosts = ModUtil::apiFunc($this->name, 'block', 'getLastPosts', $paramArray);
        $this->view->assign('lastposts', $lastPosts)
            ->assign('lastpostcount', count($lastPosts));
        $topPosters = ModUtil::apiFunc($this->name, 'block', 'getTopPosters', $paramArray);
        $this->view->assign('topposters', $topPosters)
            ->assign('toppostercount', count($topPosters))
            ->assign('months', $paramArray['months']);
        $this->view->assign('total_topics', ModUtil::apiFunc($this->name, 'user', 'countstats', array('type' => 'alltopics')));
        $this->view->assign('total_posts', ModUtil::apiFunc($this->name, 'user', 'countstats', array('type' => 'allposts')));
        $this->view->assign('total_forums', ModUtil::apiFunc($this->name, 'user', 'countstats', array('type' => 'forum')));
        $this->view->assign('last_user', ModUtil::apiFunc($this->name, 'user', 'countstats', array('type' => 'lastuser')));

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
        if (!SecurityUtil::checkPermission($this->name . '::StatisticsBlock', $blockInfo['bid'] . '::', ACCESS_ADMIN)) {
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
        if (!SecurityUtil::checkPermission($this->name . '::StatisticsBlock', $blockInfo['bid'] . '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        // Break out options from our content field
        $vars = BlockUtil::varsFromContent($blockInfo['content']);
        // ensure default values
        $vars['params'] = !empty($vars['params']) ? $vars['params'] : 'maxposts=5';
        $vars['template'] = !empty($vars['template']) ? $vars['template'] : 'statisticsblock.tpl';

        return $this->view->assign('vars', $vars)
            ->fetch('Block/statisticsblock_modify.tpl');
        
    }

}
