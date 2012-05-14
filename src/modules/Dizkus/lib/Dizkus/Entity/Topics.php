<?php

use Doctrine\ORM\Mapping as ORM;


/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_topics")
 */
class Dizkus_Entity_Topics extends Zikula_EntityAccess
{

    /**
     * The following are annotations which define the topic_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $topic_id;


    /**
     * The following are annotations which define the topic_title field.
     * 
     * @ORM\Column(type="string", length="255")
     */
    private $topic_title = '';
    
    
    
    
    public function gettopic_id()
    {
        return $this->topic_id;
    }
    
    public function gettopic_title()
    {
        return $this->topic_title;
    }
    

}