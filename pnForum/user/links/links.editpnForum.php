<?php
/**
 * addon for userpage
 * @version $Id:
 * @author Andreas Krapohl
 * @copyright 2003 by Andreas Krapohl
 * @package phpBB_14 (aka pnForum)
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 */

// check if we get called directly via URL, we do not like this
if (eregi(basename(__FILE__), $_SERVER['PHP_SELF'])) {
	die ("You can't access this file directly...");
}

pnModAPILoad('pnForum', 'user');  // only to load the language file, so that we do not need the lang folder :-)
$modInfo = pnModGetInfo(pnModGetIDFromName('pnForum'));
user_menu_add_option("user.php?op=editpnForum", ""._PNFORUM_FORUM."", "modules/$modInfo[directory]/pnimages/admin.gif");

?>