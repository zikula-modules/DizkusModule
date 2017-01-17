<?php

/**
 * Dizkus.
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @link https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Hook\ProcessHook;
use Zikula\Core\RouteUrl; // used in annotations - do not remove
use Zikula\DizkusModule\Manager\ForumManager; // used in annotations - do not remove

class PostController extends AbstractController
{
    /**
     * @Route("/post/edit")
     *
     * Edit post
     *
     * User interface to edit a new post
     *
     * @return string
     */
//    public function editpostAction()
//    {
//        $form = FormUtil::newForm($this->name, $this);
//
//        return new Response($form->execute('User/post/edit.tpl', new EditPost()));
//    }

    /**
     * @Route("/post/edit", options={"expose"=true})
     * @Method("POST")
     *
     * Edit a post.
     *
     * @param Request $request
     *                         post The post id to edit.
     *
     * RETURN: The edit post form.
     *
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException
     *
     * @return AjaxResponse
     */
    public function editpostAction(Request $request)
    {
        $this->errorIfForumDisabled();
        $this->checkAjaxToken();
        $post_id = $request->request->get('post', null);
        $currentUserId = UserUtil::getVar('uid');
        if (!empty($post_id)) {
            $managedPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post_id); // new PostManager($post_id);
            $forum = $managedPost->get()->getTopic()->getForum();
            $managedForum = new ForumManager(null, $forum);
            if ($managedPost->get()->getPoster()->getUser_id() == $currentUserId || $managedForum->isModerator()) {
                $this->view->setCaching(false);
                $this->view->assign('post', $managedPost->get());
                // simplify our live
                $this->view->assign('postingtextareaid', 'postingtext_'.$managedPost->getId().'_edit');
                $this->view->assign('isFirstPost', $managedPost->get()->isFirst());

                return new AjaxResponse($this->view->fetch('Ajax/editpost.tpl'));
            } else {
                throw new AccessDeniedException();
            }
        }
        throw new \InvalidArgumentException($this->__f('Error! No post ID in %s.', '\'Dizkus/Ajax/editpost()\''));
    }

    /**
     * @Route("/post/update", options={"expose"=true})
     * @Method("POST")
     *
     * Update a post.
     *
     * @param Request $request
     *                         postId           The post id to update.
     *                         title
     *                         message          The new post message.
     *                         delete_post      Delete this post?
     *                         attach_signature Attach signature?
     *
     * RETURN: array($action The executed action.
     *               $newText The new post text (can be empty).
     *               $redirect The page to redirect to (can be empty).
     *              )
     *
     * @throws \InvalidArgumentException
     * @throws AccessDeniedException     If the user tries to delete the only post of a topic.
     *
     * @return AjaxResponse
     */
    public function updatepostAction(Request $request)
    {
        $this->errorIfForumDisabled();
        $this->checkAjaxToken();
        $post_id = $request->request->get('postId', '');
        $title = $request->request->get('title', '');
        $message = $request->request->get('message', '');
        $delete = $request->request->get('delete_post', 0) == '1' ? true : false;
        $attach_signature = $request->request->get('attach_signature', 0) == '1' ? true : false;
        if (!empty($post_id)) {
            $message = ModUtil::apiFunc($this->name, 'user', 'dzkstriptags', $message);
            $this->checkMessageLength($message);
            $managedOriginalPost = $this->get('zikula_dizkus_module.post_manager')->getManager($post_id); //new PostManager($post_id);
            if ($delete) {
                if ($managedOriginalPost->get()->isFirst()) {
                    throw new AccessDeniedException($this->__('Error! Cannot delete the first post in a topic. Delete the topic instead.'));
                } else {
                    $response = ['action' => 'deleted'];
                }
                $managedOriginalPost->delete();
                $this->dispatchHooks('dizkus.ui_hooks.post.process_delete', new ProcessHook($managedOriginalPost->getId()));
            } else {
                $data = [
                    'title'           => $title,
                    'post_text'       => $message,
                    'attachSignature' => $attach_signature, ];
                $managedOriginalPost->update($data);
                $url = RouteUrl::createFromRoute('zikuladizkusmodule_user_viewtopic', ['topic' => $managedOriginalPost->getTopicId()], 'pid'.$managedOriginalPost->getId());
                $this->dispatchHooks('dizkus.ui_hooks.post.process_edit', new ProcessHook($managedOriginalPost->getId(), $url));
                if ($attach_signature && !$this->getVar('removesignature')) {
                    // include signature in response text
                    $sig = UserUtil::getVar('signature', $managedOriginalPost->get()->getPoster_id());
                    $message .= !empty($sig) ? "<div class='dzk_postSignature'>{$this->getVar('signature_start')}<br />{$sig}<br />{$this->getVar('signature_end')}</div>" : '';
                }
                // must dzkVarPrepHTMLDisplay the message content here because the template modifies cannot be run in ajax
                $newText = ModUtil::apiFunc($this->name, 'user', 'dzkVarPrepHTMLDisplay', $message);
                // process hooks
                $newText = $this->dispatchHooks('dizkus.filter_hooks.post.filter', new FilterHook($newText))->getData();
                // process internal quotes/hooks
                $newText = ModUtil::apiFunc($this->name, 'ParseTags', 'transform', ['message' => $newText]);
                $response = [
                    'action'  => 'updated',
                    'newText' => $newText, ];
            }

            return new AjaxResponse($response);
        }
        throw new \InvalidArgumentException($this->__f('Error! No post_id in %s.', '\'Dizkus/Ajax/updatepost()\''));
    }

    /**
     * @Route("/post/move")
     *
     * User interface to move a single post to another thread
     *
     * @return string
     */
    public function movepostAction()
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate')) {
            throw new AccessDeniedException();
        }

        // get the input
        $id = (int) $this->request->query->get('post');

        $this->post_id = $id;

        $managedPost = new PostManager($id);

        $this->old_topic_id = $managedPost->getTopicId();

        if ($managedPost->get()->isFirst()) {
            $this->request->getSession()->getFlashBag()->add('error', 'You can not move the first post of a topic!');
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $managedPost->getTopicId()], RouterInterface::ABSOLUTE_URL);

            return $view->redirect($url);
        }

        return true;
        if ($args['commandName'] == 'cancel') {
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $this->old_topic_id, 'start' => 1], RouterInterface::ABSOLUTE_URL).'#pid'.$this->post_id;

            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();
        $data['old_topic_id'] = $this->old_topic_id;
        $data['post_id'] = $this->post_id;

        $newTopicPostCount = ModUtil::apiFunc($this->name, 'post', 'move', $data);
        $start = $newTopicPostCount - $newTopicPostCount % ModUtil::getVar($this->name, 'posts_per_page', 15);

        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $data['to_topic_id'], 'start' => $start], RouterInterface::ABSOLUTE_URL).'#pid'.$this->post_id;

        return $view->redirect($url);

//        $form = FormUtil::newForm($this->name, $this);
//
//        return new Response($form->execute('User/post/move.tpl', new MovePost()));
    }

    /**
     * @Route("/post/report")
     *
     * Report
     *
     * User interface to notify a moderator about a (bad) posting.
     *
     * @return string
     */
    public function reportAction()
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new AccessDeniedException();
        }
        // get the input
        $id = (int) $this->request->query->get('post');

        if (!isset($id)) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Missing post id.'));
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_index', [], RouterInterface::ABSOLUTE_URL);

            return $view->redirect($url);
        }

        $this->_post = new PostManager($id);
        $view->assign('post', $this->_post->get());
        $view->assign('notify', true);
        list(, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', ['ranktype' => RankEntity::TYPE_POSTCOUNT]);
        $this->view->assign('ranks', $ranks);

        return true;

        if ($args['commandName'] == 'cancel') {
            $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $this->_post->getTopicId(), 'start' => 1], RouterInterface::ABSOLUTE_URL).'#pid'.$this->_post->getId();

            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        // some spam checks:
        // - remove html and compare with original comment
        // - use censor and compare with original comment
        // if only one of this comparisons fails -> trash it, it is spam.
        if (!UserUtil::isLoggedIn()) {
            if (strip_tags($data['comment']) != $data['comment']) {
                // possibly spam, stop now
                // get the users ip address and store it in zTemp/Dizkus_spammers.txt
                $this->dzk_blacklist();
                // set 403 header and stop
                header('HTTP/1.0 403 Forbidden');
                System::shutDown();
            }
        }

        ModUtil::apiFunc($this->name, 'notify', 'notify_moderator', ['post' => $this->_post->get(),
            'comment'                                                       => $data['comment'], ]);

        $start = ModUtil::apiFunc($this->name, 'user', 'getTopicPage', ['replyCount' => $this->_post->get()->getTopic()->getReplyCount()]);

        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', ['topic' => $this->_post->getTopicId(), 'start' => $start], RouterInterface::ABSOLUTE_URL);

        return $view->redirect($url);

//        $form = FormUtil::newForm($this->name, $this);
//
//        return new Response($form->execute('User/notifymod.tpl', new Report()));
    }

    /**
     * Checks if a message is shorter than 65535 - 8 characters.
     *
     * @param string $message The message to check.
     *
     * @throws \LengthException
     *
     * @return void
     */
    private function checkMessageLength($message)
    {
        if (!ModUtil::apiFunc($this->name, 'post', 'checkMessageLength', ['message' => $message])) {
            throw new \LengthException($this->__('Error! The message is too long. The maximum length is 65,535 characters.'));
        }
    }
}

//    /**
//
//
//    Report
//
//
//     * dzk_blacklist()
//     * blacklist the users ip address if considered a spammer
//     */
//    private function dzk_blacklist()
//    {
//        $ztemp = System::getVar('temp');
//        $blacklistfile = $ztemp . '/Dizkus_spammer.txt';
//        $request = ServiceUtil::get('request');
//
//        $fh = fopen($blacklistfile, 'a');
//        if ($fh) {
//            $ip = $this->dzk_getip();
//            $line = implode(',', array(strftime('%Y-%m-%d %H:%M:%S'),
//                $ip,
//                $request->server->get('REQUEST_METHOD'),
//                $request->server->get('REQUEST_URI'),
//                $request->server->get('SERVER_PROTOCOL'),
//                $request->server->get('HTTP_REFERRER'),
//                $request->server->get('HTTP_USER_AGENT')));
//            fwrite($fh, DataUtil::formatForStore($line) . "\n");
//            fclose($fh);
//        }
//
//        return;
//    }
//
//    /**
//     * check for valid ip address
//     * original code taken form spidertrap
//     * @author       Thomas Zeithaml <info@spider-trap.de>
//     * @copyright    (c) 2005-2006 Spider-Trap Team
//     */
//    private function dzk_validip($ip)
//    {
//        if (!empty($ip) && ip2long($ip) != -1) {
//            $reserved_ips = array(
//                array('0.0.0.0', '2.255.255.255'),
//                array('10.0.0.0', '10.255.255.255'),
//                array('127.0.0.0', '127.255.255.255'),
//                array('169.254.0.0', '169.254.255.255'),
//                array('172.16.0.0', '172.31.255.255'),
//                array('192.0.2.0', '192.0.2.255'),
//                array('192.168.0.0', '192.168.255.255'),
//                array('255.255.255.0', '255.255.255.255')
//            );
//
//            foreach ($reserved_ips as $r) {
//                $min = ip2long($r[0]);
//                $max = ip2long($r[1]);
//                if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max))
//                    return false;
//            }
//
//            return true;
//        } else {
//            return false;
//        }
//    }
//
//    /**
//     * get the users ip address
//     * changes: replaced references to $_SERVER with System::serverGetVar()
//     * original code taken form spidertrap
//     * @author       Thomas Zeithaml <info@spider-trap.de>
//     * @copyright    (c) 2005-2006 Spider-Trap Team
//     */
//    private function dzk_getip()
//    {
//        $request = ServiceUtil::get('request');
//        if ($this->dzk_validip($request->server->get("HTTP_CLIENT_IP"))) {
//            return $request->server->get("HTTP_CLIENT_IP");
//        }
//
//        foreach (explode(',', $request->server->get("HTTP_X_FORWARDED_FOR")) as $ip) {
//            if ($this->dzk_validip(trim($ip))) {
//                return $ip;
//            }
//        }
//
//        if ($this->dzk_validip($request->server->get("HTTP_X_FORWARDED"))) {
//            return $request->server->get("HTTP_X_FORWARDED");
//        } elseif ($this->dzk_validip($request->server->get("HTTP_FORWARDED_FOR"))) {
//            return $request->server->get("HTTP_FORWARDED_FOR");
//        } elseif ($this->dzk_validip($request->server->get("HTTP_FORWARDED"))) {
//            return $request->server->get("HTTP_FORWARDED");
//        } elseif ($this->dzk_validip($request->server->get("HTTP_X_FORWARDED"))) {
//            return $request->server->get("HTTP_X_FORWARDED");
//        } else {
//            return $request->server->get("REMOTE_ADDR");
//        }
//    }
