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

if(pnSecAuthAction(0, 'pnForum::', '::', ACCESS_OVERVIEW)) {
    pnModLangLoad('pnForum', 'user'); 
    $modInfo = pnModGetInfo(pnModGetIDFromName('pnForum'));
    user_menu_add_option("user.php?op=editpnForum", ""._PNFORUM_FORUM."", "modules/" . pnVarPrepForDisplay($modInfo[directory]) . "/pnimages/icon_forumprefs.gif");
}

?>