<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController; // used in annotations - do not remove
use Zikula\DizkusModule\Manager\ForumManager; // used in annotations - do not remove

class FeedController extends AbstractController
{
    /**
     * @Route("/feed")
     * @Method("GET")
     *
     * generate and display an RSS feed of recent topics
     *
     * @param Request $request
     *
     * @throws AccessDeniedException on failed perm check
     *
     * @return Response|RedirectResponse
     */
    public function feedAction(Request $request)
    {
        // @todo - refactor feeds
        $request->getSession()->getFlashBag()->add('warning', $this->__('Sorry! Feeds are not available at the moment.'));

        return $this->redirectToRoute('zikuladizkusmodule_forum_index');

//        $forum_id = $request->query->get('forum_id', null);
//        $count = (int) $request->query->get('count', 10);
//        $feed = $request->query->get('feed', 'rss20');
//        $user = $request->query->get('user', null);
//        // get the module info
//        $dzkinfo = ModUtil::getInfo(ModUtil::getIdFromName($this->name));
//        $dzkname = $dzkinfo['displayname'];
//        $mainUrl = $this->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL);
//
//        if (isset($forum_id) && !is_numeric($forum_id)) {
//            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! An invalid forum ID %s was encountered.', $forum_id));
//
//            return new RedirectResponse($mainUrl);
//        }
//        /**
//         * check if template for feed exists.
//         */
//        $templatefile = 'Feed/'.DataUtil::formatForOS($feed).'.tpl';
//        if (!$this->view->template_exists($templatefile)) {
//            // silently stop working
//            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! Could not find a template for an %s-type feed.', $feed));
//
//            return new RedirectResponse($mainUrl);
//        }
//        /*
//         * get user id
//         */
//        if (!empty($user)) {
//            $uid = UserUtil::getIDFromName($user);
//        }
//        /**
//         * set some defaults.
//         */
//        // form the url
//        $link = $mainUrl;
//        $forumname = DataUtil::formatForDisplay($dzkname);
//        // default where clause => no where clause
//        $where = [];
//        /*
//         * check for forum_id
//         */
//        if (!empty($forum_id)) {
//            $managedForum = new ForumManager($forum_id);
//            if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead', ['forum_id' => $forum_id])) {
//                throw new AccessDeniedException();
//            }
//            $where = ['t.forum', (int) $forum_id];
//            $link = $this->get('router')->generate('zikuladizkusmodule_user_viewforum', ['forum' => $forum_id], RouterInterface::ABSOLUTE_URL);
//            $forumname = $managedForum->get()->getName();
//        } elseif (isset($uid) && false != $uid) {
//            $where = ['p.poster', $uid];
//        } else {
//            $allowedforums = ModUtil::apiFunc($this->name, 'forum', 'getForumIdsByPermission');
//            if (count($allowedforums) > 0) {
//                $where = ['t.forum', $allowedforums];
//            }
//        }
//        $this->view->assign('forum_name', $forumname);
//        $this->view->assign('forum_link', $link);
//        $this->view->assign('sitename', System::getVar('sitename'));
//        $this->view->assign('adminmail', System::getVar('adminmail'));
//        $this->view->assign('current_date', date(DATE_RSS));
//        $this->view->assign('current_language', ZLanguage::getLocale());
//        $qb = $this->entityManager->createQueryBuilder();
//        $qb->select('t, f, p, fu')
//            ->from('Zikula\DizkusModule\Entity\TopicEntity', 't')
//            ->join('t.forum', 'f')
//            ->join('t.last_post', 'p')
//            ->join('p.poster', 'fu');
//        if (!empty($where)) {
//            if (is_array($where[1])) {
//                $qb->where($qb->expr()->in($where[0], ':param'))->setParameter('param', $where[1]);
//            } else {
//                $qb->where($qb->expr()->eq($where[0], ':param'))->setParameter('param', $where[1]);
//            }
//        }
//        $qb->orderBy('t.topic_time', 'DESC')->setMaxResults($count);
//        $topics = $qb->getQuery()->getResult();
//        $posts_per_page = $this->getVar('posts_per_page');
//        $posts = [];
//        $i = 0;
//        foreach ($topics as $topic) {
//            /* @var $topic \Zikula\Module\DizkusModule\Entity\TopicEntity */
//            $posts[$i]['title'] = $topic->getTitle();
//            $posts[$i]['parenttitle'] = $topic->getForum()->getParent()->getName();
//            $posts[$i]['forum_name'] = $topic->getForum()->getName();
//            $posts[$i]['time'] = $topic->getTopic_time();
//            $posts[$i]['unixtime'] = $topic->getTopic_time()->format('U');
//            $start = (int) ((ceil(($topic->getReplyCount() + 1) / $posts_per_page) - 1) * $posts_per_page) + 1;
//            $posts[$i]['post_url'] = $this->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $topic->getTopic_id(), 'start' => $start], RouterInterface::ABSOLUTE_URL);
//            $posts[$i]['last_post_url'] = $this->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $topic->getTopic_id(), 'start' => $start], RouterInterface::ABSOLUTE_URL)."#pid{$topic->getLast_post()->getPost_id()}";
//            $posts[$i]['rsstime'] = $topic->getTopic_time()->format(DATE_RSS);
//            $i++;
//        }
//        $this->view->assign('posts', $posts);
//        $this->view->assign('dizkusinfo', $dzkinfo);
//
//        return new Response($this->view->fetch($templatefile), Response::HTTP_OK, ['Content-Type' => 'text/xml']);
    }
}
