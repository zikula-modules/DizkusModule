<?php

/*
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Block;

use Zikula\BlocksModule\AbstractBlockHandler;

/**
 * Class RecentPostsBlock.
 */
class RecentPostsBlock extends AbstractBlockHandler
{
    /**
     * Display the block.
     */
    public function display(array $properties)
    {
        if (!$this->hasPermission('ZikulaDizkusModule::RecentPostsBlock', "$properties[bid]::", ACCESS_READ)) {
            return '';
        }

        // check if forum is turned off
        $forum_enabled = $this->getVar('forum_enabled');
        if (!$forum_enabled) {
            return $this->renderView('@ZikulaDizkusModule/Block/dizkus.disabled.html.twig', [
                'forum_disabled_info' => $this->getVar('forum_disabled_info'),
            ]);
        }

        // return immediately if no posts exist @todo
//        if (ModUtil::apiFunc($this->name, 'user', 'countstats', array('type' => 'all')) == 0) {
//            return false;
//        }

        // check if template is set, if not, use the default block template
        $template = empty($properties['template']) ? 'recentposts' : $properties['template'];

        if (empty($properties['params'])) {
            $properties['params'] = 'maxposts=5';
        }

        if (empty($properties['showfooter'])) {
            $properties['showfooter'] = true;
        }

        // convert param string to php array
        $paramarray = [];
        $params = explode(',', $properties['params']);
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $param) {
                $paramdata = explode('=', $param);
                $paramarray[trim($paramdata[0])] = trim($paramdata[1]);
            }
        }

        $posts = $this->get('doctrine')->getEntityManager()->getRepository('Zikula\DizkusModule\Entity\PostEntity')->getLastPosts();

//        $lastPosts = [];
//        if (!empty($topics)) {
//            $posts_per_page = $this->variableApi->get($this->name, 'posts_per_page');
//            /* @var $topic \Zikula\Module\DizkusModule\Entity\TopicEntity */
//            foreach ($topics as $topic) {
//                $lastPost = [];
//                $lastPost['title'] = DataUtil::formatforDisplay($topic->getTitle());
//                $lastPost['replyCount'] = DataUtil::formatforDisplay($topic->getReplyCount());
//                $lastPost['name'] = DataUtil::formatforDisplay($topic->getForum()->getName());
//                $lastPost['forum_id'] = DataUtil::formatforDisplay($topic->getForum()->getForum_id());
//                $lastPost['cat_title'] = DataUtil::formatforDisplay($topic->getForum()->getParent()->getName());
//
//                $start = 1;
//                if ($postSortOrder == 'ASC') {
//                    $start = ((ceil(($topic->getReplyCount() + 1) / $posts_per_page) - 1) * $posts_per_page) + 1;
//                }
//
//                if ($topic->getPoster()->getUserId() > 0) {
//                    $coreUser = $topic->getLast_post()->getPoster()->getUser();
//                    $user_name = $coreUser['uname'];
//                    if (empty($user_name)) {
//                        // user deleted from the db?
//                        $user_name = $this->variableApi->get('ZikulaUsersModule', 'anonymous'); // @todo replace with "deleted user"?
//                    }
//                } else {
//                    $user_name = $this->variableApi->get('ZikulaUsersModule', 'anonymous');
//                }
//                $lastPost['poster_name'] = DataUtil::formatForDisplay($user_name);
//                // @todo see ticket #184 maybe this should be using UserApi::dzkVarPrepHTMLDisplay ????
//                $lastPost['post_text'] = DataUtil::formatForDisplay(nl2br($topic->getLast_post()->getPost_text()));
//                $lastPost['posted_time'] = DateUtil::formatDatetime($topic->getLast_post()->getPost_time(), 'datetimebrief');
//                $lastPost['last_post_url'] = DataUtil::formatForDisplay($this->router->generate('zikuladizkusmodule_topic_viewtopic', [
//                    'topic' => $topic->getTopic_id(),
//                    'start' => $start, ]));
//                $lastPost['last_post_url_anchor'] = $lastPost['last_post_url'].'#pid'.$topic->getLast_post()->getPost_id();
//                $lastPost['word'] = $topic->getReplyCount() >= 1 ? $this->translator->__('Last') : $this->translator->__('New');
//
//                array_push($lastPosts, $lastPost);
//            }
//        }

        return $this->renderView("@ZikulaDizkusModule/Block/$template.html.twig", [
            'lastposts'  => $posts,
            'showfooter' => $properties['showfooter'],
        ]);
    }

    public function getFormClassName()
    {
        return 'Zikula\DizkusModule\Form\Type\RecentPostsBlockType';
    }

    public function getFormTemplate()
    {
        return '@ZikulaDizkusModule/Block/recentposts.modify.html.twig';
    }
}
