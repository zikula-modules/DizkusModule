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

namespace Zikula\DizkusModule\Form\Type\Forum;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\DizkusModule\Manager\ForumManager;

class ForumSelectType extends AbstractType
{
    private $forumManagerService;

    private $forums;

    public function __construct(ForumManager $forumManagerService)
    {
        $this->forumManagerService = $forumManagerService;
        $this->forums = $this->forumManagerService->getAllChildren();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getName()
    {
        return 'zikula_dizkus_module_forum_select_type';
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'forum' => null,
            'choices' => $this->forums,
            'multiple' => false,
            'expanded' => false,
            'required' => false
        ]);
    }
}
