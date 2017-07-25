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

namespace Zikula\DizkusModule\Helper;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\DizkusModule\ImportHandler\ImportHandlerInterface;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * ImportHelper
 *
 * @author Kaik
 */
class ImportHelper
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @var ImportInterface[]
     */
    private $importHandlers;

    public function __construct(
            RequestStack $requestStack,
            EntityManager $entityManager,
            VariableApi $variableApi
         ) {
        $this->name = 'ZikulaDizkusModule';
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->variableApi = $variableApi;
        $this->importHandlers = [];

    }

    public function getImportHandlers()
    {
        return $this->importHandlers;
    }

    public function addImportHandlder(ImportHandlerInterface $importHandler)
    {
        $this->importHandlers[$importHandler->getId()] = $importHandler;
    }

    public function getImportHandler($id)
    {
        if (!isset($this->importHandlers[$id])) {
            throw new \InvalidArgumentException('Id does not exist!');
        }
        return $this->importHandlers[$id];
    }

    public function hasImportHandler($id)
    {
        return isset($this->importHandlers[$id]);
    }

    public function isUpgrade()
    {
        return $this->variableApi->get('ZikulaDizkusModule', 'upgrading', false);
    }
}