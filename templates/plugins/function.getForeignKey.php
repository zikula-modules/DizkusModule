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
 * allowedhtml plugin
 * lists all allowed html tags
 *
 */
function smarty_function_getForeignKey($params, Zikula_View $view)
{
    if (!isset($params['entity']) || !isset($params['key'])) {
        return false;
    }
    $em = ServiceUtil::getService('doctrine.entitymanager');
    $output = $em->getUnitOfWork()->getEntityIdentifier($params['entity']);
    if (!array_key_exists($params['key'], $output)) {
        return false;
    }
    if (!empty($params['assign'])) {
        $view->assign($params['assign'], $output[$params['key']]);

        return;
    }

    return $output[$params['key']];
}
