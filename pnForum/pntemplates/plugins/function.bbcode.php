<?php
// $Id$
// ----------------------------------------------------------------------
// PostNuke Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------

/**
 * bbcode plugin
 * shows all available bbcode tags
 *
 */
function smarty_function_bbcode($params, &$smarty) 
{
    extract($params); 
	unset($params);

	if(pnModAvailable('pn_bbcode') && pnModGetVar('pnForum', 'show_bbcode') == "yes") {	
    	// get the correct modfolder
    	$modInfo = pnModGetInfo(pnModGetIDFromName(pnModGetName()));
    	$modDir = pnVarPrepForOS($modInfo['directory']);
    	// get the corresponding language
    	$lang = pnVarPrepForOS(pnUserGetLang());
    	// build up the path
    	$bbcodefolder = "modules/$modDir/pnimages/$lang/bbcode";
    
        $out = "<br />\n";
        $out .= ""._PNFORUM_USEBBCODE."<br />\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('url');\" onkeypress=\"DoPrompt('url');\"><IMG src=\"$bbcodefolder/b_url.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode URL\"></a>\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('email');\" onkeypress=\"DoPrompt('email');\"><IMG src=\"$bbcodefolder/b_email.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode: Email\"></a>\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('image');\" onkeypress=\"DoPrompt('image');\"><IMG src=\"$bbcodefolder/b_image.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode: Bild Image\"></a>\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('bold');\" onkeypress=\"DoPrompt('bold');\"><IMG src=\"$bbcodefolder/b_bold.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode: bold\"></a>\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('italic');\" onkeypress=\"DoPrompt('italic');\"><IMG src=\"$bbcodefolder/b_italic.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode: italic\"></a>\n";
        $out .= "<br/>\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('quote');\" onkeypress=\"DoPrompt('quote');\"><IMG src=\"$bbcodefolder/b_quote.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode: Quote\"></a>\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('code');\" onkeypress=\"DoPrompt('code');\"><IMG src=\"$bbcodefolder/b_code.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode: Code\"></a>\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('listopen');\" onkeypress=\"DoPrompt('listopen');\"><IMG src=\"$bbcodefolder/b_listopen.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode: List open\"></a>\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('listitem');\" onkeypress=\"DoPrompt('listitem');\"><IMG src=\"$bbcodefolder/b_listitem.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode: Listitem\"></a>\n";
        $out .= "<a href=\"javascript: x()\" onClick=\"DoPrompt('listclose');\" onkeypress=\"DoPrompt('listclose');\"><IMG src=\"$bbcodefolder/b_listclose.gif\" width=\"59\" height=\"18\" border=\"0\" alt=\"BBCode: List close\"></a>\n";
	}
    return $out;
}

?>