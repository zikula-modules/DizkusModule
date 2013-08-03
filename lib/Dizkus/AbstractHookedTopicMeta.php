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
abstract class Dizkus_AbstractHookedTopicMeta
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
     * @var Zikula_ModUrl
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
     * @var string
     */
    protected $content = '';

    /**
     * Constructor
     * 
     * @param integer $objectId
     * @param string $areaId
     * @param string $module
     * @param Zikula_ModUrl $urlObject 
     */
    function __construct(Zikula_ProcessHook $hook)
    {
        $this->setObjectId($hook->getId());
        $this->setAreaId($hook->getAreaId());
        $this->setModule($hook->getCaller());
        $this->setUrlObject($hook->getUrl());
    }

    public function setObjectId($id)
    {
        $this->objectId = $id;
    }

    public function getObjectId()
    {
        return $this->objectId;
    }

    public function setAreaId($id)
    {
        $this->areaId = $id;
    }

    public function getAreaId()
    {
        return $this->areaId;
    }

    public function setModule($name)
    {
        $this->module = $name;
    }

    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set the object's Url Object
     * @param Zikula_ModUrl $objectUrlObject 
     */
    public function setUrlObject(Zikula_ModUrl $objectUrlObject)
    {
        $this->urlObject = $objectUrlObject;
    }

    /**
     * Get the object's Url Object
     * @return Zikula_ModUrl
     */
    public function getUrlObject()
    {
        return $this->urlObject;
    }

    abstract public function setTitle($title);

    public function getTitle()
    {
        return $this->title;
    }

    abstract public function setContent($content);

    public function getContent()
    {
        return $this->content;
    }

    public function getLink()
    {
        $title = $this->getTitle();
        $link = null;
        if (!empty($title)) {
            $url = $this->getUrlObject()->getUrl();
            $link = "<a href='$url'>$title</a>";
        }
        return $link;
    }

}