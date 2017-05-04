<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;

/**
 * Class StatisticsBlock.
 */
class StatisticsBlock extends AbstractBlockHandler
{
    /**
     * Display the block.
     */
    public function display(array $properties)
    {
        if (!$this->hasPermission('ZikulaDizkusModule::StatisticsBlock', "$properties[bid]::", ACCESS_READ)) {
            return '';
        }

        // check if forum is turned off
        $forum_enabled = $this->getVar('forum_enabled');
        if (!$forum_enabled) {
            return $this->renderView('@ZikulaDizkusModule/Block/dizkus.disabled.html.twig', [
                'forum_disabled_info' => $this->getVar('forum_disabled_info'),
            ]);
        }

        // return immediately if no posts exist @todo
//        if (ModUtil::apiFunc($this->name, 'user', 'countstats', array('type' => 'all')) == 0) {
//            return false;
//        }
//
        // check if template is set, if not, use the default block template
        $template = empty($properties['template']) ? 'statistics' : $properties['template'];

        if (empty($properties['params'])) {
            $properties['params'] = 'maxposts=5';
        }

        if (empty($properties['showfooter'])) {
            $properties['showfooter'] = true;
        }

        // convert param string to php array
        $paramarray = [];
        $params = explode(',', $properties['params']);
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $param) {
                $paramdata = explode('=', $param);
                $paramarray[trim($paramdata[0])] = trim($paramdata[1]);
            }
        }
        // must set this default
        $paramArray['months'] = !empty($paramArray['months']) ? $paramArray['months'] : 6;

        $topforums = $this->get('zikula_dizkus_module.forum_manager')->getTopForums($paramArray);
        $lastposts = $this->get('zikula_dizkus_module.post_manager')->getLastPosts($paramArray);
        $topposters = $this->get('zikula_dizkus_module.post_manager')->getTopPosters($paramArray);

        return $this->renderView("@ZikulaDizkusModule/Block/$template.html.twig", [
            'topforums'      => $topforums,
            'topforumscount' => count($topforums),
            'lastposts'      => $lastposts,
            'lastpostcount'  => count($lastposts),
            'topposters'     => $topposters,
            'toppostercount' => count($topposters),
            'months'         => $paramArray['months'],
//          ModUtil::apiFunc($this->name, 'user', 'countstats', ['type' => 'alltopics']) @todo
            'total_topics' => 0,
            'total_posts'  => 0,
            'total_forums' => 0,
            'last_user'    => 0,
            'showfooter'   => $properties['showfooter'],
        ]);
    }

    public function getFormClassName()
    {
        return 'Zikula\DizkusModule\Form\Type\StatisticsBlockType';
    }

    public function getFormTemplate()
    {
        return '@ZikulaDizkusModule/Block/statistics.modify.html.twig';
    }
}
