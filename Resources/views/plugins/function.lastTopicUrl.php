<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * lastTopicUrl plugin
 */
function smarty_function_lastTopicUrl($params, Zikula_View $view)
{
    $dizkusModuleName = "ZikulaDizkusModule";
    $topic = $params['topic'];
    $urlParams = array(
        'topic' => $topic->getTopic_id(),
        'start' => ModUtil::apiFunc($dizkusModuleName, 'user', 'getTopicPage', array('replyCount' => $topic->getReplyCount())),
    );
    $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', $urlParams) . "#pid" . $topic->getLast_post()->getPost_id();

    return $url;
}
