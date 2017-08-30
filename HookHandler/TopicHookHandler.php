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

namespace Zikula\DizkusModule\HookHandler;

//use Zikula\Bundle\HookBundle\Hook\AbstractHookListener;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
//use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
//use Zikula\DizkusModule\Container\HookContainer;
//use Zikula\DizkusModule\Entity\ForumEntity;
//use Zikula\DizkusModule\Entity\RankEntity;
//use Zikula\DizkusModule\HookedTopicMeta\Generic;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Manager\TopicManager;

//use Zikula\DizkusModule\Manager\PostManager;

/**
 * Hooks Handlers.
 */
class TopicHookHandler extends AbstractHookHandler
{
    protected $forumUserService;

    protected $forumService;

    protected $topicService;

    public function setForumUserService(ForumUserManager $forumUserService)
    {
        $this->forumUserService = $forumUserService;
    }

    public function setForumService(ForumManager $forumService)
    {
        $this->forumService = $forumService;
    }

    public function setTopicService(TopicManager $topicService)
    {
        $this->topicService = $topicService;
    }

    /**
     * Display hook for view.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function uiView(DisplayHook $hook)
    {
        // first check if the user is allowed to do any comments for this module/objectid
//        if (!SecurityUtil::checkPermission("{$hook->getCaller()}", '::', ACCESS_READ)) {
//            return;
//        }

        $currentForumUser = $this->forumUserService->getManager();
        $currentForum = null;
        $currentTopic = null;
        $start = $this->request->get('start', 1);
        $order = $this->request->get('order', $currentForumUser->getPostOrder());

        $config = $this->getHookConfig($hook->getCaller(), $hook->getAreaId());
        if (!$config['forum']) {
            goto display;
        }

        $currentForum = $this->forumService->getManager($config['forum'], null, false);

        if ($currentForum->exists()) {
            if (!empty($hook->getId())) {
                $currentTopic = $this->topicService->getHookedTopicManager($hook, false);
                if ($currentTopic->exists()) {
                    $currentTopic->loadPosts($start - 1, $order)
                            ->incrementViewsCount()
                                ->store();
                }
            }
        }

        display:

        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:topic.view.html.twig', [
            'hook' => $hook,
            'config' => $config,
            'currentForum' => $currentForum,
            'currentForumUser' => $currentForumUser,
            'currentTopic'    => $currentTopic,
            'start'           => $start,
            'order'           => $order,
            'preview'         => false,
            'settings'        => $this->variableApi->getAll($this->name)
        ]);

        $this->uiResponse($hook, $content);
    }

    /**
     * Display hook for edit.
     * Display a UI interface during the creation of the hooked object.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function uiEdit(DisplayHook $hook)
    {
        $currentForumUser = $this->forumUserService->getManager();
        $currentForum = null;
        $currentTopic = null;
        $start = $this->request->get('start', 1);
        $order = $this->request->get('order', $currentForumUser->getPostOrder());

        $config = $this->getHookConfig($hook->getCaller());
        if (!$config || !array_key_exists($hook->getAreaId(), $config)) {
            goto display;
        }

        $currentForum = $this->forumService->getManager($config[$hook->getAreaId()]['forum'], null, false);

        if ($currentForum->exists()) {
            if (!empty($hook->getId())) {
                $currentTopic = $this->topicService->getHookedTopicManager($hook, false);
                if ($currentTopic->exists()) {
                    $currentTopic->loadPosts($start - 1, $order)
                            ->incrementViewsCount()
                                ->store();
                }
            }
        }

        display:

        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:topic.edit.html.twig', [
            'hook' => $hook,
            'currentForum' => $currentForum,
            'currentForumUser' => $currentForumUser,
            'currentTopic'    => $currentTopic,
            'start'           => $start,
            'order'           => $order,
            'preview'         => false,
            'settings'        => $this->variableApi->getAll($this->name)
        ]);

        $this->uiResponse($hook, $content);
    }

    /**
     * Display hook for delete.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function uiDelete(DisplayHook $hook)
    {
        //        $topic = $this->_em->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
//        if (isset($topic)) {
//            $this->view->assign('forum', $topic->getForum()->getName());
//            $deleteHookAction = ModUtil::getVar(self::MODULENAME, 'deletehookaction');
//            // lock or remove
//            $actionWord = $deleteHookAction == 'lock' ? $this->__('locked', $this->domain) : $this->__('deleted', $this->domain);
//            $this->view->assign('actionWord', $actionWord);
//            //   $hook->setResponse(new DisplayHookResponse(HookContainer::PROVIDER_UIAREANAME, $this->view, 'Hook/delete.tpl'));
//        }
    }

    /**
     * Validate hook for edit.
     *
     * @param ValidationHook $hook the hook
     *
     * @return void (unused)
     */
    public function validateEdit(ValidationHook $hook)
    {
    }

    /**
     * Validate hook for delete.
     *
     * @param ValidationHook $hook the hook
     *
     * @return void (unused)
     */
    public function validateDelete(ValidationHook $hook)
    {
    }

    /**
     * Process hook for edit.
     *
     * @param ProcessHook $hook the hook
     *
     * @return bool
     */
    public function processEdit(ProcessHook $hook)
    {
        //        $data = $this->view->getRequest()->request->get('dizkus', null);
//        if (!isset($config[$hook->getAreaId()]['forum'])) {
//            // admin didn't choose a forum, so create one and set as choice
//            $managedForum = new ForumManager();
//            $data = [
//                'name' => __f('Discussion for %s', $hook->getCaller(), $this->domain),
//                'status' => ForumEntity::STATUS_LOCKED,
//                'parent' => $this->_em->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->findOneBy([
//                    'name' => ForumEntity::ROOTNAME,]),];
//            $managedForum->store($data);
//            $hookconfig[$hook->getAreaId()]['forum'] = $managedForum->getId();
//            ModUtil::setVar($hook->getCaller(), 'dizkushookconfig', $hookconfig);
//        }
//        $createTopic = isset($data['createTopic']) ? true : false;
//        if ($createTopic) {
//            $hookconfig = $this->getHookConfig($hook);
//            $topic = $this->_em->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
//            // use Meta class to create topic data
//            $topicMetaInstance = $this->getClassInstance($hook);
//            if (!isset($topic)) {
//                // create the new topic
//                $newManagedTopic = new TopicManager();
//                // format data for topic creation
//                $data = [
//                    'forum_id' => $hookconfig[$hook->getAreaId()]['forum'],
//                    'title' => $topicMetaInstance->getTitle(),
//                    'message' => $topicMetaInstance->getContent(),
//                    'subscribe_topic' => false,
//                    'attachSignature' => false,];
//                $newManagedTopic->prepare($data);
//                // add hook data to topic
//                $newManagedTopic->setHookData($hook);
//                // store new topic
//                $newManagedTopic->create();
//            } else {
//                // create new post
//                $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager(); //new PostManager();
//                $data = [
//                    'topic_id' => $topic->getTopic_id(),
//                    'post_text' => $topicMetaInstance->getContent(),
//                    'attachSignature' => false,];
//                // create the post in the existing topic
//                $managedPost->create($data);
//                // store the post
//                $managedPost->persist();
//            }
//            // cannot notify hooks in non-controller
//            // notify topic & forum subscribers
        ////            ModUtil::apiFunc(self::MODULENAME, 'notify', 'emailSubscribers', array(
        ////                'post' => $newManagedTopic->getFirstPost()));
//            $this->view->getRequest()->getSession()->getFlashBag()->add('status', $this->__('Dizkus: Hooked discussion topic created.', $this->domain));
//        }
//
        return true;
    }

    /**
     * Process hook for delete.
     *
     * @param ProcessHook $hook the hook
     *
     * @return bool
     */
    public function processDelete(ProcessHook $hook)
    {
        //        $deleteHookAction = ModUtil::getVar(self::MODULENAME, 'deletehookaction');
//        // lock or remove
//        $topic = $this->_em->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->getHookedTopic($hook);
//        if (isset($topic)) {
//            switch ($deleteHookAction) {
//                case 'remove':
//                    ModUtil::apiFunc(self::MODULENAME, 'Topic', 'delete', ['topic' => $topic]);
//                    break;
//                case 'lock':
//                default:
//                    $topic->lock();
//                    $this->_em->flush();
//                    break;
//            }
//        }
//        $actionWord = $deleteHookAction == 'lock' ? $this->__('locked', $this->domain) : $this->__('deleted', $this->domain);
//        $this->view->getRequest()->getSession()->getFlashBag()->add('status', $this->__f('Dizkus: Hooked discussion topic %s.', $actionWord, $this->domain));
//
        return true;
    }

    /**
     * Factory class to find Meta Class and instantiate.
     *
     * @param ProcessHook $hook
     *
     * @return object of found class
     */
    private function getClassInstance(ProcessHook $hook)
    {
        //        if (empty($hook)) {
//            return false;
//        }
//        $moduleName = $hook->getCaller();
//        $locations = [$moduleName, self::MODULENAME]; // locations to search for the class
//        foreach ($locations as $location) {
//            $moduleObj = ModUtil::getModule($location);
//            $classname = null === $moduleObj ? "{$location}_HookedTopicMeta_{$moduleName}" : "\\{$moduleObj->getNamespace()}\\HookedTopicMeta\\$moduleName";
//            if (class_exists($classname)) {
//                $instance = new $classname($hook);
//                if ($instance instanceof AbstractHookedTopicMeta) {
//                    return $instance;
//                }
//            }
//        }
//
//        return new Generic($hook);
    }

    /**
     * get settings for hook
     * generates value if not yet set.
     *
     * @param $hook
     *
     * @return array
     */
    public function getHookConfig($module, $areaid = null)
    {
        $config = $this->variableApi->get($module, 'dizkushookconfig', false);
        $default = [
            'forum' => ($config && array_key_exists($areaid, $config) && array_key_exists('forum', $config[$areaid])) ? $config[$areaid]['forum'] : null,
            // 0 - only admin is allowed to enable comments (create topic)
            // 1 - object owner is allowed to enable comments (create topic)
            // 2 - topic is created automatically with first comment
            'threadCreationMode' => ($config && array_key_exists($areaid, $config) && array_key_exists('threadCreationMode', $config[$areaid])) ? $config[$areaid]['threadCreationMode'] : 2,
            ];

        return $default;
    }
}
//
//
//        // attempt to retrieve return url from hook or create if not available
//        $url = $hook->getUrl();
//        if (isset($url)) {
//            $urlParams = $url->toArray();
//        } else {
//            $urlParams = $request->query->all();
//            $route = $request->get('_route');
//            if (isset($route)) {
//                $urlParams['route'] = $route;
//            }
//        }
//        $returnUrl = htmlspecialchars(json_encode($urlParams));
//        $this->view->assign('returnUrl', $returnUrl);
//
//
//
//        list(, $ranks) = ModUtil::apiFunc(self::MODULENAME, 'Rank', 'getAll', ['ranktype' => RankEntity::TYPE_POSTCOUNT]);
//        $this->view->assign('ranks', $ranks);
//        $this->view->assign('start', $start);
//        $this->view->assign('topic', $managedTopic->get()->toArray());
//        $this->view->assign('posts', $managedTopic->getPosts(--$start));
//        $this->view->assign('pager', $managedTopic->getPager());
//        $this->view->assign('permissions', $managedTopic->getPermissions());
//        $this->view->assign('breadcrumbs', $managedTopic->getBreadcrumbs());
//        $this->view->assign('isSubscribed', $managedTopic->isSubscribed());
//        $this->view->assign('nextTopic', $managedTopic->getNext());
//        $this->view->assign('previousTopic', $managedTopic->getPrevious());
//        //$this->view->assign('last_visit', $last_visit);
//        //$this->view->assign('last_visit_unix', $last_visit_unix);
//        $managedTopic->incrementViewsCount();
//        PageUtil::addVar('stylesheet', '@ZikulaDizkusModule/Resources/public/css/style.css');
        //  $hook->setResponse(new DisplayHookResponse(HookContainer::PROVIDER_UIAREANAME, $this->view, 'Hook/topicview.tpl'));

//        $repository = $this->entityManager->getRepository('CmfcmfMediaModule:HookedObject\HookedObjectEntity');
//        $hookedObject = $repository->getByHookOrCreate($hook);