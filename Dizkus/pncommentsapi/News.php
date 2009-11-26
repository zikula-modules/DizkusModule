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

/**
 * param: objectid
 */
function Dizkus_commentsapi_News($args)
{
    $news = pnModAPIFunc('News', 'user', 'get', array('objectid' => $args['objectid']));
    $link = pnModURL('News', 'user', 'display', array('sid' => $args['objectid']), null, null, true);
    $lang = ZLanguage::getLanguageCode();

    if (pnModIsHooked('bbcode', 'Dizkus')) {
        $notes = '[i]' . $news['notes'] . '[/i]';
        $link  = '[url]' .$link. '[/url]';
    }

    $topic = $news['__CATEGORIES__']['Main']['display_name'][$lang];
    $totaltext = $news['hometext'] . "\n\n" . $news['bodytext'] . "\n\n" . $news['notes'] . "\n\n" . $link . "\n\n";

    return array($news['title'], $totaltext , $topic, $news['cr_uid']);
}
