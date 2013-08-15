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
 * Zikula_View plugin
 * This file is a plugin for Zikula_View, the Zikula implementation of Smarty
 */

/**
 * Smarty modifier to create a link to a topic
 *
 * Available parameters:

 * Example
 *
 *   {$topic_id|viewtopiclink}
 *
 *
 * @author       Frank Schummertz
 * @author       The Dizkus team
 * @since        16. Sept. 2003
 * @param        array    $string     the contents to transform
 * @return       string   the modified output
 */
function smarty_modifier_viewtopiclink($topic_id=null, $subject=null, $forum_name=null, $class='', $start=null, $last_post_id=null)
{
    // @ToDo: Possibility do disable topic previews

    if (!isset($topic_id)) {
        return '';
    }

    $class = 'class="tooltips ' . DataUtil::formatForDisplay($class) . '"';

    $args = array('topic' => (int)$topic_id);
    if (isset($start)) {
        $args['start'] = (int)$start;
    }

    $url = ModUtil::url('Dizkus', 'user', 'viewtopic', $args);
    if (isset($last_post_id)) {
        $url .= '#pid' . (int)$last_post_id;
    }

    // get first post text
    $firstPostText = '';
    /* @var $em Doctrine\ORM\EntityManager */
    $em = ServiceUtil::getService('doctrine.entitymanager');
    $firstPost = $em->getRepository('Dizkus_Entity_Post')->findOneBy(array('topic' => $topic_id, 'isFirstPost' => true));

    if (isset($firstPost)) {
        $firstPostText = DataUtil::formatForDisplayHTML(substr($firstPost->getPost_text(), 0, 255));
        $hook = new \Zikula\Core\Hook\FilterHook($firstPostText);
        $title = ServiceUtil::getManager()->get('hook_dispatcher')->dispatch('dizkus.filter_hooks.post.filter', $hook)->getData();
    }

    return '<a '. $class .' href="' . DataUtil::formatForDisplay($url) . '" title="' . $title .'">' . $subject . '</a>';
}
