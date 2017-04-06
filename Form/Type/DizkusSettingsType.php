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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DizkusSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('forum_enabled', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('forum_disabled_info', 'textarea', [
                    'required' => false
                ])
                ->add('indexTo', 'text', [
                    'required' => false
                ])
                ->add('email_from', 'email', [
                    'required' => false
                ])
                ->add('defaultPoster', 'integer', [
                    'required' => false
                ])
                ->add('hot_threshold', 'integer', [
                    'required' => false
                ])
                ->add('posts_per_page', 'integer', [
                    'required' => false
                ])
                ->add('topics_per_page', 'integer', [
                    'required' => false
                ])
                ->add('url_ranks_images', 'text', [
                    'required' => false
                ])
                ->add('ajax', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('solved_enabled', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('fulltextindex', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('extendedsearch', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('showtextinsearchresults', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('minsearchlength', 'integer', [
                    'required' => false
                ])
                ->add('maxsearchlength', 'integer', [
                    'required' => false
                ])
                ->add('post_sort_order', 'choice', ['choices' => ['ASC' => 'Ascending', 'DESC' => 'Descending'],
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true])
                ->add('signature_start', 'textarea', [
                    'required' => false
                ])
                ->add('signature_end', 'textarea', [
                    'required' => false
                ])
                ->add('signaturemanagement', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('removesignature', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('log_ip', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('striptags', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('timespanforchanges', 'integer', [
                    'required' => false
                ])
                ->add('striptagsfromemail', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('notifyAdminAsMod', 'choice', ['choices' => $options['admins'],
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true])
                ->add('m2f_enabled', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('rss2f_enabled', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'disabled' => true,
                    'expanded' => true,
                    'required' => true])
                ->add('favorites_enabled', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('deletehookaction', 'choice', ['choices' => ['remove' => 'Delete topic', 'lock' => 'Lock topic'],
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true])
                ->add('onlineusers_moderatorcheck', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('restore', 'submit', [
                    'label' => 'Restore defaults'
                ])
                ->add('save', 'submit', [
                    'label' => 'Save'
        ]);
    }

    public function getName()
    {
        return 'zikuladizkusmodule_admin_settings_forum';
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
