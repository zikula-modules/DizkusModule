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
 * PostTextSubBundle
 *
 * @author Kaik
 */
class PostTextSubBundle extends AbstractSubBundle
{
    public function __construct($title = '')
    {
        $owner = 'ZikulaDizkusModule';
        $area = 'subscriber.dizkus.ui_hooks.post_text';
        $category = 'ui_hooks';

        parent::__construct($owner, $area, $category, $title);

        $this->addEvent('display_view', 'dizkus.ui_hooks.post_text.ui_view');
        $this->addEvent('form_edit', 'dizkus.ui_hooks.post_text.ui_edit');
        $this->addEvent('form_delete', 'dizkus.ui_hooks.post_text.ui_delete');
        $this->addEvent('validate_edit', 'dizkus.ui_hooks.post_text.validate_edit');
        $this->addEvent('validate_delete', 'dizkus.ui_hooks.post_text.validate_delete');
        $this->addEvent('process_edit', 'dizkus.ui_hooks.post_text.process_edit');
        $this->addEvent('process_delete', 'dizkus.ui_hooks.post_text.process_delete');
    }
}