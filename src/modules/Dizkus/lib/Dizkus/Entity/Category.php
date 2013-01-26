<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Forums entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forums")
 */
class Dizkus_Entity_Category extends Zikula_EntityAccess
{

    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="forum_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The following are annotations which define the forum_name field.
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