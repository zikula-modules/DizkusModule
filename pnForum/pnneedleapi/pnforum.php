<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id: pnuser.php 804 2007-09-14 18:00:46Z landseer $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

Loader::includeOnce('modules/pnForum/common.php');

/**
 * pnforum needle
 * @param $args['nid'] needle id
 * @return array()
 */
function pnForum_needleapi_pnforum($args)
{
    // Get arguments from argument array
    $nid = $args['nid'];
    unset($args);
    
    // cache the results
    static $cache;
    if(!isset($cache)) {
        $cache = array();
    } 

    if(!empty($nid)) {
        if(!isset($cache[$nid])) {
            // not in cache array
            // set the default
            $cache[$nid] = $result;
            if(pnModAvailable('pnForum')) {
                
                // nid is like F_## or T_##
                $temp = explode('-', $nid);
                $type = '';
                if(is_array($temp) && count($temp)==2) {
                    $type = $temp[0];
                    $id   = $temp[1];
                }
                
                pnModDBInfoLoad('pnForum');
                $dbconn =& pnDBGetConn(true);
                $pntable =& pnDBGetTables();
        
                switch($type) {
                    case 'F':
                        $tblforums = $pntable['pnforum_forums'];
                        $colforums = $pntable['pnforum_forums_column'];
                        
                        $sql = 'SELECT ' . $colforums['forum_name'] . ',
                                       ' . $colforums['cat_id'] . '
                                FROM   ' . $tblforums . '
                                WHERE  ' . $colforums['forum_id'] . '=' . (int)DataUtil::formatForStore($id);
                        $res = $dbconn->Execute($sql);
                        if($dbconn->ErrorNo()==0 && !$res->EOF) {
                            list($title, $cat_id) = $res->fields;
                            if(allowedtoreadcategoryandforum($cat_id, $id)) {
                                $url   = DataUtil::formatForDisplay(pnModURL('pnForum', 'user', 'viewforum', array('forum' => $id)));
                                $title = DataUtil::formatForDisplay($title);
                                $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
                            } else {
                                $cache[$nid] = '<em>' . DataUtil::formatForDisplay(_PNFORUM_NEEDLE_NOAUTHFORFORUM . ' (' . $id . ')') . '</em>';
                            }
                        } else {
                            $cache[$nid] = '<em>' . DataUtil::formatForDisplay(_PNFORUM_NEEDLE_UNKNOWNFORUM . ' (' . $id . ')') . '</em>';
                        }
                        break;
                    case 'T':
                        $tbltopics = $pntable['pnforum_topics'];
                        $coltopics = $pntable['pnforum_topics_column'];
                        $tblforums = $pntable['pnforum_forums'];
                        $colforums = $pntable['pnforum_forums_column'];
                        
                        $sql = 'SELECT    ' . $coltopics['topic_title'] . ',
                                          ' . $coltopics['forum_id'] . ',
                                          ' . $colforums['cat_id'] . ' 
                                FROM      ' . $tbltopics . '
                                LEFT JOIN ' . $tblforums . '
                                ON        ' . $colforums['forum_id'] . '=' . $coltopics['forum_id'] . '
                                WHERE     ' . $coltopics['topic_id'] . '=' . DataUtil::formatForStore($id);
                        $res = $dbconn->Execute($sql);
                        if($dbconn->ErrorNo()==0 && !$result->EOF) {
                            list($title, $forum_id, $cat_id) = $res->fields;
                            if(allowedtoreadcategoryandforum($cat_id, $forum_id)) {
                                $url   = DataUtil::formatForDisplay(pnModURL('pnForum', 'user', 'viewtopic', array('topic' => $id)));
                                $title = DataUtil::formatForDisplay($title);
                                $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
                            } else {
                                $cache[$nid] = '<em>' . DataUtil::formatForDisplay(_PNFORUM_NEEDLE_NOAUTHFORTOPIC . ' (' . $id . ')') . '</em>';
                            }
                        } else {
                            $cache[$nid] = '<em>' . DataUtil::formatForDisplay(_PNFORUM_NEEDLE_UNKNOWNTOPIC . ' (' . $id . ')') . '</em>';
                        }
                        break;
                    default:
                        $cache[$nid] = '<em>' . DataUtil::formatForDisplay(_PNFORUM_NEEDLE_UNKNOWNTYPE) . '</em>';
                }
            } else {
                $cache[$nid] = '<em>' . DataUtil::formatForDisplay(_PNFORUM_NEEDLE_NOTAVAILABLE) . '</em>';
            }    
        }
        $result = $cache[$nid];
    } else {
        $result = '<em>' . DataUtil::formatForDisplay(_PNFORUM_NEEDLE_NONEEDLEID) . '</em>';
    }
    return $result;
    
}
