<?php

/*
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Form\Extension\EventListener;

use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\FormEvent;

/**
 * AddAreaProviderSettingsFormListener
 *
 * @author Kaik
 */
class AddAreaProviderSettingsFormListener extends ResizeFormListener
{
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = [];
        }
        // @todo maybe some data check?
//        $this->
//        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
//            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
//        }

        if ($data->getForm()) {
            $class = $data->getForm();
            $type = new $class();
            $form->add('settings', $type, array_replace(['property_path' => '[settings]'], $this->options));
        }
    }

    public function onSubmit(FormEvent $event)
    {
        // @todo finish to full sf forms
        $form = $event->getForm();
        $data = $event->getData();
        // At this point, $data is an array or an array-like object that already contains the
        // new entries, which were added by the data mapper. The data mapper ignores existing
        // entries, so we need to manually unset removed entries in the collection.
        if (null === $data) {
            $data = [];
        }

        $event->setData($data);
    }
}
