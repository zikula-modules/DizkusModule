<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Admin extends Zikula_AbstractApi {


    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();
        if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url'   => ModUtil::url('Dizkus', 'admin', 'tree'),
                'text'  => $this->__('Edit forum tree'),
                'title' => $this->__('Create, delete, edit and re-order categories and forums'),
                'class' => 'z-icon-es-options',
            );
            $links[] = array('url' => ModUtil::url('Dizkus', 'admin', 'ranks', array('ranktype' => 0)),
                    'text' => $this->__('Edit user ranks'),
                    'class' => 'z-icon-es-group',
                    'title' => $this->__('Create, edit and delete user rankings acquired through the number of a user\'s posts'),
                    'links' => array(
                        array(
                            'url'   => ModUtil::url('Dizkus', 'admin', 'ranks', array('ranktype' => 0)),
                            'text'  => $this->__('Edit user ranks'),
                            'title' => $this->__('Create, edit and delete user rankings acquired through the number of a user\'s posts')),
                        array(
                            'url'   => ModUtil::url('Dizkus', 'admin', 'ranks', array('ranktype' => 1)),
                            'text'  => $this->__('Edit honorary ranks'),
                            'title' => $this->__('Create, delete and edit special ranks for particular users')
                        ),
                        array(
                            'url'   => ModUtil::url('Dizkus', 'admin', 'assignranks'),
                            'text'  => $this->__('Assign honorary rank'),
                            'title' => $this->__('Assign honorary user ranks to users'))
                        )
                    );
            $links[] = array(
                            'url'   => ModUtil::url('Dizkus', 'admin', 'manageSubscriptions'),
                            'text'  => $this->__('Manage subscriptions'),
                            'title' => $this->__('Remove a user\'s topic and forum subscriptions'),
                            'class' => 'z-icon-es-mail'
                       );
            $links[] = array(
                'url' => ModUtil::url('Dizkus', 'admin', 'preferences'),
                'text' => $this->__('Settings'),
                'title' => $this->__('Edit general forum-wide settings'),
                'class' => 'z-icon-es-config',
            );
        }

        return $links;
    }
}