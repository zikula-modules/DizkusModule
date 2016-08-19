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
 * count the number of topics in a forum by the forum id
 *
 * @param $params
 * @param Zikula_View $view
 * @return mixed
 */
function smarty_function_countfreetopics($params, Zikula_View $view)
{
    $id = (!empty($params['id'])) ? $params['id'] : '0';

    /** @var \Doctrine\ORM\EntityManager $em */
    $em = $view->getContainer()->get('doctrine.entitymanager');
    $query = $em->createQuery('SELECT COUNT(t.topic_id) FROM Zikula\Module\DizkusModule\Entity\TopicEntity t WHERE t.forum=:fid');
    $query->setParameter('fid', $id);
    $count = $query->getSingleScalarResult();
    if (!empty($params['assign'])) {
        $view->assign($params['assign'], $count);
    } else {
        return $count;
    }
}
