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
 * Renderer plugin
 *
 * This file is a plugin for Renderer, the Zikula implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   Renderer
 * @version      $Id$
 * @author       The Zikula development team
 * @link         http://www.zikula.org  The Zikula Home Page
 * @copyright    Copyright (C) 2002 by the Zikula Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
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
function smarty_function_viewtopiclink($params, &$smarty)
{
    if (!isset($params['topic_id'])) {
        return '';
    }


    $args = array('topic' => (int)$params['topic_id']);
    if (isset($params['start'])) {
        $args['start'] = (int)$params['start'];
    }

    $url = ModUtil::url('Dizkus', 'topic', 'viewtopic', $args);
    if (isset($params['last_post_id'])) {
        $url .= '#pid' . (int)$params['last_post_id'];
    }

    /*$title = __('Go to topic');

    if (isset($forum_name) && !empty($forum_name)) {
        $title .= ' ' . DataUtil::formatForDisplay($forum_name) . ' ::';
    }

    if (isset($subject) && !empty($subject)) {
        $subject = DataUtil::formatForDisplay($subject);
        $title .= ' ' . $subject;
    }*/
    
    
    $post = DBUtil::selectObjectByID('dizkus_posts', $params['topic_id'], 'topic_id');
    $lastposttext = DataUtil::formatForDisplayHTML($post['post_text']);
   
    
    $smarty->assign('lastposttext', $lastposttext);
    $smarty->assign('lastposturl', $url);
}
