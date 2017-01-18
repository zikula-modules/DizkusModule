<?php

/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @link https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Form\Type\Topic;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteType extends AbstractType 
{
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder->add('topic', HiddenType::class, [
                    'required' => false,
                    'mapped'   => false,
                    'data'     => $options['topic'],
                ])
                ->add('reason', TextareaType::class, [
                    'required' => false,
                ])
                ->add('sendReason', ChoiceType::class, [
                    'choices'   => ['0' => 'Off', '1' => 'On'],
                    'multiple'  => false,
                    'expanded'  => true,
                    'required'  => true,
                    'data'      => 0,
                ])
                ->add('delete', SubmitType::class, [
                    'label' => 'Delete',
                ])
                ->add('cancel', SubmitType::class, [
                    'label' => 'Cancel',
        ]);
    }

    public function getName()
    {
        return 'topic_delete_form';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) 
    {
        $resolver->setDefaults([
            'translator' => null,
            'topic'      => null,
        ]);
    }

}
