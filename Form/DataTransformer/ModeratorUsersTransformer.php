<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Form\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Zikula\DizkusModule\Entity\ModeratorUserEntity;
use Zikula\DizkusModule\Manager\ForumUserManager;

/**
 * Description of ModeratorUsersTransformer
 *
 * @author Kaik
 */
class ModeratorUsersTransformer implements DataTransformerInterface
{
    private $om;

    private $forumUserManagerService;

    public function __construct(ObjectManager $om, ForumUserManager $forumUserManagerService)
    {
        $this->om = $om;
        $this->forumUserManagerService = $forumUserManagerService;
    }

    /**
     * Transforms $moderatorUsersCollection to array.
     *
     * @param  ArrayCollection $moderatorUsersCollection
     * @return string
     */
    public function transform($moderatorUsersCollection)
    {
        if ($moderatorUsersCollection->isEmpty()) {
            return [];
        }

        $selectedUsersArr = [];
        foreach ($moderatorUsersCollection as $element) {
            $uid = null === $element->getForumUser()->getUser() ? null : $element->getForumUser()->getUser()->getUid();
            if (!$uid) {
                continue;
            }
            $selectedUsersArr[$element->getForumUser()->getUser()->getUname()] = $uid;
        }

        return $selectedUsersArr;
    }

    /**
     * Transforms an array to ArrayCollection.
     *
     * @param  array $moderatorUsersArray
     * @return Issue|null
     * @throws TransformationFailedException if object (issue) is not found
     */
    public function reverseTransform($moderatorUsersArray)
    {
        $moderatorUsersCollection = new ArrayCollection();
        // this will
        if (empty($moderatorUsersArray)) {
            return $moderatorUsersCollection;
        }

        foreach ($moderatorUsersArray as $moderatorUid) {
            $moderator = new ModeratorUserEntity();
            $managedForumUser = $this->forumUserManagerService->getManager($moderatorUid);
            $moderator->setForumUser($managedForumUser->get());
            $moderatorUsersCollection->add($moderator);
        }

        return $moderatorUsersCollection;
    }
}
