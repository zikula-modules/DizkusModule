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

use ModUtil;
//use UserUtil;
use Doctrine\ORM\EntityRepository;
//use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ForumType extends AbstractType {

    public function __construct() {
    
        $em = \ServiceUtil::get('doctrine.entitymanager');
        $users = $em->getRepository('ZikulaUsersModule:UserEntity')->findAll();
//                 $this->users = new ArrayCollection($usersArr);
//        foreach ($users as $user) {
//            $usersArr['uid'] = $user['uname'];
//        }

        $this->users = $users;
//          dump($this->users);        
//        $users = UserUtil::
//        $adminGroup = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'get', array('gid' => 2));
//        $admins = ['-1' => 'disable'];
//        foreach ($adminGroup['members'] as $admin) {
//            $admins[$admin['uid']] = UserUtil::getVar('uname', $admin['uid']);
//        }
//        $this->admins = $admins;
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
                ->add('parent', 'entity' , [
                    'class' => 'Zikula\DizkusModule\Entity\ForumEntity',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('f')
                            ->orderBy('f.root', 'ASC')
                            ->addOrderBy('f.lft', 'ASC');},
                    'choice_label' => function ($parent) {                        
                        return str_repeat("--", $parent->getLvl()).$parent->getName();
                    },
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true])
                ->add('moderatorUsers', 'entity' , [
                    'class' => 'Zikula\DizkusModule\Entity\ModeratorUserEntity',
                    'choices' => $this->users,
                    'property' => 'uid',
                    'choice_label' => function ($moderator) {   
                        //dump($moderator);
                        return $moderator->getUname();
                    },
                    'multiple' => true,
                    'expanded' => false,
                    'required' => false])
                ->addEventListener(
                    FormEvents::POST_SET_DATA,
                    function(FormEvent $event) {
                        $form = $event->getForm();
                        $data = $event->getData();
                        dump($data);
                    }
                    )                            
//                ->add('forum_disabled_info', 'textarea', [
//                    'required' => false
//                ])
//                ->add('indexTo', 'text', [
//                    'required' => false
//                ])
//                ->add('email_from', 'email', [
//                    'required' => false
//                ])
//                ->add('defaultPoster', 'integer', [
//                    'required' => false
//                ])
//                ->add('hot_threshold', 'integer', [
//                    'required' => false
//                ])
//                ->add('posts_per_page', 'integer', [
//                    'required' => false
//                ])
//                ->add('topics_per_page', 'integer', [
//                    'required' => false
//                ])
//                ->add('url_ranks_images', 'text', [
//                    'required' => false
//                ])
//                ->add('ajax', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('solved_enabled', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('fulltextindex', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'disabled' => true,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('extendedsearch', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'disabled' => true,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('showtextinsearchresults', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('minsearchlength', 'integer', [
//                    'required' => false
//                ])
//                ->add('maxsearchlength', 'integer', [
//                    'required' => false
//                ])
//                ->add('post_sort_order', 'choice', ['choices' => ['ASC' => 'Ascending', 'DESC' => 'Descending'],
//                    'multiple' => false,
//                    'expanded' => false,
//                    'required' => true])
//                ->add('signature_start', 'textarea', [
//                    'required' => false
//                ])
//                ->add('signature_end', 'textarea', [
//                    'required' => false
//                ])
//                ->add('signaturemanagement', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'disabled' => true,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('removesignature', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('log_ip', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'disabled' => true,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('striptags', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('timespanforchanges', 'integer', [
//                    'required' => false
//                ])
//                ->add('striptagsfromemail', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('notifyAdminAsMod', 'choice', ['choices' => $this->admins,
//                    'multiple' => false,
//                    'expanded' => false,
//                    'required' => true])
//                ->add('m2f_enabled', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'disabled' => true,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('rss2f_enabled', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'disabled' => true,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('favorites_enabled', 'choice', ['choices' => ['0' => 'Off', '1' => 'On'],
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => true])
//                ->add('deletehookaction', 'choice', ['choices' => ['remove' => 'Delete topic', 'lock' => 'Lock topic'],
//                    'multiple' => false,
//                    'expanded' => false,
//                    'required' => true])
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Zikula\DizkusModule\Entity\ForumEntity',
            'translator' => null
        ]);
    }
}
