<?php

declare(strict_types=1);

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
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\Hook;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\ValidationHook;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\ServiceIdTrait;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\HookedTopicMeta\AbstractHookedTopicMeta;
use Zikula\DizkusModule\HookedTopicMeta\Generic;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\PostManager;
use Zikula\DizkusModule\Manager\TopicManager;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Api\PermissionApi;

/**
 * TopicProBundle
 *
 * @author Kaik
 */
class TopicProBundle extends AbstractProBundle implements HookProviderInterface
{
    use ServiceIdTrait;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

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
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * @var ForumUserManager
     */
    private $forumUserManagerService;

    /**
     * @var ForumManager
     */
    private $forumManagerService;

    /**
     * @var TopicManager
     */
    private $topicManagerService;

    /**
     * @var PostManager
     */
    private $postManagerService;

    /**
     * @var area
     */
    private $area = 'provider.dizkus.ui_hooks.topic';

    /**
     * Construct the manager
     */
    public function __construct(
        ZikulaHttpKernelInterface $kernel,
        TranslatorInterface $translator,
        RouterInterface $router,
        RequestStack $requestStack,
        EngineInterface $renderEngine,
        VariableApi $variableApi,
        PermissionApi $permissionApi,
        ForumUserManager $forumUserManagerService,
        ForumManager $forumManagerService,
        TopicManager $topicManagerService,
        PostManager $postManagerService
    ) {
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->renderEngine = $renderEngine;
        $this->variableApi = $variableApi;
        $this->permissionApi = $permissionApi;
        $this->forumUserManagerService = $forumUserManagerService;
        $this->forumManagerService = $forumManagerService;
        $this->topicManagerService = $topicManagerService;
        $this->postManagerService = $postManagerService;
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
        if (!$this->permissionApi->hasPermission("{$hook->getCaller()}", '::', ACCESS_READ)) {
            return;
        }

        $currentForumUser = $this->forumUserManagerService->getManager();
        // comments pager
        $start = $this->request->get('start', 1);
        $order = $this->request->get('order', $currentForumUser->getPostOrder());
        // preview marker
        $preview = false;
        // configuration of this hook behaviour
        $config = $this->getHookConfig($hook->getCaller(), $hook->getAreaId());
        if (!$config['forum']) {
            $currentForum = null;
            $currentTopic = null;
            goto display;
        }

        $currentForum = $this->forumManagerService->getManager($config['forum'], null, false);
        if ($currentForum->exists()) {
            // forum exists
            // prepare request for topic create/reply controller
            $this->request->attributes->set('template', 'comment');
            $this->request->attributes->set('format', 'ajax.html');
            if (!empty($hook->getId())) {
                $currentTopic = $this->topicManagerService->getHookedTopicManager($hook, false);
                if ($currentTopic->exists()) {
                    // Topic exists user either wants to see comments or add reply
                    // Reply marker is in request post
                    // $form = $this->request->get('zikula_dizkus_form_topic_reply');
                    // @todo reply creation
                    // load posts with or without reply and display
                    $currentTopic->loadPosts($start - 1, $order)
                            ->incrementViewsCount()
                                ->noSync()
                                ->store();
                } else {
                    // create topic if there is comment data in request POST
                    $topicMetaInstance = $this->getClassInstance($hook);
                    if (2 === $config['topic_mode'] && $topicMetaInstance instanceof AbstractHookedTopicMeta) {
//                    $form = $this->request->get('zikula_dizkus_form_topic_new');
//                    dump($form);
//                        if (array_key_exists('posts', $form) && array_key_exists(0, $form['posts']) && array_key_exists('post_text', $form['posts'][0])) {
//                            // create the new topic
//                            $currentTopic->create();
//                            $topic = $currentTopic->get();
//                            $topic->setForum($currentForum->get());
//                            $topic->setTitle($topicMetaInstance->getTitle());
//                            $topic->setPoster($currentForumUser->get());
//                            // this is first post
//                            $managedPost = $this->postManagerService->getManager();
//                            $post = $managedPost->get();
//                            $post->setIsFirstPost(true);
//                            $post->setPostText($topicMetaInstance->getContent());
//                            $post->setPoster($currentForumUser->get());
//                            $post->setTopic($topic);
//                            $topic->addPost($post);
//                            // this is comment
//                            $managedComment = $this->postManagerService->getManager();
//                            $comment = $managedComment->get();
//                            $comment->setPostText($form['posts'][0]['post_text']);
//                            $comment->setPoster($currentForumUser->get());
//                            $comment->setTopic($topic);
//                            $topic->addPost($comment);
//                            $currentTopic->update($topic);
//                            // add hook data to topic
//                            $currentTopic->setHookData($hook);
//                            // store new topic
//                            if (array_key_exists('preview', $form)) {
//                                $preview = true;
//                            } elseif (array_key_exists('save', $form)) {
//                                $currentTopic->store();
//                            }
//                        }
                    }
                }
            }
        }

        display:

        $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:topic.view.html.twig', [
            'hook'             => $hook,
            'config'           => $config,
            'currentForum'     => $currentForum,
            'currentForumUser' => $currentForumUser,
            'currentTopic'     => $currentTopic,
            'start'            => $start,
            'order'            => $order,
            'preview'          => $preview,
            'settings'         => $this->variableApi->getAll($this->getOwner())
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
            'config'           => $config,
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
     * This function creates topic only under specific circumstances
     * - forum is set and exists
     * - topic mode is either 0 or 1 (2 is automatic on first post)
     * - createTopic is set to true
     *
     * When topic does not exists and comments are enabled nothing will be shown unless
     * mode is set to 2 in this case first comment form will be shown
     *
     * When mode is set to 0 or 1 either admin or item creator/owner/editor is able to create topic and enable comments on this item
     *
     * @param ProcessHook $hook the hook
     *
     * @return bool
     */
    public function processEdit(ProcessHook $hook)
    {
        $currentForumUser = $this->forumUserManagerService->getManager(null, true);
        $config = $this->getHookConfig($hook->getCaller(), $hook->getAreaId());
        if (!$config['forum']) {
            return true;
            // @todo decide if automatic forum creation is a good idea
        }

        $data = $this->request->get('dizkus', null);
        // in case of mode 2 topic will be created with the first comment
        $createTopic = (0 === $config['topic_mode'] || 1 === $config['topic_mode'] && is_array($data) && array_key_exists('createTopic', $data) && $data['createTopic'])
                        ? true : false;
        if ($createTopic) {
            $managedTopic = $this->topicManagerService->getHookedTopicManager($hook, false);
            // use Meta class to create topic data
            $topicMetaInstance = $this->getClassInstance($hook);
            if (!$topicMetaInstance) {
                return true; // return silently
            }

            if (!$managedTopic->exists()) {
                $managedForum = $this->forumManagerService->getManager($config['forum'], null, false);
                if (!$managedForum->exists()) {
                    return true; //sorry no forum no topic
                }
                // create the new topic
                $managedTopic->create();
                $topic = $managedTopic->get();
                $topic->setForum($managedForum->get());
                $topic->setTitle($topicMetaInstance->getTitle());
                $topic->setPoster($currentForumUser->get());
                $managedPost = $this->postManagerService->getManager();
                $post = $managedPost->get();
                $post->setIsFirstPost(true);
                $post->setPostText($topicMetaInstance->getContent());
                $post->setPoster($currentForumUser->get());
                $post->setTopic($topic);
                $topic->addPost($post);
                $managedTopic->update($topic);
                // add hook data to topic
                $managedTopic->setHookData($hook);
                // store new topic
                $managedTopic->store();
            }
        }

        return true;
    }

    /**
     * Display hook for delete.
     *
     * @param DisplayHook $hook the hook
     *
     * @return string
     */
    public function delete(DisplayHook $hook)
    {
        $managedTopic = $this->topicManagerService->getHookedTopicManager($hook, false);
        if ($managedTopic->exists()) {
            $config = $this->getHookConfig($hook->getCaller(), $hook->getAreaId());
            $content = $this->renderEngine->render('ZikulaDizkusModule:Hook:topic.delete.html.twig', [
                'hook'             => $hook,
                'config'           => $config,
                'managedForum'     => $managedTopic->getManagedForum(),
                'managedTopic'     => $managedTopic
            ]);

            $hook->setResponse(new DisplayHookResponse('provider.dizkus.ui_hooks.topic', $content));
        }
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
        $config = $this->getHookConfig($hook->getCaller(), $hook->getAreaId());
        $managedTopic = $this->topicManagerService->getHookedTopicManager($hook, false);
        if ($managedTopic->exists() && array_key_exists('delete_action', $config)) {
            switch ($config['delete_action']) {
                case 'remove':
                    $managedTopic->delete();
                    $this->request->getSession()->getFlashBag()->add('status', $this->translator->__('Dizkus: Hooked discussion topic removed.'));

                    break;
                case 'lock':
                    $managedTopic->lock()
                        ->noSync() // no need to sync on lock
                        ->store();
                    $this->request->getSession()->getFlashBag()->add('status', $this->translator->__('Dizkus: Hooked discussion topic locked.'));

                    break;
                default: // do nothig
                    break;
            }
        }

        return true;
    }

    /**
     * Factory class to find Meta Class and instantiate.
     *
     * @return object of found class
     */
    private function getClassInstance(Hook $hook)
    {
        if (empty($hook)) {
            return false;
        }

        $locations = [$hook->getCaller(), $this->getOwner()]; // locations to search for the class
        foreach ($locations as $location) {
            try {
                $moduleObj = $this->kernel->getModule($location);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
            $classname = "\\{$moduleObj->getNamespace()}\\HookedTopicMeta\\{$hook->getCaller()}";
            if (class_exists($classname)) {
                $instance = new $classname($hook);
                if ($instance instanceof AbstractHookedTopicMeta) {
                    $instance->setTranslator($this->translator);

                    return $instance;
                }
            }
        }

        return new Generic($hook);
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
            'topic_mode' => 2,
            // none - nothing happens to topic when object is deleted
            // lock - topic is locked
            // delete - topic is removed
            'delete_action' => 'none',
        ];
        // module settings
        $settings = $this->variableApi->get($this->getOwner(), 'hooks', false);
        // this provider config
        $config = array_key_exists(str_replace('.', '-', $this->area), $settings['providers']) ? $settings['providers'][str_replace('.', '-', $this->area)] : null;
        // no configuration for this module return default
        if (null === $config) {
            return $default;
        }
        $default['forum'] = array_key_exists('forum', $config) ? $config['forum'] : $default['forum'];
        $default['topic_mode'] = array_key_exists('topic_mode', $config) ? $config['topic_mode'] : $default['topic_mode'];
        $default['delete_action'] = array_key_exists('delete_action', $config) ? $config['delete_action'] : $default['delete_action'];

        // module provider area module area settings
        if (array_key_exists($module, $config['modules']) && array_key_exists('areas', $config['modules'][$module]) && array_key_exists(str_replace('.', '-', $areaid), $config['modules'][$module]['areas'])) {
            $subscribedModuleAreaSettings = $config['modules'][$module]['areas'][str_replace('.', '-', $areaid)];
            if (array_key_exists('settings', $subscribedModuleAreaSettings)) {
                $default['forum'] = array_key_exists('forum', $subscribedModuleAreaSettings['settings']) ? $subscribedModuleAreaSettings['settings']['forum'] : $default['forum'];
                $default['topic_mode'] = array_key_exists('topic_mode', $subscribedModuleAreaSettings['settings']) ? $subscribedModuleAreaSettings['settings']['topic_mode'] : $default['topic_mode'];
                $default['delete_action'] = array_key_exists('delete_action', $subscribedModuleAreaSettings['settings']) ? $subscribedModuleAreaSettings['settings']['delete_action'] : $default['delete_action'];
            }
        }

        return $default;
    }
}
//else {
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
//        }
         //            $hookconfig = $this->getHookConfig($hook);
            // admin didn't choose a forum, so create one and set as choice
//            $managedForum = new ForumManager();
//            $data = [
//                'name' => __f('Discussion for %s', $hook->getCaller(), $this->domain),
//                'status' => ForumEntity::STATUS_LOCKED,
//                'parent' => $this->_em->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->findOneBy([
//                    'name' => ForumEntity::ROOTNAME,]),];
//            $managedForum->store($data);
//            $hookconfig[$hook->getAreaId()]['forum'] = $managedForum->getId();
//            ModUtil::setVar($hook->getCaller(), 'dizkushookconfig', $hookconfig);
            //                $hookconfig[$hook->getAreaId()]['forum'];
                // format data for topic creation
//                $topicMetaInstance->getContent()
//                $data = [
//                    'forum_id' => ,
//                    'title' => ,
//                    'message' => ,
//                    'subscribe_topic' => false,
//                    'attachSignature' => false,];
//            // cannot notify hooks in non-controller
//            // notify topic & forum subscribers
        ////            ModUtil::apiFunc(self::MODULENAME, 'notify', 'emailSubscribers', array(
        ////                'post' => $newManagedTopic->getFirstPost()));
//            $this->view->getRequest()->getSession()->getFlashBag()->add('status', $this->__('Dizkus: Hooked discussion topic created.', $this->domain));
//        , 'forum': currentForum.get.id , '_format': 'ajax.html', 'template' : 'comment'
