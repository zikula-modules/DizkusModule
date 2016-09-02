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
use Zikula\DizkusModule\ZikulaDizkusModule;
use Zikula\DizkusModule\Entity\RankEntity;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\DizkusModule\Manager\ForumManager;

use Zikula\DizkusModule\Form\Type\PreferencesType;
use Zikula\DizkusModule\DizkusModuleInstaller;
use Zikula\DizkusModule\Manager\ForumUserManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Zikula\DizkusModule\Form\Handler\Admin\DeleteForum;
use Zikula\DizkusModule\Form\Handler\Admin\ManageSubscriptions;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

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
        
        $topiccount = ModUtil::apiFunc('ZikulaDizkusModule', 'user', 'countstats', ['id' => $id,
                'type' => 'forumtopics']);  
        $postcount = ModUtil::apiFunc('ZikulaDizkusModule', 'user', 'countstats', ['id' => $id,
                'type' => 'forumposts']);        
        
        $form = $this->createForm('Zikula\DizkusModule\Form\Type\ForumType', $forum, []);        
        
        return $this->render('@ZikulaDizkusModule/Admin/modifyforum.html.twig', [
                    'topiccount' => $topiccount,
                    'postcount' => $postcount,
                    'forum' => $forum,
                    'form' => $form->createView(),
            ]);   
    }

    /**
     * @Route("/delete")
     *
     * @return Response
     */
    public function deleteforumAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('Admin/deleteforum.tpl', new DeleteForum()));
    }

    /**
     * @Route("/subscriptions", options={"expose"=true})
     *
     * @return Response
     */
    public function manageSubscriptionsAction()
    {
        $form = FormUtil::newForm($this->name, $this);

        return new Response($form->execute('Admin/managesubscriptions.tpl', new ManageSubscriptions()));
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
        $hookconfig = ModUtil::getVar($moduleName, 'dizkushookconfig');
        $module = ModUtil::getModule($moduleName);
        if (isset($module)) {
            $classname = $module->getVersionClass();
        } else {
            $classname = $moduleName . '_Version';
        }
        $moduleVersionObj = new $classname();
        $bindingsBetweenOwners = \HookUtil::getBindingsBetweenOwners($moduleName, ZikulaDizkusModule::NAME);
        foreach ($bindingsBetweenOwners as $k => $binding) {
            $areaname = $this->entityManager->getRepository('Zikula\\Component\\HookDispatcher\\Storage\\Doctrine\\Entity\\HookAreaEntity')->find($binding['sareaid'])->getAreaname();
            $bindingsBetweenOwners[$k]['areaname'] = $areaname;
            $bindingsBetweenOwners[$k]['areatitle'] = $this->view->__($moduleVersionObj->getHookSubscriberBundle($areaname)->getTitle());
        }
        $this->view->assign('areas', $bindingsBetweenOwners);
        $this->view->assign('dizkushookconfig', $hookconfig);
        $this->view->assign('activeModule', $moduleName);
        $this->view->assign('forums', ModUtil::apiFunc(ZikulaDizkusModule::NAME, 'Forum', 'getParents', array('includeLocked' => true)));

        return new Response($this->view->fetch('Hook/modifyconfig.tpl'));
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