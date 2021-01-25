<?php

declare(strict_types=1);

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

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SplitTopicType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;
        $builder
            ->add('forum', EntityType::class, [
                'class'         => 'Zikula\DizkusModule\Entity\ForumEntity',
                'query_builder' => function (EntityRepository $er) {
                    $forums = $er->createQueryBuilder('f')
                        ->where('f.lvl != 0')
                        ->orderBy('f.root', 'ASC')
                        ->addOrderBy('f.lft', 'ASC');

                    return $forums;
                },
                'choice_label'  => function ($forum) {
                    return ($forum->getId() === $this->options['forum']) ? str_repeat("--", $forum->getLvl()) . ' ' . $forum->getName() . ' ' . $this->options['translator']->__(' * origin topic forum') : str_repeat("--", $forum->getLvl()) . ' ' . $forum->getName();
                },
                'multiple'      => false,
                'expanded'      => false,
                'mapped'        => false,
                'choice_attr'   => function ($forum) {
                    return $forum->getId() === $this->options['forum'] ? ['selected' => 'selected'] : [];
                }
            ])
            ->add('subject', TextType::class, [
                  'mapped'      => false,
                  'data'        => $this->options['subject'],
                  'required'    => true,
            ]);
        if ($options['addReason']) {
            $builder->add('reason', TextareaType::class, [
                'mapped'        => false,
                'required'      => false,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'zikula_dizkus_form_topic_split';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'subject'       => '',
            'forum'         => null,
            'forums'        => null,
            'translator'    => null,
            'addReason'     => false,
            'settings'      => null
        ]);
    }
}
