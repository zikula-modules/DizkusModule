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

use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\Event\GenericEvent;
use Zikula\Core\RouteUrl;
use Zikula\Core\Response\PlainResponse;

use Zikula\DizkusModule\Manager\ForumUserManager;
use Zikula\DizkusModule\Manager\ForumManager;

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
 * 
 */
class ForumController extends AbstractController
{

    /**
     * @Route("")
     *
     * Show all forums a user may see
     *
     * @param Request $request
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function indexAction(Request $request)
    {
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                        'forum_disabled_info' => $this->getVar('forum_disabled_info')
            ]); 
        }
        $indexTo = $this->getVar('indexTo');
        if (!empty($indexTo)) {
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_viewforum', ['forum' => (int) $indexTo], RouterInterface::ABSOLUTE_URL));
        }
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            throw new AccessDeniedException();
        }
        $lastVisitUnix = $this->get('zikula_dizkus_module.forum_user_helper')->getLastVisit();

        // get the forums to display
        $showOnlyFavorites = $this->get('zikula_dizkus_module.favorites_helper')->getStatus();   //ModUtil::apiFunc($this->name, 'Favorites', 'getStatus');
        $siteFavoritesAllowed = $this->getVar('favorites_enabled');
        $uid = ($request->getSession()->get('uid') > 1 ) ? $request->getSession()->get('uid') : 1;
        $loggedIn = $uid > 1 ? true : false ;
        $qb = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity')->childrenQueryBuilder();
        if ($loggedIn && $siteFavoritesAllowed && $showOnlyFavorites) {
            // display only favorite forums
            $qb->join('node.favorites', 'fa');
            $qb->andWhere('fa.forumUser = :uid');
            $qb->setParameter('uid', $uid);
        } else {
             //display an index of the level 1 forums
            $qb->andWhere('node.lvl = 1');
        }
        $forums = $qb->getQuery()->getResult();
        // filter the forum array by permissions
        $forums = $this->get('zikula_dizkus_module.security')->filterForumArrayByPermission($forums);
        // check to make sure there are forums to display
        if (count($forums) < 1) {
            if ($showOnlyFavorites) {
                $request->getSession()->getFlashBag()->add('error', $this->__('You have not selected any favorite forums. Please select some and try again.'));
                $managedForumUser = new ForumUserManager($uid);
                $managedForumUser->displayFavoriteForumsOnly(false);
                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
            } else {
                $request->getSession()->getFlashBag()->add('error', $this->__('This site has not set up any forums or they are all private. Contact the administrator.'));
            }
        }
        
        return $this->render('@ZikulaDizkusModule/Forum/main.html.twig', [
                    'last_visit_unix' => $lastVisitUnix,
                    'forums' => $forums,
                    'totalposts' => $this->get('zikula_dizkus_module.count_helper')->getAllPostsCount(),
                    'settings' => $this->getVars()
        ]);            
        
    }       

    /**
     * @Route("/forum/{forum}/{start}", requirements={"topic" = "^[1-9]\d*$", "start" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * View forum by id
     * opens a forum and shows the last postings
     *
     * @param Request $request
     * @param integer $forum the forum id
     * @param integer $start the posting to start with if on page 1+
     *
     * @throws NotFoundHttpException if forumID <= 0
     * @throws AccessDeniedException if perm check fails
     *
     * @return Response|RedirectResponse
     */
    public function viewforumAction(Request $request, $forum, $start = 1)
    {
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                        'forum_disabled_info' => $this->getVar('forum_disabled_info')
            ]); 
        }
        $lastVisitUnix = $this->get('zikula_dizkus_module.forum_user_helper')->getLastVisit();

        $managedForum = new ForumManager($forum);
        if (!$managedForum->exists()) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! The forum you selected (ID: %s) was not found. Please try again.', [$forum]));
            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
        }
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead($managedForum->get())) {
            throw new AccessDeniedException();
        }        
        // filter the forum children by permissions
        $forum = $this->get('zikula_dizkus_module.security')->filterForumChildrenByPermission($managedForum->get());

        return $this->render('@ZikulaDizkusModule/Forum/view.html.twig', [
                    'topics' => $managedForum->getTopics($start),
                    'last_visit_unix' => $lastVisitUnix,
                    'forum' => $forum,
                    'pager' => $managedForum->getPager(),
                    'permissions' => $managedForum->getPermissions(),
                    'isModerator' => $managedForum->isModerator(),
                    'breadcrumbs' => $managedForum->getBreadcrumbs(),
                    'settings' => $this->getVars()
        ]);          
    }    
    

    /**
     * @Route("/forum/modify", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request
     *  forum
     *  action
     *
     * @return string PlainResponse|BadDataResponse|UnavailableResponse
     *
     * @throws AccessDeniedException on failed perm check
     */
    public function modifyForumAction(Request $request)
    {
        //@todo - create non ajax version of this func (below) add ajax security
        //$this->checkAjaxToken();
        if (!$this->getVar('forum_enabled')) {
            return new UnavailableResponse([], strip_tags($this->getVar('forum_disabled_info')));
        }
        if (!$this->getVar('favorites_enabled')) {
            return new BadDataResponse([], $this->__('Error! Favourites have been disabled.'));
        }

        $forum = $request->request->get('forum');
        $action = $request->request->get('action');
        if (empty($forum)) {
            return new BadDataResponse([], $this->__('Error! No forum ID in \'Dizkus/Ajax/modifyForum()\'.'));
        }
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            // only need read perms to make a favorite
            throw new AccessDeniedException();
        }
        $this->get('zikula_dizkus_module.forum_helper')->modify($forum, $action);

        return new PlainResponse('successful');
    }

    /**
     * @ Route("/forum/modify/{forum}/{action}", requirements={"forum" = "^[1-9]\d*$", "action" = "addToFavorites|removeFromFavorites|subscribe|unsubscribe"})
     * @Method("GET")
     *
     * @param integer $forum
     * @param string $action
     *
     * Change a param of a forum
     * WARNING: this method is overridden by an Ajax method
     *
     * @return RedirectResponse
     */
//    public function modifyForumAction($forum, $action);
//    {
//        ModUtil::apiFunc($this->name, 'Forum', 'modify', array('forum' => $forum, 'action' => $action));
//
//        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_viewforum', array('forum' => $forum), RouterInterface::ABSOLUTE_URL));
//    }      
    
    
    /**
     * @Route("/forum/moderate")
     *
     * Moderate forum
     *
     * User interface for moderation of multiple topics.
     *
     * @return string
     */
    public function moderateForumAction(Request $request)
    {
        
        $forum_id = (int)$request->query->get('forum', null);
        if (!isset($forum_id)) {
            throw new \InvalidArgumentException();
        }
        // Get the Forum and Permission-Check
        $this->_managedForum = new ForumManager($forum_id);

        if (!$this->_managedForum->isModerator()) {
            // both zikula perms and Dizkus perms denied
            throw new AccessDeniedException();
        }


        $lastVisitUnix = $this->get('zikula_dizkus_module.forum_user_helper')->getLastVisit();

//        $this->view->assign('forum_id', $forum_id);
//        $this->view->assign('mode', '');
//        $this->view->assign('topic_ids', []);
//        $this->view->assign('last_visit_unix', $lastVisitUnix);
//        $this->view->assign('forum', $this->_managedForum->get());
        $topics = $this->_managedForum->getTopics();
        $topicSelect = [
            [
                'value' => '',
                'text' => "<< " . $this->__("Choose target topic") . " >>"],
        ];
        foreach ($topics as $topic) {
            $text = substr($topic->getTitle(), 0, 50);
            $text = strlen($text) < strlen($topic->getTitle()) ? "$text..." : $text;
            $topicSelect[] = [
                'value' => $topic->getTopic_id(),
                'text' => $text
            ];
        }
//        $this->view->assign('topicSelect', $topicSelect);
        $actions = [
            [
                'value' => '',
                'text' => "<< " . $this->__("Choose action") . " >>"],
            [
                'value' => 'solve',
                'text' => $this->__("Mark selected topics as 'solved'")],
            [
                'value' => 'unsolve',
                'text' => $this->__("Remove 'solved' status from selected topics")],
            [
                'value' => 'sticky',
                'text' => $this->__("Give selected topics 'sticky' status")],
            [
                'value' => 'unsticky',
                'text' => $this->__("Remove 'sticky' status from selected topics")],
            [
                'value' => 'lock',
                'text' => $this->__("Lock selected topics")],
            [
                'value' => 'unlock',
                'text' => $this->__("Unlock selected topics")],
            [
                'value' => 'delete',
                'text' => $this->__("Delete selected topics")],
            [
                'value' => 'move',
                'text' => $this->__("Move selected topics")],
            [
                'value' => 'join',
                'text' => $this->__("Join topics")],
        ];
//        $this->view->assign('actions', $actions);
        // For Movetopic
        $forums = ModUtil::apiFunc($this->name, 'Forum', 'getAllChildren');
        array_unshift($forums, [
            'value' => '',
            'text' => "<< " . $this->__("Select target forum") . " >>"]);
//        $this->view->assign('forums', $forums)
//            ->assign('pager', $this->_managedForum->getPager());

                return $this->render('@ZikulaDizkusModule/Forum/moderate.html.twig', [
//                    'form' => $form->createView(),
                    'settings' => $this->getVars()
        ]); 
    }

    /**
     * @Route("/forumusers", options={"expose"=true})
     *
     * update the "users online" section in the footer
     *
     * used in User/footer_with_ajax.tpl
     *
     * @return UnavailableResponse
     */
    public function forumusersAction()
    {
        if (!$this->getVar('forum_enabled')) {
            return new UnavailableResponse([], strip_tags($this->getVar('forum_disabled_info')));
        }
        $this->view->setCaching(false);
        if (System::getVar('shorturls')) {
            $this->view->_get_plugin_filepath('outputfilter', 'shorturls');
            $this->view->register_outputfilter('smarty_outputfilter_shorturls');
        }
        $this->view->display('Ajax/forumusers.tpl');
        System::shutDown();
    }   
    
    /**
     * Checks if the forum is disabled.
     *
     * @throws AccessDeniedException
     *
     * @return void
     */
    private function errorIfForumDisabled()
    {
        if (!$this->getVar('forum_enabled')) {
            throw new AccessDeniedException(strip_tags($this->getVar('forum_disabled_info')));
        }
    }   
}