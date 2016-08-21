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

namespace Zikula\DizkusModule;

use ServiceUtil;
use SecurityUtil;
use ModUtil;
use PageUtil;
use System;
use ZLanguage;
use Zikula_View;
use Zikula\Core\Hook\AbstractHookListener;
use Zikula\Core\Hook\DisplayHook;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\Hook\DisplayHookResponse;
use Zikula\Core\Hook\ValidationHook;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Entity\TopicEntity;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Manager\PostManager;
use Zikula\DizkusModule\Manager\TopicManager;
use Zikula\DizkusModule\HookedTopicMeta\Generic;

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
        if (!SecurityUtil::checkPermission("{$hook->getCaller()}", '::', ACCESS_READ)) {
            return;
        }
        $request = $this->view->getRequest();
        $start = (int)$request->query->get('start', 1);
        $topic = $this->_em->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (isset($topic)) {
            $managedTopic = new TopicManager(null, $topic);
        } else {
            return;
        }
        // attempt to retrieve return url from hook or create if not available
        $url = $hook->getUrl();
        if (isset($url)) {
            $urlParams = $url->toArray();
        } else {
            $urlParams = $request->query->all();
            $route = $request->get('_route');
            if (isset($route)) {
                $urlParams['route'] = $route;
            }
        }
        $returnUrl = htmlspecialchars(json_encode($urlParams));
        $this->view->assign('returnUrl', $returnUrl);
        list(, $ranks) = ModUtil::apiFunc(self::MODULENAME, 'Rank', 'getAll', array('ranktype' => RankEntity::TYPE_POSTCOUNT));
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
        PageUtil::addVar('stylesheet', "@ZikulaDizkusModule/Resources/public/css/style.css");
        $hook->setResponse(new DisplayHookResponse(DizkusModuleVersion::PROVIDER_UIAREANAME, $this->view, 'Hook/topicview.tpl'));
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
        $hookconfig = $this->getHookConfig($hook);
        $forum = $this->_em->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->find($hookconfig[$hook->getAreaId()]['forum']);
        $this->view->assign('forum', $forum->getName());
        $itemId = $hook->getId();
        if (!empty($itemId)) {
            $topic = $this->_em->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
            $this->view->assign('topic', $topic);
            $this->view->assign('newTopic', false);
        } else {
            $this->view->assign('topic', null);
            $this->view->assign('newTopic', true);
        }
        // add this response to the event stack
        $hook->setResponse(new DisplayHookResponse(DizkusModuleVersion::PROVIDER_UIAREANAME, $this->view, 'Hook/edit.tpl'));
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
        $topic = $this->_em->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
        if (isset($topic)) {
            $this->view->assign('forum', $topic->getForum()->getName());
            $deleteHookAction = ModUtil::getVar(self::MODULENAME, 'deletehookaction');
            // lock or remove
            $actionWord = $deleteHookAction == 'lock' ? $this->__('locked', $this->domain) : $this->__('deleted', $this->domain);
            $this->view->assign('actionWord', $actionWord);
            $hook->setResponse(new DisplayHookResponse(DizkusModuleVersion::PROVIDER_UIAREANAME, $this->view, 'Hook/delete.tpl'));
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
        $data = $this->view->getRequest()->request->get('dizkus', null);
        $createTopic = isset($data['createTopic']) ? true : false;
        if ($createTopic) {
            $hookconfig = $this->getHookConfig($hook);
            $topic = $this->_em->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
            // use Meta class to create topic data
            $topicMetaInstance = $this->getClassInstance($hook);
            if (!isset($topic)) {
                // create the new topic
                $newManagedTopic = new TopicManager();
                // format data for topic creation
                $data = array(
                    'forum_id' => $hookconfig[$hook->getAreaId()]['forum'],
                    'title' => $topicMetaInstance->getTitle(),
                    'message' => $topicMetaInstance->getContent(),
                    'subscribe_topic' => false,
                    'attachSignature' => false);
                $newManagedTopic->prepare($data);
                // add hook data to topic
                $newManagedTopic->setHookData($hook);
                // store new topic
                $newManagedTopic->create();
            } else {
                // create new post
                $managedPost = new PostManager();
                $data = array(
                    'topic_id' => $topic->getTopic_id(),
                    'post_text' => $topicMetaInstance->getContent(),
                    'attachSignature' => false);
                // create the post in the existing topic
                $managedPost->create($data);
                // store the post
                $managedPost->persist();
            }
            // cannot notify hooks in non-controller
            // notify topic & forum subscribers
//            ModUtil::apiFunc(self::MODULENAME, 'notify', 'emailSubscribers', array(
//                'post' => $newManagedTopic->getFirstPost()));
            $this->view->getRequest()->getSession()->getFlashBag()->add('status', $this->__('Dizkus: Hooked discussion topic created.', $this->domain));
        }

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
        $topic = $this->_em->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
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

    /**
     * get the modvar settings for hook
     * generates value if not yet set
     *
     * @param $hook
     * @return array
     */
    private function getHookConfig($hook)
    {
        // ModVar: dizkushookconfig => array('areaid' => array('forum' => value))
        $hookconfig = ModUtil::getVar($hook->getCaller(), 'dizkushookconfig', array());
        if (!isset($hookconfig[$hook->getAreaId()]['forum'])) {
            // admin didn't choose a forum, so create one and set as choice
            $managedForum = new ForumManager();
            $data = array(
                'name' => __f('Discussion for %s', $hook->getCaller(), $this->domain),
                'status' => ForumEntity::STATUS_LOCKED,
                'parent' => $this->_em->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->findOneBy(array(
                        'name' => ForumEntity::ROOTNAME)));
            $managedForum->store($data);
            $hookconfig[$hook->getAreaId()]['forum'] = $managedForum->getId();
            ModUtil::setVar($hook->getCaller(), 'dizkushookconfig', $hookconfig);
        }

        return $hookconfig;
    }

}