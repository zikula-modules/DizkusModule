<?php
/**
 * title definitions for pnTitle hack by jn (http://lottasophie.sf.net)
 * @version $Id$
 * @author Andreas Krapohl 
 * @copyright 2003 by Andreas Krapohl, Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html> 
 * @link http://www.pnforum.de
 */

/**
 * get the title
 */
function pnForum_title() {
    list ($func, $topic, $forum) = pnVarCleanFromInput('func', 'topic', 'forum');

	pnModDBInfoLoad('pnForum');
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

    if ($func == 'viewtopic') {
        $column = &$pntable['pnforum_column'];
	 	$sql = "SELECT t.topic_title, f.forum_name, c.cat_title
                FROM  ".$pntable['pnforum_topics']." t
                LEFT JOIN ".$pntable['pnforum_forums']." f ON f.forum_id = t.forum_id
                LEFT JOIN ".$pntable['pnforum_categories']." AS c ON c.cat_id = f.cat_id
                WHERE t.topic_id = '".(int)pnVarPrepForStore($topic)."'";
		$result = $dbconn->Execute($sql);
        list($title,$forum_name,$cat_title) = $result->fields;
        $result->Close();
        $title = $cat_title." :: ".$forum_name." :: ".$title;
    } elseif ($func == 'viewforum') { 
        $column = &$pntable['pnforum_column'];
		$sql = "SELECT f.forum_name, c.cat_title
                FROM $pntable[pnforum_forums] f
                LEFT JOIN ".$pntable['pnforum_categories']." AS c ON c.cat_id = f.cat_id
                WHERE forum_id = '".(int)pnVarPrepForStore($forum)."'";
		$result = $dbconn->Execute($sql);
        list($forum_name,$cat_title) = $result->fields;
        $result->Close();
        $title = $cat_title." :: ".$forum_name;
	} else {
        $title = 'pnForum';
    }
    return  pnConfigGetVar('sitename').' - '.$title;
}
?>
