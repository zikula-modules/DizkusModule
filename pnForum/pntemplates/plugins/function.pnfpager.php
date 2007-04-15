<?php
// $Id: function.forumpager.php 505 2006-03-11 14:35:55Z landseer $
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
 * pnfpager plugin
 * creates a forum or a topic pager
 *
 *@param $params['total'] int total number of topics in this forum
 *@param $params['separator'] string  text to show between the pages, default |
 *@param $params['add_prevnext'] bool add -100 -10 -1 +1 -10 +100 links if needed, default true
 *@param $params['linkall'] bool makes the recent page a link too, useful when linking to a topicpager in a forum view
 *                          default false
 *@param $params['force'] string force the pager to user this function for building the urls, also useful
 *                        for linking to a topic in a forum view
 *                        possible values: viewforum, viewtopic, moderateforum
 *                        default: taken from the func parameter in the url
 *
 * This logic is taken from phpbb.
 *
 */
function smarty_function_pnfpager($params, &$smarty)
{                  
    $total = $params['total'];

    // check if we are in view or moderate mode
    $force = $params['force'];
    
    $func = isset($params['force']) ? $params['force'] : pnVarCleanFromInput('func');
    switch($func) {
        case 'viewforum':
        case 'moderateforum':
            $per_page = pnModGetVar('pnForum', 'topics_per_page');
            $objectname = 'forum';
            $objectid = pnVarCleanFromInput('forum');
            break;
        case 'viewtopic':
            $per_page = pnModGetVar('pnForum', 'posts_per_page');
            $objectname = 'topic';
            $objectid = pnVarCleanFromInput('topic');
            break;
        default:
            // silently stop....
            return '';
    }

    $start = pnVarCleanFromInput('start');
    if(empty($start)) {
        $start= 1;
    }
    $add_prevnext = (isset($params['add_prevnext']) && ($params['add_prevnext']==false)) ? false : true;
    $linkall      = (isset($params['linkall']) && ($params['linkall']==true)) ? true : false;
    $separator    = (isset($params['separator']) && !empty($params['separator'])) ? $params['separator'] : ' - ';

    $total_pages = ceil($total/$per_page);                                                                                                                             
    if ( $total_pages == 1 ) {                                                                                                                                                                      
        return '';                                                                                                                                                             
    }                                                                                                                                                                      
    $on_page = floor($start / $per_page) + 1;                                                                                                                         
                                                                                                                                                                          
    $page_string = '';                                                                                                                                                     
    if ( $total_pages > 10 ) {                                                                                                                                                                      
        $init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;                                                                                                              
                                                                                                                                                                           
        for($i = 1; $i < $init_page_max + 1; $i++) {                                                                                                                            
            $page_string .= (($i == $on_page) && ($linkall==false)) ? '<strong>' . $i . '</strong>' : '<a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';     
            if ( $i <  $init_page_max ) {                                                                                                                                           
                $page_string .= $separator;                                                                                                                                                  
            }                                                                                                                                                                      
        }                                                                                                                                                                      
                                                                                                                                                                           
        if ( $total_pages > 3 ) {                                                                                                                                               
            if ( $on_page > 1  && $on_page < $total_pages ) {                                                                                                                       
                $page_string .= ( $on_page > 5 ) ? ' ... ' : $separator;                                                                                                                     
                                                                                                                                                                           
                $init_page_min = ( $on_page > 4 ) ? $on_page : 5;                                                                                                                      
                $init_page_max = ( $on_page < $total_pages - 4 ) ? $on_page : $total_pages - 4;                                                                                        
                                                                                                                                                                           
                for($i = $init_page_min - 1; $i < $init_page_max + 2; $i++) {    
                    $page_string .= (($i == $on_page) && ($linkall==false)) ? '<strong>' . $i . '</strong>' : '<a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';       
                    if ( $i <  $init_page_max + 1 ) {                                                                                                                                       
                        $page_string .= $separator;                                                                                                                                                  
                    }                                                                                                                                                                      
                }                                                                                                                                                                      
                                                                                                                                                                           
                $page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : $separator;                                                                                                      
            } else  {                                                                                                                                                                      
                $page_string .= ' ... ';                                                                                                                                               
            }                                                                                                                                                                      
                                                                                                                                                                           
            for($i = $total_pages - 2; $i < $total_pages + 1; $i++) {                                                                                                                                                                      
                $page_string .= (($i == $on_page) && ($linkall==false)) ? '<strong>' . $i . '</strong>'  : '<a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';    
                if( $i <  $total_pages ) {                                                                                                                                                                      
                    $page_string .= $separator;                                                                                                                                                  
                }                                                                                                                                                                      
            }                                                                                                                                                                      
        }                                                                                                                                                                      
    } else {                                                                                                                                                                      
        for($i = 1; $i < $total_pages + 1; $i++) {                                                                                                                                                                      
            $page_string .= (($i == $on_page) && ($linkall==false)) ? '<strong>' . $i . '</strong>' : '<a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';     
            if ( $i <  $total_pages ) {                                                                                                                                                                      
                $page_string .= $separator;                                                                                                                                                  
            }                                                                                                                                                                      
        }                                                                                                                                                                      
    }                                                                                                                                                                      
      
    $add_prev_set = false;                                                                                                                                                                     
    $add_next_set = false;                                                                                                                                                                     
    if($add_prevnext==true) {                                                                                                                                                                      
        if ( $on_page > 1 ) {                                                                                                                                                                      
            $page_string = '<a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => ( $on_page - 2 ) * $per_page ))) . '">-1</a>] ' . $page_string;
            $add_prev_set = true;
        }                                                                                                                                                                      
        if ( $on_page > 10 ) {                                                                                                                                                                      
            $page_string = '<a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => ( $on_page - 11) * $per_page ))) . '">-10</a> ' . $page_string;
            $add_prev_set = true;
        }                                                                                                                                                                      
        if ( $on_page > 100 ) {                                                                                                                                                                      
            $page_string = '<a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => ( $on_page - 101) * $per_page ))) . '">-100</a> ' . $page_string;
            $add_prev_set = true;
        }                                                                                                                                                                      
        if($add_prev_set == true) {
            $page_string = '[' . $page_string;
        }
                                                                                                                                                                           
        if ( $on_page < $total_pages ) {                                                                                                                                                                      
            $page_string .= ' [<a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => $on_page * $per_page ))) . '">+1</a>';                           
            $add_next_set = true;
        }
        if($total_pages - $on_page > 10) {
            $page_string .= ' <a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => ($on_page + 9) * $per_page ))) . '">+10</a>';                           
            $add_next_set = true;
        }                                                                                                                                                                      
        if($total_pages - $on_page > 100) {
            $page_string .= ' <a href="' . pnVarPrepForDisplay(pnModURL('pnForum', 'user', $func, array($objectname => $objectid, 'start' => ($on_page + 99) * $per_page ))) . '">+100</a>';                           
            $add_next_set = true;
        }                                                                                                                                                                      
        if($add_next_set == true) {
            $page_string .= ']';
        }
                                                                                                                                                                           
    }                                                                                                                                                                      
                                                                                                                                                                           
    $page_string = '<p>' . _PNFORUM_GOTOPAGE . ': ' . $page_string . '</p>';                                                                                                                
                                                                                                                                                                           
    return $page_string;                                                                                                                                                   


}      

?>                                                                                                                                                                