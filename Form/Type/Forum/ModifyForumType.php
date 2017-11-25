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

namespace Zikula\DizkusModule\Form\Type\Forum;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Form\DataTransformer\ModeratorUsersTransformer;
use Zikula\DizkusModule\Form\DataTransformer\ModeratorGroupsTransformer;

class ModifyForumType extends AbstractType
{
    private $em;

    private $forumUserManagerService;

    public function __construct(EntityManager $em, ForumUserManager $forumUserManagerService)
    {
        $this->em = $em;
        $this->forumUserManagerService = $forumUserManagerService;

//        // users
//        $users = $em->getRepository('ZikulaUsersModule:UserEntity')->findAll();
//        foreach ($users as $user) {
//            $usersArr[$user->getUid()] = $user->getUname();
//        }
        $this->users = []; //$usersArr;

        // groups
        $groups = $em->getRepository('ZikulaGroupsModule:GroupEntity')->findAll();
        $allGroupsAsDrowpdownList = [];
        foreach ($groups as $group) {
            $allGroupsAsDrowpdownList[$group->getGid()] = $group->getName();
        }
        $this->groups = $allGroupsAsDrowpdownList;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $moderatorUsersTransformer = new ModeratorUsersTransformer($this->em, $this->forumUserManagerService);
        $moderatorGroupsTransformer = new ModeratorGroupsTransformer($this->em);

        $builder->add('name', TextType::class, [])
        ->add('description', TextareaType::class, [
            'required' => false
        ])
        ->add('status', ChoiceType::class, ['choices' => ['0' => 'Unlocked', '1' => 'Locked'],
            'multiple' => false,
            'expanded' => true,
            'required' => true])

        ->add('parent', EntityType::class, [
            'class' => 'Zikula\DizkusModule\Entity\ForumEntity',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('f')
                ->orderBy('f.root', 'ASC')
                ->addOrderBy('f.lft', 'ASC');
            },
            'choice_label' => function ($parent) {
                return str_repeat("--", $parent->getLvl()) . $parent->getName();
            },
            'multiple' => false,
            'expanded' => false,
            'required' => true])

        ->add($builder->create('moderatorUsers', ChoiceType::class, [
            'choices' => $this->users,
            'multiple' => true,
            'expanded' => false,
            'required' => false]
        )->addModelTransformer($moderatorUsersTransformer))

        ->add($builder->create('moderatorGroups', ChoiceType::class, [
            'choices' => $this->groups,
            'multiple' => true,
            'expanded' => false,
            'required' => false]
        )->addModelTransformer($moderatorGroupsTransformer))

        ->add('restore', SubmitType::class, [
        ])
        ->add('save', SubmitType::class, [
        ]);
    }

    public function getName()
    {
        return 'zikula_dizkus_module_forum_modify';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\DizkusModule\Entity\ForumEntity',
        ]);
    }
}
