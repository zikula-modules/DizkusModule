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

include_once "modules/pnForum/common.php";

/**
 * simplepager
 * A simple copy of the pnHTML pager :-)
 *
 *@params $params['startnum'] int
 *@params $params['total'] int
 *@params $params['urltemplate'] string
 *@params $params['perpage'] int
 */
function smarty_function_simplepager($params, &$smarty) 
{
    extract($params);
    unset($params);
    // Quick check to ensure that we have work to do
    if (empty($perpage)) {
        $perpage = 10;
    }

    if ($total <= $perpage) {
        return;
    }
    $compoutput = "";

    if (empty($startnum)) {
        $startnum = 1;
    }

    // Make << and >> do paging properly
    // Display subset of pages if large number

    // Show startnum link
    if ($startnum != 1) {
        $url = preg_replace('/%%/', 1, $urltemplate);
        $compoutput .= "<a href=\"$url\" title=\"1\"><<</a>"; // ->URL($url, '<<');
    } else {
        $compoutput .= "<<";
    }
    $compoutput .= " ";

    // Show following items 
    $pagenum = 1; 

    for ($curnum = 1; $curnum <= $total; $curnum += $perpage) 
    { 
        if (($startnum < $curnum) || ($startnum > ($curnum + $perpage - 1))) 
        { 
            //mod by marsu - use sliding window for pagelinks 
            if ((($pagenum%10)==0) // link if page is multiple of 10 
                    || ($pagenum==1) // link first page 
                    || (($curnum >($startnum-6*$perpage)) //link -5 and +5 pages 
                    &&($curnum <($startnum+6*$perpage))) 
            ) { 
            // Not on this page - show link 
            $url = preg_replace('/%%/', $curnum, $urltemplate); 
            $compoutput .= "<a href=\"$url\" title=\"".pnVarPrepForDisplay(_PNFORUM_PAGE)." $pagenum\">$pagenum</a>";
            $compoutput .= " "; 
            } 
            //end mod by marsu 
        } else { 
            // On this page - show text 
            $compoutput .= $pagenum . " "; 
        } 
        $pagenum++; 
    } 
    if (($curnum >= $perpage + 1) && ($startnum < $curnum - $perpage)) {
        $temp = $curnum - $perpage;
        $url = preg_replace('/%%/', $curnum - $perpage, $urltemplate);
        $compoutput .= "<a href=\"$url\" title=\"".pnVarPrepForDisplay(_PNFORUM_PAGE)." $temp\">>></a>";
    } else {
        $compoutput .= ">>";
    }
    return $compoutput;
}
?>