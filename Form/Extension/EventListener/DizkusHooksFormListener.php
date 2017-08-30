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
 * DizkusHooksFormListener
 *
 * @author Kaik
 */
class DizkusHooksFormListener extends ResizeFormListener {

    public function preSetData(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = [];
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        foreach ($data as $name => $hookBundle) {
            //global hook bundle settings form
            if ($hookBundle->getSettingsForm()) {
                $class = $hookBundle->getSettingsForm();
                $type = new $class();
                $form->add($name, $type ,array_replace(['property_path' => '[' . $name . ']', ], $this->options));
            }
        }
    }
}
