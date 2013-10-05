<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Api;

use ModUtil;
use SecurityUtil;

class PrefsApi extends \Zikula_AbstractApi
{

    /**
     * get available user pref panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array(
                );
        if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_OVERVIEW)) {
            $links[] = array(
                'url' => ModUtil::url($this->name, 'user', 'prefs'),
                'text' => $this->__('Personal settings'),
                'title' => $this->__('Modify personal settings'),
                'icon' => 'wrench');
            $links[] = array(
                'url' => ModUtil::url($this->name, 'user', 'manageForumSubscriptions'),
                'text' => $this->__('Forum subscriptions'),
                'title' => $this->__('Manage forum subscriptions'),
                'icon' => 'envelope-alt');
            $links[] = array(
                'url' => ModUtil::url($this->name, 'user', 'manageTopicSubscriptions'),
                'text' => $this->__('Topic subscriptions'),
                'title' => $this->__('Manage topic subscriptions'),
                'icon' => 'envelope-alt');
            if (ModUtil::getVar($this->name, 'signaturemanagement') == 'yes') {
                $links[] = array(
                    'url' => ModUtil::url($this->name, 'user', 'signaturemanagement'),
                    'text' => $this->__('Signature'),
                    'title' => $this->__('Manage signature'),
                    'icon' => 'pencil');
            }
        }

        return $links;
    }

}
