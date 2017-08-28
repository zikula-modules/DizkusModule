<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Form\Extension\EventListener;

use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\FormEvent;
//use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Zikula\DizkusModule\Form\Type\Hook\AreasType;
/**
 * Description of ResizeFormListener
 *
 * @author Kaik
 */
class ValueResizeFormListener extends ResizeFormListener {

    public function preSetData(FormEvent $event) {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            $data = array();
        }
//        $this->
//        if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
//            throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
//        }

        // First remove all rows
//        foreach ($form as $name => $child) {
//            $form->remove($name);
//        }
//        dump($data);
//        dump($form);

        $form->add('modules', AreasType::class, $this->options);
        $modulesForm  = $form->get('modules');
        $form->remove('modules');

        foreach ($data->getModules() as $name => $binding) {
//                global hook bundle settings form
                if ($data->getBindingForm()) {
                    $class = $data->getBindingForm();
                    $type = new $class();
                    $modulesForm->add($name, $type ,array_replace(['property_path' => '[' . $name . ']', ], $this->options));
                }
        }
        $form->add($modulesForm);
    }
}

//        $form-
//        dump($data->getModules());

//        foreach ($data->getModules() as $name => $binding) {
//                //global hook bundle settings form
//                if ($data->getBindingForm()) {
//                    $class = $data->getBindingForm();
//                    $type = new $class();
//                    $form->add($name, $type ,array_replace(['property_path' => '[' . $name . ']', ], $this->options));
//                }
//        }

//                    $options = array_replace([
//                        'property_path' => '[modules]',
////                        'entry_type' => $type
//                        ], $this->options);
//                $form->add('modules', CollectionType::class , $options);

//        foreach ($hookBundle->getHooked() as $moduleName => $bindings) {
//                //binding settings
//                if ($hookBundle->getBindingForm()) {
////                    $class = $hookBundle->getBindingForm();
////                    $type = new $class();
//                    $options = array_replace([
//                        'property_path' => '[' . $moduleName . ']',
////                        'entry_type' => $type
//                        ], $this->options);
//
//                    dump($options);
//                    $form->add($moduleName, CollectionType::class , $options);
//                }
//            }

//            $nameArr = explode(".", $name);
//            $fieldName = str_replace('.', '_', $name);
//            $class = "Zikula\\DizkusModule\\Form\\Type\Hook\\" . implode('', array_map('ucfirst', $nameArr)) . "Type";
//            foreach( $value['hooked'] as $module => $area) {
//                $fxieldName = $fieldName .'_' . strtolower($module);
//                $type = new $class();
//                $form->add($fxieldName, $type ,array_replace([
//                    'property_path' => '[' . $fxieldName . ']',
//                                ], $this->options));
//            }
//        $mixed = $data[0]['providers'];//array_merge($data['providers'], $data['subscribers']);
//
////        // Then add all rows again in the correct order
//        foreach ($mixed as $name => $value) {
//                $class = $value->getForm();
//                $type = new $class();
//                $form->add($name, $type ,array_replace([
//                    'property_path' => '[' . $name . ']',
//                                ], $this->options));
//        }

//            dump($value->getForm());
//            dump($name)