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

namespace Zikula\DizkusModule\Hooks;

/**
 * PostFilterSubBundle
 *
 * @author Kaik
 */
class PostFilterSubBundle extends AbstractSubBundle
{
    public function __construct($title = '')
    {
        $owner = 'ZikulaDizkusModule';
        $area = 'subscriber.dizkus.filter_hooks.post';
        $category = 'filter_hooks';

        parent::__construct($owner, $area, $category, $title);

        $this->addEvent('filter', 'dizkus.filter_hooks.post.filter');
    }
}
