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

namespace Zikula\DizkusModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserPreferencesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('postOrder', ChoiceType::class, ['choices' => ['1' => 'Ascending', '0' => 'Descending'],
            'multiple' => false,
            'expanded' => false,
            'required' => true
            ]);

        if ($options['settings']['favorites_enabled']) {
            $builder->add('displayOnlyFavorites', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                'multiple' => false,
                'expanded' => true,
                'required' => true
                ]);
        }
        if ($options['settings']['topic_subscriptions_enabled']) {
            $builder->add('autosubscribe', ChoiceType::class, ['choices' => ['0' => 'Off', '1' => 'On'],
                'multiple' => false,
                'expanded' => true,
                'required' => true
                ]);
        }
        $builder->add('save', 'submit', [
            'label' => 'Save',
        ]);
    }

    public function getName()
    {
        return 'zikula_dizkus_form_user_preferences';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'settings' => false,
        ]);
    }
}
