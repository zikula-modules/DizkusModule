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

namespace Zikula\DizkusModule\HookedTopicMeta;

use ZLanguage;
use Zikula\DizkusModule\AbstractHookedTopicMeta;

class Generic extends AbstractHookedTopicMeta
{

    private $dom;

    public function setup()
    {
        $this->dom = ZLanguage::getModuleDomain('ZikulaDizkusModule');
    }

    public function setTitle()
    {
        $item = __('item', $this->dom);
        $this->title = "{$this->getModule()} {$item} (id# {$this->getObjectId()})";
    }

    public function setContent()
    {
        $this->content = __f('Discussion of item at: %s', $this->getLink(), $this->dom);
    }

}
