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

namespace Zikula\DizkusModule\Form\Type\Post;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPostType extends AbstractType
{
    protected $loggedIn;

    public function __construct($loggedIn)
    {
        $this->loggedIn = $loggedIn;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('post_text', TextareaType::class, [
                    'required' => true, ])
                ->add('attachSignature', CheckboxType::class, [
                    'required' => false,
                    'data'     => $this->loggedIn,
                    'disabled' => !$this->loggedIn,
                    ])
                ->add('subscribeTopic', CheckboxType::class, [
                    'required' => false,
                    'mapped' => false,
                    'data'     => $this->loggedIn,
                    'disabled' => !$this->loggedIn,
                    ])
                ->add('save', SubmitType::class)
                ->add('preview', SubmitType::class);
    }

    public function getName()
    {
        return 'zikula_dizkus_form_post_edit';
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
