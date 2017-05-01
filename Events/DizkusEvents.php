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

namespace Zikula\DizkusModule\Events;

/**
 *
 */
class DizkusEvents
{
    /**
     * Occurs before a forum is created. All handlers are notified. The forum data is available
     * as the subject.
     */
    const FORUM_PREPARE = 'dizkus.forum.prepare';

    /**
     * Occurs after a forum is created. All handlers are notified. The full forum record created is available
     * as the subject.
     */
    const FORUM_CREATE = 'dizkus.forum.create';

    /**
     *
     * TOPIC EVENTS
     */

    /**
     * Occurs before a topic is created. All handlers are notified. The topic data is available
     * as the subject.
     */
    const TOPIC_PREPARE = 'dizkus.topic.prepare';

    /**
     * Occurs after a topic is created. All handlers are notified. The new topic record is available
     * as the subject.
     */
    const TOPIC_CREATE = 'dizkus.topic.create';

    /**
     * Occurs after a topic is updated. All handlers are notified. The topic record is available
     * as the subject.
     */
    const TOPIC_UPDATE = 'dizkus.topic.update';

    /**
     * Occurs after a topic is replayed. All handlers are notified. The topic record is available
     * as the subject.
     */
    const TOPIC_REPLY = 'dizkus.topic.reply';

    /**
     * Occurs after a topic is joined. All handlers are notified. The topic record is available
     * as the subject.
     */
    const TOPIC_JOIN = 'dizkus.topic.join';

    /**
     * Occurs after a topic is moved. All handlers are notified. The topic record is available
     * as the subject.
     */
    const TOPIC_MOVE = 'dizkus.topic.move';

    /**
     * Occurs after a topic is splited. All handlers are notified. The topic record is available
     * as the subject.
     */
    const TOPIC_SPLIT = 'dizkus.topic.split';

    /**
     * Occurs after a topic is deleted. All handlers are notified. The unmanaged topic is available
     * as the subject.
     */
    const TOPIC_DELETE = 'dizkus.topic.delete';

    /**
     *
     * POST EVENTS
     */

    /**
     * Occurs before a post is created. All handlers are notified. The post data is available
     * as the subject.
     */
    const POST_PREPARE = 'dizkus.post.prepare';

    /**
     * Occurs after a post is created. All handlers are notified. The post data is available
     * as the subject.
     */
    const POST_CREATE = 'dizkus.post.create';

    /**
     * Occurs after a post is updated. All handlers are notified. The post data is available
     * as the subject.
     */
    const POST_UPDATE = 'dizkus.post.update';

    /**
     * Occurs after a post is moved. All handlers are notified. The post data is available
     * as the subject.
     */
    const POST_MOVE = 'dizkus.post.move';

    /**
     * Occurs after a post is deleted. All handlers are notified. The post data is available
     * as the subject.
     */
    const POST_DELETE = 'dizkus.post.delete';

    /**
     * Occurs when notification is needed. All handlers are notified. The post data is available
     * as the subject.
     */
    const POST_NOTIFY_MODERATOR = 'dizkus.post.notify.moderator';
}