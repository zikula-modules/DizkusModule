<?php

use Doctrine\ORM\Mapping as ORM;


/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_forums")
 */
class Dizkus_Entity_Forums extends Zikula_EntityAccess
{
    
    

    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $forum_id;

    /**
     * The following are annotations which define the forum_name field.
     * 
     * @ORM\Column(type="string", length="150")
     */
    private $forum_name = '';
                                                 
       
    /**
     * The following are annotations which define the forum_desc field.
     * 
     * @ORM\Column(type="text")
     */
    private $forum_desc = '';
    
    
    /**
     * The following are annotations which define the forum_topics field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_topics = 0;
    
    /**
     * The following are annotations which define the forum_last_post_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $forum_last_post_id = 0;
    
    /**
     * The following are annotations which define the cat_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $cat_id = 0;
    
    /**
     * The following are annotations which define the forum_id field.
     *
     * @ORM\Column(type="integer")
     */
    private $is_subforum = 0;
    

    
    
    
    
    
    public function getforum_id()
    {
        return $this->forum_id;
    }
    
    
    
    public function getforum_name()
    {
        return $this->forum_name;
    }
    
    
    public function getforum_desc()
    {
        return $this->forum_desc;
    }
    
    
    public function getcat_id()
    {
        return $this->cat_id;
    }
    

}