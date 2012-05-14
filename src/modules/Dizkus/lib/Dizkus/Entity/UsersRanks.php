<?php

use Doctrine\ORM\Mapping as ORM;


/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="objectdata_attributes")
 */
class Dizkus_Entity_UsersRanks extends Zikula_EntityAccess
{
    
    
    /**
     * The following are annotations which define the uid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    
    /**
     * The following are annotations which define the uid field.
     *
     * @var Dizkus_Entity_Users
     * @ORM\ManyToOne(targetEntity="Dizkus_Entity_Users", inversedBy="attributes")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="uid")
     */
    private $object_id;
    
    
    /**
     * The following are annotations which define the uname field.
     * 
     * @ORM\Column(type="string", length="80")
     */
    private $attribute_name = '';
    
    
    
    /**
     * The following are annotations which define the uname field.
     * 
     * @ORM\Column(type="text")
     */
    private $value;

    
    public function getValue()
    {
        return $this->value;
    }
    
   

}