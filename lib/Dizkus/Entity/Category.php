<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * Category entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forums")
 */
class Dizkus_Entity_Category extends Zikula_EntityAccess
{

    /**
     * forum_id
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="forum_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * forum_name
     * 
     * @ORM\Column(type="string", length=150, name="forum_name")
     */
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

}