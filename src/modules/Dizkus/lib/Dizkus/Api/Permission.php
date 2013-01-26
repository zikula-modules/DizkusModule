<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
class Dizkus_Api_Permission extends Zikula_AbstractApi
{

    public function get($args)
    {
        $permissions = array();
        $permissions['see'] = $this->canSee($args);
        $permissions['read'] = $permissions['see'] && $this->canRead($args);
        $permissions['comment'] = $permissions['read'] && $this->canWrite($args);
        $permissions['moderate'] = $permissions['comment'] && $this->canModerate($args);
        $permissions['edit'] = $permissions['moderate'];
        $permissions['admin'] = $permissions['moderate'] && $this->canAdministrate($args);

        return $permissions;
    }

    /**
     * Check if a user is allowed to see category and forum.
     *
     * @param array $args Arguments.
     *
     * @return boolean
     */
    public function canSee($args)
    {
        return $this->checkPermission($args, ACCESS_OVERVIEW);
    }

    /**
     * Check if a user is allowed to read category and forum.
     *
     * @param array $args Arguments.
     *
     * @return boolean
     */
    public function canRead($args)
    {
        return $this->checkPermission($args, ACCESS_READ);
    }

    /**
     * Check if a user is allowed to write category and forum.
     *
     * @param array $args Arguments.
     *
     * @return boolean
     */
    public function canWrite($args)
    {
        return $this->checkPermission($args, ACCESS_COMMENT);
    }

    /**
     * Check if a user is allowed to moderate category and forum.
     *
     * @param array $args Arguments.
     *
     * @return boolean
     */
    public function canModerate($args)
    {
        return $this->checkPermission($args, ACCESS_MODERATE);
    }

    /**
     * Check if a user is allowed to administrate category and forum.
     *
     * @param array $args Arguments.
     *
     * @return boolean
     */
    public function canAdministrate($args)
    {
        return $this->checkPermission($args, ACCESS_ADMIN);
    }

    /**
     * check Permission
     *
     * @param array $args  Arguments.
     * @param int   $level Level.
     *
     * @return boolean
     */
    private function checkPermission($args, $level = ACCESS_READ)
    {
        if (gettype($args) == 'object') {
            $args = $args->toArray();
        }

        if ($this->getVar('forum_enabled') == 'no') {
            return LogUtil::registerError($this->getVar('forum_disabled_info'));
        }

        if (empty($args['user_id'])) {
            $args['user_id'] = null; // current user
        }
        if (!isset($args['cat_id'])) {
            $args['cat_id'] = '';
        }
        if (!isset($args['forum_id'])) {
            $args['forum_id'] = '';
        }

        $component = 'Dizkus::';
        $instance = $args['cat_id'] . ':' . $args['forum_id'] . ':';
        $user = $args['user_id'];

        return SecurityUtil::checkPermission($component, $instance, $level, $user);
    }

}