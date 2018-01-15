<?php

/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\DoctrineStorage;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\DizkusModule\ZikulaDizkusModule;

class ModuleListener implements EventSubscriberInterface
{
    private $entityManager;

    private $requestStack;

    private $router;

    private $hookDispatcherStorage;

    private $translator;

    private $variableApi;

    protected $container;

    public function __construct(
        RequestStack $requestStack,
        EntityManager $entityManager,
        RouterInterface $router,
        DoctrineStorage $hookDispatcherStorage,
        TranslatorInterface $translator,
        ContainerInterface $container
    ) {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->hookDispatcherStorage = $hookDispatcherStorage;
        $this->translator = $translator;
        $this->container = $container;
        $this->variableApi = $this->container->get('zikula_extensions_module.api.variable');
    }

    public static function getSubscribedEvents()
    {
        return [
            'zikula.link_collector' => ['serviceLinks'],
            'installer.module.uninstalled' => ['moduleDelete'],
        ];
    }

    /**
     * respond to event 'module_dispatch.service_links'
     * populate Services menu with hook option link.
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function serviceLinks(GenericEvent $event)
    {
        //Disabled due to hooks settings changes
//        $dom = $this->container->get('kernel')->getModule(ZikulaDizkusModule::NAME)->getTranslationDomain();
//        $bindingCount = count($this->hookDispatcherStorage->getBindingsBetweenOwners($event->getSubject(), ZikulaDizkusModule::NAME));
//
//        if ($bindingCount > 0 && $event->getSubject() != ZikulaDizkusModule::NAME && (empty($event->data) || is_array($event->data) && !in_array([
//            'url' => $this->router->generate('zikuladizkusmodule_admin_hookconfig', ['moduleName' => $event->getSubject()]),
//            'text' => $this->translator->__('Dizkus Hook Settings', $dom), ], $event->data))) {
//            $event->data[] = [
//                'url' => $this->router->generate('zikuladizkusmodule_admin_hookconfig', ['moduleName' => $event->getSubject()]),
//                'text' => $this->translator->__('Dizkus Hook Settings', $dom), ];
//        }
    }

    /**
     * respond to event "installer.module.uninstalled".
     * Receives $modinfo as event $args.
     *
     * On module delete handle associated hooked topics
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function moduleDelete(GenericEvent $event)
    {
//        $args = $event->getArguments(); // $modinfo
//        $module = $args['name'];
//        $dom = $this->container->get('kernel')->getModule(ZikulaDizkusModule::NAME)->getTranslationDomain();
//        $deleteHookAction = $this->variableApi->get(ZikulaDizkusModule::NAME, 'deletehookaction');
//        // lock or remove
//        $topics = $this->entityManager->getRepository('Zikula\DizkusModule\Entity\TopicEntity')->findBy(['hookedModule' => $module]);
//        $count = 0;
//        $total = 0;
//        foreach ($topics as $topic) {
//            switch ($deleteHookAction) {
//                case 'remove':
//                    // @TODO add
//                    //ModUtil::apiFunc(ZikulaDizkusModule::NAME, 'Topic', 'delete', ['topic' => $topic]);
//                    break;
//                case 'lock':
//                default:
//                    $topic->lock();
//                    $count++;
//                    if ($count > 20) {
//                        $this->entityManager->flush();
//                        $count = 0;
//                    }
//
//                    break;
//            }
//            $total++;
//        }
//        // clear last remaining batch
//        $this->entityManager->flush();
//        $actionWord = 'lock' == $deleteHookAction ? $this->translator->__('locked', $dom) : $this->translator->__('deleted', $dom);
//        if ($total > 0) {
//            $request = $this->requestStack->getCurrentRequest();
//            $request->getSession()->getFlashBag()->add('status', $this->translator->__f('Dizkus: All hooked discussion topics %s.', $actionWord, $dom));
//        }
    }
}
