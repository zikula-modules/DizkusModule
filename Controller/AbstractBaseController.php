<?php

declare(strict_types=1);

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

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Zikula\Core\Controller\AbstractController;

/**
 * BaseController class
 */
abstract class AbstractBaseController extends AbstractController
{
    /**
     * Decode request format
     *
     * @return string
     */
    public function decodeFormat(Request $request)
    {
        if (0 === mb_strpos($request->headers->get('Accept'), 'application/json')) {
            $format = 'json';
        } elseif ($request->isXmlHttpRequest()) {
            $format = 'html';
        } elseif (null !== $request->attributes->get('format', null)) {
            $format = $request->attributes->get('format');
        } else {
            $format = $request->getRequestFormat(); // default 'html'
        }

        // json or ajax.html or html
        return $format;
    }

    /**
     * Decode request format
     *
     * @return string
     */
    public function decodeTemplate(Request $request)
    {
        if (0 === mb_strpos($request->headers->get('Accept'), 'application/json')) {
            $template = 'json';
        } elseif ($request->get('template', false)) {
            $template = $request->get('template');
        } elseif ($request->isXmlHttpRequest()) {
            $template = 'ajax';
        } else {
            $template = 'default';
        }

        // json or ajax.html or html
        return $template;
    }

    /**
     * Error experimental
     *
     * The idea is to DRY display/error handling
     * but at the moment it
     *
     * @param Request $request
     *
     * @return string
     */
    public function errorResponse($error = null, $redirectUrl = null, $format = 'html')
    {
        if (empty($error)) {
            $error = $this->__('Sorry, unknown error occured. Please try again');
        }

        if (empty($redirectUrl)) {
            $redirectUrl = $this->get('router')->generate('zikuladizkusmodule_forum_index', [], RouterInterface::ABSOLUTE_URL);
        }

        if ('json' === $format || 'ajax.html' === $format) {
            return new Response(json_encode(['error' => $error]));
        }

        $this->addFlash('error', $error);

        return new RedirectResponse($redirectUrl);
    }

    /**
     * Error experimental
     *
     * The idea is to DRY display/error handling
     * but at the moment it
     *
     * @param Request $request
     *
     * @return string
     */
    public function errorDisplay($error = null, $format = 'html')
    {
        if (empty($error)) {
            $error = $this->__('Sorry, unknown error occured. Please try again');
        }

        if ('json' === $format || 'ajax.html' === $format) {
            return ['error' => $error];
        }

        $this->addFlash('error', $error);

        return;
    }

    /**
     * experimental
     *
     * The idea is to DRY display/error handling
     * but at the moment it is experimental
     *
     * @param Request $request
     *
     * @return string
     */
    public function formatResponse($content, $format = 'html')
    {
        if ('json' === $format) {
            $response = json_encode(['data' => $content]);
        } elseif ('ajax.html' === $format) {
            $response = json_encode(['html' => $content]);
        } else {
            $response = $content;
        }

        return new Response($response);
    }
}
