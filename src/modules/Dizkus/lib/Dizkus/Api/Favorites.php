<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Favorites extends Zikula_AbstractApi {

    /**
     * get_favorite_status
     *
     * read the flag from the users table that indicates the users last choice: show all forum (0) or favorites only (1)
     * @params $args['user_id'] int the users id
     * @returns boolean
     *
     */
    public function getStatus($args)
    {
        if (!isset($args['user_id'])) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }

        $status = (int)UserUtil::getVar('dizkus_user_favorites', $args['user_id']);
        return ($status == 1) ? true : false;
    }


    /**
     * change_favorite_status
     *
     * changes the flag in the users table that indicates the users last choice: show all forum (0) or favorites only (1)
     * @params $args['user_id'] int the users id
     * @returns 0 or 1
     *
     */
    public function change_favorite_status($args)
    {
        if (!isset($args['user_id'])) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }

        $recentstatus = $this->get_favorite_status(array('user_id' => $args['user_id']));
        $user_favorites = ($recentstatus==true) ? 0 : 1;
        UserUtil::setVar('dizkus_user_favorites', $user_favorites, $args['user_id']);
        // force reload from db
        UserUtil::getVars($args['user_id'], true);
        //DBUtil::updateObject($args, 'dizkus__users', '', 'user_id');

        return (bool)$user_favorites;
    }

    /**
     * Get forum subscription status
     *
     * @params $args['user_id'] int the users uid
     * @params $args['forum_id'] int the forums id
     * @returns bool true if the user is subscribed or false if not
     */
    public function isFavorite($args)
    {
        if (empty($args['forum_id'])) {
            return LogUtil::registerArgsError();
        }
        if (empty($args['user_id'])) {
            $args['user_id'] = UserUtil::getVar('uid');
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(f.forum_id)')
           ->from('Dizkus_Entity_Favorites', 'f')
           ->where('f.user_id = :user')
           ->setParameter('user', $args['user_id'])
           ->andWhere('f.forum_id = :forum')
           ->setParameter('forum', $args['forum_id'])
           ->setMaxResults(1);
        $count = $qb->getQuery()->getSingleScalarResult();

        return ($count > 0) ? true : false;
        
    }

    /**
     * add forum to the favorites
     *
     * @params $args['forum_id'] int the forums id
     * @params $args['user_id'] int - Optional - the user id
     * @returns void
     */
    public function add($args)
    {
        if (!isset($args['user_id'])) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }

        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', $args['forum_id'])) {
            return LogUtil::registerPermissionError();
        }

        print_r('xxx');

        if ($this->getStatus($args) == false) {
            // add user only if not already a favorite
            // we can use the args parameter as-is
            $favorite = new Dizkus_Entity_Favorites();
            $favorite->merge($args);
            $this->entityManager->persist($favorite);
            $this->entityManager->flush();
        }

        return true;
    }

    /**
     * remove forum from the favorites
     *
     * @params $args['forum_id'] int the forums id
     * @params $args['user_id'] int - Optional - the user id
     * @returns bool
     */
    public function remove($args)
    {
        if (!isset($args['user_id'])) {
            $args['user_id'] = (int)UserUtil::getVar('uid');
        }

        // remove from favorites - no need to check the favorite status, we delete it anyway
        $user_id  = (int)DataUtil::formatForStore($args['user_id']);
        $forum_id = (int)DataUtil::formatForStore($args['forum_id']);

        $favorite = $this->entityManager->getRepository('Dizkus_Entity_Favorites')
            ->findOneBy(array('user_id' => $user_id, 'forum_id' => $forum_id));
        $this->entityManager->remove($favorite);
        $this->entityManager->flush();
        return true;
    }



}
