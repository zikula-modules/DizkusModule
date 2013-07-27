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
 * Rank entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_ranks")
 */
class Dizkus_Entity_Rank extends Zikula_EntityAccess
{
    const TYPE_HONORARY = 1;
    const TYPE_POSTCOUNT = 0;

    /**
     * rank_id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $rank_id;

    /**
     * title
     * 
     * @ORM\Column(type="string", length=50)
     */
    private $title = '';

    /**
     * description
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $description = '';

    /**
     * minimumCount
     * 
     * @ORM\Column(type="integer")
     */
    private $minimumCount = 0;

    /**
     * maximumCount
     * 
     * @ORM\Column(type="integer")
     */
    private $maximumCount = 0;

    /**
     * rank_special
     * 
     * @ORM\Column(type="integer", length=2)
     */
    private $rank_special = 0;

    /**
     * rank_image
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $rank_image = '';

    public function getRank_id()
    {
        return $this->rank_id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getMinimumCount()
    {
        return $this->minimumCount;
    }

    public function getMaximumCount()
    {
        return $this->maximumCount;
    }

    public function getRank_special()
    {
        return $this->rank_special;
    }

    public function getRank_image()
    {
        return $this->rank_image;
    }
    
    /**
     * compute and return the rank link
     * @return string
     */
    public function getRank_link()
    {
        $link = (substr($this->description, 0, 7) == 'http://') ? $this->description : '';
        if (!empty($this->rank_image)) {
            $this->rank_image = ModUtil::getVar('Dizkus', 'url_ranks_images') . '/' . $this->rank_image;
        }
        return $link;
    }

    public function setRank_id($rank_id)
    {
        $this->rank_id = $rank_id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setMinimumCount($minimumCount)
    {
        $this->minimumCount = $minimumCount;
    }

    public function setMaximumCount($maximumCount)
    {
        $this->maximumCount = $maximumCount;
    }

    public function setRank_special($rank_special)
    {
        $this->rank_special = $rank_special;
    }

    public function setRank_image($rank_image)
    {
        $this->rank_image = $rank_image;
    }

}