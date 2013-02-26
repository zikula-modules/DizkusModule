<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Needles_Dizkus extends Zikula_AbstractHelper
{
    public function info()
    {
        $info = array('module'  => 'Dizkus', // module name
                      'info'    => 'DIZKUS{F-forumid|T-topicid}',   // possible needles  
                      'inspect' => true);     //reverse lookpup possible, needs MultiHook_needleapi_dizkus_inspect() function

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
                    if (is_array($temp) && count($temp)==2) {
                        $type = $temp[0];
                        $id   = $temp[1];
                    }

                    switch ($type) {
                        case 'F':
                            $managedForum = new Dizkus_Manager_Forum($id);

                            if (!empty($managedForum)) {
                                if (ModUtil::apiFunc($this->name, 'Permission', 'canRead', $managedForum->get())) {
                                    $url   = DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewforum', array('forum' => $id)));
                                    $title = DataUtil::formatForDisplay($result[0]['forum_name']);
                                    $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
                                } else {
                                    $cache[$nid] = '<em>' . $this->__f('Error! You do not have the necessary authorisation for forum ID %s.', $id) . '</em>';
                                }
                            } else {
                                $cache[$nid] = '<em>' . $this->__f('Error! The forum ID %s is unknown.', $id) . '</em>';
                            }
                            break;

                        case 'T':
                            $managedTopic = new Dizkus_Manager_Topic($id);

                            if (!empty($managedTopic)) {
                                if (ModUtil::apiFunc($this->name, 'Permission', 'canRead', $managedTopic->get())) {
                                    $url   = DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $id)));
                                    $title = DataUtil::formatForDisplay($result[0]['topic_title']);
                                    $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
                                } else {
                                    $cache[$nid] = '<em>' . $this->__f('Error! You do not have the necessary authorisation for topic ID %s.', $id) . '</em>';
                                }
                            } else {
                                $cache[$nid] = '<em>' . $this->__f('Error! The topic ID %s is unknown.', $id) .'</em>';
                            }
                            break;

                        default:
                            $cache[$nid] = '<em>' . $this->__("Error! Unknown parameter at position #1 ('F' or 'T').") . '</em>';
                    }
                } else {
                    $cache[$nid] = '<em>' . $this->__('Error! The Dizkus module is not available.') . '</em>';
                }    
            }
            $result = $cache[$nid];
        } else {
            $result = '<em>' . $this->__('Error! No needle ID.') . '</em>';
        }

        return $result;
    }
}