<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * forumpager plugin
 * creates a forum pager
 *
 * @param $params['total'] int total number of topics in this forum
 * @param $params['forum_id'] int forum id
 * @param $params['start'] int start value
 * @param $params['separator'] string  text to show between the pages, default |
 * @param $params['add_prevnext'] bool add -100 -10 -1 +1 -10 +100 links if needed, default true
 *
 */
function smarty_function_forumpager($params, &$smarty)
{
    $total    = $params['total'];
    $per_page = ModUtil::getVar('Dizkus', 'topics_per_page');
    $start    = FormUtil::getPassedValue('start', 1, 'GETPOST');

    $add_prevnext = (isset($params['add_prevnext']) && !empty($params['add_prevnext'])) ? (bool)$params['add_prevnext'] : true;
    $forum_id     = $params['forum_id'];
    if (empty($forum_id)) {
        $smarty->trigger_error("Error! Missing 'forum_id' parameter for forum pager.");
    }

    $separator = (isset($params['separator']) && !empty($params['separator'])) ? $params['separator'] : ' - ';

    // check if we are in view or moderate mode
    $func = FormUtil::getPassedValue('func', 'viewforum', 'GETPOST');
    $func = (($func=='viewforum') || ($func=='moderateforum')) ? $func : 'viewforum';

    $total_pages = ceil($total/$per_page);
    if ( $total_pages == 1 ) {
        return '';
    }
    $on_page = floor($start / $per_page) + 1;

    $page_string = '';
    if ( $total_pages > 10 ) {
        $init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;

        for($i = 1; $i < $init_page_max + 1; $i++) {
            $page_string .= ( $i == $on_page ) ? '<strong>' . $i . '</strong>' : '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';
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
                    $page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';
                    if ( $i <  $init_page_max + 1 ) {

                        $page_string .= $separator;
                    }
                }

                $page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : $separator;
            } else  {
                $page_string .= ' ... ';
            }

            for($i = $total_pages - 2; $i < $total_pages + 1; $i++) {
                $page_string .= ( $i == $on_page ) ? '<strong>' . $i . '</strong>'  : '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';
                if ( $i <  $total_pages ) {
                    $page_string .= $separator;
                }
            }
        }
    } else {
        for($i = 1; $i < $total_pages + 1; $i++) {
            $page_string .= ( $i == $on_page ) ? '<strong>' . $i . '</strong>' : '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';
            if ( $i <  $total_pages ) {
                $page_string .= $separator;
            }
        }
    }

    $add_prev_set = false;
    $add_next_set = false;
    if ( $add_prevnext ) {
        if ( $on_page > 1 ) {
            $page_string = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => ( $on_page - 2 ) * $per_page ))) . '">-1</a>] ' . $page_string;
            $add_prev_set = true;
        }
        if ( $on_page > 10 ) {
            $page_string = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => ( $on_page - 11) * $per_page ))) . '">-10</a> ' . $page_string;
            $add_prev_set = true;
        }
        if ( $on_page > 100 ) {
            $page_string = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => ( $on_page - 101) * $per_page ))) . '">-100</a> ' . $page_string;
            $add_prev_set = true;
        }
        if ($add_prev_set == true) {
            $page_string = '[' . $page_string;
        }

        if ( $on_page < $total_pages ) {
            $page_string .= ' [<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => $on_page * $per_page ))) . '">+1</a>';
            $add_next_set = true;
        }
        if ($total_pages - $on_page > 10) {
            $page_string .= ' <a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => ($on_page + 9) * $per_page ))) . '">+10</a>';
            $add_next_set = true;
        }
        if ($total_pages - $on_page > 100) {
            $page_string .= ' <a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array('forum' => $forum_id, 'start' => ($on_page + 99) * $per_page ))) . '">+100</a>';
            $add_next_set = true;
        }
        if ($add_next_set == true) {
            $page_string .= ']';
        }

    }

    $page_string = '<p>' . __f('Go to page %s: ',$page_string) . '</p>';

    return $page_string;
}
