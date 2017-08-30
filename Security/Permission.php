<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Security;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * TopicProviderBindingType
 *
 * @author Kaik
 */
class Permission
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var VariableApi
     */
    private $variableApi;

    public function __construct(RequestStack $requestStack,
            EntityManager $entityManager,
            TranslatorInterface $translator,
            PermissionApi $permissionApi,
            VariableApi $variableApi)
    {
        $this->name = 'ZikulaDizkusModule';
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
    }

    /**
     * Checks the permissions of a user for a specific forum.
     *
     * @param string $args
     *
     * @return array|mixed
     */
    public function get($args)
    {
        $permissions = [];
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
     * @param array $args arguments
     *
     * @return bool
     */
    public function canSee($args)
    {
        return $this->checkPermission($args, ACCESS_OVERVIEW);
    }

    /**
     * Check if a user is allowed to read forum.
     *
     * @param array $args arguments
     *
     * @return bool
     */
    public function canRead($args)
    {
        return $this->checkPermission($args, ACCESS_READ);
    }

    /**
     * Check if a user is allowed to write forum.
     *
     * @param array $args arguments
     *
     * @return bool
     */
    public function canWrite($args)
    {
        return $this->checkPermission($args, ACCESS_COMMENT);
    }

    /**
     * Check if a user is allowed to moderate forum.
     *
     * @param array $args arguments
     *
     * @return bool
     */
    public function canDelete($args)
    {
        return $this->checkPermission($args, ACCESS_DELETE);
    }

    /**
     * Check if a user is allowed to moderate forum.
     *
     * @param array $args arguments
     *
     * @return bool
     */
    public function canModerate($args)
    {
        return $this->checkPermission($args, ACCESS_MODERATE);
    }

    /**
     * Check if a user is allowed to administrate forum.
     *
     * @param array $args arguments
     *
     * @return bool
     */
    public function canAdministrate($args)
    {
        return $this->checkPermission($args, ACCESS_ADMIN);
    }

    /**
     * check Permission.
     *
     * @param array|object $args  arguments
     * @param int          $level level
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     *
     * @return bool
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
                        if (isset($args['forum_id'])) {
                            $forum = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumEntity', $args['forum_id']);
                        }
                        $userId = isset($args['user_id']) ? $args['user_id'] : null;
                    } else {
                        throw new \InvalidArgumentException();
                    }
                }
            }
        }

        if (!$this->variableApi->get($this->name, 'forum_enabled') && !$this->permissionApi->hasPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            $this->request->getSession()->getFlashBag()->add('error', $this->variableApi->get($this->name, 'forum_disabled_info'));

            return false;
        }

        if (empty($userId)) {
            $userId = $this->request->getSession()->get('uid') > 1 ? $this->request->getSession()->get('uid') : 1;
        }
        if (!isset($forum)) {
            return $this->permissionApi->hasPermission($this->name.'::', '::', $level, $userId);
        }
        // loop through current forum and all parents and check for perms,
        // if ever false (at any parent) return false
        while ($forum->getLvl() != 0) {
            $perm = $this->permissionApi->hasPermission($this->name.'::', $forum->getForum_id().'::', $level, $userId);
            if (!$perm) {
                return false;
            }
            $forum = $forum->getParent();
        }

        return true;
    }

    /**
     * check and filter and array of forums and their children for READ permissions.
     *
     * @param array $forums
     *
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
     * check and filter child forums for READ permissions.
     *
     * @param ForumEntity $forum
     *
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

    /**
     * Get the ids of all the forums the user is allowed to see.
     *
     * @param int $args['parent']
     * @param int $args['userId']
     *
     * @return array
     */
    public function getForumIdsByPermission($args)
    {
        $parent = isset($args['parent']) ? $args['parent'] : null;
        $userId = (isset($args['userId']) && $args['userId'] > 1) ? $args['userId'] : null;
        $ids = [];
        $forums = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->findAll();
        /** @var $forum ForumEntity */
        foreach ($forums as $forum) {
            $parent = $forum->getParent();
            $parentId = isset($parent) ? $parent->getForum_id() : null;
            $forumId = $forum->getForum_id();
            if ($this->permissionApi->hasPermission($this->name.'::', "{$parentId}:{$forumId}:", ACCESS_READ, $userId)) {
                $ids[] = $forumId;
            }
        }

        return $ids;
    }
}
