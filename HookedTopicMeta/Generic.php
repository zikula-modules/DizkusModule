<?php

declare(strict_types=1);

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

class Generic extends AbstractHookedTopicMeta
{
    public function setup()
    {
    }

    public function setTitle()
    {
        $item = __('item');
        $this->title = "{$this->getModule()} {$item} (id# {$this->getObjectId()})";
    }

    public function setContent()
    {
        $this->content = __f('Discussion of item at: %s', $this->getLink());
    }
}
