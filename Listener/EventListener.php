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

namespace Zikula\DizkusModule\Listener;

use Doctrine\ORM\EntityManager;
use HookUtil;
use ModUtil;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\DizkusModule\Entity\ForumUserEntity;
use Zikula\DizkusModule\ZikulaDizkusModule;
use ZLanguage;

class EventListener implements EventSubscriberInterface
{
    private $entityManager;
    private $requestStack;
    private $router;

    public function __construct(RequestStack $requestStack, EntityManager $entityManager, RouterInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            'module_dispatch.service_links' => ['serviceLinks'],
            'installer.module.uninstalled'  => ['moduleDelete'],
            'user.account.delete'           => ['deleteUser'],
        ];
    }

    /**
     * respond to event 'module_dispatch.service_links'
     * populate Services menu with hook option link.
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function serviceLinks(GenericEvent $event)
    {
        $dom = ZLanguage::getModuleDomain(ZikulaDizkusModule::NAME);
        $moduleName = $event['modname'];
        $bindingCount = count(HookUtil::getBindingsBetweenOwners($moduleName, ZikulaDizkusModule::NAME));
        if ($bindingCount > 0 && $moduleName != ZikulaDizkusModule::NAME && (empty($event->data) || is_array($event->data) && !in_array([
                    'url'  => $this->router->generate('zikuladizkusmodule_admin_hookconfig', ['moduleName' => $moduleName]),
                    'text' => __('Dizkus Hook Settings', $dom), ], $event->data))) {
            $event->data[] = [
                'url'  => $this->router->generate('zikuladizkusmodule_admin_hookconfig', ['moduleName' => $moduleName]),
                'text' => __('Dizkus Hook Settings', $dom), ];
        }
    }

    /**
     * respond to event "installer.module.uninstalled".
     * Receives $modinfo as event $args.
     *
     * On module delete handle associated hooked topics
     *
     * @param GenericEvent $z_event
     *
     * @return void
     */
    public function moduleDelete(GenericEvent $z_event)
    {
        $args = $z_event->getArguments(); // $modinfo
        $module = $args['name'];
        $dom = ZLanguage::getModuleDomain(ZikulaDizkusModule::NAME);
        $deleteHookAction = ModUtil::getVar(ZikulaDizkusModule::NAME, 'deletehookaction');
        // lock or remove
        $topics = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->findBy(['hookedModule' => $module]);
        $count = 0;
        $total = 0;
        foreach ($topics as $topic) {
            switch ($deleteHookAction) {
                case 'remove':
                    ModUtil::apiFunc(ZikulaDizkusModule::NAME, 'Topic', 'delete', ['topic' => $topic]);
                    break;
                case 'lock':
                default:
                    $topic->lock();
                    $count++;
                    if ($count > 20) {
                        $this->entityManager->flush();
                        $count = 0;
                    }
                    break;
            }
            $total++;
        }
        // clear last remaining batch
        $this->entityManager->flush();
        $actionWord = $deleteHookAction == 'lock' ? __('locked', $dom) : __('deleted', $dom);
        if ($total > 0) {
            $request = $this->requestStack->getCurrentRequest();
            $request->getSession()->getFlashBag()->add('status', __f('Dizkus: All hooked discussion topics %s.', $actionWord, $dom));
        }
    }

    /**
     * respond to event 'user.account.delete'.
     *
     * on User delete, handle associated information in Dizkus
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function deleteUser(GenericEvent $event)
    {
        $user = $event->getSubject(); // user is an array formed by UserUtil::getVars();
        // remove subscriptions - topic
        $dql = 'DELETE Zikula\DizkusModule\Entity\TopicSubscriptionEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove subscriptions - forum
        $dql = 'DELETE Zikula\DizkusModule\Entity\ForumSubscriptionEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove favorites
        $dql = 'DELETE Zikula\DizkusModule\Entity\ForumUserFavoriteEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove moderators
        $dql = 'DELETE Zikula\DizkusModule\Entity\ModeratorUserEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // change user level - unused at the moment
        $dql = 'UPDATE Zikula\DizkusModule\Entity\ForumUserEntity u
            SET u.level = :level
            WHERE u.user_id = :uid';
        $this->entityManager->createQuery($dql)
            ->setParameter('uid', $user['uid'])
            ->setParameter('level', ForumUserEntity::USER_LEVEL_DELETED)
            ->execute();
    }
}
