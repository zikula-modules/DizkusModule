<?php

declare(strict_types=1);

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\DizkusModule\Form\Type\Hook\DizkusHooksProvidersType;
use Zikula\DizkusModule\Form\Type\Hook\DizkusHooksSubscribersType;

class DizkusHooksType extends AbstractType
{
    public function __construct()
    {
        $this->name = 'ZikulaDizkusModule';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                // Hooks P
                ->add('providers', DizkusHooksProvidersType::class)
                // Hooks S
                ->add('subscribers', DizkusHooksSubscribersType::class)
        ;
    }

    public function getName()
    {
        return 'zikula_dizkus_module_hooks_settings';
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
        ]);
        $resolver->setNormalizer('options', $optionsNormalizer);
    }
}
