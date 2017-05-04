<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * Rank entity class
 *
 * @ORM\Entity
 * @ORM\Table(name="dizkus_ranks")
 */
class RankEntity extends EntityAccess
{
    const TYPE_HONORARY = 1;
    const TYPE_POSTCOUNT = 0;
    /**
     * Module name
     * @var string
     */
    const MODULENAME = 'ZikulaDizkusModule';

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
     * type
     *
     * @ORM\Column(type="integer", length=2)
     */
    private $type = 0;

    /**
     * image
     *
     * @ORM\Column(type="string", length=255)
     */
    private $image = '';

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

    public function getType()
    {
        return $this->type;
    }

    public function getImage()
    {
        return $this->image;
    }

    /**
     * compute and return the rank link
     * @return string
     */
    public function getRank_link()
    {
        $link = substr($this->description, 0, 7) == 'http://' ? $this->description : '';

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

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }
}
