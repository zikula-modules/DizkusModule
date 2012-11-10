<?php
/**
 * Copyright 2009 Zikula Foundation.
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
 * EZComments Hooks Handlers.
 */
class Dizkus_HookHandlers extends Zikula_Hook_AbstractHandler
{

    /**
     * Display hook for view.
     *
     * Subject is the object being viewed that we're attaching to.
     * args[id] Is the id of the object.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_Hook $hook The hook.
     *
     * @return void
     */
    public function uiView(Zikula_DisplayHook $hook)
    {
        // work out the input from the hook
        $mod = $hook->getCaller();
        $objectid = $hook->getId();

        // first check if the user is allowed to do any comments for this module/objectid
        if (!SecurityUtil::checkPermission('Dizkus::', "$mod:$objectid:", ACCESS_OVERVIEW)) {
            return;
        }

   
        $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_reference',
                                 array('reference' => '52-Kaik'));
    
        if ($topic_id <> false) {
            
        $start = 0;
        $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic',
                              array('topic_id'   => $topic_id,
                                    'start'      => $start,
                                    'count'      => true));
                     
        }
   

        // create the output object
        $view = Zikula_View::getInstance('Dizkus', false, null, true);

        $view->assign('areaid', $hook->getAreaId());
        $view->assign('topic', $topic);
        $view->assign('post_count', count($topic['posts']));
        $view->assign('last_visit', $last_visit);
        $view->assign('last_visit_unix', $last_visit_unix);
        $view->assign('modinfo', ModUtil::getInfo(ModUtil::getIdFromName($mod)));
        $view->assign('msgmodule', System::getVar('messagemodule', ''));
        $view->assign('prfmodule', System::getVar('profilemodule', ''));
        $view->assign('allowadd', SecurityUtil::checkPermission('Dizkus::', "$mod:$objectid:", ACCESS_COMMENT));
        $view->assign('loggedin', UserUtil::isLoggedIn());

        $modUrl = $hook->getUrl();
        $redirect = (!is_null($modUrl)) ? $modUrl->getUrl() : '';
        $view->assign('returnurl', $redirect);

        // encode the url - otherwise we can get some problems out there....
        $redirect = base64_encode($redirect);
        $view->assign('redirect', $redirect);
        $view->assign('objectid', $objectid);

        // assign the user is of the content owner
        $view->assign('owneruid', $owneruid);

        // assign url that should be stored in db and sent in email if it
        // differs from the redirect url
        $view->assign('useurl', $useurl);

        // flag to recognize the main call
        static $mainScreen = true;
        $view->assign('mainscreen', $mainScreen);
        $mainScreen = false;
        
        PageUtil::addVar('stylesheet', 'modules/Dizkus/style/style.css');
        
        $hook->setResponse(new Zikula_Response_DisplayHook('provider.dizkus.ui_hooks.comments', $view, DataUtil::formatForOS($templateset) . '/user/topic/view.tpl'));
    }

}
