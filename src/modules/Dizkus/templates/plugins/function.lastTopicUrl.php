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
function smarty_function_lastTopicUrl($params, &$smarty)
{
    $numberOfPosts = 0;
    if (empty($params['replies']) || $params['replies'] < 0) {
        $em = ServiceUtil::getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('count(p)')
            ->from('Dizkus_Entity_Posts', 'p')
            ->where('p.topic_id = :topicId')
            ->setParameter('topicId', $params['topic']);
        // only the oldiest one
        $numberOfPosts = (int)$qb->getQuery()->getSingleScalarResult();
    } else {
        $numberOfPosts = $params['replies']+1;
    }

    $postPerPage = (int)ModUtil::getVar('Dizkus', 'posts_per_page');

    $params = array(
        'topic' => $params['topic'],
        'start' => floor($numberOfPosts/$postPerPage)*$postPerPage
    );

    return ModUtil::url('Dizkus', 'user', 'viewtopic', $params);

}
