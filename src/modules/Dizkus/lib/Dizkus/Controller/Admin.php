<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */
class Dizkus_Controller_Admin extends Zikula_AbstractController
{

    public function postInitialize()
    {
        $this->view->setCaching(false)->add_core_data();
    }

    /**
     * the main administration function
     *
     */
    public function main()
    {
        $url = ModUtil::url($this->name, 'admin', 'tree');

        return System::redirect($url);
    }

    /**
     * Change forum order
     *
     * Move up or down a forum in the tree
     *
     * @return boolean
     */
    public function changeForumOrder()
    {
        $action = $this->request->query->get('action', 'moveUp');
        $forumId = $this->request->query->get('forum', null);
        if (empty($forumId)) {
            return LogUtil::registerArgsError();
        }
        $repo = $this->entityManager->getRepository('Dizkus_Entity_Forum');
        $forum = $repo->find($forumId);
        if ($action == 'moveUp') {
            $repo->moveUp($forum, true);
        } else {
            $repo->moveDown($forum, true);
        }
        $this->entityManager->flush();
        $url = ModUtil::url($this->name, 'admin', 'tree');

        return System::redirect($url);
    }

    /**
     * the main administration function
     *
     */
    public function m()
    {
        DoctrineHelper::updateSchema($this->entityManager, array('Dizkus_Entity_Forum'));


        // import new tree
        $order = array('cat_order' => 'ASC');
        $categories = $this->entityManager->getRepository('Dizkus_Entity_310_Category')->findBy(array(), $order);
        foreach ($categories as $category) {
            $newCatForum = new Dizkus_Entity_Forum();
            $newCatForum->setforum_name($category->getcat_title());
            $this->entityManager->persist($newCatForum);

            $where = array('root' => $category->getcat_id());
            $forums = $this->entityManager->getRepository('Dizkus_Entity_Forum')->findBy($where);
            foreach ($forums as $forum) {
                $forum->setParent($newCatForum);
                $this->entityManager->persist($forum);
            }
        }
        $this->entityManager->flush();



        // create missing poster data
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
                ->from('Dizkus_Entity_310_Post', 'p')
                ->groupBy('p.poster_id');
        $posts = $qb->getQuery()->getArrayResult();

        foreach ($posts as $post) {
            if ($post['poster_id'] > 0) {
                $poster = $this->entityManager->getRepository('Dizkus_Entity_Poster')->find($post['poster_id']);
                if (!$poster) {
                    $poster = new Dizkus_Entity_Poster();
                    $poster->setuser_id($post['poster_id']);
                    $this->entityManager->persist($poster);
                }
            }
        }
        $this->entityManager->flush();



        ModUtil::apiFunc('Dizkus', 'Sync', 'all');



        return ' ';
    }

    /**
     * preferences
     *
     */
    public function preferences()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Create output object
        $form = FormUtil::newForm('Dizkus', $this);

        // Return the output that has been generated by this function
        return $form->execute('admin/preferences.tpl', new Dizkus_Form_Handler_Admin_Prefs());
    }

    /**
     * syncforums
     */
    public function syncforums()
    {
        $showstatus = !($this->request->request->get('silent', 0));

        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $succesful = ModUtil::apiFunc('Dizkus', 'Sync', 'forums');
        if ($showstatus && $succesful) {
            LogUtil::registerStatus($this->__('Done! Synchronized forum index.'));
        } else {
            return LogUtil::registerError($this->__("Error synchronizing forum index"));
        }

        $succesful = ModUtil::apiFunc('Dizkus', 'Sync', 'topics');
        if ($showstatus && $succesful) {
            LogUtil::registerStatus($this->__('Done! Synchronized topics.'));
        } else {
            return LogUtil::registerError($this->__("Error synchronizing topics."));
        }

        $succesful = ModUtil::apiFunc('Dizkus', 'Sync', 'posters');
        if ($showstatus && $succesful) {
            LogUtil::registerStatus($this->__('Done! Synchronized posts counter.'));
        } else {
            return LogUtil::registerError($this->__("Error synchronizing posts counter."));
        }

        return System::redirect(ModUtil::url('Dizkus', 'admin', 'main'));
    }

    /**
     * ranks
     */
    public function ranks()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $submit = $this->request->getPost()->filter('submit', 2);
        $ranktype = $this->request->getGet()->filter('ranktype', 0, FILTER_SANITIZE_NUMBER_INT);

        if ($submit == 2) {
            list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => $ranktype));

            $this->view->assign('ranks', $ranks);
            $this->view->assign('ranktype', $ranktype);
            $this->view->assign('rankimages', $rankimages);

            if ($ranktype == 0) {
                return $this->view->fetch('admin/ranks.tpl');
            } else {
                return $this->view->fetch('admin/honoraryranks.tpl');
            }
        } else {
            $ranks = $this->request->getPost()->filter('ranks', '', FILTER_SANITIZE_STRING);
            //$ranks = FormUtil::getPassedValue('ranks');
            ModUtil::apiFunc($this->name, 'Rank', 'save', array('ranks' => $ranks));
        }

        return System::redirect(ModUtil::url($this->name, 'admin', 'ranks', array('ranktype' => $ranktype)));
    }

    /**
     * ranks
     */
    public function assignranks()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $submit = $this->request->query->get('submit');
        $letter = $this->request->query->get('letter');
        $lastletter = $this->request->query->get('lastletter');
        $page = (int)$this->request->query->get('page', 1);

        // check for a letter parameter
        if (!empty($lastletter)) {
            $letter = $lastletter;
        }

        // count users and forbid '*' if more than 1000 users are present
        if (empty($letter) || strlen($letter) != 1) {
            $letter = 'a';
        }
        $letter = strtolower($letter);

        if (is_null($submit)) {
            list($rankimages, $ranks) = ModUtil::apiFunc('Dizkus', 'Rank', 'getAll', array('ranktype' => 1));
            $perpage = 20;

            /* $inlinecss = '<style type="text/css">' ."\n";
              $rankpath = ModUtil::getVar('Dizkus', 'url_ranks_images') .'/';
              foreach ($ranks as $rank) {
              $inlinecss .= '#dizkus_admin option[value='.$rank['rank_id'].']:before { content:url("'.System::getBaseUrl() . $rankpath . $rank['rank_image'].'"); }' . "\n";
              }
              $inlinecss .= '</style>' . "\n";
              PageUtil::addVar('rawtext', $inlinecss); */

            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('u.uid, u.uname, a.value as rank_id')
                    ->from('Dizkus_Entity_Users', 'u')
                    ->leftJoin('u.attributes', 'a')
                    ->where("a.attribute_name = 'dizkus_user_rank'")
                    ->orderBy("u.uname");


            if (!empty($letter) and $letter != '*') {
                $qb->andWhere("u.uname LIKE :letter")
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
            $this->view->assign('usercount', $count);

            return $this->view->fetch('admin/assignranks.tpl');
        } else {
            // avoid some vars in the url of the pager
            unset($_GET['submit']);
            unset($_POST['submit']);
            unset($_REQUEST['submit']);
            $setrank = $this->request->request->get('setrank');
            ModUtil::apiFunc('Dizkus', 'Rank', 'assignranksave', array('setrank' => $setrank));
        }

        return System::redirect(ModUtil::url('Dizkus', 'admin', 'assignranks', array('letter' => $letter,
                            'page' => $page)));
    }

    /**
     * tree
     *
     * Show the forum tree.
     *
     * @return string
     */
    public function tree()
    {
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        $tree = $this->entityManager->getRepository('Dizkus_Entity_Forum')->getTree();

        return $this->view->assign('tree', $tree)
                ->fetch('admin/tree.tpl');
    }

    /**
     *
     */
    public function modifycategory()
    {
        $form = FormUtil::newForm('Dizkus', $this);

        return $form->execute('admin/modifycategory.tpl', new Dizkus_Form_Handler_Admin_ModifyCategory());
    }

    /**
     *
     */
    public function deletecategory()
    {
        $form = FormUtil::newForm('Dizkus', $this);

        return $form->execute('admin/deletecategory.tpl', new Dizkus_Form_Handler_Admin_DeleteCategory());
    }

    /**
     *
     */
    public function modifyforum()
    {
        $form = FormUtil::newForm('Dizkus', $this);

        return $form->execute('admin/modifyforum.tpl', new Dizkus_Form_Handler_Admin_ModifyForum());
    }

    /**
     *
     */
    public function deleteforum()
    {
        $form = FormUtil::newForm('Dizkus', $this);

        return $form->execute('admin/deleteforum.tpl', new Dizkus_Form_Handler_Admin_DeleteForum());
    }

    /**
     * managesubscriptions
     *
     */
    public function manageSubscriptions()
    {
        $form = FormUtil::newForm('Dizkus', $this);

        return $form->execute('admin/managesubscriptions.tpl', new Dizkus_Form_Handler_Admin_ManageSubscriptions());
    }

}