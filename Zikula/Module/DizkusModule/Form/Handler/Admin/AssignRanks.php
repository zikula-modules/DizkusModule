<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Form\Handler\Admin;

use ModUtil;
use SecurityUtil;
use System;
use Zikula_Form_View;
use Zikula\Module\DizkusModule\Entity\RankEntity;
use Zikula\Module\DizkusModule\Manager\ForumUserManager;
use Zikula\Core\ModUrl;
use ZLanguage;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This class provides a handler to Assign ranks
 */
class AssignRanks extends \Zikula_Form_AbstractHandler
{

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $letter = $this->request->query->get('letter');
        $lastletter = $this->request->query->get('lastletter');
        $page = (int)$this->request->query->get('page', 1);

        // check for a letter parameter
        if (!empty($lastletter)) {
            $letter = $lastletter;
        }

        if (empty($letter) || strlen($letter) != 1) {
            $letter = '*';
        }
        $letter = strtolower($letter);

        list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => RankEntity::TYPE_HONORARY));

        $perpage = 20;

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('u')
                ->from('Zikula\Module\UsersModule\Entity\UserEntity', 'u')
                ->orderBy('u.uname', 'ASC');
        if (!empty($letter) and $letter != '*') {
            $qb->andWhere('u.uname LIKE :letter')
                ->setParameter('letter', $letter . '%');
        }
        $query = $qb->getQuery();
        $query->setFirstResult(($page - 1) * $perpage)
            ->setMaxResults($perpage);

        // Paginator
        $allusers = new Paginator($query);

        // recreate the array of users as ForumUserEntities
        $userArray = array();
        /** @var $user \Zikula\Module\UsersModule\Entity\UserEntity */
        foreach ($allusers as $user) {
            $managedForumUser = new ForumUserManager($user->getUid(), false);
            $forumUser = $managedForumUser->get();
            if (isset($forumUser)) {
                $userArray[$user->getUid()] = $forumUser;
            }
        }

        $this->view->assign('ranks', $ranks);
        $this->view->assign('rankimages', $rankimages);
        $this->view->assign('allusers', $userArray);
        $this->view->assign('letter', $letter);
        $this->view->assign('page', $page);
        $this->view->assign('perpage', $perpage);
        $this->view->assign('usercount', count($userArray));
        return true;
    }

    /**
     * Handle form submission.
     *
     * @param Zikula_Form_View $view  Current Zikula_Form_View instance.
     * @param array            &$args Arguments.
     *
     * @return bool|void
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        $queryParams = $this->request->query->all();
        unset($queryParams['module'], $queryParams['type'], $queryParams['func']);

        $setrank = $this->request->request->get('setrank');
        ModUtil::apiFunc($this->name, 'Rank', 'assign', array('setrank' => $setrank));
        $url = new ModUrl($this->name, 'admin', 'assignranks', ZLanguage::getLanguageCode(), $queryParams);

        return $view->redirect($url);
    }

}
