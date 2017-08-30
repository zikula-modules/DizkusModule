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

namespace Zikula\DizkusModule\Form\Type\Hook;

use Zikula\DizkusModule\Form\Type\Hook\AbstractHookType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

/**
 * AreasType not used?
 *
 * @author Kaik
 */
class AreasType extends AbstractHookType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['allow_add'] && $options['prototype']) {
            $prototype = $builder->create($options['prototype_name'], $options['type'], array_replace([
                'required' => $options['required'],
                'label' => $options['prototype_name'].'label__',
            ], $options['options']));
            $builder->setAttribute('prototype', $prototype->getForm());
        }
    }

    public function getName()
    {
        return 'areas_type';
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $optionsNormalizer = function (Options $options, $value) {
            $value['block_name'] = 'entry';
            return $value;
        };
        $resolver->setDefaults(array(
            'allow_add' => false,
            'allow_delete' => false,
            'prototype' => true,
            'prototype_name' => '__name__',
            'type' => 'text',
            'options' => array(),
            'delete_empty' => false,
//            'data_class' => 'Zikula\DizkusModule\Hooks\TopicProBundle'
        ));
        $resolver->setNormalizer('options', $optionsNormalizer);
    }
}
