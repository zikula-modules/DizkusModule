<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\DizkusModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class TextBlockType.
 */
class StatisticsBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('template', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            ])
            ->add('params', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            ])
            ->add('showfooter', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple'                       => false,
                    'expanded'                       => true,
                    'required'                       => true,
                    'data'                           => 1, ]);

        // @todo add show footer setting
    }

    public function getName()
    {
        return 'zikuladizkusmodule_statisticsblock';
    }
}
