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

namespace Zikula\DizkusModule\Hooks;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\ServiceIdTrait;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Manager\TopicManager;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * TopicProBundle
 *
 * @author Kaik
 */
class TopicProBundle extends AbstractProBundle implements HookProviderInterface
{
    use ServiceIdTrait;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EngineInterface
     */
    protected $renderEngine;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var forumUserManagerService
     */
    private $forumUserManagerService;

    /**
     * @var VariableApi
     */
    private $forumManagerService;

    /**
     * @var VariableApi
     */
    private $topicManagerService;

    private $area = 'provider.dizkus.ui_hooks.topic';

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        RequestStack $requestStack,
        EngineInterface $renderEngine,
        VariableApi $variableApi,
        ForumUserManager $forumUserManagerService,
        ForumManager $forumManagerService,
        TopicManager $topicManagerService
    ) {
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->renderEngine = $renderEngine;
        $this->variableApi = $variableApi;
        $this->forumUserManagerService = $forumUserManagerService;
        $this->forumManagerService = $forumManagerService;
        $this->topicManagerService = $topicManagerService;
        parent::__construct();
    }

    public function getCategory()
    {
        return UiHooksCategory::NAME;
    }

    public function getTitle()
    {
        return $this->translator->__('Dizkus Topic Provider');
    }

    public function getProviderTypes()
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'view',
            UiHooksCategory::TYPE_FORM_EDIT => 'edit',
            UiHooksCategory::TYPE_VALIDATE_EDIT => 'validateEdit',
            UiHooksCategory::TYPE_PROCESS_EDIT => 'processEdit',
            UiHooksCategory::TYPE_FORM_DELETE => 'delete',
            UiHooksCategory::TYPE_VALIDATE_DELETE => 'validateDelete',
            UiHooksCategory::TYPE_PROCESS_DELETE => 'processDelete',
        ];
    }

    public function getSettingsForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\TopicProviderSettingsType';
    }

    public function getBindingForm()
    {
        return 'Zikula\\DizkusModule\\Form\\Type\\Hook\\TopicProviderBindingType';
    }

    /**
     * Display hook for view.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function view(DisplayHook $hook)
    {
        // first check if the user is allowed to do any comments for this module/objectid
//        if (!SecurityUtil::checkPermission("{$hook->getCaller()}", '::', ACCESS_READ)) {
//            return;
//        }

        $currentForumUser = $this->forumUserManagerService->getManager();
        $start = $this->request->get('start', 1);
        $order = $this->request->get('order', $currentForumUser->getPostOrder());

        $config = $this->getHookConfig($hook->getCaller(), $hook->getAreaId());
        if (!$config['forum']) {
            $currentForum = null;
            $currentTopic = null;
            goto display;
        }

        $currentForum = $this->forumManagerService->getManager($config['forum'], null, false);

        if ($currentForum->exists()) {
            if (!empty($hook->getId())) {
                $currentTopic = $this->topicManagerService->getHookedTopicManager($hook, false);
                if ($currentTopic->exists()) {
                    $currentTopic->loadPosts($start - 1, $order)
                            ->incrementViewsCount()
                                ->noSync()
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

        $hook->setResponse(new DisplayHookResponse('provider.dizkus.ui_hooks.topic', $content));
    }

    /**
     * Display hook for edit.
     * Display a UI interface during the creation of the hooked object.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function edit(DisplayHook $hook)
    {
        $currentForumUser = $this->forumUserManagerService->getManager();
        $start = $this->request->get('start', 1);
        $order = $this->request->get('order', $currentForumUser->getPostOrder());

        $config = $this->getHookConfig($hook->getCaller(), $hook->getAreaId());
        if (!$config['forum']) {
            $currentForum = null;
            $currentTopic = null;
            goto display;
        }

        $currentForum = $this->forumManagerService->getManager($config['forum'], null, false);

        if ($currentForum->exists()) {
            if (!empty($hook->getId())) {
                $currentTopic = $this->topicManagerService->getHookedTopicManager($hook, false);
                if ($currentTopic->exists()) {
                    $currentTopic->loadPosts($start - 1, $order)
                            ->noSync()
                            ->incrementViewsCount()
                                ->store();
                }
            }
        }

        display:

        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:topic.edit.html.twig', [
            'hook'             => $hook,
            'currentForum'     => $currentForum,
            'currentForumUser' => $currentForumUser,
            'currentTopic'     => $currentTopic,
            'start'            => $start,
            'order'            => $order,
            'preview'          => false,
            'settings'         => $this->variableApi->getAll($this->getOwner())
        ]);

        $hook->setResponse(new DisplayHookResponse('provider.dizkus.ui_hooks.topic', $content));
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

//
//    /**
//     * Factory class to find Meta Class and instantiate.
//     *
//     * @param ProcessHook $hook
//     *
//     * @return object of found class
//     */
//    private function getClassInstance(ProcessHook $hook)
//    {
//        //        if (empty($hook)) {
////            return false;
////        }
////        $moduleName = $hook->getCaller();
////        $locations = [$moduleName, self::MODULENAME]; // locations to search for the class
////        foreach ($locations as $location) {
////            $moduleObj = ModUtil::getModule($location);
////            $classname = null === $moduleObj ? "{$location}_HookedTopicMeta_{$moduleName}" : "\\{$moduleObj->getNamespace()}\\HookedTopicMeta\\$moduleName";
////            if (class_exists($classname)) {
////                $instance = new $classname($hook);
////                if ($instance instanceof AbstractHookedTopicMeta) {
////                    return $instance;
////                }
////            }
////        }
////
////        return new Generic($hook);
//    }

    /**
     * Display hook for delete.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function delete(DisplayHook $hook)
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
     * get settings for hook
     * generates value if not yet set.
     *
     * @param $hook
     *
     * @return array
     */
    public function getHookConfig($module, $areaid = null)
    {
        $default = [
            'forum' => null,
            // 0 - only admin is allowed to enable comments (create topic)
            // 1 - object owner is allowed to enable comments (create topic)
            // 2 - topic is created automatically with first comment
            'threadCreationMode' => 2,
        ];
        // module settings
        $settings = $this->variableApi->get($this->getOwner(), 'hooks', false);
        // this provider config
        $config = array_key_exists(str_replace('.', '-', $this->area), $settings['providers']) ? $settings['providers'][str_replace('.', '-', $this->area)] : null;
        // no configuration for this module return default
        if (null == $config) {
            return $default;
        } else {
            $default['forum'] = array_key_exists('forum', $config) ? $config['forum'] : $default['forum'];
            $default['threadCreationMode'] = array_key_exists('topic_mode', $config) ? $config['topic_mode'] : $default['threadCreationMode'];
        }
        // module provider area module area settings
        if (array_key_exists($module, $config['modules']) && array_key_exists('areas', $config['modules'][$module]) && array_key_exists(str_replace('.', '-', $areaid), $config['modules'][$module]['areas'])) {
            $subscribedModuleAreaSettings = $config['modules'][$module]['areas'][str_replace('.', '-', $areaid)];
            if (array_key_exists('settings', $subscribedModuleAreaSettings)) {
                $default['forum'] = array_key_exists('forum', $subscribedModuleAreaSettings['settings']) ? $subscribedModuleAreaSettings['settings']['forum'] : $default['forum'];
                $default['threadCreationMode'] = array_key_exists('topic_mode', $subscribedModuleAreaSettings['settings']) ? $subscribedModuleAreaSettings['settings']['topic_mode'] : $default['threadCreationMode'];
            }
        }

        return $default;
    }
}
