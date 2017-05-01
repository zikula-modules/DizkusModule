<?php

/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\UsersModule\Api\CurrentUserApi;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var CurrentUserApi
     */
    private $currentUser;

    /**
     * constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface     $router
     * @param PermissionApi       $permissionApi
     * @param VariableApi         $variableApi
     * @param CurrentUserApi      $currentUserApi
     */
    public function __construct(
    TranslatorInterface $translator, RouterInterface $router, PermissionApi $permissionApi, VariableApi $variableApi, CurrentUserApi $currentUserApi
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
        $this->variableApi = $variableApi;
        $this->currentUser = $currentUserApi;
    }

    /**
     * get Links of any type for this extension
     * required by the interface.
     *
     * @param string $type
     *
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        $method = 'get'.ucfirst(strtolower($type));
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return [];
    }

    /**
     * get the Admin links for this extension.
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = [];
        if ($this->permissionApi->hasPermission('ZikulaDizkusModule::', '::', ACCESS_ADMIN)) {
            $links[] = [
                'url'   => $this->router->generate('zikuladizkusmodule_admin_tree'),
                'text'  => $this->translator->__('Edit forum tree'),
                'title' => $this->translator->__('Create, delete, edit and re-order forums'),
                'icon'  => 'list', ];
            $links[] = [
                'url'   => $this->router->generate('zikuladizkusmodule_admin_ranks', ['ranktype' => RankEntity::TYPE_POSTCOUNT]),
                'text'  => $this->translator->__('Edit user ranks'),
                'icon'  => 'star-half-o',
                'title' => $this->translator->__('Create, edit and delete user rankings acquired through the number of a user\'s posts'),
                'links' => [
                    [
                        'url'   => $this->router->generate('zikuladizkusmodule_admin_ranks', ['ranktype' => RankEntity::TYPE_POSTCOUNT]),
                        'text'  => $this->translator->__('Edit user ranks'),
                        'title' => $this->translator->__('Create, edit and delete user rankings acquired through the number of a user\'s posts'), ],
                    [
                        'url'   => $this->router->generate('zikuladizkusmodule_admin_ranks', ['ranktype' => RankEntity::TYPE_HONORARY]),
                        'text'  => $this->translator->__('Edit honorary ranks'),
                        'title' => $this->translator->__('Create, delete and edit special ranks for particular users'), ],
                    [
                        'url'   => $this->router->generate('zikuladizkusmodule_admin_assignranks'),
                        'text'  => $this->translator->__('Assign honorary rank'),
                        'title' => $this->translator->__('Assign honorary user ranks to users'), ], ], ];
            $links[] = [
                'url'   => $this->router->generate('zikuladizkusmodule_admin_managesubscriptions'),
                'text'  => $this->translator->__('Manage subscriptions'),
                'title' => $this->translator->__('Remove a user\'s topic and forum subscriptions'),
                'icon'  => 'envelope-o', ];
            $links[] = [
                'url'   => $this->router->generate('zikuladizkusmodule_admin_preferences'),
                'text'  => $this->translator->__('Settings'),
                'title' => $this->translator->__('Edit general forum-wide settings'),
                'icon'  => 'wrench', ];
        }

        return $links;
    }

    /**
     * get the User Links for this extension.
     *
     * @return array
     */
    private function getAccount()
    {
        $links = [];
        if (!$this->currentUser->isLoggedIn()) {
            return $links;
        }

        $links[] = [
            'url'   => $this->router->generate('zikuladizkusmodule_user_prefs'),
            'text'  => $this->translator->__('Forum preferences'),
            'title' => $this->translator->__('Edit your forum preferences.'),
            'icon'  => 'wrench', ];

        return $links;
    }

    /**
     * get available user pref panel links.
     *
     * @return array array of admin links
     */
    public function getPrefs()
    {
        $links = [];
        if ($this->permissionApi->hasPermission('ZikulaDizkusModule::', '::', ACCESS_OVERVIEW)) {
            $links[] = [
                'url'   => $this->router->generate('zikuladizkusmodule_user_prefs'),
                'text'  => $this->translator->__('Personal settings'),
                'title' => $this->translator->__('Modify personal settings'),
                'icon'  => 'wrench', ];
            if ($this->variableApi->get('ZikulaDizkusModule', 'forum_subscriptions_enabled')) {
            $links[] = [
                'url'   => $this->router->generate('zikuladizkusmodule_user_manageforumsubscriptions'),
                'text'  => $this->translator->__('Forum subscriptions'),
                'title' => $this->translator->__('Manage forum subscriptions'),
                'icon'  => 'envelope-alt', ];
            }
            if ($this->variableApi->get('ZikulaDizkusModule', 'topic_subscriptions_enabled')) {
            $links[] = [
                'url'   => $this->router->generate('zikuladizkusmodule_user_managetopicsubscriptions'),
                'text'  => $this->translator->__('Topic subscriptions'),
                'title' => $this->translator->__('Manage topic subscriptions'),
                'icon'  => 'envelope-alt', ];
            }
            if ($this->variableApi->get('ZikulaDizkusModule', 'favorites_enabled')) {
                $links[] = [
                    'url'   => $this->router->generate('zikuladizkusmodule_user_managefavoriteforums'),
                    'text'  => $this->translator->__('Favorite forums'),
                    'title' => $this->translator->__('Manage favorite forums'),
                    'icon'  => 'envelope-alt', ];
            }
            if ($this->variableApi->get('ZikulaDizkusModule', 'signaturemanagement')) {
                $links[] = [
                    'url'   => $this->router->generate('zikuladizkusmodule_user_signaturemanagement'),
                    'text'  => $this->translator->__('Signature'),
                    'title' => $this->translator->__('Manage signature'),
                    'icon'  => 'pencil', ];
            }
        }

        return $links;
    }

    public function getBundleName()
    {
        return 'ZikulaDizkusModule';
    }
}
