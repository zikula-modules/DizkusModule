<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\EventHandlers;

use Zikula\Core\Event\GenericEvent;

class SystemListeners
{
    /**
     * Event: 'user.account.delete'.
     *
     * @param GenericEvent $event
     *
     * @return void
     */
    public function deleteUser(GenericEvent $event)
    {
        // remove subscriptions - topic & forum
        // remove favorites
        // remove moderators
        // mark forumUser somehow?
        // change rank?
    }
}