<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DizkusSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('forum_enabled', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('forum_disabled_info', TextareaType::class, [
                    'required' => false
                ])
                ->add('indexTo', TextType::class, [
                    'required' => false
                ])
                ->add('email_from', EmailType::class, [
                    'required' => false
                ])
                ->add('ajax', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                // Forum users
                ->add('defaultPoster', IntegerType::class, [
                    'required' => false
                ])
                ->add('url_ranks_images', TextType::class, [
                    'required' => false
                ])
                ->add('post_sort_order', ChoiceType::class, ['choices' => ['ASC' => 'Ascending', 'DESC' => 'Descending'],
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true])
                ->add('signaturemanagement', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('signature_start', TextareaType::class, [
                    'required' => false
                ])
                ->add('signature_end', TextareaType::class, [
                    'required' => false
                ])
                ->add('removesignature', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('onlineusers_moderatorcheck', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                // Forums
                ->add('favorites_enabled', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('forum_subscriptions_enabled', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('topics_per_page', IntegerType::class, [
                    'required' => false
                ])
                ->add('fulltextindex', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('extendedsearch', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('showtextinsearchresults', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('minsearchlength', IntegerType::class, [
                    'required' => false
                ])
                ->add('maxsearchlength', IntegerType::class, [
                    'required' => false
                ])
                // Topics
                ->add('solved_enabled', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('topic_subscriptions_enabled', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('hot_threshold', IntegerType::class, [
                    'required' => false
                ])
                ->add('posts_per_page', IntegerType::class, [
                    'required' => false
                ])
                // Posts
                ->add('striptags', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                // Security
                ->add('log_ip', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('timespanforchanges', IntegerType::class, [
                    'required' => false
                ])
                ->add('striptagsfromemail', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('notifyAdminAsMod', ChoiceType::class, ['choices' => $options['admins'],
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true])
                // External
                ->add('m2f_enabled', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('rss2f_enabled', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('deletehookaction', ChoiceType::class, ['choices' => ['remove' => 'Delete topic', 'lock' => 'Lock topic'],
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true])

                ->add('restore', SubmitType::class, [])
                ->add('save', SubmitType::class, []);
    }

    public function getName()
    {
        return 'zikula_dizkus_module_admin_settings_forum';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'admins' => [],
        ]);
    }
}
