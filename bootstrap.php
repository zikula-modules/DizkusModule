<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

$helper = ServiceUtil::getService('doctrine_extensions');
$helper->getListener('timestampable');
$helper->getListener('standardfields');

$em = ServiceUtil::getService('doctrine.entitymanager');
$em->getEventManager()->addEventSubscriber(new \Gedmo\Tree\TreeListener());

/**
 * internal debug function
 *
 */
function dzkdebug($name='', $data, $die = false)
{
    if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        $type = gettype($data);
        echo "\n<!-- begin debug of $name -->\n<div style=\"color: red;\">$name ($type";
        if (is_array($data)||is_object($data)) {
            if (count($data) > 0) {
                echo ', size=$size entries):<pre>';
                echo htmlspecialchars(print_r($data, true));
                echo '</pre>:<br />';
            } else {
                echo '):empty<br />';
            }
        } else if (is_bool($data)) {
            echo ($data==true) ? ") true<br />" : ") false<br />";
        } else if (is_string($data)) {
            echo ', len='.strlen($data).') :'.DataUil::formatForDisplay($data).':<br />';
        } else {
            echo ') :'.$data.':<br />';
        }
        echo '</div><br />\n<!-- end debug of $name -->';
        if ($die==true) {
            System::shutDown();
        }
    }
}