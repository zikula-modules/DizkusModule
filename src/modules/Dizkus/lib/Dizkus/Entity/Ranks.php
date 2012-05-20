<?php

use Doctrine\ORM\Mapping as ORM;


/**
 * Favorites entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_ranks")
 */
class Dizkus_Entity_Ranks extends Zikula_EntityAccess
{
    /**
     * The following are annotations which define the rank_id field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $rank_id;
    
    
    /**
     * The following are annotations which define the rank_title field.
     * 
     * @ORM\Column(type="string", length="50")
     */
    private $rank_title = '';
    
    /**
     * The following are annotations which define the rank_desc field.
     * 
     * @ORM\Column(type="string", length="255")
     */
    private $rank_desc = '';
    
    /**
     * The following are annotations which define the rank_min field.
     * 
     * @ORM\Column(type="integer")
     */
    private $rank_min = 0;
    
    
    /**
     * The following are annotations which define the rank_max field.
     * 
     * @ORM\Column(type="integer")
     */
    private $rank_max = 0;
    
    
    /**
     * The following are annotations which define the rank_special field.
     * 
     * @ORM\Column(type="integer", length=2)
     */
    private $rank_special = 0;
    
    
    
    /**
     * The following are annotations which define the rank_image field.
     * 
     * @ORM\Column(type="string", length="255")
     */
    private $rank_image = '';
    
    
    public function getrank_id()
    {
        return $this->rank_id;
    }
    
    public function getrank_title()
    {
        return $this->rank_title;
    }
    
    public function getrank_desc()
    {
        return $this->rank_desc;
    }
    
    public function getrank_min()
    {
        return $this->rank_min;
    }
    
    public function getrank_max()
    {
        return $this->rank_max;
    }
    
    public function getrank_special()
    {
        return $this->rank_special;
    }
    
    public function getrank_image()
    {
        return $this->rank_image;
    }
  
    public function setrank_id($rank_id)
    {
        $this->rank_id = $rank_id;
    }
    
    public function setrank_title($rank_title)
    {
        $this->rank_title = $rank_title;
    }
    
    public function setrank_desc($rank_desc)
    {
        $this->rank_desc = $rank_desc;
    }
    
    public function setrank_min($rank_min)
    {
        $this->rank_min = $rank_min;
    }
    
    public function setrank_max($rank_max)
    {
        $this->rank_max = $rank_max;
    }
    
    public function setrank_special($rank_special)
    {
        $this->rank_special =$rank_special;
    }
    
    public function setrank_image($rank_image)
    {
        $this->rank_image = $rank_image;
    }
    
    
}