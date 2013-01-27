<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

//
// store the absolut path to your Zikula folder here
//
chdir('/opt/webdev/htdocs');

//
// no changes necessary beyond this point!
//
include 'lib/ZLoader.php';
ZLoader::register();
System::init();

$debug = FormUtil::getPassedValue('debug', 0, 'GETPOST');
$debug = ($debug==1) ? true : false;

$forums = ModUtil::apiFunc('Dizkus', 'admin', 'readforums', array('permcheck' => 'nocheck'));
if (is_array($forums) && count($forums) > 0)
{
    echo count($forums) . " forums read<br />";
    foreach($forums as $forum)
    {
        if ($forum['externalsource'] == 1) {    // Mail
            ModUtil::apiFunc('Dizkus', 'user', 'mailcron',
                         array('forum' => $forum,
                               'debug' => $debug));
        }
    }
}
