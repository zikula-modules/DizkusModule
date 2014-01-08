<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use SecurityUtil;
use ModUtil;
use HookUtil;
use System;
use ZLanguage;
use Zikula_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Event\GenericEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Zikula\Module\DizkusModule\ZikulaDizkusModule;
use Zikula\Module\DizkusModule\Entity\ForumUserEntity;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

class EventListener implements EventSubscriberInterface
{
    private $entityManager;
    private $requestStack;

    function __construct(RequestStack $requestStack, EntityManager $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'module_dispatch.service_links' => array('serviceLinks'),
            'controller.method_not_found' => array('dizkusHookConfig', 'dizkusHookConfigProcess'),
            'installer.module.uninstalled' => array('moduleDelete'),
            'user.account.delete' => array('deleteUser'),
        );
    }

    /**
     * respond to event 'module_dispatch.service_links'
     * populate Services menu with hook option link
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function serviceLinks(GenericEvent $event)
    {
        $dom = ZLanguage::getModuleDomain(ZikulaDizkusModule::NAME);
        $module = ModUtil::getName();
        $bindingCount = count(HookUtil::getBindingsBetweenOwners($module, ZikulaDizkusModule::NAME));
        if ($bindingCount > 0 && $module != ZikulaDizkusModule::NAME && (empty($event->data) || is_array($event->data) && !in_array(array(
                    'url' => ModUtil::url($module, 'admin', 'dizkushookconfig'),
                    'text' => __('Dizkus Hook Settings', $dom)), $event->data))) {
            $event->data[] = array(
                'url' => ModUtil::url($module, 'admin', 'dizkushookconfig'),
                'text' => __('Dizkus Hook Settings', $dom));
        }
    }

    /**
     * respond to event 'controller.method_not_found'
     * add hook config options to hooked module's module config
     *
     * @param GenericEvent $z_event
     *
     * @return void
     */
    public function dizkusHookConfig(GenericEvent $z_event)
    {
        // check if this is for this handler
        $subject = $z_event->getSubject();
        if (!($z_event['method'] == 'dizkushookconfig' && (strrpos(get_class($subject), '_Controller_Admin') || strrpos(get_class($subject), '\\AdminController')))) {
            return;
        }
        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $view = Zikula_View::getInstance(ZikulaDizkusModule::NAME, false);
        $hookconfig = ModUtil::getVar($moduleName, 'dizkushookconfig');
        $module = ModUtil::getModule($moduleName);
        if (isset($module)) {
            $classname = $module->getVersionClass();
        } else {
            $classname = $moduleName . '_Version';
        }
        $moduleVersionObj = new $classname();
        $bindingsBetweenOwners = HookUtil::getBindingsBetweenOwners($moduleName, ZikulaDizkusModule::NAME);
        foreach ($bindingsBetweenOwners as $k => $binding) {
            $areaname = $this->entityManager->getRepository('Zikula\\Component\\HookDispatcher\\Storage\\Doctrine\\Entity\\HookAreaEntity')->find($binding['sareaid'])->getAreaname();
            $bindingsBetweenOwners[$k]['areaname'] = $areaname;
            $bindingsBetweenOwners[$k]['areatitle'] = $view->__($moduleVersionObj->getHookSubscriberBundle($areaname)->getTitle());
        }
        $view->assign('areas', $bindingsBetweenOwners);
        $view->assign('dizkushookconfig', $hookconfig);
        $view->assign('ActiveModule', $moduleName);
        $view->assign('forums', ModUtil::apiFunc(ZikulaDizkusModule::NAME, 'Forum', 'getParents', array('includeLocked' => true)));
        $z_event->setData($view->fetch('hook/modifyconfig.tpl'));
        $z_event->stopPropagation();
    }

    /**
     * respond to event 'controller.method_not_found'
     * process results of dizkushookconfig
     *
     * @param GenericEvent $z_event
     *
     * @return void|RedirectResponse
     */
    public function dizkusHookConfigProcess(GenericEvent $z_event)
    {
        // check if this is for this handler
        $subject = $z_event->getSubject();
        if (!($z_event['method'] == 'dizkushookconfigprocess' && (strrpos(get_class($subject), '_Controller_Admin') || strrpos(get_class($subject), '\\AdminController')))) {
            return;
        }
        $dom = ZLanguage::getModuleDomain(ZikulaDizkusModule::NAME);
        $request = $this->requestStack->getCurrentRequest();
        $hookdata = $request->request->get('dizkus', array());
        $token = isset($hookdata['dizkus_csrftoken']) ? $hookdata['dizkus_csrftoken'] : null;
        if (!SecurityUtil::validateCsrfToken($token)) {
            throw new AccessDeniedException();
        }
        unset($hookdata['dizkus_csrftoken']);
        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        foreach ($hookdata as $area => $data) {
            if (!isset($data['forum']) || empty($data['forum'])) {
                $request->getSession()->getFlashBag()->add('error', __f('Error: No forum selected for area \'%s\'', $area, $dom));
                $hookdata[$area]['forum'] = null;
            }
        }
        ModUtil::setVar($moduleName, 'dizkushookconfig', $hookdata);
        // ModVar: dizkushookconfig => array('areaid' => array('forum' => value))
        $request->getSession()->getFlashBag()->add('status', __('Dizkus: Hook option settings updated.', $dom));
        $z_event->setData(true);
        $z_event->stopPropagation();

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($moduleName, 'admin', 'main')));
    }

    /**
     * respond to event "installer.module.uninstalled".
     * Receives $modinfo as event $args
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
        $topics = $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->findBy(array('hookedModule' => $module));
        $count = 0;
        $total = 0;
        foreach ($topics as $topic) {
            switch ($deleteHookAction) {
                case 'remove':
                    ModUtil::apiFunc(ZikulaDizkusModule::NAME, 'Topic', 'delete', array('topic' => $topic));
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
        $dql = 'DELETE Zikula\Module\DizkusModule\Entity\TopicSubscriptionEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove subscriptions - forum
        $dql = 'DELETE Zikula\Module\DizkusModule\Entity\ForumSubscriptionEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove favorites
        $dql = 'DELETE Zikula\Module\DizkusModule\Entity\ForumUserFavoriteEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // remove moderators
        $dql = 'DELETE Zikula\Module\DizkusModule\Entity\ModeratorUserEntity u
            WHERE u.forumUser = :uid';
        $this->entityManager->createQuery($dql)->setParameter('uid', $user['uid'])->execute();
        // change user level - unused at the moment
        $dql = 'UPDATE Zikula\Module\DizkusModule\Entity\ForumUserEntity u
            SET u.level = :level
            WHERE u.user_id = :uid';
        $this->entityManager->createQuery($dql)
            ->setParameter('uid', $user['uid'])
            ->setParameter('level', ForumUserEntity::USER_LEVEL_DELETED)
            ->execute();
    }

}