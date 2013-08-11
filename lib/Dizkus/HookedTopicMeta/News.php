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
class Dizkus_HookedTopicMeta_News extends Dizkus_AbstractHookedTopicMeta
{
    private $newsItem = null;

    public function setUp()
    {
        $newsItem = ModUtil::apiFunc('News', 'user', 'get', array('sid' => $this->getObjectId()));
        // the api takes care of the permissions check. we must check for pending/expiration & status
        $expired = (isset($newsItem['to']) && (strtotime($newsItem['to']) < strtotime("now")));
        $pending = (strtotime($newsItem['from']) > strtotime("now"));
        $statuspublished = ($newsItem['published_status'] == News_Api_User::STATUS_PUBLISHED);
        if ($newsItem && $statuspublished && !$pending && !$expired) {
            $this->newsItem = $newsItem;
        }
    }

    public function setTitle()
    {
        $this->title = $this->newsItem['title'];
    }

    public function setContent()
    {
        $this->content = $this->newsItem['hometext'];
    }

}