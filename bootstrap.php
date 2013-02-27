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