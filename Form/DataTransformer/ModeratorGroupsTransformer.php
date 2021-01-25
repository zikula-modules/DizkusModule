<?php

declare(strict_types=1);

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Zikula\DizkusModule\Entity\ModeratorGroupEntity;

/**
 * Description of ModeratorUsersTransformer
 *
 * @author Kaik
 */
class ModeratorGroupsTransformer implements DataTransformerInterface
{
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms $moderatorGroupssCollection to array.
     *
     * @param  ArrayCollection $moderatorGroupsCollection
     * @return array
     */
    public function transform($moderatorGroupsCollection)
    {
        if ($moderatorGroupsCollection->isEmpty()) {
            return [];
        }

        $selectedGroupsArr = [];
        foreach ($moderatorGroupsCollection as $element) {
            $selectedGroupsArr[$element->getGroup()->getGid()] = $element->getGroup()->getGid();
        }

        return $selectedGroupsArr;
    }

    /**
     * Transforms an array to ArrayCollection.
     *
     * @param  array $moderatorGroupsArray
     * @return ArrayCollection
     * @throws TransformationFailedException if object (issue) is not found
     */
    public function reverseTransform($moderatorGroupsArray)
    {
        $moderatorGroupsCollection = new ArrayCollection();
        // this will
        if (empty($moderatorGroupsArray)) {
            return $moderatorGroupsCollection;
        }

        foreach ($moderatorGroupsArray as $groupId) {
            $moderatorGroup = new ModeratorGroupEntity();
            $group = $this->om->getRepository('ZikulaGroupsModule:GroupEntity')->find($groupId); //@todo what if not found?
            if (!$group) {
                throw new TransformationFailedException();
            }
            $moderatorGroup->setGroup($group);
            $moderatorGroupsCollection->add($moderatorGroup);
        }

        return $moderatorGroupsCollection;
    }
}
