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

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MovePostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //brutally load all topics
        //@todo make forum selectable topic select
        $builder->add('topic', EntityType::class, [
            'class' => 'Zikula\DizkusModule\Entity\TopicEntity',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                ->orderBy('t.topic_time', 'ASC');
            },
            'choice_label' => function ($topic) {
                return $topic->getTitle(); //str_repeat("--", $topic->get) . $parent->getName();
            },
            'multiple' => false,
            'expanded' => false,
            'required' => true]);

        if ($options['addReason']) {
            $builder->add('reason', TextareaType::class, [
                'mapped' => false,
                'required' =>false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'zikula_dizkus_form_post_move';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'addReason' => false
        ]);
    }
}
