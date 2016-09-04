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

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ForumType extends AbstractType {

    public function __construct() {

        //@todo use service injection 
        
        // assign all users for the moderator selection
        $em = \ServiceUtil::get('doctrine.entitymanager');
        $users = $em->getRepository('ZikulaUsersModule:UserEntity')->findAll();
        foreach ($users as $user) {
            $usersArr[$user->getUid()] = $user->getUname();
        }
        $this->users = $usersArr;

        // assign all groups for the moderator selection
        $groups = \UserUtil::getGroups();
        $allGroupsAsDrowpdownList = [];
        foreach ($groups as $value) {
            $allGroupsAsDrowpdownList[$value['gid']] = $value['name'];
        }
        $this->groups = $allGroupsAsDrowpdownList;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('name', 'text', [])
                ->add('description', 'textarea', [
                    'required' => false
                ])
                ->add('status', 'choice', ['choices' => ['0' => 'Unlock', '1' => 'Lock'],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true])
                ->add('parent', 'entity', [
                    'class' => 'Zikula\DizkusModule\Entity\ForumEntity',
                    'query_builder' => function(EntityRepository $er) {
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
                //stuff below works but probably there is better way            
                ->addEventListener(
                        FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();
                    $selectedCollection = $data->getModeratorUsers()->getValues();
                    $selectedArr = [];
                    foreach ($selectedCollection as $element) {
                        $selectedArr[$element->getForumUser()->getUser_id()] = $element->getForumUser()->getUser_id();
                    }
                    $form->add('moderatorUsers', ChoiceType::class, [
                        'data' => $selectedArr,
                        'choices' => $this->users,
                        'multiple' => true,
                        'expanded' => false,
                        'required' => false]
                    );
                }
                )
                ->addEventListener(
                        FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                    $form = $event->getForm();
                    $data = $event->getData();
                    $selectedCollection = $data->getModeratorGroups()->getValues();
                    $selectedArr = [];
                    foreach ($selectedCollection as $element) {
                        $selectedArr[$element->getGroup()->getGid()] = $element->getGroup()->getGid();
                    }
                    $form->add('moderatorGroups', ChoiceType::class, [
                        'data' => $selectedArr,
                        'choices' => $this->groups,
                        //'mapped' => false,
                        'multiple' => true,
                        'expanded' => false,
                        'required' => false]
                    );
                }
                )
                ->add('restore', 'submit', [
                    'label' => 'Restore defaults'
                ])
                ->add('save', 'submit', [
                    'label' => 'Save'
        ]);
    }

    public function getName() {
        return 'forum_form';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'Zikula\DizkusModule\Entity\ForumEntity',
            'translator' => null
        ]);
    }

}
