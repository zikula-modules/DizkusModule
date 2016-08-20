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

namespace Zikula\DizkusModule;

use Zikula\Core\UrlInterface;
use Zikula\Core\Hook\ProcessHook;

abstract class AbstractHookedTopicMeta
{

    /**
     * Hooked module object id
     *
     * @var integer
     */
    private $objectId;

    /**
     * Hooked module area id
     *
     * @var string
     */
    private $areaId;

    /**
     * Hooked module
     *
     * @var string
     */
    private $module;

    /**
     * URL for view of hooked object
     *
     * @var UrlInterface
     */
    private $urlObject;

    /**
     * Topic title
     *
     * @var string
     */
    protected $title = '';

    /**
     * Topic post content
     *
     * @var string
     */
    protected $content = '';

    /**
     * Constructor
     *
     * @param ProcessHook $hook
     */
    public function __construct(ProcessHook $hook)
    {
        $this->setObjectId($hook->getId());
        $this->setAreaId($hook->getAreaId());
        $this->setModule($hook->getCaller());
        $this->setUrlObject($hook->getUrl());
        $this->setup();
        $this->setTitle();
        $this->setContent();
    }

    private function setObjectId($id)
    {
        $this->objectId = $id;
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    private function setAreaId($id)
    {
        $this->areaId = $id;
    }

    public function getAreaId()
    {
        return $this->areaId;
    }

    private function setModule($name)
    {
        $this->module = $name;
    }

    public function getModule()
    {
        return $this->module;
    }

    private function setUrlObject(UrlInterface $objectUrlObject)
    {
        $this->urlObject = $objectUrlObject;
    }

    public function getUrlObject()
    {
        return $this->urlObject;
    }

    /**
     * post-constructor setup hook
     */
    protected function setup()
    {

    }

    /**
     * set the title for the topic
     */
    abstract public function setTitle();

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * set the content of the topic's first post
     */
    abstract public function setContent();

    public function getContent()
    {
        return $this->content;
    }

    protected function getLink()
    {
        $title = $this->getTitle();
        $link = null;
        if (!empty($title)) {
            $url = $this->getUrlObject()->getUrl();
            $link = "<a href='{$url}'>{$title}</a>";
        }

        return $link;
    }

}
