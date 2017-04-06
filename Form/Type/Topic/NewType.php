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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewType extends AbstractType
{
    protected $loggedIn;

    public function __construct($loggedIn)
    {
        $this->loggedIn = $loggedIn;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', [
                    'required' => true, ])
                ->add('message', 'textarea', [
                    'required' => true, ])
                ->add('attachSignature', 'checkbox', [
//                    'label_attr' => ['class' => $this->loggedIn ? '' : ' text-muted'], moved to template
                    'required' => false,
                    'data'     => $this->loggedIn,
                    'disabled' => !$this->loggedIn,
                    ])
                ->add('isSupportQuestion', 'checkbox', [
                    'required' => false,
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
        return 'topic_new_form';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
        ]);
    }
}
