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
use Zikula\Core\Event\GenericEvent;

/**
 * EZComments Hooks Handlers.
 */
class Dizkus_HookHandlers extends Zikula_Hook_AbstractHandler
{

    /**
     * Zikula_View instance
     * @var Zikula_View
     */
    private $view;
    
    /**
     * Zikula entity manager instance
     * @var Doctrine\ORM\EntityManager
     */
    private $_em;

    /**
     * Post constructor hook.
     *
     * @return void
     */

    public function setup()
    {
        $this->view = Zikula_View::getInstance('Dizkus', false); // set caching off
        $this->_em = ServiceUtil::getService('doctrine.entitymanager');
        $this->domain = ZLanguage::getModuleDomain('Dizkus');
    }

    /**
     * Display hook for view.
     *
     * Subject is the object being viewed that we're attaching to.
     * args[id] Is the id of the object.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_DisplayHook $hook The hook.
     *
     * @return void
     */
    public function uiView(Zikula_DisplayHook $hook)
    {
        // work out the input from the hook
        $mod = $hook->getCaller();
        $objectid = $hook->getId();

        // first check if the user is allowed to do any comments for this module/objectid
        // TODO: This securityschema doesn't exist 
//        if (!SecurityUtil::checkPermission('Dizkus::', "$mod:$objectid:", ACCESS_OVERVIEW)) {
//            return;
//        }

        $start = 0;

        $topic = $this->_em->getRepository('Dizkus_Entity_Topic')
                ->findOneBy(array('reference' => '52-Kaik'));
        if (isset($topic)) {
            $managedTopic = new Dizkus_Manager_Topic(null, $topic);
        }

        $this->view->assign('areaid', $hook->getAreaId());
        $this->view->assign('topic', $topic);
        $this->view->assign('post_count', $managedTopic->getPost_count());
        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);
        $this->view->assign('modinfo', ModUtil::getInfo(ModUtil::getIdFromName($mod)));
        $this->view->assign('msgmodule', System::getVar('messagemodule', ''));
        $this->view->assign('prfmodule', System::getVar('profilemodule', ''));
        $this->view->assign('allowadd', SecurityUtil::checkPermission('Dizkus::', "$mod:$objectid:", ACCESS_COMMENT));
        $this->view->assign('loggedin', UserUtil::isLoggedIn());

        $modUrl = $hook->getUrl();
        $redirect = (!is_null($modUrl)) ? $modUrl->getUrl() : '';
        $this->view->assign('returnurl', $redirect);

        // encode the url - otherwise we can get some problems out there....
        $this->redirect = base64_encode($redirect);
        $this->view->assign('redirect', $redirect);
        $this->view->assign('objectid', $objectid);

        // assign the user is of the content owner
        $this->view->assign('owneruid', $owneruid);

        // assign url that should be stored in db and sent in email if it
        // differs from the redirect url
        $this->view->assign('useurl', $useurl);

        // flag to recognize the main call
        static $mainScreen = true;
        $this->view->assign('mainscreen', $mainScreen);
        $mainScreen = false;

        PageUtil::addVar('stylesheet', 'modules/Dizkus/style/style.css');

        // TODO: This hook area name no longer exists
        $hook->setResponse(new Zikula_Response_DisplayHook('provider.dizkus.ui_hooks.comments', $this->view, DataUtil::formatForOS($templateset) . '/user/topic/view.tpl'));
    }

    public function uiEdit(Zikula_DisplayHook $hook)
    {
        $hookconfig = ModUtil::getVar($hook->getCaller(), 'dizkushookconfig');
        $forumId = $hookconfig[$hook->getAreaId()]['forum'];
        if (!isset($forumId)) {
            // admin didn't choose a forum, so create one and set as choice
            $forum = new Dizkus_Manager_Forum();
            $data = array(
                'name' => __f("Discussion for %s", $hook->getCaller(), ZLanguage::getModuleDomain('Dizkus')),
                'status' => Dizkus_Entity_Forum::STATUS_LOCKED,
                'parent' => Dizkus_Entity_Forum::ROOTNAME,
            );
            $forum->store($data);
            // cannot notify hooks in non-controller
            $hookconfig[$hook->getAreaId()]['forum'] = $forum->getId();
            ModUtil::setVar($hook->getCaller(), 'dizkushookconfig', $hookconfig);
            $forumId = $forum->getId();
        }
        $forum = $this->_em->getRepository('Dizkus_Entity_Forum')->find($forumId);
        echo "Creating a discussion topic in the <em>{$forum->getName()}</em> forum for this item.";
    }

    public function uiDelete(Zikula_DisplayHook $hook)
    {

    }

    public function validateEdit(Zikula_ValidationHook $hook)
    {
        return;
    }

    public function validateDelete(Zikula_ValidationHook $hook)
    {
        return;
    }

    public function processEdit(Zikula_ProcessHook $hook)
    {
        $hookconfig = ModUtil::getVar($hook->getCaller(), 'dizkushookconfig');

        // create new topic in selected forum
        $topic = $this->_em->getRepository('Dizkus_Entity_Topic')->getHookedTopic($hook);
        if (!isset($topic)) {
            $topic = new Dizkus_Entity_Topic();
        }

        // use Meta class to create topic data
        $topicMetaInstance = $this->getClassInstance($hook);

        // format data for topic creation
        $data = array(
            'forum_id' => $hookconfig[$hook->getAreaId()]['forum'],
            'title' => $topicMetaInstance->getTitle(),
            'message' => $topicMetaInstance->getContent(),
            'subscribe_topic' => false,
            'attachSignature' => false,
        );

        // create the new topic
        $newManagedTopic = new Dizkus_Manager_Topic(null, $topic); // inject new topic into manager
        $newManagedTopic->prepare($data);
        // add hook data to topic
        $newManagedTopic->setHookData($hook);

        // store new topic
        $newManagedTopic->create();
        $topicUrl = new Zikula_ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $newManagedTopic->getId()));
        // cannot notify hooks in non-controller

        // notify topic & forum subscribers
        ModUtil::apiFunc('Dizkus', 'notify', 'emailSubscribers', array('post' => $newManagedTopic->getFirstPost()));

        LogUtil::registerStatus($this->__("Dizkus: Hooked discussion topic created.", ZLanguage::getModuleDomain('Dizkus')));

        return true;
    }

    public function processDelete(Zikula_ProcessHook $hook)
    {

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
        if (!($z_event['method'] == 'dizkushookconfig' && (strrpos(get_class($subject), '_Controller_Admin')) ||
                                                       strrpos(get_class($subject), '\\AdminController'))) {
            return;
        }
        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }
        $view = Zikula_View::getInstance('Dizkus', false);
        $hookconfig = ModUtil::getVar($moduleName, 'dizkushookconfig');

        $classname = $moduleName . '_Version';
        $moduleVersionObj = new $classname;
        $_em = ServiceUtil::getService('doctrine.entitymanager');
        $bindingsBetweenOwners = HookUtil::getBindingsBetweenOwners($moduleName, 'Dizkus');
        foreach ($bindingsBetweenOwners as $k => $binding) {
            $areaname = $_em->getRepository('Zikula\Component\HookDispatcher\Storage\Doctrine\Entity\HookAreaEntity')->find($binding['sareaid'])->getAreaname();
            $bindingsBetweenOwners[$k]['areaname'] = $areaname;
            $bindingsBetweenOwners[$k]['areatitle'] = $view->__($moduleVersionObj->getHookSubscriberBundle($areaname)->getTitle());
            $hookconfig[$binding['sareaid']]['admincatselected'] = isset($hookconfig[$binding['sareaid']]['admincatselected']) ? $hookconfig[$binding['sareaid']]['admincatselected'] : 0;
            $hookconfig[$binding['sareaid']]['optoverride'] = isset($hookconfig[$binding['sareaid']]['optoverride']) ? $hookconfig[$binding['sareaid']]['optoverride'] : false;
        }
        $view->assign('areas', $bindingsBetweenOwners);
        $view->assign('dizkushookconfig', $hookconfig);

        $view->assign('ActiveModule', $moduleName);

        $view->assign('forums', ModUtil::apiFunc('Dizkus', 'Forum', 'getParents', array('includeLocked' => false)));

        $z_event->setData($view->fetch('hook/modifyconfig.tpl'));
        $z_event->stop();
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
        if (!($z_event['method'] == 'dizkushookconfigprocess' && (strrpos(get_class($subject), '_Controller_Admin')) ||
                                                                  strrpos(get_class($subject), '\\AdminController'))) {
            return;
        }

        $dom = ZLanguage::getModuleDomain('Dizkus');

        $request = ServiceUtil::getService('request');
        $hookdata = $request->request->get('dizkus', array());
        $token = isset($hookdata['dizkus_csrftoken']) ? $hookdata['dizkus_csrftoken'] : null;
        if (!SecurityUtil::validateCsrfToken($token)) {
            throw new Zikula_Exception_Forbidden(__('Security token validation failed', $dom));
        }
        unset($hookdata['dizkus_csrftoken']);

        $moduleName = $subject->getName();
        if (!SecurityUtil::checkPermission($moduleName . '::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        foreach ($hookdata as $area => $data) {
            if ((!isset($data['forum'])) || (empty($data['forum']))) {
                LogUtil::registerError(__f("Error: No forum selected for area '%s'", $area, $dom));
                $hookdata[$area]['forum'] = null;
            }
        }

        ModUtil::setVar($moduleName, 'dizkushookconfig', $hookdata);
        // ModVar: dizkushookconfig => array('areaid' => array('forum' => value))

        LogUtil::registerStatus(__("Dizkus: Hook option settings updated.", $dom));

        $z_event->setData(true);
        $z_event->stop();
        return System::redirect(ModUtil::url($moduleName, 'admin', 'main'));
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
        $dom = ZLanguage::getModuleDomain('Dizkus');
        $_em = ServiceUtil::getService('doctrine.entitymanager');

        $deleteHookAction = ModUtil::getVar('Dizkus', 'deletehookaction'); // lock or remove

        // take action on all topics with associated module
        // @todo
    }


    /********************
    // @TODO need a handler for when the module is unhooked to remove the hookconfig
    /********************


    /**
     * populate Services menu with hook option link
     *
     * @param GenericEvent $event
     */
    public static function servicelinks(GenericEvent $event)
    {
        $dom = ZLanguage::getModuleDomain('Dizkus');
        $module = ModUtil::getName();
        $bindingCount = count(HookUtil::getBindingsBetweenOwners($module, 'Dizkus'));
        if (($bindingCount > 0) && ($module <> 'Dizkus') && (empty($event->data) || (is_array($event->data)
                && !in_array(array('url' => ModUtil::url($module, 'admin', 'dizkushookconfig'), 'text' => __('Dizkus Hook Settings', $dom)), $event->data)))) {
            $event->data[] = array('url' => ModUtil::url($module, 'admin', 'dizkushookconfig'), 'text' => __('Dizkus Hook Settings', $dom));
        }
    }

    /**
     * Find Meta Class and instantiate
     *
     * @param Zikula_ProcessHook $hook
     * @return instantiated object of found class
     */
    private function getClassInstance(Zikula_ProcessHook $hook)
    {
        if (empty($hook)) {
            return false;
        }
        $module = $hook->getCaller();

        $locations = array($module, 'Dizkus'); // locations to search for the class
        foreach ($locations as $location) {
            $classname = $location . '_HookedTopicMeta_' . $module;
            if (class_exists($classname)) {
                $instance = new $classname($hook);
                if ($instance instanceof Dizkus_AbstractHookedTopicMeta) {
                    return $instance;
                }
            }
        }
        return new Dizkus_HookedTopicMeta_Generic($hook);
    }

}
