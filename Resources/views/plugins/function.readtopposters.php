<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * readtopposters
 * reads the top $maxposters users depending on their post count and assigns them in the
 * variable topposters and the number of them in toppostercount
 *
 * @params maxposters (int) number of users to read, default = 3
 *
 */
function smarty_function_readtopposters($params, Zikula_View $view)
{
    $params = $params['params'];
    $postermax = (!empty($params['maxposters'])) ? $params['maxposters'] : 3;

    /** @var $em Doctrine\ORM\EntityManager */
    $em = $view->getContainer()->get('doctrine.entitymanager');
    /* @var $qb Doctrine\ORM\QueryBuilder */
    $qb = $em->createQueryBuilder();
    $qb->select('u')
            ->from('Dizkus\Entity\ForumUserEntity', 'u')
            ->orderBy('u.postCount', 'DESC');
    $qb->setMaxResults($postermax);
    $forumUsers = $qb->getQuery()->getResult();

    $topposters = array();
    if (!empty($forumUsers)) {
        foreach ($forumUsers as $forumUser) {
            $topposters[] = array(
                'user_name' => DataUtil::formatForDisplay($forumUser->getUser()->getUname()),
                // for BC reasons
                'postCount' => DataUtil::formatForDisplay($forumUser->getPostCount()),
                'user_id' => DataUtil::formatForDisplay($forumUser->getUser_id()));
        }
    }

    $view->assign('toppostercount', count($topposters));
    $view->assign('topposters', $topposters);

    return;
}
