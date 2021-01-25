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

namespace Zikula\DizkusModule\Form\Type\Hook;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\DizkusModule\Form\Type\Forum\ForumSelectType;

/**
 * TopicProviderBindingType
 *
 * @author Kaik
 */
class TopicProviderBindingType extends AbstractHookType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('topic_mode', ChoiceType::class, [
            'choices' => [
                '0' => 'Admin',
                '1' => 'Owner',
                '2' => 'First comment',
            ],
        ])
        ->add('delete_action', ChoiceType::class, [
            'choices' => [
                'none' => 'Do nothing',
                'lock' => 'Lock topic',
                'remove' => 'Delete topic',
                ],
        ])
        ->add('forum', ForumSelectType::class, [])
        ;
    }

    public function getName()
    {
        return 'topic_provider_binding_type';
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
