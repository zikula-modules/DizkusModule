<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Api;

use SecurityUtil;
use Zikula\DizkusModule\Entity\RankEntity;

class AdminApi extends \Zikula_AbstractApi
{
    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getLinks()
    {
        $links = array(
                );
        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => $this->get('router')->generate('zikuladizkusmodule_admin_tree'),
                'text' => $this->__('Edit forum tree'),
                'title' => $this->__('Create, delete, edit and re-order forums'),
                'icon' => 'list');
            $links[] = array(
                'url' => $this->get('router')->generate('zikuladizkusmodule_admin_ranks', array('ranktype' => RankEntity::TYPE_POSTCOUNT)),
                'text' => $this->__('Edit user ranks'),
                'icon' => 'star-half-o',
                'title' => $this->__('Create, edit and delete user rankings acquired through the number of a user\'s posts'),
                'links' => array(
                    array(
                        'url' => $this->get('router')->generate('zikuladizkusmodule_admin_ranks', array('ranktype' => RankEntity::TYPE_POSTCOUNT)),
                        'text' => $this->__('Edit user ranks'),
                        'title' => $this->__('Create, edit and delete user rankings acquired through the number of a user\'s posts')),
                    array(
                        'url' => $this->get('router')->generate('zikuladizkusmodule_admin_ranks', array('ranktype' => RankEntity::TYPE_HONORARY)),
                        'text' => $this->__('Edit honorary ranks'),
                        'title' => $this->__('Create, delete and edit special ranks for particular users')),
                    array(
                        'url' => $this->get('router')->generate('zikuladizkusmodule_admin_assignranks'),
                        'text' => $this->__('Assign honorary rank'),
                        'title' => $this->__('Assign honorary user ranks to users'))));
            $links[] = array(
                'url' => $this->get('router')->generate('zikuladizkusmodule_admin_managesubscriptions'),
                'text' => $this->__('Manage subscriptions'),
                'title' => $this->__('Remove a user\'s topic and forum subscriptions'),
                'icon' => 'envelope-o');
            $links[] = array(
                'url' => $this->get('router')->generate('zikuladizkusmodule_admin_preferences'),
                'text' => $this->__('Settings'),
                'title' => $this->__('Edit general forum-wide settings'),
                'icon' => 'wrench');
        }

        return $links;
    }
}
