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
    $dizkusModuleName = "ZikulaDizkusModule";
    $params = $params['params'];
    $forummax = (!empty($params['maxforums'])) ? $params['maxforums'] : 5;

    /** @var $em Doctrine\ORM\EntityManager */
    $em = $view->getContainer()->get('doctrine.entitymanager');
    /* @var $qb Doctrine\ORM\QueryBuilder */
    $qb = $em->createQueryBuilder();
    $qb->select('f')
            ->from('Zikula\Module\DizkusModule\Entity\ForumEntity', 'f')
            ->orderBy('f.postCount', 'DESC');
    $qb->setMaxResults($forummax);
    $forums = $qb->getQuery()->getResult();

    $topforums = array();
    if (empty($forums)) {
        foreach ($forums as $topforum) {
            if (ModUtil::apiFunc($dizkusModuleName, 'Permission', 'canRead', $topforum)) {
                $topforum['name'] = DataUtil::formatForDisplay($topforum->getName());
                $parent = $topforum->getParent();
                $parentName = isset($parent) ? $parent->getName() : $view->__('Root');
                $topforum['cat_title'] = DataUtil::formatForDisplay($parentName);
                array_push($topforums, $topforum);
            }
        }
    }

    $view->assign('topforumscount', count($topforums));
    $view->assign('topforums', $topforums);

    return;
}
