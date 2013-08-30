<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
//
// store the absolute path to your Zikula folder here
//
chdir('/opt/webdev/htdocs');
//
// no changes necessary beyond this point!
//
include 'lib/ZLoader.php';
ZLoader::register();
System::init();
$debug = $this->request->query->get('debug', $this->request->request->get('debug', 0));
$debug = $debug == 1 ? true : false;
// user userId = 2 (site owner) to avoid perm limits
$forums = ModUtil::apiFunc('Dizkus', 'forum', 'getForumIdsByPermission', array('userId' => 2));
if (is_array($forums) && count($forums) > 0) {
    echo count($forums) . ' forums read<br />';
    foreach ($forums as $forum) {
        if ($forum['externalsource'] == 1) {
            // Mail
            ModUtil::apiFunc('Dizkus', 'cron', 'mail', array('forum' => $forum, 'debug' => $debug));
        }
    }
}
