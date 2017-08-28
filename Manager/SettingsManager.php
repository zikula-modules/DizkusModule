<?php
/**
 * Copyright Dizkus Team 2012.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\DizkusModule\Manager;

use Doctrine\ORM\EntityManager;
//use Doctrine\Common\Collections\AbstractLazyCollection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcher;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\Helper\SynchronizationHelper;
use Zikula\DizkusModule\Security\Permission;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Api\CurrentUserApi;

/**
 * Settings manager
 */
class SettingsManager
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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CurrentUserApi
     */
    private $userApi;

    /**
     * @var Permission
     */
    private $permission;

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

    /**
     * @var synchronizationHelper
     */
    private $synchronizationHelper;

    /**
     * @var \Zikula_HookDispatcher
     */
    private $hookDispatcher;

    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        RequestStack $requestStack,
        EntityManager $entityManager,
        CurrentUserApi $userApi,
        Permission $permission,
        VariableApi $variableApi,
        ForumUserManager $forumUserManagerService,
        ForumManager $forumManagerService,
        TopicManager $topicManagerService,
        SynchronizationHelper $synchronizationHelper,
//    @deprecated
        \Zikula_HookDispatcher $hookDispatcher
    ) {
        $this->name = 'ZikulaDizkusModule';
        $this->translator = $translator;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->userApi = $userApi;
        $this->permission = $permission;
        $this->variableApi = $variableApi;

        $this->forumUserManagerService = $forumUserManagerService;
        $this->forumManagerService = $forumManagerService;
        $this->topicManagerService = $topicManagerService;
        $this->synchronizationHelper = $synchronizationHelper;
        $this->hookDispatcher = $hookDispatcher;
        $this->settings = $this->variableApi->getAll($this->name);

    }

    public function setSettings($settings) {

//        if(!$settings->containsKey($this->name)){
//           return false;
//        }
//
//        $this->settings->postSubmit($settings);

        return true;
    }
    public function getSettings() {

        return $this->settings;
    }

    public function getSettingsArray() {

        $array = $this->settings->toArray();
        return $array;
    }

    public function getSettingsForForm() {

        $settings = $this->settings;
        /*
         * Hmm
         *
         */
        $settings['hooks'] = $this->getHooks();
//        dump($settings);
        return $settings;
    }

    public function saveSettings() {

        return $this->variablesManager->setAll($this->name, $this->settings->toArrayForStorage());
    }

    public function getAdmins()
    {
        $adminsGroup = $this->entityManager->getRepository('Zikula\GroupsModule\Entity\GroupEntity')->find(2);
        $admins = ['-1' => $this->translator->__('disabled')];
        foreach ($adminsGroup['users'] as $admin) {
            $admins[$admin->getUid()] = $admin->getUname();
        }

        return $admins;
    }

    public function getHooks()
    {
        $HookContainer = new \Zikula\DizkusModule\Container\HookContainer($this->translator);
        $hooks['providers'] = array_values($HookContainer->getHookProviderBundles());
        foreach ($hooks['providers'] as $provider) {
            $area = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                ->findOneBy(['areaname' => $provider->getArea()]);

            if (!$area) {
//                $hooks['providers'][$area] = null;

                continue;
            }
            $provider->setAreaData($area);

            $areaIdField = ($area->getAreatype() == 'p') ? 'pareaid' : 'sareaid';
            $order = new \Doctrine\ORM\Query\Expr\OrderBy();
            $order->add('t.sortorder', 'ASC');
            $order->add('t.sareaid', 'ASC');
            $bindings = $this->entityManager->createQueryBuilder()->select('t')
                             ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                             ->where("t.$areaIdField = ?1")
                             ->orderBy($order)
                             ->getQuery()->setParameter(1, $area->getId())
                             ->getArrayResult();

            $provider->setBindings($bindings);

            foreach ($bindings as $binding) {
                $s = $this->variableApi->get($binding['sowner'], 'dizkushookconfig');
                $settings[$binding['sowner']][$binding['sareaid']] = (is_array($s) && array_key_exists($binding['sareaid'], $s)) ? $s[$binding['sareaid']] : [];
            }

            $provider->setSettings($settings);

            $provider->getHookedModules();

        }

        return $hooks;
    }

    public function getHookedObject($param)
    {



        return $hooked;
    }
}



           //     $module[$binding['sowner']] = $binding;
////                $a = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
////                    ->findOneBy(['id' => $binding['sareaid']]);

//        $pareas = $this->hookDispatcher->getProviderAreasByOwner($this->name);
//        foreach ($pareas as $area ) {
//            $area = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
//                ->findOneBy(['areaname' => $area]);
//
//            if (!$area) {
////                $hooks['providers'][$area] = null;
//
//                continue;
//            }


//            $order = new \Doctrine\ORM\Query\Expr\OrderBy();
//            $order->add('t.sortorder', 'ASC');
//            $order->add('t.sareaid', 'ASC');
//            $results = $this->entityManager->createQueryBuilder()->select('t')
//                             ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
//                             ->where("t.$areaIdField = ?1")
//                             ->orderBy($order)
//                             ->getQuery()->setParameter(1, $area->getId())
//                             ->getArrayResult();
//
//                            //$hooks['providers'][$area->getId()]['hooked'] = [];
//                             foreach ($results as $binding) {
//                                $data = $binding;
//                                $a = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
//                                    ->findOneBy(['id' => $binding['sareaid']]);
//                                if (!$a) {
//                                    $data['sarea'] = null;
//                                }
//                                $hooks['providers'][] = $a;
////                                $data['sarea'] = $a;
////                                $s = $this->variableApi->get($binding['sowner'], 'dizkushookconfig');
////                                $data['settings'] = (is_array($s) && array_key_exists($binding['sareaid'], $s)) ? $s[$binding['sareaid']] : [];
//
//
////                                $hooks['providers'][$area->getId()]['hooked'][$binding['sowner']][] = $data;
//                             }

//            $hooks[0]['providers'][] = $HookContainer->getHookProviderBundle($area->getAreaname());
//        }

//        dump($hooks);

//        $hooks['subscribers'] = [];
//        $sareas = $this->hookDispatcher->getSubscriberAreasByOwner($this->name);
//        foreach ($sareas as $area ) {
//            $area = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
//                ->findOneBy(['areaname' => $area]);
//            if (!$area) {
//                $hooks['subscribers'][$area] = null;
//
//                continue;
//            }
//
//            $areaIdField = ($area->getAreatype() == 'p') ? 'pareaid' : 'sareaid';
//            $order = new \Doctrine\ORM\Query\Expr\OrderBy();
//            $order->add('t.sortorder', 'ASC');
//            $order->add('t.sareaid', 'ASC');
//            $results = $this->entityManager->createQueryBuilder()->select('t')
//                             ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
//                             ->where("t.$areaIdField = ?1")
//                             ->orderBy($order)
//                             ->getQuery()->setParameter(1, $area->getId())
//                             ->getArrayResult();
//
//                            $hooks['subscribers'][$area->getId()]['hooked'] = [];
//                             foreach ($results as $binding) {
//                                $data = $binding;
//                                $a = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
//                                    ->findOneBy(['id' => $binding['pareaid']]);
//                                if (!$a) {
//                                    $data['parea'] = null;
//                                }
//
//                                $data['parea'] = $a;
//                                $s = $this->variableApi->get($binding['powner'], 'dizkushookconfig');
//                                $data['settings'] = (is_array($s) && array_key_exists($binding['pareaid'], $s)) ? $s[$binding['pareaid']] : [];
//                                $hooks['subscribers'][$area->getId()]['hooked'][$binding['powner']][] = $data;
//                             }
//
//            $hooks['subscribers'][$area->getId()]['area'] = $HookContainer->getHookSubscriberBundle($area->getAreaname());
//        }
