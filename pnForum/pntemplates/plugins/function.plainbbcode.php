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
 *@params $params $images boolean if true then show images instead of text links
 */
function smarty_function_plainbbcode($params, &$smarty) 
{
    extract($params); 
	unset($params);

	if(pnModAvailable('pn_bbcode')) {	
    	// language 
	    $lang =  pnVarPrepForOS(pnUserGetLang());

        $out = "<br />\n";
        $out .= ""._PNFORUM_USEBBCODE."<br />\n";
        if($images==true) {
            $out .= bb_button("url", _PNFORUM_BBCODE_URL_HINT, "w", "bb_url.gif", $lang);
            $out .= bb_button("email", _PNFORUM_BBCODE_MAIL_HINT, "m", "bb_email.gif", $lang);
            $out .= bb_button("image", _PNFORUM_BBCODE_IMAGE_HINT, "p", "bb_image.gif", $lang);
            $out .= bb_button("quote", _PNFORUM_BBCODE_QUOTE_HINT, "q", "bb_quote.gif", $lang);
            $out .= bb_button("code", _PNFORUM_BBCODE_CODE_HINT, "c", "bb_code.gif", $lang);
            $out .= "<br/>\n";
            $out .= bb_button("listopen", _PNFORUM_BBCODE_LISTOPEN_HINT, "l", "bb_openlist.gif", $lang);
            $out .= bb_button("listitem", _PNFORUM_BBCODE_LISTITEM_HINT, "o", "bb_listitem.gif", $lang);
            $out .= bb_button("listclose", _PNFORUM_BBCODE_LISTCLOSE_HINT, "x", "bb_closelist.gif", $lang);
            $out .= bb_button("bold", _PNFORUM_BBCODE_BOLD_HINT, "b", "bb_bold.gif", $lang);
            $out .= bb_button("italic", _PNFORUM_BBCODE_ITALIC_HINT, "i", "bb_italic.gif", $lang);
            $out .= bb_button("underline", _PNFORUM_BBCODE_UNDERLINE_HINT, "u", "bb_underline.gif", $lang);
        } else {
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_URL_HINT)."\" type=\"button\" accesskey=\"w\" name=\"url\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_URL)." \" style=\"text-decoration: underline; width: 50px;\" onClick=\"DoPrompt('url')\">\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_MAIL_HINT)."\" type=\"button\" accesskey=\"m\" name=\"mail\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_MAIL)." \" style=\"text-decoration: underline; width: 50px;\" onClick=\"DoPrompt('email')\">\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_IMAGE_HINT)."\" type=\"button\" accesskey=\"p\" name=\"image\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_IMAGE)." \" style=\"width: 50px;\" onClick=\"DoPrompt('image')\">\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_QUOTE_HINT)."\" type=\"button\" accesskey=\"q\" name=\"quote\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_QUOTE)." \" style=\"width: 50px;\" onClick=\"DoPrompt('quote')\">\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_CODE_HINT)."\" type=\"button\" accesskey=\"c\" name=\"code\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_CODE)." \" style=\"width: 50px;\" onClick=\"DoPrompt('code')\">\n";
            $out .= "<br/>\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_LISTOPEN_HINT)."\" type=\"button\" accesskey=\"l\" name=\"listopen\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_LISTOPEN)." \" style=\"width: 40px;\" onClick=\"DoPrompt('listopen')\">\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_LISTITEM_HINT)."\" type=\"button\" accesskey=\"o\" name=\"listitem\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_LISTITEM)." \" style=\"width: 40px;\" onClick=\"DoPrompt('listitem')\">\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_LISTCLOSE_HINT)."\" type=\"button\" accesskey=\"x\" name=\"listclose\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_LISTCLOSE)." \" style=\"width: 40px;\" onClick=\"DoPrompt('listclose')\">\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_BOLD_HINT)."\" type=\"button\" accesskey=\"b\" name=\"bold\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_BOLD)." \" style=\"font-weight:bold; width: 40px;\" onClick=\"DoPrompt('bold')\">\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_ITALIC_HINT)."\" type=\"button\" accesskey=\"i\" name=\"italic\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_ITALIC)." \" style=\"font-style: italic; width: 40px;\" onClick=\"DoPrompt('italic')\">\n";
            $out .= "<input title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_UNDERLINE_HINT)."\" type=\"button\" accesskey=\"u\" name=\"underline\" value=\" ".pnVarPrepForDisplay(_PNFORUM_BBCODE_UNDERLINE)." \" style=\"text-decoration: underline; width: 40px;\" onClick=\"DoPrompt('underline')\">\n";
        }
        $out .= "<br />";
        if(pnModGetVar('pn_bbcode', 'color_enabled')=="yes") {
            $out .= pnVarPrepForDisplay(_PNFORUM_BBCODE_FONTCOLOR).":\n";
            $out .= "<select title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_HINT)."\" name=\"fontcolor\" onChange=\"DoColor(this.form.fontcolor.options[this.form.fontcolor.selectedIndex].value)\">\n";
            $out .= "    <option style=\"color:black; background-color: #FFFFFF \" value=\"black\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_DEFAULT)."</option>\n";
            $out .= "    <option style=\"color:darkred; background-color: #DEE3E7\" value=\"darkred\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_DARKRED)."</option>\n";
            $out .= "    <option style=\"color:red; background-color: #DEE3E7\" value=\"red\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_RED)."</option>\n";
            $out .= "    <option style=\"color:orange; background-color: #DEE3E7\" value=\"orange\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_ORANGE)."</option>\n";
            $out .= "    <option style=\"color:brown; background-color: #DEE3E7\" value=\"brown\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_BROWN)."</option>\n";
            $out .= "\n";
            $out .= "    <option style=\"color:yellow; background-color: #DEE3E7\" value=\"yellow\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_YELLOW)."</option>\n";
            $out .= "    <option style=\"color:green; background-color: #DEE3E7\" value=\"green\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_GREEN)."</option>\n";
            $out .= "    <option style=\"color:olive; background-color: #DEE3E7\" value=\"olive\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_OLIVE)."</option>\n";
            $out .= "    <option style=\"color:cyan; background-color: #DEE3E7\" value=\"cyan\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_CYAN)."</option>\n";
            $out .= "    <option style=\"color:blue; background-color: #DEE3E7\" value=\"blue\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_BLUE)."</option>\n";
            $out .= "    <option style=\"color:darkblue; background-color: #DEE3E7\" value=\"darkblue\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_DARKBLUE)."</option>\n";
            $out .= "\n";
            $out .= "    <option style=\"color:indigo; background-color: #DEE3E7\" value=\"indigo\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_INDIGO)."</option>\n";
            $out .= "    <option style=\"color:violet; background-color: #DEE3E7\" value=\"violet\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_VIOLET)."</option>\n";
            $out .= "    <option style=\"color:white; background-color: #DEE3E7\" value=\"white\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_WHITE)."</option>\n";
            $out .= "    <option style=\"color:black; background-color: #DEE3E7\" value=\"black\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_BLACK)."</option>\n";
            if(pnModGetVar('pn_bbcode', 'allow_usercolor')=="yes") {
                $out .= "    <option style=\"color:black; background-color: #DEE3E7\" value=\"#".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_TEXTCOLORCODE)."\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_COLOR_DEFINE)."</option>\n";
            }
            $out .= "</select>&nbsp;\n";
        }
        if(pnModGetVar('pn_bbcode', 'size_enabled')=="yes") {
            $out .= pnVarPrepForDisplay(_PNFORUM_BBCODE_FONTSIZE).":\n";
            $out .= "<select title=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_SIZE_HINT)."\" name=\"fontsize\" onChange=\"DoSize(this.form.fontsize.options[this.form.fontsize.selectedIndex].value)\">\n";
            $out .= "    <option value=\"tiny\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_SIZE_TINY)."</option>\n";
            $out .= "    <option value=\"small\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_SIZE_SMALL)."</option>\n";
            $out .= "    <option value=\"normal\" selected=\"selected\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_SIZE_NORMAL)."</option>\n";
            $out .= "    <option value=\"large\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_SIZE_LARGE)."</option>\n";
            $out .= "    <option value=\"huge\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_SIZE_HUGE)."</option>\n";
            if(pnModGetVar('pn_bbcode', 'allow_usersize')=="yes") {
                $out .= "    <option value=\"".pnVarPrepForDisplay(_PNFORUM_BBCODE_SIZE_TEXTSIZE)."\">".pnVarPrepForDisplay(_PNFORUM_BBCODE_SIZE_DEFINE)."</option>\n";
            }
            $out .= "</select>";
        }


	}
    return $out;
}

function bb_button($name, $title, $key, $image, $lang)
{
    if(file_exists("modules/pnForum/pnimages/$lang/$image")) {
        $imgfile = "modules/pnForum/pnimages/$lang/$image";   
    } else if(file_exists("modules/pnForum/pnimages/$image")) {
        $imgfile = "modules/pnForum/pnimages/$image";
    }
    $attr = getimagesize($imgfile);
    
    return "<button name=\"".pnVarPrepForDisplay($name)."\" type=\"button\" value=\"".pnVarPrepForDisplay($name)."\"
            style=\"border:none; background: transparent;\"
            accesskey=\"$key\" onClick=\"DoPrompt('".pnVarPrepForDisplay($name)."')\">
            <img src=\"$imgfile\" ".$attr[3]." alt=\"".pnVarPrepForDisplay($title)."\">
            </button>";
}

?>