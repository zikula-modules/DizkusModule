<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Needles;

use ModUtil;
use DataUtil;
use Zikula\Module\DizkusModule\Manager\TopicManager;
use Zikula\Module\DizkusModule\Manager\ForumManager;

class Dizkus extends \Zikula_AbstractHelper
{
    const NAME = 'ZikulaDizkusModule';

    public function info()
    {
        $info = array(
            'module' => self::NAME,
            'info' => 'DIZKUS{F-forumid|T-topicid}',
            'inspect' => true);
        //reverse lookup possible, needs MultiHook_needleapi_dizkus_inspect() function
        return $info;
    }

    /**
     * Dizkus needle
     * @param $args['nid'] needle id
     * @return array()
     */
    public static function needle($args)
    {
        // Get arguments from argument array
        $nid = $args['nid'];
        unset($args);
        // cache the results
        static $cache;
        if (!isset($cache)) {
            $cache = array();
        }
        if (!empty($nid)) {
            if (!isset($cache[$nid])) {
                // not in cache array
                // set the default
                $cache[$nid] = '';
                if (ModUtil::available('Dizkus')) {
                    // nid is like F-## or T-##
                    $temp = explode('-', $nid);
                    $type = '';
                    if (is_array($temp) && count($temp) == 2) {
                        $type = $temp[0];
                        $id = $temp[1];
                    }
                    switch ($type) {
                        case 'F':
                            $managedForum = new ForumManager($id);
                            if (!empty($managedForum)) {
                                if (ModUtil::apiFunc(self::NAME, 'Permission', 'canRead', $managedForum->get())) {
                                    $url = \ServiceUtil::getService('router')->generate('zikuladizkusmodule_user_viewforum', array('forum' => $id));
                                    $title = DataUtil::formatForDisplay($managedForum->get()->getName());
                                    $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
                                } else {
                                    $cache[$nid] = '<em>' . __f('Error! You do not have the necessary authorisation for forum ID %s.', $id) . '</em>';
                                }
                            } else {
                                $cache[$nid] = '<em>' . __f('Error! The forum ID %s is unknown.', $id) . '</em>';
                            }
                            break;
                        case 'T':
                            $managedTopic = new TopicManager($id);
                            if (!empty($managedTopic)) {
                                if (ModUtil::apiFunc(self::NAME, 'Permission', 'canRead', $managedTopic->get()->getForum())) {
                                    $url = \ServiceUtil::getService('router')->generate('zikuladizkusmodule_user_viewtopic', array('topic' => $id));
                                    $title = DataUtil::formatForDisplay($managedTopic->get()->getTitle());
                                    $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
                                } else {
                                    $cache[$nid] = '<em>' . __f('Error! You do not have the necessary authorisation for topic ID %s.', $id) . '</em>';
                                }
                            } else {
                                $cache[$nid] = '<em>' . __f('Error! The topic ID %s is unknown.', $id) . '</em>';
                            }
                            break;
                        default:
                            $cache[$nid] = '<em>' . __('Error! Unknown parameter at position #1 (\'F\' or \'T\').') . '</em>';
                    }
                } else {
                    $cache[$nid] = '<em>' . __('Error! The Dizkus module is not available.') . '</em>';
                }
            }
            $result = $cache[$nid];
        } else {
            $result = '<em>' . __('Error! No needle ID.') . '</em>';
        }

        return $result;
    }

}
