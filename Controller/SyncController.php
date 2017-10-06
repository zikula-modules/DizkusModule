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

namespace Zikula\DizkusModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\DizkusModule\Controller\AbstractBaseController as AbstractController;
use Zikula\DizkusModule\Entity\ForumEntity;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin/sync")
 */
class SyncController extends AbstractController
{
    /**
     * @Route("", options={"expose"=true})
     *
     * @Theme("admin")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function syncAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        $this->getDoctrine()->getManager()->getConfiguration()->addCustomHydrationMode('tree', 'Gedmo\Tree\Hydrator\ORM\TreeObjectHydrator');
        $repo = $this->getDoctrine()->getManager()->getRepository('Zikula\DizkusModule\Entity\ForumEntity');
        $tree = $repo->createQueryBuilder('node')->getQuery()
            ->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getResult('tree');

        return $this->render('@ZikulaDizkusModule/Admin/sync.html.twig', [
            'status'       => $repo->verify(),
            'tree'         => $tree,
            'importHelper' => $this->get('zikula_dizkus_module.import_helper')
        ]);
    }

    /**
     * @Route("/forum/{forum}", requirements={"forum" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * @Method("GET")
     *
     * @param ForumEntity $forum
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException on Perm check failure
     */
    public function forumAction(Request $request, ForumEntity $forum)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_sync_sync', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/forum/{forum}/topics", requirements={"forum" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * @Method("GET")
     *
     * @param ForumEntity $forum
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException on Perm check failure
     */
    public function forumTopicsAction(Request $request, ForumEntity $forum)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_sync_sync', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/forum/{forum}/posts", requirements={"forum" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * @Method("GET")
     *
     * @param ForumEntity $forum
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException on Perm check failure
     */
    public function forumPostsAction(Request $request, ForumEntity $forum)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_sync_sync', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/forum/{forum}/lastpost", requirements={"forum" = "^[1-9]\d*$"}, options={"expose"=true})
     *
     * @Method("GET")
     *
     * @param ForumEntity $forum
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException on Perm check failure
     */
    public function forumLastPostAction(Request $request, ForumEntity $forum)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $request->getSession()->getFlashBag()->add('status', $this->__('The forum last post synchronised.'));

        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_sync_sync', [], RouterInterface::ABSOLUTE_URL));
    }
}
