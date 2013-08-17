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
     * Check if a user is allowed to see forum.
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
     * Check if a user is allowed to read forum.
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
     * Check if a user is allowed to write forum.
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
     * Check if a user is allowed to moderate forum.
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
     * Check if a user is allowed to administrate forum.
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
            $userId = null; // will default to current user
        }

        if (!isset($forum)) {
            return SecurityUtil::checkPermission('Dizkus::', '::', $level, $userId);
        }

        // loop through current forum and all parents and check for perms,
        // if ever false (at any parent) return false
        while ($forum->getLvl() != 0) {
            $perm = SecurityUtil::checkPermission('Dizkus::', $forum->getForum_id() . '::', $level, $userId);
            if (!$perm) {
                return false;
            }
            $forum = $forum->getParent();
        }
        return true;
    }

    /**
     * check and filter and array of forums and their children for READ permissions
     * @param array $forums
     */
    public function filterForumArrayByPermission(array $forums) {
        // confirm user has permissions to view each forum
        // in this case, it is know that there are only two levels to the tree, $forum and $subforum
        foreach ($forums as $key => $forum) {
            // $forums is an array
            if (!$this->canRead($forum)) {
                $this->entityManager->detach($forum); // ensure that future operations are not persisted
                unset($forums[$key]);
                continue;
            }
            $this->filterForumChildrenByPermission($forum);
        }
        return $forums;
    }

    /**
     * check and filter child forums for READ permissions
     * @param Dizkus_Entity_Forum $forum
     */
    public function filterForumChildrenByPermission(Dizkus_Entity_Forum $forum) {
        $subforums = $forum->getChildren();
        foreach ($subforums as $subforum) {
            // $subforums is a PersistentCollection
            if (!$this->canRead($subforum)) {
                $this->entityManager->detach($subforum); // ensure that future operations are not persisted
                $forum->getChildren()->removeElement($subforum);
            }
        }
        return $forum;
    }
}