<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserPreferencesType extends AbstractType {

    public function __construct() {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('postOrder', 'choice', ['choices' => ['1' => 'Ascending', '0' => 'Descending'],
            'multiple' => false,
            'expanded' => false,
            'required' => true]);

        if ($options['favorites_enabled']) {
            $builder->add('displayOnlyFavorites', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
                'multiple' => false,
                'expanded' => true,
                'required' => true]);
        }
        $builder->add('autosubscribe', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
            'multiple' => false,
            'expanded' => true,
            'required' => true]);

        $builder->add('save', 'submit', [
            'label' => 'Save'
        ]);
        $builder->add('cancel', 'submit', [
            'label' => 'Cancel'
        ]);        
    }

    public function getName() {
        return 'user_preferences_form';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     * 
     * @param OptionsResolverInterface $resolver            
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(['favorites_enabled' => false]);
    }

}
