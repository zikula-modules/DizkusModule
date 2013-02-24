<?php

/**
 * readtopforums
 * reads the last $maxforums forums and assign them in a
 * variable topforums and the number of them in topforumscount
 *
 * @params maxforums (int) number of forums to read, default = 5
 *
 */
function smarty_function_readtopforums($params, Zikula_View $view)
{
    $forummax = (!empty($params['maxforums'])) ? $params['maxforums'] : 5;

    /** @var $em Doctrine\ORM\EntityManager */
    $em = $view->getContainer()->get('doctrine.entitymanager');
    /* @var $qb Doctrine\ORM\QueryBuilder */
    $qb = $em->createQueryBuilder();
    $qb->select('f')
            ->from('Dizkus_Entity_Forum', 'f')
            ->orderBy('f.forum_posts', 'DESC');
    $qb->setMaxResults($forummax);
    $forums = $qb->getQuery()->getResult();

    $topforums = array();
    if (empty($forums)) {
        foreach ($forums as $topforum) {
            if (ModUtil::apiFunc('Dizkus', 'Permission', 'canRead', $topforum)) {
                $topforum['forum_name'] = DataUtil::formatForDisplay($topforum->getForum_name());
                $parent = $topforum->getParent();
                $parentName = isset($parent) ? $parent->getForum_name() : $view->__('Root');
                $topforum['cat_title'] = DataUtil::formatForDisplay($parentName);
                array_push($topforums, $topforum);
            }
        }
    }

    $view->assign('topforumscount', count($topforums));
    $view->assign('topforums', $topforums);
    return;
}
