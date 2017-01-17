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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReplyType extends AbstractType
{
    protected $loggedIn;

    public function __construct($loggedIn)
    {
        $this->loggedIn = $loggedIn;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('message', 'textarea', [
                    'required'    => false,
                    'constraints' => new NotBlank(),
                    ])
                ->add('topic', 'hidden', [
                    'required' => false,
                    'mapped'   => false,
                    'data'     => $options['topic'],
                    ])
                ->add('attachSignature', 'checkbox', [
//                    'label_attr' => ['class' => $this->loggedIn ? '' : ' text-muted'], moved to template
                    'required' => false,
                    'data'     => $this->loggedIn,
                    'disabled' => !$this->loggedIn,
                    ])
                ->add('subscribeTopic', 'checkbox', [
//                    'label_attr' => ['class' => $this->loggedIn ? '' : ' text-muted'], moved to template
                    'required' => false,
                    'data'     => $this->loggedIn,
                    'disabled' => !$this->loggedIn,
                    ])
                ->add('save', 'submit', [
                    'label' => 'Submit',
                ])
                ->add('preview', 'submit', [
                    'label' => 'Preview',
                ]);
    }

    public function getName()
    {
        return 'topic_reply_form';
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
