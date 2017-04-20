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
     * Occurs after a group is created. All handlers are notified. The full group record created is available
     * as the subject.
     */
    const FORUM_CREATE = 'dizkus.forum.create';

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
     * Occurs before a topic is created. All handlers are notified. The topic data is available
     * as the subject.
     */
    const POST_PREPARE = 'dizkus.post.prepare';
}
