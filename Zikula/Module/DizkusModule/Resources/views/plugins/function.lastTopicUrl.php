<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * lastTopicUrl plugin
 * lists all allowed html tags
 *
 */
function smarty_function_lastTopicUrl($params, Zikula_View $view)
{
    $dizkusModuleName = "ZikulaDizkusModule";
    $topic = $params['topic'];
    $params = array(
        'topic' => $topic->getTopic_id(),
        'start' => ModUtil::apiFunc($dizkusModuleName, 'user', 'getTopicPage', array('replyCount' => $topic->getReplyCount())),
    );
    $url = new \Zikula\Core\ModUrl($dizkusModuleName, 'user', 'viewtopic', ZLanguage::getLanguageCode(), $params, "pid" . $topic->getLast_post()->getPost_id());

    return $url->getUrl();
}
