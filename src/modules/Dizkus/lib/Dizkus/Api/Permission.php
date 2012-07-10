<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Permission extends Zikula_AbstractApi {


    /**
     * canRead
     *
     * Check if user has the permissions to read a page.
     *
     * @return boolean
     */
    public function canRead()
    {

        if ($this->getVar('forum_enabled') == 'no') {
            return LogUtil::registerError($this->getVar('forum_disabled_info'));
        }

        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        return true;
    }

}