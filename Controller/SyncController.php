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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\DizkusModule\Controller\AbstractBaseController as AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin/sync")
 */
class SyncController extends AbstractController
{
    /**
     * @Route("/index", options={"expose"=true})
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

        return $this->render('@ZikulaDizkusModule/Admin/sync.html.twig', [
//              'importHelper' =>  $this->get('zikula_dizkus_module.import_helper')
        ]);

//        $showstatus = !$request->request->get('silent', 0);
//        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
//            throw new AccessDeniedException();
//        }
//
//        if ($showstatus && $this->get('zikula_dizkus_module.synchronization_helper')->forums()) {
//            $this->addFlash('status', $this->__('Done! Synchronized forums index.'));
//        } else {
//            $this->addFlash('error', $this->__('Error synchronizing forums index.'));
//        }
//
//        if ($showstatus && $this->get('zikula_dizkus_module.synchronization_helper')->topics()) {
//            $this->addFlash('status', $this->__('Done! Synchronized topics.'));
//        } else {
//            $this->addFlash('error', $this->__('Error synchronizing topics.'));
//        }
//
//        if ($showstatus && $this->get('zikula_dizkus_module.synchronization_helper')->posters()) {
//            $this->addFlash('status', $this->__('Done! Synchronized posts counter.'));
//        } else {
//            $this->addFlash('error', $this->__('Error synchronizing posts counter.'));
//        }
//
//        return new RedirectResponse($this->get('router')->generate('zikuladizkusmodule_admin_tree', [], RouterInterface::ABSOLUTE_URL));
    }

    /**
     * @Route("/forumtree", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function forumtreeAction()
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $data['tree'] = [];

        return new Response(json_encode($data));
    }
}
