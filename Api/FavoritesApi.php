<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Api;

use UserUtil;

class FavoritesApi extends \Zikula_AbstractApi
{

    private $_displayOnlyFavorites = array();

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
        $uid = UserUtil::getVar('uid');
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
    public function isFavorite($args)
    {
        if (empty($args['forum'])) {
            throw new \InvalidArgumentException();
        }
        if (empty($args['user_id'])) {
            $args['user_id'] = UserUtil::getVar('uid');
        }
        $forumUserFavorite = $this->entityManager
            ->getRepository('Zikula\DizkusModule\Entity\ForumUserFavoriteEntity')
            ->findOneBy(array('forum' => $args['forum'], 'forumUser' => $args['user_id']));

        return isset($forumUserFavorite);
    }

}
