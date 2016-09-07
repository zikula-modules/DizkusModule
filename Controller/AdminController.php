<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Controller;

use ModUtil;
use System;

use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\Event\GenericEvent;

use Zikula\DizkusModule\ZikulaDizkusModule;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Form\Type\PreferencesType;
use Zikula\DizkusModule\DizkusModuleInstaller;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Form\Handler\Admin\DeleteForum;
use Zikula\DizkusModule\Form\Handler\Admin\ManageSubscriptions;
use Zikula\DizkusModule\Container\HookContainer;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("")
     *
     * the main administration function
     *
     * @return RedirectResponse
     */
    public function indexAction()
    {
        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_tree', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/order/{action}/{forum}", requirements={"action" = "moveUp|moveDown", "forum" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * Change forum order
     * Move up or down a forum in the tree
     * 
     * @param string $action
     * @param ForumEntity $forum
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException on Perm check failure
     */
    public function changeForumOrderAction($action, ForumEntity $forum)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        /** @var $repo \Gedmo\Tree\Entity\Repository\NestedTreeRepository */
        $repo = $em->getRepository('Zikula\DizkusModule\Entity\ForumEntity');
        if ($action == 'moveUp') {
            $repo->moveUp($forum, true);
        } else {
            $repo->moveDown($forum, true);
        }
        $em->flush();

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_tree', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/prefs")
     *
     * preferences
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function preferencesAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        
        $form = $this->createForm(new PreferencesType, $this->getVars(), []);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                $this->addFlash('status', $this->__('Done! Updated configuration.'));
            }
            if ($form->get('restore')->isClicked()) {
                $this->setVars(DizkusModuleInstaller::getDefaultVars());                
                $this->addFlash('status', $this->__('Done! Reset configuration to default values.'));
            }
            return $this->redirect($this->generateUrl('zikuladizkusmodule_admin_preferences'));
        }

        return $this->render('@ZikulaDizkusModule/Admin/preferences.html.twig', [
                    'form' => $form->createView(),
        ]);     
    }

    /**
     * @Route("/sync")
     * @Method("POST")
     *
     * syncforums
     * 
     * @param Request $request
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException
     */
    public function syncforumsAction(Request $request)
    {
        $showstatus = !$request->request->get('silent', 0);
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $succesful = ModUtil::apiFunc($this->name, 'Sync', 'forums');
        if ($showstatus && $succesful) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Synchronized forum index.'));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error synchronizing forum index'));
        }
        $succesful = ModUtil::apiFunc($this->name, 'Sync', 'topics');
        if ($showstatus && $succesful) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Synchronized topics.'));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error synchronizing topics.'));
        }
        $succesful = ModUtil::apiFunc($this->name, 'Sync', 'posters');
        if ($showstatus && $succesful) {
            $request->getSession()->getFlashBag()->add('status', $this->__('Done! Synchronized posts counter.'));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__('Error synchronizing posts counter.'));
        }

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_tree', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/ranks")
     *
     * ranks
     * 
     * @param Request $request
     * 
     * @return Response|RedirectResponse
     * 
     * @throws AccessDeniedException
     */
    public function ranksAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $submit = $request->request->get('submit', 2);
        $ranktype = (int) $request->query->get('ranktype', RankEntity::TYPE_POSTCOUNT);
        if ($submit == 2) {
        list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', ['ranktype' => $ranktype]);
            $template = 'honoraryranks';
            if ($ranktype == 0) {
                $template = 'ranks';
            }
 
            return $this->render("@ZikulaDizkusModule/Admin/$template.html.twig", [
                        'ranks' => $ranks,
                        'ranktype' => $ranktype,
                        'rankimages' => $rankimages,
                        'settings' => $this->getVars()
            ]); 
        } else {
            $ranks = $request->request->filter('ranks', '', FILTER_SANITIZE_STRING);
            ModUtil::apiFunc($this->name, 'Rank', 'save', ['ranks' => $ranks]);
        }

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_ranks', ['ranktype' => $ranktype], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/assignranks")
     *
     * ranks
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function assignranksAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        
        $page = (int)$request->query->get('page', 1);        
        $letter = $request->query->get('letter');      
    
        if ($request->getMethod() == 'POST') {
            $letter = $request->request->get('letter');            
            $page = (int)$request->request->get('page', 1);        
            
            $setrank = $request->request->get('setrank');
            ModUtil::apiFunc($this->name, 'Rank', 'assign', array('setrank' => $setrank));
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_assignranks', ['page' => $page, 'letter' => $letter], RouterInterface::ABSOLUTE_URL));
        }
        
        $letter = (empty($letter) || strlen($letter) != 1) ? '*' : $letter;
        $perpage = 20;
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('u')
                ->from('Zikula\UsersModule\Entity\UserEntity', 'u')
                ->orderBy('u.uname', 'ASC');
        if ($letter != '*') {
            $qb->andWhere('u.uname LIKE :letter')
                ->setParameter('letter', strtolower($letter) . '%');
        }
        $query = $qb->getQuery();
        $query->setFirstResult(($page - 1) * $perpage)
            ->setMaxResults($perpage);

        // Paginator
        $allusers = new Paginator($query);
        $count = $allusers->count();

        // recreate the array of users as ForumUserEntities
        $userArray = [];
        /** @var $user \Zikula\UsersModule\Entity\UserEntity */
        foreach ($allusers as $user) {
            $managedForumUser = new ForumUserManager($user->getUid(), false);
            $forumUser = $managedForumUser->get();
            if (isset($forumUser)) {
                $userArray[$user->getUid()] = $forumUser;
            } else {
                $count--;
            }
        }
        
        list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', ['ranktype' => RankEntity::TYPE_HONORARY]);
        
        return $this->render('@ZikulaDizkusModule/Admin/assignranks.html.twig', [
                    'ranks' => $ranks,
                    'rankimages' => $rankimages,
                    'allusers' => $userArray,
                    'letter' => $letter,
                    'page' => $page,
                    'perpage' => $perpage,
                    'usercount' => $count
        ]);       
    }

    /**
     * @Route("/tree")
     *
     * Show the forum tree.
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function treeAction()
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        
        return $this->render('@ZikulaDizkusModule/Admin/tree.html.twig', [
                    'tree' => $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->childrenHierarchy(null, false)
            ]);        
    }

    /**
     * @Route("/modify/{id}")
     *
     * @return Response
     */
    public function modifyForumAction(Request $request, $id = null)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // disallow editing of root forum
        if ($id == 1) {
            $request->getSession()->getFlashBag()->add('error', $this->__("Editing of root forum is disallowed", 403));
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_tree', [], RouterInterface::ABSOLUTE_URL));            
        }        
        
        if ($id){
            $forum = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->find($id);
        }else {
            $forum = new ForumEntity();   
        }
        //@todo use services
        $topiccount = ModUtil::apiFunc('ZikulaDizkusModule', 'user', 'countstats', ['id' => $id,
                'type' => 'forumtopics']);  
        $postcount = ModUtil::apiFunc('ZikulaDizkusModule', 'user', 'countstats', ['id' => $id,
                'type' => 'forumposts']);        
        
        $form = $this->createForm('Zikula\DizkusModule\Form\Type\ForumType', $forum, []);        
        
        $em = $this->getDoctrine()->getManager();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($forum);
            $em->flush();
  
            if ($id) {
                $request->getSession()->getFlashBag()->add('status', $this->__('Forum successfully updated.'));
            } else {
                $request->getSession()->getFlashBag()->add('status', $this->__('Forum successfully created.'));
            }            
        }        
        
        return $this->render('@ZikulaDizkusModule/Admin/modifyforum.html.twig', [
                    'topiccount' => $topiccount,
                    'postcount' => $postcount,
                    'forum' => $forum,
                    'form' => $form->createView(),
            ]);   
    }

    /**
     * @Route("/delete/{id}")
     *
     * @return Response
     */
    public function deleteforumAction(Request $request, $id = null)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        
        if ($id) {
            $forum = $this->getDoctrine()->getManager()->find('Zikula\DizkusModule\Entity\ForumEntity', $id);
            if ($forum) {
            } else {
                $request->getSession()->getFlashBag()->add('error', $this->__f('Forum with id %s not found', ['%s' => $id]), 403);
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_tree', [], RouterInterface::ABSOLUTE_URL));                 
            }
        } else {
                $request->getSession()->getFlashBag()->add('error', $this->__('No forum id'), 403);
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_tree', [], RouterInterface::ABSOLUTE_URL)); 
        }

        $forumRoot = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->findOneBy(['name' => ForumEntity::ROOTNAME]);
        $destinations = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->getChildren($forumRoot);

        $form = $this->createFormBuilder([])
                ->add('action', 'choice', [
                    'choices' => ['0' => $this->__('Remove them'),
                                  '1' => $this->__('Move them to a new parent forum')],
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true])
                ->add('destination', 'choice', [
                    'choices' => $destinations,
                    'choice_value' => function($destination){
                        //for some reason last element is null @FIXME
                        return $destination === null ? null : $destination->getForum_id();
                    },
                    'choice_label' => function ($destination) use ($forum) {
                        $isChild = $destination->getLft() > $forum->getLft() && $destination->getRgt() < $forum->getRgt() ? ' (' . $this->__("is child forum") . ')' : '';
                        $current = $destination->getForum_id() === $forum->getForum_id()? ' (' . $this->__("current") . ')' : '';
                        $locked = $destination->isLocked() ? ' (' . $this->__("is locked") . ')' : '';
                        return str_repeat("--", $destination->getLvl()) . $destination->getName() . $current . $locked. $isChild;
                    },
                    'choice_attr' => function($destination) use ($forum){
                        $isChild = $destination->getLft() > $forum->getLft() && $destination->getRgt() < $forum->getRgt() ? true : false ;
                        return $destination->getForum_id() === $forum->getForum_id() || $destination->isLocked() || $isChild ? ['disabled' => 'disabled'] : [];
                    },
                    'choices_as_values' => true,
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true])
                ->add('remove', 'submit')
                ->getForm();        
        
        $form->handleRequest($request);
                    
        // check hooked modules for validation
        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.forum.validate_delete', $hook)->getValidators();
       
        if ($form->isValid() && !$hookvalidators->hasErrors()) {
            $data = $form->getData();  
            if ($data['action'] == 1) {
                // get the child forums and move them
                $children = $forum->getChildren();
                foreach ($children as $child) {
                    $child->setParent($data['destination']);
                }
                $forum->removeChildren();

                // get child topics and move them
                $topics = $forum->getTopics();
                foreach ($topics as $topic) {
                    $topic->setForum($data['destination']);
                    $forum->getTopics()->removeElement($topic);
                }
                $this->getDoctrine()->getManager()->flush();
            }
            // remove the forum
            $this->getDoctrine()->getManager()->remove($forum);
            $this->getDoctrine()->getManager()->flush();

            $this->get('hook_dispatcher')->dispatch('dizkus.ui_hooks.forum.process_delete', new ProcessHook($forum->getForum_id()));

            if (isset($data['destination'])) {
                // sync last post in destination
                ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', ['forum' => $data['destination']]);
            }

            // repair the tree
            $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->recover();
            $this->getDoctrine()->getManager()->clear();

            // resync all forums, topics & posters
            ModUtil::apiFunc($this->name, 'sync', 'all');                   
        }        
       
        return $this->render('@ZikulaDizkusModule/Admin/deleteforum.html.twig', [
                    'forum' => $forum,
                    'form' => $form->createView(),
            ]);  
    }

    /**
     * @Route("/subscriptions/{uid}", options={"expose"=true})
     *
     * @return Response
     */
    public function manageSubscriptionsAction(Request $request, $uid = null)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $topicsubscriptions = [];
        $forumsubscriptions = [];
        
        $username = null;
        if (!empty($uid)) {
            $username = \UserUtil::getVar('uname', $uid);
        }
        
        if (!empty($uid)) {
            $params = ['uid' => $uid];
            $topicsubscriptions = ModUtil::apiFunc($this->name, 'Topic', 'getSubscriptions', $params);
            $forumsubscriptions = ModUtil::apiFunc($this->name, 'Forum', 'getSubscriptions', $params);
        }
        
        if ($request->isMethod('POST')) {
            
            $forumSub = $request->request->get('forumsubscriptions', []);          
            foreach ($forumSub as $id => $selected) {
                if ($selected) {
                    ModUtil::apiFunc($this->name, 'forum', 'unsubscribe', [
                        'user_id' => $uid,
                        'forum' => $id
                    ]);
                }
            }
            
            $topicSub = $request->request->get('topicsubscriptions', []);
            foreach ($topicSub as $id => $selected) {
                if ($selected) {
                    ModUtil::apiFunc($this->name, 'topic', 'unsubscribe', [
                        'user_id' => $uid,
                        'topic' => $id
                    ]);
                }
            } 
        }   
        
        return $this->render('@ZikulaDizkusModule/Admin/managesubscriptions.html.twig', [
                    'uid' => $uid,
                    'username' => $username,
                    'topicsubscriptions' => $topicsubscriptions,
                    'forumsubscriptions' => $forumsubscriptions
            ]);        
    }

    /**
     * @Route("/hookconfig/{moduleName}")
     * @Method("GET")
     *
     * configure dizkus hook options for given module
     *
     * @param $moduleName
     * @return Response
     */
    public function hookConfigAction($moduleName)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        
        //use extensions @todo
        $hookconfig = ModUtil::getVar($moduleName, 'dizkushookconfig');
        //use service @todo
        $module = ModUtil::getModule($moduleName);
        
        $bindingsBetweenOwners = $this->get('hook_dispatcher')->getBindingsBetweenOwners($moduleName, $this->name);
        foreach ($bindingsBetweenOwners as $k => $binding) {
            $areaname = $this->getDoctrine()->getManager()->getRepository('Zikula\Bundle\HookBundle\Dispatcher\Storage\Doctrine\Entity\HookAreaEntity')->find($binding['sareaid'])->getAreaname();
            $bindingsBetweenOwners[$k]['areaname'] = $areaname;
            //find way to get area title @todo
            $bindingsBetweenOwners[$k]['areatitle'] = 'Area title';
            //$bindingsBetweenOwners[$k]['areatitle'] = $this->__($moduleVersionObj->getHookSubscriberBundle($areaname)->getTitle());
        }

        return $this->render('@ZikulaDizkusModule/Hook/modifyconfig.html.twig', [
                    'areas' => $bindingsBetweenOwners,
                    'dizkushookconfig' => $hookconfig,
                    'activeModule' => $moduleName,
                    'forums' => ModUtil::apiFunc($this->name, 'Forum', 'getParents', ['includeLocked' => true])
            ]);    
    }

    /**
     * @Route("/hookconfig")
     * @Method("POST")
     *
     * process dizkus hook options for a given module
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function hookConfigProcessAction(Request $request)
    {
        $hookdata = $request->request->get('dizkus', array());
        $moduleName = $request->request->get('activeModule');
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        foreach ($hookdata as $area => $data) {
            if (!isset($data['forum']) || empty($data['forum'])) {
                $request->getSession()->getFlashBag()->add('error', $this->__f('Error: No forum selected for area \'%s\'', $area));
                $hookdata[$area]['forum'] = null;
            }
        }
        ModUtil::setVar($moduleName, 'dizkushookconfig', $hookdata);
        // ModVar: dizkushookconfig => array('areaid' => array('forum' => value))
        $request->getSession()->getFlashBag()->add('status', $this->__('Dizkus: Hook option settings updated.'));

        return new RedirectResponse(System::normalizeUrl(ModUtil::url($moduleName, 'admin', 'index')));
    }

}