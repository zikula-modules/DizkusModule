<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Api;

use SecurityUtil;
use Zikula\DizkusModule\Entity\ForumEntity;

class PermissionApi extends \Zikula_AbstractApi
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
     * check Permission
     *
     * @param array|object $args  Arguments.
     * @param int          $level Level.
     *
     * @return boolean
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    private function checkPermission($args, $level = ACCESS_READ)
    {
        // ensure always working with an ForumEntity object or null
        if (empty($args)) {
            $forum = null;
        } else {
            if ($args instanceof ForumEntity) {
                $forum = $args;
            } else {
                if (is_numeric($args)) {
                    $forum = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $args);
                } else {
                    if (is_array($args)) {
                        // reconstitute object
                        $forum = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $args['forum_id']);
                        $userId = isset($args['user_id']) ? $args['user_id'] : null;
                    } else {
                        throw new \InvalidArgumentException();
                    }
                }
            }
        }
        if ($this->getVar('forum_enabled') == 'no' && !SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            $this->request->getSession()->getFlashBag()->add('error', $this->getVar('forum_disabled_info'));
            return false;
        }
        if (empty($userId)) {
            $userId = null;
        }
        if (!isset($forum)) {
            return SecurityUtil::checkPermission($this->name . '::', '::', $level, $userId);
        }
        // loop through current forum and all parents and check for perms,
        // if ever false (at any parent) return false
        while ($forum->getLvl() != 0) {
            $perm = SecurityUtil::checkPermission($this->name . '::', $forum->getForum_id() . '::', $level, $userId);
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
     * @return array
     */
    public function filterForumArrayByPermission(array $forums)
    {
        // confirm user has permissions to view each forum
        // in this case, it is know that there are only two levels to the tree, $forum and $subforum
        foreach ($forums as $key => $forum) {
            // $forums is an array
            if (!$this->canRead($forum)) {
                $this->entityManager->detach($forum);
                // ensure that future operations are not persisted
                unset($forums[$key]);
                continue;
            }
            $this->filterForumChildrenByPermission($forum);
        }

        return $forums;
    }

    /**
     * check and filter child forums for READ permissions
     * @param ForumEntity $forum
     * @return \Zikula\Module\DizkusModule\Entity\ForumEntity
     */
    public function filterForumChildrenByPermission(ForumEntity $forum)
    {
        $subforums = $forum->getChildren();
        foreach ($subforums as $subforum) {
            // $subforums is a PersistentCollection
            if (!$this->canRead($subforum)) {
                $this->entityManager->detach($subforum);
                // ensure that future operations are not persisted
                $forum->getChildren()->removeElement($subforum);
            }
        }

        return $forum;
    }

}
