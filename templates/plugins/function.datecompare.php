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
 * Zikula_View plugin
 * This file is a plugin for Zikula_View, the Zikula implementation of Smarty
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
