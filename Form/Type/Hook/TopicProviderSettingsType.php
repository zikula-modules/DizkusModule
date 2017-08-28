<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Form\Type\Hook;

use Zikula\DizkusModule\Form\Type\Hook\AbstractHookType;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Component\Form\Extension\Core\Type\IntegerType;
//use Symfony\Component\Form\Extension\Core\Type\EmailType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Zikula\DizkusModule\Form\Extension\EventListener\ValueResizeFormListener;
use Zikula\DizkusModule\Form\Type\Hook\TopicProviderBindingType;

class TopicProviderSettingsType extends AbstractHookType
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
//
        $resizeListener = new ValueResizeFormListener(
            $options['type'],
            $options['options'],
            $options['allow_add'],
            $options['allow_delete'],
            $options['delete_empty']
        );

        $builder->addEventSubscriber($resizeListener);
        // optional fields
        $builder
        ->add('title', TextType::class, [
                    'required' => false
        ])
//        ->add('modules', CollectionType::class, [
//                    'required' => false
//        ])
        ;
    }

    public function getName()
    {
        return 'topic_provider_settings_type';
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
