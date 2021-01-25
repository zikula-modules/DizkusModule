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

namespace Zikula\DizkusModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\DizkusModule\Controller\AbstractBaseController as AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * @Route("/admin")
 */
class ImportController extends AbstractController
{
    /**
     * @Route("/import", options={"expose"=true})
     * @Theme("admin")
     * @return Response
     */
    public function importAction(Request $request)
    {
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        return $this->render('@ZikulaDizkusModule/Import/index.html.twig', [
              'importHelper' =>  $this->get('zikula_dizkus_module.import_helper')
        ]);
    }
}
