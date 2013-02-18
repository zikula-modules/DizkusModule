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

                    ModUtil::dbInfoLoad('Dizkus');
                    $ztable = DBUtil::getTables();

                    switch ($type) {
                        case 'F':
                            $sql = 'SELECT forum_name,
                                           cat_id
                                    FROM   ' . $ztable['dizkus_forums'] . '
                                    WHERE  forum_id=' . (int)DataUtil::formatForStore($id);
                            $res = DBUtil::executeSQL($sql);
                            $colarray = array('forum_name', 'cat_id');
                            $result    = DBUtil::marshallObjects($res, $colarray);

                            if (is_array($result) && !empty($result)) {
                                $forum = array(
                                    'cat_id' => $result[0]['cat_id'],
                                    'forum_id' => $id
                                );
                                if (ModUtil::apiFunc($this->name, 'Permission', 'canRead', $forum)) {
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
                            $sql = 'SELECT    t.topic_title,
                                              t.forum_id,
                                              f.cat_id 
                                    FROM      ' . $ztable['dizkus_topics'] . ' as t
                                    LEFT JOIN ' . $ztable['dizkus_forums'] . ' as f
                                    ON        f.forum_id=t.forum_id
                                    WHERE     t.topic_id=' . DataUtil::formatForStore($id);
                            $res = DBUtil::executeSQL($sql);
                            $colarray = array('topic_title', 'forum_id', 'cat_id');
                            $result    = DBUtil::marshallObjects($res, $colarray);

                            if (is_array($result) && !empty($result)) {
                                if (ModUtil::apiFunc($this->name, 'Permission', 'canRead', $result[0])) {
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