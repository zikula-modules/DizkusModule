<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

Loader::includeOnce('modules/Dizkus/common.php');

/**
 * Dizkus needle
 * @param $args['nid'] needle id
 * @return array()
 */
function Dizkus_needleapi_pnforum($args)
{
    $dom = ZLanguage::getModuleDomain('Dizkus');

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

            if (pnModAvailable('Dizkus'))
            {
                // nid is like F_## or T_##
                $temp = explode('-', $nid);
                $type = '';
                if (is_array($temp) && count($temp)==2) {
                    $type = $temp[0];
                    $id   = $temp[1];
                }

                pnModDBInfoLoad('Dizkus');
                $dbconn =& pnDBGetConn(true);
                $pntable =& pnDBGetTables();

                switch ($type)
                {
                    case 'F':
                        $tblforums = $pntable['dizkus_forums'];
                        $colforums = $pntable['dizkus_forums_column'];
                        
                        $sql = 'SELECT ' . $colforums['forum_name'] . ',
                                       ' . $colforums['cat_id'] . '
                                FROM   ' . $tblforums . '
                                WHERE  ' . $colforums['forum_id'] . '=' . (int)DataUtil::formatForStore($id);
                        $res = $dbconn->Execute($sql);
                        if ($dbconn->ErrorNo()==0 && !$res->EOF) {
                            list($title, $cat_id) = $res->fields;
                            if (allowedtoreadcategoryandforum($cat_id, $id)) {
                                $url   = DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewforum', array('forum' => $id)));
                                $title = DataUtil::formatForDisplay($title);
                                $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
                            } else {
                                $cache[$nid] = '<em>' . __f('Sorry! You do not have the necessary authorisation for forum ID %s.', $id, $dom) . '</em>';
                            }
                        } else {
                            $cache[$nid] = '<em>' . __f('Error! Forum ID %s is unknown.', $id, $dom) . '</em>';
                        }
                        break;

                    case 'T':
                        $tbltopics = $pntable['dizkus_topics'];
                        $coltopics = $pntable['dizkus_topics_column'];
                        $tblforums = $pntable['dizkus_forums'];
                        $colforums = $pntable['dizkus_forums_column'];
                        
                        $sql = 'SELECT    ' . $coltopics['topic_title'] . ',
                                          ' . $coltopics['forum_id'] . ',
                                          ' . $colforums['cat_id'] . ' 
                                FROM      ' . $tbltopics . '
                                LEFT JOIN ' . $tblforums . '
                                ON        ' . $colforums['forum_id'] . '=' . $coltopics['forum_id'] . '
                                WHERE     ' . $coltopics['topic_id'] . '=' . DataUtil::formatForStore($id);
                        $res = $dbconn->Execute($sql);
                        if ($dbconn->ErrorNo()==0 && !$result->EOF) {
                            list($title, $forum_id, $cat_id) = $res->fields;
                            if (allowedtoreadcategoryandforum($cat_id, $forum_id)) {
                                $url   = DataUtil::formatForDisplay(pnModURL('Dizkus', 'user', 'viewtopic', array('topic' => $id)));
                                $title = DataUtil::formatForDisplay($title);
                                $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
                            } else {
                                $cache[$nid] = '<em>' . __f('Sorry! You do not have the necessary authorisation for topic ID %s.', $id , $dom) . '</em>';
                            }
                        } else {
                            $cache[$nid] = '<em>' . __f('Error! Topic ID %s is unknown.', $id, $dom) .'</em>';
                        }
                        break;

                    default:
                        $cache[$nid] = '<em>' . __('Error! Unknown parameter at pos.1 (F or T)', $dom) . '</em>';
                }
            } else {
                $cache[$nid] = '<em>' . __('Error! Dizkus is not available.', $dom) . '</em>';
            }    
        }
        $result = $cache[$nid];
    } else {
        $result = '<em>' . __('Error! No needle ID.', $dom) . '</em>';
    }

    return $result;
}
