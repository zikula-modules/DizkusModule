<?php
/**
 * forum backend (with permission check)
 * to be placed in the postnuke root
 * @version $Id$
 * @author Andreas Krapohl 
 * @copyright 2003 by Andreas Krapohl
 * @package phpBB_14 (aka pnForum) 
 * @license GPL <http://www.gnu.org/licenses/gpl.html> 
 * @link http://www.pnforum.de
 */

/**
 * initialize the PostNuke environment 
 */
include 'includes/pnAPI.php'; 
pnInit(); 

/**
 * check for counter,  if not set, set the output count to 10
 */
$count = ( !isset($HTTP_GET_VARS['count']) ) ? 10 : intval($HTTP_GET_VARS['count']);
$count = ( $count == 0 ) ? 10 : $count;

/**
 * set the xml header for output
 */
header("Content-Type: text/xml");

/**
 * build up the header
 */
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n\n";
echo "<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\"\n";
echo " \"http://my.netscape.com/publish/formats/rss-0.91.dtd\">\n\n";

/**
 * open the channel/rss output
 */
echo "<rss version=\"0.91\">\n\n";
echo "<channel>\n";
echo "<title>".pnVarPrepForDisplay(pnConfigGetVar('sitename')). " - Forum</title>\n";
echo "<link>".pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'main'))."</link>\n";
echo "<description>".pnVarPrepForDisplay(pnConfigGetVar('sitename')). " - Forum</description>\n";
echo "<webMaster>".pnVarPrepForDisplay(pnConfigGetVar('adminmail'))."</webMaster>\n";
echo "</channel>";

/**
 * get database information
 */
pnModDBInfoLoad('pnForum');
$dbconn =& pnDBGetConn(true);
$pntable =& pnDBGetTables();

/**
 * SQL statement to fetch last 10 topics
 */
$sql = "SELECT t.topic_id, 
				t.topic_title, 
    			f.forum_name, 
				pt.poster_id,
				c.cat_title
    	FROM ".$pntable['pnforum_topics']." as t, 
    			".$pntable['pnforum_forums']." as f,
				".$pntable['pnforum_posts']." as pt,
				".$pntable['pnforum_categories']." as c
    	WHERE t.forum_id = f.forum_id AND
				t.topic_last_post_id = pt.post_id AND
				f.cat_id = c.cat_id
		ORDER by t.topic_time DESC";
			
$result = $dbconn->Execute($sql);
if($dbconn->ErrorNo() != 0) {
	die("Error accesing to the database " . $dbconn->ErrorNo() . ": " . $dbconn->ErrorMsg() . "<br />");
}
$result_postmax = $result->PO_RecordCount();
if ($result_postmax <= $count) {
	$count = $result_postmax;
}
$shown_results=0;

while ((list($topic_id, $topic_title, $forum_name, $poster_id, $cat_title) = $result->FetchRow())
	  		  && ($shown_results < $count) ) {
	if (pnSecAuthAction(0, 'pnForum::Forum', "$forum_name::", ACCESS_READ) && 
		pnSecAuthAction(0, 'pnForum::Category', "$cat_title::", ACCESS_READ))   { 
		$shown_results++;
		$url = pnVarPrepForDisplay(pnModURL('pnForum', 'user', 'viewtopic', array('topic'=> $topic_id)));
		echo "<item>\n";
		echo "<title>". pnVarPrepHTMLDisplay($topic_title) ."</title>\n";
		echo "<link>". $url ."</link>\n";
		echo "<description>$cat_title :: $forum_name</description>\n";
		echo "</item>\n";
		$result->MoveNext();
    }
}

/**
 * close the channel/rss output
 */
echo "</rss>\n";
?>