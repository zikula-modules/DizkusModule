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
 * Description of ImportInterface
 *
 * @author Kaik
 */
interface ImportHandlerInterface
{
    /**
     * The id
     *
     * @return string
     */
    public function getId();

    /**
     * The title
     *
     * @return string
     */
    public function getTitle();

    /**
     * The description
     *
     * @return string
     */
    public function getDescription();
    
    /**
     * The status
     *
     * @return array
     */
    public function getStatus();
}
