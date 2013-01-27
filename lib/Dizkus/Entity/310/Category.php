<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_categories")
 */
class Dizkus_Entity_310_Category extends Zikula_EntityAccess
{

    /**
     * The following are annotations which define the cat_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $cat_id;

    /**
     * The following are annotations which define the cat_title field.
     * 
     * @ORM\Column(type="string", length=100)
     */
    private $cat_title = '';

    /**
     * The following are annotations which define the cat_order field.
     * 
     * @ORM\Column(type="integer")
     */
    private $cat_order = 1;

    public function getcat_id()
    {
        return $this->cat_id;
    }

    public function getcat_title()
    {
        return $this->cat_title;
    }

    public function getcat_order()
    {
        return $this->cat_order;
    }

    public function setcat_id($cat_id)
    {
        $this->cat_id = $cat_id;
    }

    public function setcat_title($cat_title)
    {
        $this->cat_title = $cat_title;
    }

    public function setcat_order($cat_order)
    {
        $this->cat_order = $cat_order;
    }

}