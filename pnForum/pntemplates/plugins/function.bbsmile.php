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
 * bbsmile plugin
 * shows all available smilies
 *
 */
function smarty_function_bbsmile($params, &$smarty) 
{
    extract($params); 
	unset($params);

	if(pnModAvailable('pn_bbsmile')) {
		//	display smilies and bbcodes
    	$imagepath = pnModGetVar('pn_bbsmile', 'smiliepath');
        $out = "<br />\n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :-) ')\" onkeypress=\"DoSmilie(' :-) ')\" title=':-)'><img src='$imagepath/icon_smile.gif' border='0' hspace='3' vspace='3' alt='Smilie :-)'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :-( ')\" onkeypress=\"DoSmilie(' :-( ')\" title=':-('><img src='$imagepath/icon_frown.gif' border='0' hspace='3' vspace='3' alt='Smilie :-('></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :-D ')\" onkeypress=\"DoSmilie(' :-D ')\" title=':-D'><img src='$imagepath/icon_biggrin.gif' border='0' hspace='3' vspace='3' alt='Smilie :-D'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' ;-) ')\" onkeypress=\"DoSmilie(' ;-) ')\" title=';-)'><img src='$imagepath/icon_wink.gif' border='0' hspace='3' vspace='3' alt='Smilie ;-)'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :-O ')\" onkeypress=\"DoSmilie(' :-O ')\" title=':-O'><img src='$imagepath/icon_eek.gif' border='0' hspace='3' vspace='3' alt='Smilie :-O'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' 8-) ')\" onkeypress=\"DoSmilie(' 8-) ')\" title='8-)'><img src='$imagepath/icon_cool.gif' border='0' hspace='3' vspace='3' alt='Smilie 8-)'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :-? ')\" onkeypress=\"DoSmilie(' :-? ')\" title=':-?'><img src='$imagepath/icon_confused.gif' border='0' hspace='3' vspace='3' alt='Smilie :-?'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :oops: ')\" onkeypress=\"DoSmilie(' :oops: ')\" title=':oops:'><img src='$imagepath/icon_redface.gif' border='0' hspace='3' vspace='3' alt='Smilie :oops:'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :lol: ')\" onkeypress=\"DoSmilie(' :lol: ')\" title=':lol:'><img src='$imagepath/icon_lol.gif' border='0' hspace='3' vspace='3' alt='Smilie :lol:'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :-P ')\" onkeypress=\"DoSmilie(' :-P ')\" title=':-P'><img src='$imagepath/icon_razz.gif' border='0' hspace='3' vspace='3' alt='Smilie :-P'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :roll: ')\" onkeypress=\"DoSmilie(' :roll: ')\" title=':roll:'><img src='$imagepath/icon_rolleyes.gif' border='0' hspace='3' vspace='3' alt='Smilie :roll:'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :-x ')\" onkeypress=\"DoSmilie(' :-x ')\" title=':-x'><img src='$imagepath/icon_mad.gif' border='0' hspace='3' vspace='3' alt='Smilie :-x'></a> \n";
    	$out .= "<a href=\"javascript: x()\" onClick=\"DoSmilie(' :evil: ')\" onkeypress=\"DoSmilie(' :evil: ')\" title=':evil:'><img src='$imagepath/icon26.gif' border='0' hspace='3' vspace='3' alt='Smilie :evil:'></a> \n";
	}
    return $out;
}

?>