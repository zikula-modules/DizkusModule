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
 * This class provides a handler to Assign ranks
 */
class Dizkus_Form_Handler_Admin_AssignRanks extends Zikula_Form_AbstractHandler
{

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
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

        list($rankimages, $ranks) = ModUtil::apiFunc('Dizkus', 'Rank', 'getAll', array('ranktype' => Dizkus_Entity_Rank::TYPE_HONORARY));

        $perpage = 20;

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('cu.uid, cu.uname, r.rank_id')
            ->from('Dizkus_Entity_ForumUser', 'u')
            ->leftJoin('u.user', 'cu')
            ->leftJoin('u.rank', 'r')
            ->orderBy('cu.uname', 'ASC');
        if (!empty($letter) and $letter != '*') {
            $qb->andWhere('cu.uname LIKE :letter')
                ->setParameter('letter', DataUtil::formatForStore($letter) . '%');
        }
        $query = $qb->getQuery();
        
        // Paginator
        // this isn't working at the moment - Jan 26 2013
//            $startnum = ($page - 1) * $perpage;
//            $count = \DoctrineExtensions\Paginate\Paginate::getTotalQueryResults($query);
//            $paginateQuery = \DoctrineExtensions\Paginate\Paginate::getPaginateQuery($query, $startnum, $perpage); // Step 2 and 3
//            $allusers = $paginateQuery->getArrayResult();

        $allusers = $query->getArrayResult();

        $this->view->assign('ranks', $ranks);
        $this->view->assign('rankimages', $rankimages);
        $this->view->assign('allusers', $allusers);
        $this->view->assign('letter', $letter);
        $this->view->assign('page', $page);
        $this->view->assign('perpage', $perpage);
//            $this->view->assign('usercount', $count);

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
    function handleCommand(Zikula_Form_View $view, &$args)
    {
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        // avoid some vars in the url of the pager
//        unset($_GET['submit']);
//        unset($_POST['submit']);
//        unset($_REQUEST['submit']);
        $setrank = $this->request->request->get('setrank');
        ModUtil::apiFunc('Dizkus', 'Rank', 'assign', array('setrank' => $setrank));
        unset($data['setrank']);
        $url = new Zikula_ModUrl('Dizkus', 'admin', 'assignranks', ZLanguage::getLanguageCode(), $data);
        return $view->redirect($url->getUrl());
    }

}
