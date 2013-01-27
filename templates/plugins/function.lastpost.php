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
 * allowedhtml plugin
 * lists all allowed html tags
 *
 */
function smarty_function_lastpost($params, &$smarty) 
{
    // ToDo: remove plugin

    $em = ServiceUtil::getService('doctrine.entitymanager');
    $qb = $em->createQueryBuilder();
    $qb->select('p')
        ->from('Dizkus_Entity_Post', 'p')
        ->where('p.forum_id = :forumID')
        ->setParameter('forumID', $params['forumID'])
        // only the oldiest one
        ->orderBy('p.post_id', 'DESC')
        ->setMaxResults(1);
    $lastpost =  $qb->getQuery()->getArrayResult();


    if($lastpost) {
        $lastpost = $lastpost[0];
        if (empty($lastpost['post_title'])) {
            $topic =  $em->find('Dizkus_Entity_Topic', $lastpost['topic_id']);
            $lastpost['post_title'] = $topic->gettopic_title();
        }
        $smarty->assign('lastpost', $lastpost);
    }
}
