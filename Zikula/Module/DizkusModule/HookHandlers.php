<?php

/**
 * Copyright 2013 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Hooks Handlers.
 */

namespace Zikula\Module\DizkusModule;

use ServiceUtil;
use SecurityUtil;
use ModUtil;
use PageUtil;
use HookUtil;
use System;
use ZLanguage;
use Zikula_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula_Event;
use Zikula\Core\Hook\AbstractHookListener;
use Zikula\Core\Hook\DisplayHook;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\Hook\DisplayHookResponse;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Event\GenericEvent;
use Zikula\Module\DizkusModule\Entity\RankEntity;
use Zikula\Module\DizkusModule\Entity\ForumEntity;
use Zikula\Module\DizkusModule\Entity\TopicEntity;
use Zikula\Module\DizkusModule\Manager\ForumManager;
use Zikula\Module\DizkusModule\Manager\TopicManager;
use Zikula\Module\DizkusModule\HookedTopicMeta\Generic;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HookHandlers extends AbstractHookListener
{

    /**
     * Zikula_View instance
     * @var Zikula_View
     */
    private $view;

    /**
     * Zikula entity manager instance
     * @var \Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * Module name
     * @var string
     */
    const MODULENAME = 'ZikulaDizkusModule';

    /**
     * Post constructor hook.
     *
     * @return void
     */
    public function setup()
    {
        $this->view = Zikula_View::getInstance(self::MODULENAME, false);
        // set caching off
        $this->_em = ServiceUtil::get('doctrine.entitymanager');
        $this->domain = ZLanguage::getModuleDomain(self::MODULENAME);
    }

    /**
     * Display hook for view.
     *
     * @param DisplayHook $hook The hook.
     *
     * @return string
     */
    public function uiView(DisplayHook $hook)
    {
        // first check if the user is allowed to do any comments for this module/objectid
        if (!SecurityUtil::checkPermission("{$hook->getCaller()}", '::', ACCESS_COMMENT)) {
            return;
        }
        $request = $this->view->getRequest();
        $start = (int)$request->query->get('start', 1);
        $topic = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (isset($topic)) {
            $managedTopic = new TopicManager(null, $topic);
        } else {
            return;
        }
        // attempt to retrieve return url from hook or create if not available
        $url = $hook->getUrl();
        if (isset($url)) {
            $urlParameters = $url->toArray();
        } else {
            $urlParameters = $request->query->all();
        }
        $returnurlparams = htmlspecialchars(serialize($urlParameters));
        $this->view->assign('returnurl', $returnurlparams);
        list($rankimages, $ranks) = ModUtil::apiFunc(self::MODULENAME, 'Rank', 'getAll', array('ranktype' => RankEntity::TYPE_POSTCOUNT));
        $this->view->assign('ranks', $ranks);
        $this->view->assign('start', $start);
        $this->view->assign('topic', $managedTopic->get()->toArray());
        $this->view->assign('posts', $managedTopic->getPosts(--$start));
        $this->view->assign('pager', $managedTopic->getPager());
        $this->view->assign('permissions', $managedTopic->getPermissions());
        $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
        $this->view->assign('isSubscribed', $managedTopic->isSubscribed());
        $this->view->assign('nextTopic', $managedTopic->getNext());
        $this->view->assign('previousTopic', $managedTopic->getPrevious());
        //$this->view->assign('last_visit', $last_visit);
        //$this->view->assign('last_visit_unix', $last_visit_unix);
        $managedTopic->incrementViewsCount();
        $module = ModUtil::getModule(self::MODULENAME);
        PageUtil::addVar('stylesheet', $module->getRelativePath() . "/Resources/public/css/style.css");
        $hook->setResponse(new DisplayHookResponse(DizkusModuleVersion::PROVIDER_UIAREANAME, $this->view, 'hook/topicview.tpl'));
    }

    /**
     * Display hook for edit.
     * Display a UI interface during the creation of the hooked object.
     *
     * @param DisplayHook $hook The hook.
     *
     * @return string
     */
    public function uiEdit(DisplayHook $hook)
    {
        $hookconfig = ModUtil::getVar($hook->getCaller(), 'dizkushookconfig');
        $forumId = $hookconfig[$hook->getAreaId()]['forum'];
        if (!isset($forumId)) {
            // admin didn't choose a forum, so create one and set as choice
            $managedForum = new ForumManager();
            $data = array(
                'name' => __f('Discussion for %s', $hook->getCaller(), $this->domain),
                'status' => ForumEntity::STATUS_LOCKED,
                'parent' => $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->findOneBy(array(
                    'name' => ForumEntity::ROOTNAME)));
            $managedForum->store($data);
            // cannot notify hooks in non-controller
            $hookconfig[$hook->getAreaId()]['forum'] = $managedForum->getId();
            ModUtil::setVar($hook->getCaller(), 'dizkushookconfig', $hookconfig);
            $forumId = $managedForum->getId();
        }
        $forum = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->find($forumId);
        // add this response to the event stack
        $this->view->assign('forum', $forum->getName());
        $hook->setResponse(new DisplayHookResponse(DizkusModuleVersion::PROVIDER_UIAREANAME, $this->view, 'hook/edit.tpl'));
    }

    /**
     * Display hook for delete.
     *
     * @param DisplayHook $hook The hook.
     *
     * @return string
     */
    public function uiDelete(DisplayHook $hook)
    {
        $topic = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (isset($topic)) {
            $this->view->assign('forum', $topic->getForum()->getName());
            $deleteHookAction = ModUtil::getVar(self::MODULENAME, 'deletehookaction');
            // lock or remove
            $actionWord = $deleteHookAction == 'lock' ? $this->__('locked', $this->domain) : $this->__('deleted', $this->domain);
            $this->view->assign('actionWord', $actionWord);
            $hook->setResponse(new DisplayHookResponse(DizkusModuleVersion::PROVIDER_UIAREANAME, $this->view, 'hook/delete.tpl'));
        }
    }

    /**
     * Validate hook for edit.
     *
     * @param ValidationHook $hook The hook.
     *
     * @return void (unused)
     */
    public function validateEdit(ValidationHook $hook)
    {
        return;
    }

    /**
     * Validate hook for delete.
     *
     * @param ValidationHook $hook The hook.
     *
     * @return void (unused)
     */
    public function validateDelete(ValidationHook $hook)
    {
        return;
    }

    /**
     * Process hook for edit.
     *
     * @param ProcessHook $hook The hook.
     *
     * @return boolean
     */
    public function processEdit(ProcessHook $hook)
    {
        $hookconfig = ModUtil::getVar($hook->getCaller(), 'dizkushookconfig');
        // create new topic in selected forum
        $topic = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (!isset($topic)) {
            $topic = new TopicEntity();
        }
        // use Meta class to create topic data
        $topicMetaInstance = $this->getClassInstance($hook);
        // format data for topic creation
        $data = array(
            'forum_id' => $hookconfig[$hook->getAreaId()]['forum'],
            'title' => $topicMetaInstance->getTitle(),
            'message' => $topicMetaInstance->getContent(),
            'subscribe_topic' => false,
            'attachSignature' => false);
        // create the new topic
        $newManagedTopic = new TopicManager(null, $topic);
        // inject new topic into manager
        $newManagedTopic->prepare($data);
        // add hook data to topic
        $newManagedTopic->setHookData($hook);
        // store new topic
        $newManagedTopic->create();
        // cannot notify hooks in non-controller
        // notify topic & forum subscribers
        ModUtil::apiFunc(self::MODULENAME, 'notify', 'emailSubscribers', array(
            'post' => $newManagedTopic->getFirstPost()));
        $this->view->getRequest()->getSession()->getFlashBag()->add('status', $this->__('Dizkus: Hooked discussion topic created.', $this->domain));

        return true;
    }

    /**
     * Process hook for delete.
     *
     * @param ProcessHook $hook The hook.
     *
     * @return boolean
     */
    public function processDelete(ProcessHook $hook)
    {
        $deleteHookAction = ModUtil::getVar(self::MODULENAME, 'deletehookaction');
        // lock or remove
        $topic = $this->_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (isset($topic)) {
            switch ($deleteHookAction) {
                case 'remove':
                    ModUtil::apiFunc(self::MODULENAME, 'Topic', 'delete', array('topic' => $topic));
                    break;
                case 'lock':
                default:
                    $topic->lock();
                    $this->_em->flush();
                    break;
            }
        }
        $actionWord = $deleteHookAction == 'lock' ? $this->__('locked', $this->domain) : $this->__('deleted', $this->domain);
        $this->view->getRequest()->getSession()->getFlashBag()->add('status', $this->__f('Dizkus: Hooked discussion topic %s.', $actionWord, $this->domain));

        return true;
    }

    /**
     * add hook config options to hooked module's module config
     *
     * @param GenericEvent $z_event
     */
    public static function dizkushookconfig(GenericEvent $z_event)
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
        $view = Zikula_View::getInstance(self::MODULENAME, false);
        $hookconfig = ModUtil::getVar($moduleName, 'dizkushookconfig');
        $module = ModUtil::getModule($moduleName);
        if (isset($module)) {
            $classname = $module->getVersionClass();
        } else {
            $classname = $moduleName . '_Version';
        }
        $moduleVersionObj = new $classname();
        $_em = ServiceUtil::get('doctrine.entitymanager');
        $bindingsBetweenOwners = HookUtil::getBindingsBetweenOwners($moduleName, self::MODULENAME);
        foreach ($bindingsBetweenOwners as $k => $binding) {
            $areaname = $_em->getRepository('Zikula\\Component\\HookDispatcher\\Storage\\Doctrine\\Entity\\HookAreaEntity')->find($binding['sareaid'])->getAreaname();
            $bindingsBetweenOwners[$k]['areaname'] = $areaname;
            $bindingsBetweenOwners[$k]['areatitle'] = $view->__($moduleVersionObj->getHookSubscriberBundle($areaname)->getTitle());
            $hookconfig[$binding['sareaid']]['admincatselected'] = isset($hookconfig[$binding['sareaid']]['admincatselected']) ? $hookconfig[$binding['sareaid']]['admincatselected'] : 0;
            $hookconfig[$binding['sareaid']]['optoverride'] = isset($hookconfig[$binding['sareaid']]['optoverride']) ? $hookconfig[$binding['sareaid']]['optoverride'] : false;
        }
        $view->assign('areas', $bindingsBetweenOwners);
        $view->assign('dizkushookconfig', $hookconfig);
        $view->assign('ActiveModule', $moduleName);
        $view->assign('forums', ModUtil::apiFunc(self::MODULENAME, 'Forum', 'getParents', array('includeLocked' => false)));
        $z_event->setData($view->fetch('hook/modifyconfig.tpl'));
        $z_event->stopPropagation();
    }

    /**
     * process results of dizkushookconfig
     *
     * @param GenericEvent $z_event
     */
    public static function dizkushookconfigprocess(GenericEvent $z_event)
    {
        // check if this is for this handler
        $subject = $z_event->getSubject();
        if (!($z_event['method'] == 'dizkushookconfigprocess' && (strrpos(get_class($subject), '_Controller_Admin') || strrpos(get_class($subject), '\\AdminController')))) {
            return;
        }
        $dom = ZLanguage::getModuleDomain(self::MODULENAME);
        $request = ServiceUtil::get('request');
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
     * Handle module uninstall event "installer.module.uninstalled".
     * Receives $modinfo as $args
     *
     * @param Zikula_Event $z_event
     *
     * @return void
     */
    public static function moduleDelete(Zikula_Event $z_event)
    {
        $module = $z_event['name'];
        $dom = ZLanguage::getModuleDomain(self::MODULENAME);
        $_em = ServiceUtil::get('doctrine.entitymanager');
        $deleteHookAction = ModUtil::getVar(self::MODULENAME, 'deletehookaction');
        // lock or remove
        $topics = $_em->getRepository('Zikula\Module\DizkusModule\Entity\TopicEntity')->findBy(array('hookedModule' => $module));
        $count = 0;
        $total = 0;
        foreach ($topics as $topic) {
            switch ($deleteHookAction) {
                case 'remove':
                    ModUtil::apiFunc(self::MODULENAME, 'Topic', 'delete', array('topic' => $topic));
                    break;
                case 'lock':
                default:
                    $topic->lock();
                    $count++;
                    if ($count > 20) {
                        $_em->flush();
                        $count = 0;
                    }
                    break;
            }
            $total++;
        }
        // clear last remaining batch
        $_em->flush();
        $actionWord = $deleteHookAction == 'lock' ? __('locked', $dom) : __('deleted', $dom);
        if ($total > 0) {
            $request = ServiceUtil::get('request');
            $request->getSession()->getFlashBag()->add('status', __f('Dizkus: All hooked discussion topics %s.', $actionWord, $dom));
        }
    }

    /**
     * populate Services menu with hook option link
     *
     * @param GenericEvent $event
     */
    public static function servicelinks(GenericEvent $event)
    {
        $dom = ZLanguage::getModuleDomain(self::MODULENAME);
        $module = ModUtil::getName();
        $bindingCount = count(HookUtil::getBindingsBetweenOwners($module, self::MODULENAME));
        if ($bindingCount > 0 && $module != self::MODULENAME && (empty($event->data) || is_array($event->data) && !in_array(array(
                    'url' => ModUtil::url($module, 'admin', 'dizkushookconfig'),
                    'text' => __('Dizkus Hook Settings', $dom)), $event->data))) {
            $event->data[] = array(
                'url' => ModUtil::url($module, 'admin', 'dizkushookconfig'),
                'text' => __('Dizkus Hook Settings', $dom));
        }
    }

    /**
     * Factory class to find Meta Class and instantiate
     *
     * @param  ProcessHook $hook
     * @return object of found class
     */
    private function getClassInstance(ProcessHook $hook)
    {
        if (empty($hook)) {
            return false;
        }
        $moduleName = $hook->getCaller();
        $locations = array($moduleName, self::MODULENAME); // locations to search for the class
        foreach ($locations as $location) {
            $moduleObj = ModUtil::getModule($location);
            $classname = null === $moduleObj ? "{$location}_HookedTopicMeta_{$moduleName}" : "\\{$moduleObj->getNamespace()}\\HookedTopicMeta\\$moduleName";
            if (class_exists($classname)) {
                $instance = new $classname($hook);
                if ($instance instanceof AbstractHookedTopicMeta) {
                    return $instance;
                }
            }
        }

        return new Generic($hook);
    }

}
