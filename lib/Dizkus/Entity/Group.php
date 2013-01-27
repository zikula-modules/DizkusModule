<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="groups")
 */
class Dizkus_Entity_Group extends Zikula_EntityAccess
{

    /**
     * The following are annotations which define the id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $gid;

    public function getGid()
    {
        return $this->gid;
    }

    /**
     * The following are annotations which define the name field.
     *
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function getName()
    {
        return $this->name;
    }

}