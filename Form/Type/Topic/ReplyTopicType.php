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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReplyTopicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('post_text', TextareaType::class, [
                    'required' => true, ]);

        if ($options['settings']['signaturemanagement']) {
            $builder->add('attachSignature', CheckboxType::class, [
                        'required' => false,
                        'data'     => $options['loggedIn'],
                        'disabled' => !$options['loggedIn'],
                        ]);
        }
        if ($options['settings']['topic_subscriptions_enabled']) {
            $builder->add('subscribeTopic', CheckboxType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data'     => $options['loggedIn'],
                        'disabled' => !$options['loggedIn'],
                        ]);
        }

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
            'loggedIn' => false,
            'settings' => false,
        ]);
    }
}
