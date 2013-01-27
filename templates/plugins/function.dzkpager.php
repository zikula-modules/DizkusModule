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
 * dzkpager plugin
 * creates a forum or a topic pager
 *
 * @param $params['objectid']     int the id of the obejct to page, maybe a topic or forum id. If not supplied, this will be taken from the url
 * @param $params['total']        int total number of topics in this forum
 * @param $params['separator']    string  text to show between the pages, default |
 * @param $params['add_prevnext'] bool add -100 -10 -1 +1 -10 +100 links if needed, default true
 * @param $params['linkall']      bool makes the recent page a link too, useful when linking to a topicpager in a forum view
 *                               default false
 * @param $params['force'         string force the pager to user this function for building the urls, also useful
 *                               for linking to a topic in a forum view
 *                               possible values: viewforum, viewtopic, moderateforum
 *                               default: taken from the func parameter in the url
 * @param $params['tag']          string if true, the pager output is using these surrounding tags
 *                               default paragraph
 *
 * This logic is taken from phpbb.
 *
 */
function smarty_function_dzkpager($params, &$smarty)
{
    $total = $params['total'];

    // check if we are in view or moderate mode
    $func = isset($params['force']) ? $params['force'] : FormUtil::getPassedValue('func');
    switch ($func)
    {
        case 'viewforum':
        case 'moderateforum':
            $per_page = ModUtil::getVar('Dizkus', 'topics_per_page');
            $objectname = 'forum';
            $objectid = isset($params['objectid']) ? $params['objectid'] : FormUtil::getPassedValue('forum');
            break;

        case 'viewtopic':
            $per_page = ModUtil::getVar('Dizkus', 'posts_per_page');
            $objectname = 'topic';
            $objectid = isset($params['objectid']) ? $params['objectid'] : FormUtil::getPassedValue('topic');
            break;

        default:
            // silently stop....
            return '';
    }

    $start = FormUtil::getPassedValue('start', 1);
    $add_prevnext = (isset($params['add_prevnext']) && ($params['add_prevnext']==false)) ? false : true;
    $linkall      = (isset($params['linkall']) && ($params['linkall']==true)) ? true : false;
    $separator    = (isset($params['separator']) && !empty($params['separator'])) ? $params['separator'] : ' - ';
    $tag          = (isset($params['tag']) && !empty($params['tag'])) ? $params['tag'] : 'p';

    $total_pages = ceil($total/$per_page);
    if ($total_pages == 1) {
        return '';
    }
    $on_page = floor($start / $per_page) + 1;

    $page_string = '';
    if ( $total_pages > 10 ) {
        $init_page_max = ( $total_pages > 3 ) ? 3 : $total_pages;

        for ($i = 1; $i < $init_page_max + 1; $i++) {
            $page_string .= (($i == $on_page) && ($linkall==false)) ? '<strong>' . $i . '</strong>' : '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';
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
                    $page_string .= (($i == $on_page) && ($linkall==false)) ? '<strong>' . $i . '</strong>' : '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';
                    if ( $i <  $init_page_max + 1 ) {
                        $page_string .= $separator;
                    }
                }

                $page_string .= ( $on_page < $total_pages - 4 ) ? ' ... ' : $separator;
            } else  {
                $page_string .= ' ... ';
            }

            for ($i = $total_pages - 2; $i < $total_pages + 1; $i++) {
                $page_string .= (($i == $on_page) && ($linkall==false)) ? '<strong>' . $i . '</strong>'  : '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';
                if ( $i <  $total_pages ) {
                    $page_string .= $separator;
                }
            }
        }
    } else {
        for ($i = 1; $i < $total_pages + 1; $i++) {
            $page_string .= (($i == $on_page) && ($linkall==false)) ? '<strong>' . $i . '</strong>' : '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => ( $i - 1 ) * $per_page ))) . '">' . $i . '</a>';
            if ( $i <  $total_pages ) {
                $page_string .= $separator;
            }
        }
    }

    $add_prev_set = false;
    $add_next_set = false;
    if ($add_prevnext==true) {
        if ( $on_page > 1 ) {
            $page_string = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => ( $on_page - 2 ) * $per_page ))) . '">-1</a>] ' . $page_string;
            $add_prev_set = true;
        }
        if ( $on_page > 10 ) {
            $page_string = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => ( $on_page - 11) * $per_page ))) . '">-10</a> ' . $page_string;
            $add_prev_set = true;
        }
        if ( $on_page > 100 ) {
            $page_string = '<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => ( $on_page - 101) * $per_page ))) . '">-100</a> ' . $page_string;
            $add_prev_set = true;
        }
        if ($add_prev_set == true) {
            $page_string = '[' . $page_string;
        }

        if ( $on_page < $total_pages ) {
            $page_string .= ' [<a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => $on_page * $per_page ))) . '">+1</a>';
            $add_next_set = true;
        }
        if ($total_pages - $on_page > 10) {
            $page_string .= ' <a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => ($on_page + 9) * $per_page ))) . '">+10</a>';
            $add_next_set = true;
        }
        if ($total_pages - $on_page > 100) {
            $page_string .= ' <a href="' . DataUtil::formatForDisplay(ModUtil::url('Dizkus', 'user', $func, array($objectname => $objectid, 'start' => ($on_page + 99) * $per_page ))) . '">+100</a>';
            $add_next_set = true;
        }
        if ($add_next_set == true) {
            $page_string .= ']';
        }

    }

    $page_string = '<' . $tag . '>' . __f('Go to page %s: ', $page_string) . '</' . $tag . '>';

    return $page_string;
}
