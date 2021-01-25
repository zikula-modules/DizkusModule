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

namespace Zikula\DizkusModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ModerateType.
 *
 * @author Kaik
 */
class ModerateType extends AbstractType
{
    protected $translator;

    private $actions;

    private $topics;

    private $forums;

    public function __construct($translator, $managedForum)
    {
        $this->translator = $translator;

        $topics = $managedForum->getTopics();
        $topicSelect[''] = '<< ' . $this->translator->__('Choose target topic') . ' >>';
        foreach ($topics as $topic) {
            $text = mb_substr($topic->getTitle(), 0, 50);
            $text = mb_strlen($text) < mb_strlen($topic->getTitle()) ? "${text}..." : $text;
            $topicSelect[$topic->getTopic_id()] = $text;
        }
        $this->topics = $topicSelect;

        $this->actions = ['' => '<< ' . $this->translator->__('Choose action') . ' >>',
            'solve' => $this->translator->__("Mark selected topics as 'solved'"),
            'unsolve' => $this->translator->__("Remove 'solved' status from selected topics"),
            'sticky' => $this->translator->__("Give selected topics 'sticky' status"),
            'unsticky' => $this->translator->__("Remove 'sticky' status from selected topics"),
            'lock' => $this->translator->__('Lock selected topics'),
            'unlock' => $this->translator->__('Unlock selected topics'),
            'delete' => $this->translator->__('Delete selected topics'),
            'move' => $this->translator->__('Move selected topics'),
            'join' => $this->translator->__('Join topics'),
        ];

        // For Movetopic
        $forums = $managedForum->getAllChildren();
        $this->forums = (['' => '<< ' . $this->translator->__('Select target forum') . ' >>'] + $forums);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('action', 'choice', [
            'choices' => $this->actions,
            'multiple' => false,
            'expanded' => false,
            'required' => false
            ])
        ->add('moveto', 'choice', [
            'choices' => $this->forums,
            'data' => 'default',
            'multiple' => false,
            'expanded' => false,
            'required' => false
            ])
        ->add('createshadowtopic', 'choice', ['choices' => ['0' => 'No', '1' => 'Yes'],
            'data' => '0',
            'multiple' => false,
            'expanded' => true,
            'required' => true
            ])
        ->add('jointotopic', 'choice', [
            'choices' => $this->topics,
            'multiple' => false,
            'expanded' => false,
            'required' => false
            ])
        ->add('jointo', 'integer', [
            'required' => false,
            ])
        ->add('submit', 'submit');
    }

    public function getName()
    {
        return 'zikula_dizkus_form_forum_moderate';
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
