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

namespace Zikula\DizkusModule\Form\Type\Hook;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\DizkusModule\Form\Type\Hook\AbstractHookType;
use Zikula\DizkusModule\Form\Type\Hook\ModuleType;

/**
 * TopicProviderSettingsType
 *
 * @author Kaik
 */
class ProviderSettingsType extends AbstractHookType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('display_title', TextType::class, [
                    'required' => false
        ])
        ->add('modules', CollectionType::class, [
                    'entry_type' => new ModuleType(),
                    'required' => false
        ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'subscriber_settings_type';
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
        $resolver->setDefaults([
            'allow_add' => false,
            'allow_delete' => false,
            'prototype' => true,
            'prototype_name' => '__name__',
            'type' => 'text',
            'options' => [],
            'delete_empty' => false
        ]);
        $resolver->setNormalizer('options', $optionsNormalizer);
    }
}
