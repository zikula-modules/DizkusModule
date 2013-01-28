<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link      https://github.com/zikula-modules/Dizkus
 * @license   GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package   Dizkus
 */

/**
 * Renderer plugin
 * This file is a plugin for Renderer, the Zikula implementation of Smarty
 *
 * @package      Xanthia_Templating_Environment
 * @subpackage   Renderer
 * @version      $Id$
 * @author       The Zikula development team
 * @link         http://www.zikula.org  The Zikula Home Page
 * @copyright    Copyright (C) 2002 by the Zikula Development Team
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * Smarty function to compare dates
 */

function smarty_function_datecompare($params, Zikula_View $view)
{
    $date1 = isset($params['date1']) ? (($params['date1'] instanceof DateTime) ? $params['date1'] : new DateTime("@" . strtotime($params['date1']))) : new DateTime();
    $date2 = isset($params['date2']) ? (($params['date2'] instanceof DateTime) ? $params['date2'] : new DateTime("@" . strtotime($params['date2']))) : new DateTime();
    $comp = isset($params['comp']) ? $params['comp'] : "<";

    switch ($comp) {
        case ">":
            $result = ($date1 > $date2);
            break;
        case ">=":
            $result = ($date1 >= $date2);
            break;
        case "==":
            $result = ($date1 == $date2);
            break;
        case "<=":
            $result = ($date1 <= $date2);
            break;
        case "<":
        default:
            $result = ($date1 < $date2);
            break;
    }
//    $d1f = $date1->format('Y-m-d H:i:s');
//    $d2f = $date2->format('Y-m-d H:i:s');
//    return $result ? "$d1f $comp $d2f true" : "$d1f $comp $d2f false";
    
    if (isset($params['assign'])) {
        $view->assign($params['assign'], $result);
    } else {
        return $result;
    }
}
