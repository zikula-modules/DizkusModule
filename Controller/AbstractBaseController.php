<?php

/**
 * Dizkus
 *
 * @copyright (c) Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Controller;

use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Controller\AbstractController;

/**
 * BaseController class
 *
 */
abstract class AbstractBaseController extends AbstractController
{
    /**
     * Decode request format
     *
     * @param Request $request
     *
     * @return string
     */
    public function decodeFormat(Request $request) {

        if (0 === strpos($request->headers->get('Accept'), 'application/json')) {
            $format = 'json';
        } elseif ($request->isXmlHttpRequest()) {
            $format = 'ajax.html';
        } else {
            $format = $request->getRequestFormat(); // default 'html'
        }

        // json or ajax.html or html
        return $format;
    }
}
