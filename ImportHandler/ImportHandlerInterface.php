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

namespace Zikula\DizkusModule\ImportHandler;

/**
 * ImportInterface.
 *
 * @author Kaik
 */
interface ImportHandlerInterface
{
    /**
     * The id.
     *
     * @return string
     */
    public function getId();

    /**
     * Set prefix.
     *
     * @param string $prefix Import prefix
     *
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * Get prefix.
     *
     * @return string
     */
    public function getPrefix();

    /**
     * The title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * The description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get supported versions/prefixes.
     *
     * @return array
     */
    public function versionSupported();

    /**
     * Get list view rendered.
     *
     * @return object
     */
    public function getRenderListView($prefix);
}
