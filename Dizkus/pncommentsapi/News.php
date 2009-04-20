<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

Loader::includeOnce('modules/Dizkus/common.php');

/*
* param: objectid
*/

function Dizkus_commentsapi_News($args)
{
    extract($args);
    unset($args);

    $news = pnModApiFunc('News', 'user', 'get', array('objectid' => $objectid));
    $link = pnGetBaseURL() . pnModURL('News', 'user', 'display', array('sid' => $objectid));
    $lang = pnUserGetLang();

    if(pnModIsHooked('bbcode', 'Dizkus')) {
        $notes = '[i]' . $news['notes'] . '[/i]';
        $link = '[url]' .$link. '[/url]';
    }

    $topic = $news['__CATEGORIES__']['Main']['display_name'][$lang];
    $totaltext = $news['hometext'] . "\n\n" . $news['bodytext'] . "\n\n" . $news['notes'] . "\n\n" . $link . "\n\n";

    return array($news['title'], $totaltext , $topic, $news['cr_uid']);
}
