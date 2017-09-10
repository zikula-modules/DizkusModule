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

namespace Zikula\DizkusModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\DizkusModule\Controller\AbstractBaseController as AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin/import/upgrade3")
 */
class Upgrade3Controller extends AbstractController
{
    /**
     * @Route("/users/status", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function usersstatusAction()
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $data = [];
        $importHandler = $this->get('zikula_dizkus_module.import.upgrade_3');
        $importHandler->setPrefix($this->getVar('upgrading'));
        /* Ranks
         */
        $data['source']['ranks'] = $importHandler->getRanksStatus();
        $ranksIcon = ($data['source']['ranks']['found'] > 0) ? 'fa-orange' : 'fa-green';
        $ranksText = $this->__('Ranks to import found: ').$data['source']['ranks']['found'].$this->__(' Need to be imported first');
        $ranksNode = [
            'id'     => 'ranks',
            'parent' => 'users_check_root',
            'text'   => $ranksText,
            'icon'   => 'fa fa-star-half-o '. $ranksIcon
        ];
        /* Users
         */
        $data['source']['users'] = $importHandler->getUsersStatus();
        $dizkusNode = [
            'id' => 'current',
            'parent' => 'users_check_root',
            'text' => $this->__('Current dizkus users: ').count($data['source']['users']['current']),
            'icon' => 'fa fa-users fa-green'
            ];
        $oldIcon = ($data['source']['users']['old']['found'] == 0) ? 'fa-green' : 'fa-orange';
        $oldNode = [
            'id'     => 'old',
            'parent' => 'users_check_root',
            'text'   => $this->__('Users to import found: ').$data['source']['users']['old']['found'],
            'icon'   => 'fa fa-user-plus '.$oldIcon,
            ];

        $data['tree'] = [
            $ranksNode,
            $oldNode,
            $dizkusNode,
        ];

        return new Response(json_encode($data));
    }

    /**
     * @Route("/users/import", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function usersimportAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $content = $request->getContent();
        if (!empty($content)) {
            $data = json_decode($content, true); // 2nd param to get as array
        }

        $importHandler = $this->get('zikula_dizkus_module.import.upgrade_3');
        $importHandler->setPrefix($this->getVar('upgrading'));

        switch ($data['source']) {
            case 'ranks':
                $data = $importHandler->importRanks($data);

                break;
            case 'old':
                $data = $importHandler->importUsers($data);

                break;
        }

        return new Response(json_encode($data));
    }

    /**
     * @Route("/forumtree/status", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function forumtreestatusAction()
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $importHelper = $this->get('zikula_dizkus_module.import.upgrade_3');
        $prefix = $this->getVar('upgrading');
        $importHelper->setPrefix($prefix);
        $tree = $importHelper->getForumTree();
        $data['tree'] = $tree[0]->__toArray();
        if (!array_key_exists('total', $data)) {
            $data['total']['current'] = $importHelper->getTableCount('dizkus_forums');
            $data['total']['forums'] = $importHelper->getTableCount($prefix . '_dizkus_categories') + $importHelper->getTableCount($prefix . '_dizkus_forums');
            $data['total']['topics'] = $importHelper->getTableCount($prefix . '_dizkus_topics');
            $data['total']['posts'] = $importHelper->getTableCount($prefix . '_dizkus_posts');
            $data['total']['done'] = 1;
        }
        if (!array_key_exists('excluded', $data)) {
            $data['excluded'] = $importHelper->getExcluded();
        }
        
        return new Response(json_encode($data));
    }

    /**
     * @Route("/forumtree/import", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function forumtreeimportAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $content = $request->getContent();
        if (!empty($content)) {
            $data = json_decode($content, true);
        }
        $importHelper = $this->get('zikula_dizkus_module.import.upgrade_3');
        $importHelper->setPrefix($this->getVar('upgrading'));

        // there should be 3 lvl's only
        switch ($data['node']['lvl']) {
            case 0:
                if ($data['node']['id'] === 1) {
                    $data['log'][] = $this->__('Checking index forum');
                } else {
                }

                break;
            case 1:
                $data = $importHelper->importCategory($data);

                break;
            case 2:
                $data = $importHelper->importTopics($data);

                break;
            case 3:
                $data = $importHelper->importPosts($data);

                break;
        }

        return new Response(json_encode($data));
    }

    /**
     * @Route("/other/status", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function otherstatusAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $importHelper = $this->get('zikula_dizkus_module.import.upgrade_3');
        $importHelper->setPrefix($this->getVar('upgrading'));
        $data = [];

        $data['source']['favorites'] = $importHelper->getFavoritesStatus();
        $data['source']['favorites']['source'] = 'favorites';
        $favoritesIcon = 'fa-orange';
        if (count($data['source']['favorites']['toImport']) > 0) {
            $favoritesText = $this->__('Favorites to import found: ')
                    . count($data['source']['favorites']['toImport']);
        } else {
            $favoritesIcon = 'fa-green';
            $favoritesText = $this->__('Favorites found: ')
                . count($data['source']['favorites']['found'])
                . $this->__(' Favorites to import: ')
                . count($data['source']['favorites']['toImport'])
                . $this->__('');
        }
        $favoritesNode = [
            'id' => 'favorites',
            'parent' => 'other_tree_root',
            'text' => $favoritesText,
            'icon' => 'fa fa-heart '. $favoritesIcon
        ];

        $data['source']['moderators_users'] = $importHelper->getModeratorsUsersStatus();
        $data['source']['moderators_users']['source'] = 'moderators_users';
        $moderatorsUsersIcon = 'fa-orange';
        if (count($data['source']['moderators_users']['toImport']) > 0) {
            $moderatorsUsersText = $this->__('Moderator users to import found: ')
                    . count($data['source']['moderators_users']['toImport']);
        } else {
            $moderatorsUsersIcon = 'fa-green';
            $moderatorsUsersText = $this->__('Moderators users found: ')
                . count($data['source']['moderators_users']['found'])
                . $this->__(' Moderators users to import: ')
                . count($data['source']['moderators_users']['toImport'])
                . $this->__('');
        }
        $moderatorsUsersNode = [
            'id' => 'moderators_users',
            'parent' => 'other_tree_root',
            'text' => $moderatorsUsersText,
            'icon' => 'fa fa-user-secret '. $moderatorsUsersIcon
        ];

        $data['source']['moderators_groups'] = $importHelper->getModeratorsGroupsStatus();
        $data['source']['moderators_groups']['source'] = 'moderators_groups';
        $moderatorsGroupsIcon = 'fa-orange';
        if (count($data['source']['moderators_groups']['toImport']) > 0) {
            $moderatorsGroupsText = $this->__('Moderator groups to import found: ')
                    . count($data['source']['moderators_groups']['toImport']);
        } else {
            $moderatorsGroupsIcon = 'fa-green';
            $moderatorsGroupsText = $this->__('Moderators gropus found: ')
                . count($data['source']['moderators_groups']['found'])
                . $this->__(' Moderators groups to import: ')
                . count($data['source']['moderators_groups']['toImport'])
                . $this->__('');
        }
        $moderatorsGroupsNode = [
            'id' => 'moderators_groups',
            'parent' => 'other_tree_root',
            'text' => $moderatorsGroupsText,
            'icon' => 'fa fa-users '. $moderatorsGroupsIcon
        ];

        $data['source']['forum_subscriptions'] = $importHelper->getFSStatus();
        $data['source']['forum_subscriptions']['source'] = 'forum_subscriptions';
        $FSIcon = 'fa-orange';
        if (count($data['source']['forum_subscriptions']['toImport']) > 0) {
            $FSText = $this->__('Forum subscriptions to import found: ')
                    . count($data['source']['forum_subscriptions']['toImport']);
        } else {
            $FSIcon = 'fa-green';
            $FSText = $this->__('Forum subscriptions found: ')
                . count($data['source']['forum_subscriptions']['found'])
                . $this->__(' Forum subscriptions to import: ')
                . count($data['source']['forum_subscriptions']['toImport'])
                . $this->__('');
        }
        $FSNode = [
            'id' => 'forum_subscriptions',
            'parent' => 'other_tree_root',
            'text' => $FSText,
            'icon' => 'fa fa-envelope-open '. $FSIcon
        ];

        $data['source']['topic_subscriptions'] = $importHelper->getTSStatus();
        $data['source']['topic_subscriptions']['source'] = 'topic_subscriptions';
        $TSIcon = 'fa-orange';
        if (count($data['source']['topic_subscriptions']['toImport']) > 0) {
            $TSText = $this->__('Topic subscriptions to import found: ')
                    . count($data['source']['topic_subscriptions']['toImport']);
        } else {
            $TSIcon = 'fa-green';
            $TSText = $this->__('Topic subscriptions found: ')
                . count($data['source']['topic_subscriptions']['found'])
                . $this->__(' Topic subscriptions to import: ')
                . count($data['source']['topic_subscriptions']['toImport'])
                . $this->__('');
        }
        $TSNode = [
            'id' => 'topic_subscriptions',
            'parent' => 'other_tree_root',
            'text' => $TSText,
            'icon' => 'fa fa-envelope-open-o '. $TSIcon
        ];

        $data['tree'] = [
            $favoritesNode,
            $moderatorsUsersNode,
            $moderatorsGroupsNode,
            $FSNode,
            $TSNode,
        ];

        return new Response(json_encode($data));
    }

    /**
     * @Route("/other/import", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function otherimportAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $content = $request->getContent();
        if (!empty($content)) {
            $data = json_decode($content, true); // 2nd param to get as array
        }

        $importHelper = $this->get('zikula_dizkus_module.import.upgrade_3');
        $importHelper->setPrefix($this->getVar('upgrading'));

        switch ($data['source']) {
            case 'favorites':
                $data = $importHelper->importFavorites($data);

                break;
            case 'moderators_users':
                $data = $importHelper->importModeratorsUsers($data);

                break;
            case 'moderators_groups':
                $data = $importHelper->importModeratorsGroups($data);

                break;
            case 'forum_subscriptions':
                $data = $importHelper->importFS($data);

                break;
            case 'topic_subscriptions':
                $data = $importHelper->importTS($data);

                break;
        }

        return new Response(json_encode($data));
    }

    /**
     * @Route("/removeprefix", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function removePrefixAction()
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $this->delVar('upgrading');

        return new Response(json_encode(true));
    }

    /**
     * @Route("/removecontent", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function removeContentAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $content = $request->getContent();
        if (!empty($content)) {
            $data = json_decode($content, true); // 2nd param to get as array
        }

        $importHelper = $this->get('zikula_dizkus_module.import.upgrade_3');

        return new Response(json_encode($importHelper->removeContent($data)));
    }
}
