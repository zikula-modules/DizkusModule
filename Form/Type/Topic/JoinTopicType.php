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

namespace Zikula\DizkusModule\Form\Type\Topic;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JoinTopicType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;
        $builder
            ->add('to_topic_id', IntegerType::class, [
                'required'  => true,
                'mapped'    => false
            ])
            ->add('createshadowtopic', ChoiceType::class, [
                'choices'   => ['Off' => false, 'On' => true],
                'choices_as_values' => true,
                'multiple'  => false,
                'expanded'  => true,
                'required'  => true,
                'mapped'    => false,
                'data'      => 0,
            ])
            ->add('append', ChoiceType::class, [
                'choices'   => ['Mix' => false, 'Append' => true],
                'choices_as_values' => true,
                'multiple'  => false,
                'expanded'  => true,
                'required'  => true,
                'mapped'    => false,
                'data'      => true,
            ]);
        if ($options['addReason']) {
            $builder->add('reason', TextareaType::class, [
                'mapped'    => false,
                'required'  => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'zikula_dizkus_form_topic_join';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'addReason' => false,
            'settings' => null
        ]);
    }
}
