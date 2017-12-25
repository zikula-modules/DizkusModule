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

// @todo when News module will be ready

//namespace Zikula\DizkusModule\HookedTopicMeta;

//class News extends AbstractHookedTopicMeta
//{
//    private $newsItem = null;

//    public function setup()
//    {
//        $newsItem = ModUtil::apiFunc('News', 'user', 'get', ['sid' => $this->getObjectId()]);
//        // the api takes care of the permissions check. we must check for pending/expiration & status
//        $expired = isset($newsItem['to']) && strtotime($newsItem['to']) < strtotime('now');
//        $pending = strtotime($newsItem['from']) > strtotime('now');
//        $statuspublished = News_Api_User::STATUS_PUBLISHED == $newsItem['published_status'];
//        if ($newsItem && $statuspublished && !$pending && !$expired) {
//            $this->newsItem = $newsItem;
//        }
//    }

//    public function setTitle()
//    {
//        $this->title = $this->newsItem['title'];
//    }

//    public function setContent()
//    {
//        $this->content = $this->newsItem['hometext'];
//    }
//}
