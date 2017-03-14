<?php

/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Response\PlainResponse;
use Zikula\DizkusModule\Form\Type\ModerateType;

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
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                        'forum_disabled_info' => $this->getVar('forum_disabled_info'),
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
        $lastVisitUnix = $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit();

        // get the forums to display
        $showOnlyFavorites = $this->get('zikula_dizkus_module.favorites_helper')->getStatus();   //ModUtil::apiFunc($this->name, 'Favorites', 'getStatus');
        $siteFavoritesAllowed = $this->getVar('favorites_enabled');
        $uid = ($request->getSession()->get('uid') > 1) ? $request->getSession()->get('uid') : 1;
        $loggedIn = $uid > 1 ? true : false;
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
                $managedForumUser = $this->get('zikula_dizkus_module.forum_user_manager')->getManager($uid); //new ForumUserManager($uid);
                $managedForumUser->displayFavoriteForumsOnly(false);

                return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
            } else {
                $request->getSession()->getFlashBag()->add('error', $this->__('This site has not set up any forums or they are all private. Contact the administrator.'));
            }
        }

        return $this->render('@ZikulaDizkusModule/Forum/main.html.twig', [
                    'last_visit_unix' => $lastVisitUnix,
                    'forums'          => $forums,
                    'totalposts'      => $this->get('zikula_dizkus_module.count_helper')->getAllPostsCount(),
                    'settings'        => $this->getVars(),
        ]);
    }

    /**
     * @Route("/forum/{forum}/{start}", requirements={"forum" = "^[1-9]\d*$", "start" = "^[1-9]\d*$"})
     * @Method("GET")
     *
     * View forum by id
     * opens a forum and shows the last postings
     *
     * @param Request $request
     * @param int     $forum   the forum id
     * @param int     $start   the posting to start with if on page 1+
     *
     * @throws NotFoundHttpException if forumID <= 0
     * @throws AccessDeniedException if perm check fails
     *
     * @return Response|RedirectResponse
     */
    public function viewforumAction(Request $request, $forum, $start = 1)
    {
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                        'forum_disabled_info' => $this->getVar('forum_disabled_info'),
            ]);
        }
        $lastVisitUnix = $this->get('zikula_dizkus_module.forum_user_manager')->getLastVisit();

        $managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($forum); //new ForumManager($forum);
        if (!$managedForum->exists()) {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! The forum you selected (ID: %s) was not found. Please try again.', [$forum]));

            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL));
        }
        // Permission check
        if (!$this->get('zikula_dizkus_module.security')->canRead($managedForum->get())) {
            throw new AccessDeniedException();
        }

        return $this->render('@ZikulaDizkusModule/Forum/view.html.twig', [
                    'topics'          => $managedForum->getTopics($start),
                    'last_visit_unix' => $lastVisitUnix,
                     // filter the forum children by permissions
                    'forum'       => $this->get('zikula_dizkus_module.security')->filterForumChildrenByPermission($managedForum->get()),
                    'pager'       => $managedForum->getPager(),
                    'permissions' => $managedForum->getPermissions(),
                    'isModerator' => $managedForum->isModerator(),
                    'breadcrumbs' => $managedForum->getBreadcrumbs(),
                    'settings'    => $this->getVars(),
        ]);
    }

    /**
     * @Route("/forum/moderate/{forum}", requirements={"forum" = "^[1-9]\d*$"})
     *
     * Moderate forum
     *
     * User interface for moderation of multiple topics.
     *
     * @return string
     */
    public function moderateForumAction(Request $request, $forum)
    {
        // params check
        if (!isset($forum)) {
            throw new \InvalidArgumentException();
        }
        // Get the Forum and Permission-Check
        $this->_managedForum = $this->get('zikula_dizkus_module.forum_manager')->getManager($forum);

        if (!$this->_managedForum->isModerator()) {
            // both zikula perms and Dizkus perms denied
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new ModerateType($this->get('translator'), $this->_managedForum), [], []);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $action = isset($data['action']) ? $data['action'] : '';
            $shadow = $data['createshadowtopic'];
            $moveto = isset($data['moveto']) ? $data['moveto'] : null;
            $jointo = isset($data['jointo']) ? $data['jointo'] : null;
            $jointo_select = isset($data['jointotopic']) ? $data['jointotopic'] : null;
            // get this value by traditional method because checkboxen have values
            $topic_ids = $request->request->get('topic_id', []);

            if (count($topic_ids) != 0) {
                switch ($action) {
                    case 'del':
                    case 'delete':
                        foreach ($topic_ids as $topic_id) {
                            dump('delete topic'.$topic_id);
                            $forum_id = $this->get('zikula_dizkus_module.topic_manager')->delete($topic_id);
                          // $forum_id = ModUtil::apiFunc($this->name, 'topic', 'delete', ['topic' => $topic_id]);
                        }
                        break;

                    case 'move':
                        if (empty($moveto)) {
                            $request->getSession()->getFlashBag()->add('error', $this->__('Error! You did not select a target forum for the move.'));
                            dump('move to forum'.$moveto); //
                            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_moderateforum', ['forum' => $this->_managedForum->getId()], RouterInterface::ABSOLUTE_URL));
                        }
                        foreach ($topic_ids as $topic_id) {
                            dump('move topic #'.$topic_id.' to forum #'.$moveto); //
                            $this->get('zikula_dizkus_module.topic_manager')->move($topic_id, $moveto, $shadow);
//                          ModUtil::apiFunc($this->name, 'topic', 'move', ['topic_id' => $topic_id,
//                                'forum_id' => $moveto,
//                                'createshadowtopic' => $shadow]);
                        }
                        break;

                    case 'lock':
                    case 'unlock':
                    case 'solve':
                    case 'unsolve':
                    case 'sticky':
                    case 'unsticky':
                        foreach ($topic_ids as $topic_id) {
                            dump($action.' '.$topic_id); //
//                            ModUtil::apiFunc($this->name, 'topic', 'changeStatus', [
//                                'topic' => $topic_id,
//                                'action' => $action]);
                        }
                        break;

                    case 'join':
                        if (empty($jointo) && empty($jointo_select)) {
                            $request->getSession()->getFlashBag()->add('error', $this->__('Error! You did not select a target topic to join.'));

                            return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_moderateforum', ['forum' => $this->_managedForum->getId()], RouterInterface::ABSOLUTE_URL));
                        }
                        // text input overrides select box
                        if (empty($jointo) && !empty($jointo_select)) {
                            $jointo = $jointo_select;
                        }
                        if (in_array($jointo, $topic_ids)) {
                            // jointo, the target topic, is part of the topics to join
                            // we remove this to avoid a loop
                            $fliparray = array_flip($topic_ids);
                            unset($fliparray[$jointo]);
                            $topic_ids = array_flip($fliparray);
                        }
                        foreach ($topic_ids as $from_topic_id) {
                            dump('join from'.$from_topic_id.' to '.$jointo); //
//                            ModUtil::apiFunc($this->name, 'topic', 'join', ['from_topic_id' => $from_topic_id,
//                                'to_topic_id' => $jointo]);
                        }
                        break;

                    default:
                }
            }

            //return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_forum_moderateforum', ['forum' => $this->_managedForum->getId()], RouterInterface::ABSOLUTE_URL));
        }

        return $this->render('@ZikulaDizkusModule/Forum/moderate.html.twig', [
            'form'            => $form->createView(),
            'forum'           => $this->_managedForum->get(),
            'pager'           => $this->_managedForum->getPager(),
            'last_visit_unix' => $this->get('zikula_dizkus_module.forum_user_helper')->getLastVisit(),
            'settings'        => $this->getVars(),
        ]);
    }

    /**
     * AJAX.
     */

    /**
     * @Route("/forum/modify/{forum}/{action}", requirements={"forum" = "^[1-9]\d*$", "action" = "addToFavorites|removeFromFavorites|subscribe|unsubscribe"}, options={"expose"=true})
     * @ Method("POST")
     *
     * @param Request $request
     *                         forum
     *                         action
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return string PlainResponse|BadDataResponse|UnavailableResponse
     */
    public function modifyForumAction(Request $request, $forum, $action)
    {
        if (!$this->getVar('forum_enabled') && !$this->hasPermission($this->name.'::', '::', ACCESS_ADMIN)) {
            if ($request->isXmlHttpRequest()) {
                return new UnavailableResponse([], strip_tags($this->getVar('forum_disabled_info')));
            } else {
                return $this->render('@ZikulaDizkusModule/Common/dizkus.disabled.html.twig', [
                            'forum_disabled_info' => $this->getVar('forum_disabled_info'),
                ]);
            }
        }

        if (!$this->getVar('favorites_enabled')) {
            return new BadDataResponse([], $this->__('Error! Favourites have been disabled.'));
        }

        if (empty($forum)) {
            return new BadDataResponse([], $this->__('Error! No forum ID in \'Dizkus/Ajax/modifyForum()\'.'));
        }
        if (!$this->get('zikula_dizkus_module.security')->canRead([])) {
            // only need read perms to make a favorite
            throw new AccessDeniedException();
        }
        $this->get('zikula_dizkus_module.forum_manager')->modify($forum, $action);

        return new PlainResponse('successful');

//        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_user_viewforum', array('forum' => $forum), RouterInterface::ABSOLUTE_URL));
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
    }
}
