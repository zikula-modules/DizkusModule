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

    /**
     * Checks the permissions of a user for a specific forum.
     *
     * @param string $args
     *
     * @return array|mixed
     */
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
     * Check to see if COMMENT perms are granted on provided module
     *
     * @param string $module
     * @return boolean
     */
    public function canComment($module)
    {
        return SecurityUtil::checkPermission("$module::", "::", ACCESS_COMMENT);
    }

    /**
     * check Permission
     *
     * @param array|object $args  Arguments.
     * @param int   $level Level.
     *
     * @return boolean
     */
    private function checkPermission($args, $level = ACCESS_READ)
    {
        // ensure always working with an Dizkus_Entity_Forum object or null
        if (empty($args)) {
            $forum = null;
        } else if ($args instanceof Dizkus_Entity_Forum) {
            $forum = $args;
        } else if (is_numeric($args)) {
            $forum = $this->entityManager->find('Dizkus_Entity_Forum', $args);
        } else if (is_array($args)) {
            // reconsititute object
            $forum = $this->entityManager->find('Dizkus_Entity_Forum', $args['forum_id']);
            $userId = $args['user_id'];
        } else {
            return LogUtil::registerArgsError();
        }

        if (($this->getVar('forum_enabled') == 'no') && !SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerError($this->getVar('forum_disabled_info'));
        }

        if (empty($userId)) {
            $userId = null; // current user
        }

        if (isset($forum)) {
            $instance = $forum->getParent()->getForum_id() . ':' . $forum->getForum_id() . ':';
        } else {
            $instance = '::';
        }

        return SecurityUtil::checkPermission('Dizkus::', $instance, $level, $userId);
    }

}