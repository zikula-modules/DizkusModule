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

namespace Zikula\DizkusModule\Manager;

use Doctrine\ORM\EntityManager;
use Zikula\ExtensionsModule\Api\CapabilityApi;
use Doctrine\Common\Collections\ArrayCollection;
//use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcher;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\DizkusModule\Container\HookContainer;
use Zikula\DizkusModule\Hooks\HookedModuleObject;
use Zikula\DizkusModule\Hooks\BindingObject;

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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var \Zikula_HookDispatcher
     */
    private $hookDispatcher;

    /**
     * @var CapabilityApi
     */
    private $capabilityApi;

    public function __construct(
        TranslatorInterface $translator,
        EntityManager $entityManager,
        VariableApi $variableApi,
//    @deprecated
        \Zikula_HookDispatcher $hookDispatcher,
        CapabilityApi $capabilityApi
    ) {
        $this->name = 'ZikulaDizkusModule';
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->variableApi = $variableApi;
        $this->hookDispatcher = $hookDispatcher;
        $this->settings = $this->variableApi->getAll($this->name);
        $this->capabilityApi = $capabilityApi;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;

        return true;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getSettingsArray()
    {
        $array = $this->settings->toArray();

        return $array;
    }

    public function getSettingsForForm()
    {
        $settings = $this->settings;
        /*
         * Hmm
         *
         * @todo add hooks category check
         */
        $settings['hooks'] = $this->getHooks();
//        dump($settings);
        return $settings;
    }

    public function saveSettings()
    {
        return $this->variableApi->setAll($this->name, $this->settings);
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
        return ['providers' => $this->getProviders(), 'subscribers' => $this->getSubscribers()];
    }

    public function getProviders()
    {
        $settings = $this->settings['hooks']['providers'];
        $HookContainer = new HookContainer($this->translator);
        $providers = $HookContainer->getHookProviderBundles();
        $providersCollection = new ArrayCollection();
        $subscriberModules = $this->capabilityApi->getExtensionsCapableOf(CapabilityApi::HOOK_SUBSCRIBER);
        foreach ($providers as $provider) {
            $area = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                ->findOneBy(['areaname' => $provider->getArea()]);
            if (!$area) {
                $area = null;

                continue;
            }
            //each provider fresh modules/areas/bindings collection
            $modules = new ArrayCollection();
            foreach ($subscriberModules as $subscriberModule) {
                $moduleObj = new HookedModuleObject($subscriberModule->getName(), $subscriberModule->toArray());
                $class = $subscriberModule->getCapabilities();
                $subscriberModuleHookContainer = new $class['hook_subscriber']['class']($this->translator);
                $areas = array_values($subscriberModuleHookContainer->getHookSubscriberBundles());
                foreach ($areas as $areaa) {
                    $bindingObj = new BindingObject();
                    $bindingObj->setSubscriber($areaa);
                    $bindingObj->setProvider($provider);
                    $bindingObj->setForm($provider->getBindingForm());
                    $moduleObj->getAreas()->set(str_replace('.', '-', $areaa->getArea()), $bindingObj);
                }

                $modules->set($subscriberModule->getName(), $moduleObj);
            }

            $order = new \Doctrine\ORM\Query\Expr\OrderBy();
            $order->add('t.sortorder', 'ASC');
            $order->add('t.pareaid', 'ASC');

            $bindings = $this->entityManager->createQueryBuilder()->select('t')
                             ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                             ->where("t.pareaid = ?1")
                             ->orderBy($order)
                             ->getQuery()->setParameter(1, $area->getId())
                             ->getArrayResult();

            foreach ($bindings as $key => $value) {
                $area = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                     ->findOneBy(['id' => $value['sareaid']]);
                if (!$area) {
                    $area = null;

                    continue;
                }

                $moduleObj = $modules->get($value['sowner']);
                $bindingObj = $moduleObj->getAreas()->get(str_replace('.', '-', $area->getAreaname()));
                $bindingObj->setEnabled(true);
                $moduleObj->getAreas()->set(str_replace('.', '-', $area->getAreaname()), $bindingObj);
                $modules->set($moduleObj->getName(), $moduleObj);
            }

            $provider->setModules($modules);
            $providersCollection->set(str_replace('.', '-', $provider->getArea()), $provider);
        }

        foreach ($providersCollection as $key => $provider) {
            if (null != $settings && array_key_exists(str_replace('.', '-', $provider->getArea()), $settings)) {
                $providerSettings = $settings[str_replace('.', '-', $provider->getArea())];
                $provider->setSettings($providerSettings);
                if (array_key_exists('modules', $providerSettings)) {
                    foreach ($provider->getModules() as $moduleKey => $module) {
                        $moduleSettings = array_key_exists($moduleKey, $providerSettings['modules'])
                            ? $providerSettings['modules'][$moduleKey]
                            : [];
                        $module->setEnabled(array_key_exists('enabled', $moduleSettings) ? $moduleSettings['enabled'] : $module->getEnabled());
                        if (array_key_exists('areas', $moduleSettings)) {
                            foreach ($module->getAreas() as $areaKey => $area) {
                                $areaSettings = array_key_exists($areaKey, $moduleSettings['areas'])
                                    ? $moduleSettings['areas'][$areaKey]
                                    : [];
                                $area->setEnabled(array_key_exists('enabled', $areaSettings) ? $areaSettings['enabled'] : $areaSettings->getEnabled());
                                $area->setSettings($areaSettings);
                            }
                        }
                    }
                }
            }
        }

        return $providersCollection;
    }

    public function getSubscribers()
    {
        // this is zikula 1.4.x specyfic
        $settings = $this->settings['hooks']['subscribers'];
        $HookContainer = new HookContainer($this->translator);
        $subscribers = $HookContainer->getHookSubscriberBundles();
        $subscribersCollection = new ArrayCollection();
        $providerModules = $this->capabilityApi->getExtensionsCapableOf(CapabilityApi::HOOK_PROVIDER);
        foreach ($subscribers as $subscriber) {
            $area = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                      ->findOneBy(['areaname' => $subscriber->getArea()]);
            if (!$area) {
                $area = null;

                continue;
            }
            $modules = new ArrayCollection();
            foreach ($providerModules as $providerModule) {
                $moduleObj = new HookedModuleObject($providerModule->getName(), $providerModule->toArray());
                $class = $providerModule->getCapabilities();
                $providerModuleHookContainer = new $class['hook_provider']['class']($this->translator);
                $areas = array_values($providerModuleHookContainer->getHookProviderBundles());
                foreach ($areas as $areaa) {
                    $bindingObj = new BindingObject();
                    $bindingObj->setSubscriber($subscriber);
                    $bindingObj->setForm($subscriber->getBindingForm());
                    $bindingObj->setProvider($areaa);
                    $moduleObj->getAreas()->set(str_replace('.', '-', $areaa->getArea()), $bindingObj);
                }
                $modules->set($providerModule->getName(), $moduleObj);
            }
            $order = new \Doctrine\ORM\Query\Expr\OrderBy();
            $order->add('t.sortorder', 'ASC');
            $order->add('t.sareaid', 'ASC');

            $bindings = $this->entityManager->createQueryBuilder()->select('t')
                             ->from('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookBindingEntity', 't')
                             ->where("t.sareaid = ?1")
                             ->orderBy($order)
                             ->getQuery()->setParameter(1, $area->getId())
                             ->getArrayResult();

            foreach ($bindings as $key => $value) {
                $area = $this->entityManager->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')
                     ->findOneBy(['id' => $value['pareaid']]);
                if (!$area) {
                    $area = null;

                    continue;
                }
                $moduleObj = $modules->get($value['powner']);
                $bindingObj = $moduleObj->getAreas()->get(str_replace('.', '-', $area->getAreaname()));
                $bindingObj->setEnabled(true);
                $moduleObj->getAreas()->set(str_replace('.', '-', $area->getAreaname()), $bindingObj);
                $modules->set($moduleObj->getName(), $moduleObj);
            }
            $subscriber->setModules($modules);
            $subscribersCollection->set(str_replace('.', '-', $subscriber->getArea()), $subscriber);
        }

        foreach ($subscribersCollection as $key => $subscriber) {
            if (null != $settings && array_key_exists(str_replace('.', '-', $subscriber->getArea()), $settings)) {
                $subscriberSettings = $settings[str_replace('.', '-', $subscriber->getArea())];
                $subscriber->setSettings($subscriberSettings);
                if (array_key_exists('modules', $subscriberSettings)) {
                    foreach ($subscriber->getModules() as $moduleKey => $module) {
                        $moduleSettings = array_key_exists($moduleKey, $subscriberSettings['modules'])
                            ? $subscriberSettings['modules'][$moduleKey]
                            : [];
                        $module->setEnabled(array_key_exists('enabled', $moduleSettings) ? $moduleSettings['enabled'] : $module->getEnabled());
                        if (array_key_exists('areas', $moduleSettings)) {
                            foreach ($module->getAreas() as $areaKey => $area) {
                                $areaSettings = array_key_exists($areaKey, $moduleSettings['areas'])
                                    ? $moduleSettings['areas'][$areaKey]
                                    : [];
                                $area->setEnabled(array_key_exists('enabled', $areaSettings) ? $areaSettings['enabled'] : $areaSettings->getEnabled());
                                $area->setSettings($areaSettings);
                            }
                        }
                    }
                }
            }
        }

        return $subscribersCollection;
    }
}
