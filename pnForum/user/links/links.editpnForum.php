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

pnModAPILoad('pnForum', 'user');  // only to load the language file, so that we do not need the lang folder :-)
$modInfo = pnModGetInfo(pnModGetIDFromName('pnForum'));
user_menu_add_option("user.php?op=editpnForum", ""._PNFORUM_FORUM."", "modules/$modInfo[directory]/pnimages/admin.gif");
?>