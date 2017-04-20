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

namespace Zikula\DizkusModule\Form\Type\Topic;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReplyTopicType extends AbstractType
{
    protected $loggedIn;

    public function __construct($loggedIn)
    {
        $this->loggedIn = $loggedIn;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('message', TextareaType::class, [
                    'required'    => false,
                    'constraints' => new NotBlank(),
                    ])
                ->add('topic', HiddenType::class, [
                    'required' => false,
                    'mapped'   => false,
                    'data'     => $options['topic'],
                    ])
                ->add('attachSignature', CheckboxType::class, [
                    'required' => false,
                    'data'     => $this->loggedIn,
                    'disabled' => !$this->loggedIn,
                    ])
                ->add('subscribeTopic', CheckboxType::class, [
                    'required' => false,
                    'data'     => $this->loggedIn,
                    'disabled' => !$this->loggedIn,
                    ])
                ->add('save', SubmitType::class)
                ->add('preview', SubmitType::class);
    }

    public function getName()
    {
        return 'zikula_dizkus_form_topic_reply';
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
