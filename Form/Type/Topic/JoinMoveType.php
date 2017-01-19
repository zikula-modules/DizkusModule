<?php

/**
 * Dizkus.
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
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JoinMoveType extends AbstractType
{
    protected $translator;

    private $forums;

    public function __construct($translator, $forums)
    {
        $this->translator = $translator;
        $this->forums = (['' => '<< '.$this->translator->__('Select target forum').' >>'] + $forums);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->forum = $options['forum'];

        $builder
                ->add('forum_id', ChoiceType::class, [
                    'choices'       => $this->forums,
                    'multiple'      => false,
                    'expanded'      => false,
                    'required'      => false,
                    'choice_attr'   => function ($key) {
                        return $key == $this->forum ? ['disabled' => 'disabled'] : [];
                    },
                    'choice_label'   => function ($key) {
                        return $key == $this->forum ? $this->forums[$key] . ' ' .  $this->translator->__('current') : $this->forums[$key];
                    },
                ])
                ->add('to_topic_id', IntegerType::class, [
                    'required' => false,
                ])
                ->add('createshadowtopic', ChoiceType::class, [
                    'choices'   => ['0' => 'Off', '1' => 'On'],
                    'multiple'  => false,
                    'expanded'  => true,
                    'required'  => true,
                    'data'      => 0,
                ])
                ->add('move', SubmitType::class, [
                    'label' => 'Move topic',
                ])
                ->add('join', SubmitType::class, [
                    'label' => 'Join topic',
                ])
                ->add('cancel', SubmitType::class, [
                    'label' => 'Cancel',
        ]);
    }

    public function getName()
    {
        return 'topic_joinmove_form';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'topic'      => null,
            'forum'      => null,
        ]);
    }
}
