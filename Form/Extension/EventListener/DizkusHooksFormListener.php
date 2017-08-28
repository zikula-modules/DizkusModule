<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Form\Extension\EventListener;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\FormEvent;

/**
 * Description of ResizeFormListener
 *
 * @author Kaik
 */
class DizkusHooksFormListener extends ResizeFormListener {

    public function preSetData(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }

        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
        }

        // First remove all rows
        foreach ($form as $name => $child) {
            $form->remove($name);
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
