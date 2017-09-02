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

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ModuleType extends AbstractHookType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('enabled', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
        ->add('areas', CollectionType::class, [
                    'entry_type' => new AreaType(),
                    'required' => false
        ])
        ;
    }

    public function getName()
    {
        return 'zikula_dzikus_module_module_type';
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
            'delete_empty' => false,
            'data_class' => 'Zikula\DizkusModule\Hooks\HookedModuleObject'
        ]);
        $resolver->setNormalizer('options', $optionsNormalizer);
    }
}
