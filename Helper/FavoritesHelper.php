<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\Helper;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;
use Zikula\UsersModule\Api\CurrentUserApi;


/**
 * FavoritesHelper
 *
 * @author Kaik
 */
class FavoritesHelper {
    
    /**
     * @var RequestStack
     */    
    private $requestStack;      
    
    /**
     * @var EntityManager
     */
    private $entityManager;    
    
    /**
     * @var CurrentUserApi
     */    
    private $userApi;     
    
    
    private $_displayOnlyFavorites = [];    
    
    public function __construct(
            RequestStack $requestStack,
            EntityManager $entityManager,
            CurrentUserApi $userApi        
         ) {
        
        $this->name = 'ZikulaDizkusModule';
        $this->requestStack = $requestStack;
        $this->request = $requestStack->getMasterRequest();
        $this->entityManager = $entityManager;
        $this->userApi = $userApi;    
    }
    

    /**
     * display of user favorite forums only?
     *
     * read the flag from the users table that indicates the users last choice: show all forum (0) or favorites only (1)
     * @param $args['user_id'] int the users id
     * @return boolean
     *
     */
    public function getStatus()
    {
        $loggedIn = $this->userApi->isLoggedIn();
        $uid = $loggedIn ? $this->request->getSession()->get('uid') : 1;
        if ($uid < 2) {
            return false;
        }
        // caching
        if (isset($this->_displayOnlyFavorites[$uid])) {
            return $this->_displayOnlyFavorites[$uid];
        }
        $forumUser = $this->entityManager->find('Zikula\DizkusModule\Entity\ForumUserEntity', $uid);
        if (!$forumUser) {
            return false;
        }
        $this->_displayOnlyFavorites[$uid] = $forumUser->getDisplayOnlyFavorites();

        return $this->_displayOnlyFavorites[$uid];
    }

    /**
     * Get forum subscription status
     *
     * @param $args
     *      'forum' Zikula\Module\DizkusModule\Entity\ForumEntity
     *      'user_id' int the users uid (optional)
     * @return boolean - true if the forum is user favorite or false if not
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function isFavorite($forum, $user_id = null)
    {
        if (empty($forum)) {
            throw new \InvalidArgumentException();
        }
        if (empty($user_id)) {
            $loggedIn = $this->userApi->isLoggedIn();
            $user_id = $loggedIn ? $this->request->getSession()->get('uid') : 1;
        }
        $forumUserFavorite = $this->entityManager
            ->getRepository('Zikula\DizkusModule\Entity\ForumUserFavoriteEntity')
            ->findOneBy(['forum' => $forum, 'forumUser' => $user_id]);

        return isset($forumUserFavorite);
    }

}
