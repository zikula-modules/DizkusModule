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
class Dizkus_HookedTopicMeta_Generic extends Dizkus_AbstractHookedTopicMeta
{
    private $dom;

    function __construct(Zikula_ProcessHook $hook)
    {
        $this->dom = ZLanguage::getModuleDomain('Dizkus');
        parent::__construct($hook);

        $this->setTitle("");
        $this->setContent("");
    }

    public function setTitle($title)
    {
        unset($title);
        $item = __('item', $this->dom);
        $this->title = "{$this->getModule()} $item (id# {$this->getObjectId()})";
    }

    public function setContent($content)
    {
        unset($content);
        $this->content = __f("Discussion of item at: %s", $this->getLink(), $this->dom);
    }

}