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

    /**
     * rank_id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $rank_id;

    /**
     * rank_title
     * 
     * @ORM\Column(type="string", length=50)
     */
    private $rank_title = '';

    /**
     * rank_desc
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $rank_desc = '';

    /**
     * rank_min
     * 
     * @ORM\Column(type="integer")
     */
    private $rank_min = 0;

    /**
     * rank_max
     * 
     * @ORM\Column(type="integer")
     */
    private $rank_max = 0;

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

    public function getRank_title()
    {
        return $this->rank_title;
    }

    public function getRank_desc()
    {
        return $this->rank_desc;
    }

    public function getRank_min()
    {
        return $this->rank_min;
    }

    public function getRank_max()
    {
        return $this->rank_max;
    }

    public function getRank_special()
    {
        return $this->rank_special;
    }

    public function getRank_image()
    {
        return $this->rank_image;
    }

    public function setRank_id($rank_id)
    {
        $this->rank_id = $rank_id;
    }

    public function setRank_title($rank_title)
    {
        $this->rank_title = $rank_title;
    }

    public function setRank_desc($rank_desc)
    {
        $this->rank_desc = $rank_desc;
    }

    public function setRank_min($rank_min)
    {
        $this->rank_min = $rank_min;
    }

    public function setRank_max($rank_max)
    {
        $this->rank_max = $rank_max;
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