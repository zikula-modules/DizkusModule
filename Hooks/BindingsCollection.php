<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Hooks;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of SettingsObject
 *
 * @author Kaik
 */
class BindingsCollection extends ArrayCollection {

    /**
     * Initializes a new ArrayCollection.
     *
     * @param array $elements
     */
    public function __construct()
    {
        $elements = [];
        parent::__construct($elements);
    }

}
