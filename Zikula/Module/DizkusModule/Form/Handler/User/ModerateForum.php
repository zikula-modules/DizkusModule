<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Form\Handler\User;

use Zikula\Module\DizkusModule\Manager\ForumManager;
/**
 * This class provides a handler to move a post.
 */
class ModerateForum extends \Zikula_Form_AbstractHandler
{

    /**
     * forum
     *
     * @var ForumManager
     */
    private $_managedForum;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        $forum_id = (int)$this->request->query->get('forum', null);
        if (!isset($forum_id)) {
            return LogUtil::registerArgsError();
        }
        // Get the Forum and Permission-Check
        $this->_managedForum = new ForumManager($forum_id);
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $this->_managedForum->get())) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }

        $lastVisitUnix = ModUtil::apiFunc($this->name, 'user', 'setcookies');

        $this->view->assign('forum_id', $forum_id);
        $this->view->assign('mode', '');
        $this->view->assign('topic_ids', array());
        $this->view->assign('last_visit_unix', $lastVisitUnix);
        $this->view->assign('forum', $this->_managedForum->get());
        $topics = $this->_managedForum->getTopics();
        $topicSelect = array(
            array(
                'value' => '',
                'text' => "<< " . $this->__("Choose target topic") . " >>"),
        );
        foreach ($topics as $topic) {
            $topicSelect[] = array(
                'value' => $topic->getTopic_id(),
                'text' => $topic->getTitle());
        }
        $this->view->assign('topicSelect', $topicSelect);
        $actions = array(
            array(
                'value' => '',
                'text' => "<< " . $this->__("Choose action") . " >>"),
            array(
                'value' => 'sticky',
                'text' => $this->__("Give selected topics 'sticky' status")),
            array(
                'value' => 'unsticky',
                'text' => $this->__("Remove 'sticky' status from selected topics")),
            array(
                'value' => 'lock',
                'text' => $this->__("Lock selected topics")),
            array(
                'value' => 'unlock',
                'text' => $this->__("Unlock selected topics")),
            array(
                'value' => 'delete',
                'text' => $this->__("Delete selected topics")),
            array(
                'value' => 'move',
                'text' => $this->__("Move selected topics")),
            array(
                'value' => 'join',
                'text' => $this->__("Join topics")),
        );
        $this->view->assign('actions', $actions);
        // For Movetopic
        $forums = ModUtil::apiFunc($this->name, 'Forum', 'getAllChildren');
        array_unshift($forums, array(
            'value' => '',
            'text' => "<< " . $this->__("Select target forum") . " >>"));
        $this->view->assign('forums', $forums);

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
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url($this->name, 'user', 'viewforum', array('forum' => $this->_managedForum->getId()));

            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        $mode = isset($data['mode']) ? $data['mode'] : '';
        $shadow = $data['createshadowtopic'];
        $moveto = isset($data['moveto']) ? $data['moveto'] : null;
        $jointo = isset($data['jointo']) ? $data['jointo'] : null;
        $jointo_select = isset($data['jointotopic']) ? $data['jointotopic'] : null;
        // get this value by traditional method because checkboxen have values
        $topic_ids = $this->request->request->get('topic_id', array());

        if (count($topic_ids) <> 0) {
            switch ($mode) {
                case 'del':
                case 'delete':
                    foreach ($topic_ids as $topic_id) {
                        $forum_id = ModUtil::apiFunc($this->name, 'topic', 'delete', array('topic' => $topic_id));
                    }
                    break;

                case 'move':
                    if (empty($moveto)) {
                        return LogUtil::registerError($this->__('Error! You did not select a target forum for the move.'), null, ModUtil::url($this->name, 'user', 'moderateforum', array('forum' => $this->_managedForum->getId())));
                    }
                    foreach ($topic_ids as $topic_id) {
                        ModUtil::apiFunc($this->name, 'topic', 'move', array('topic_id' => $topic_id,
                            'forum_id' => $moveto,
                            'createshadowtopic' => $shadow));
                    }
                    break;

                case 'lock':
                case 'unlock':
                case 'sticky':
                case 'unsticky':
                    foreach ($topic_ids as $topic_id) {
                        ModUtil::apiFunc($this->name, 'topic', 'changeStatus', array(
                            'topic_id' => $topic_id,
                            'action' => $mode));
                    }
                    break;
                    $this->entityManager->flush();

                case 'join':
                    if (empty($jointo) && empty($jointo_select)) {
                        return LogUtil::registerError($this->__('Error! You did not select a target topic to join.'), null, ModUtil::url($this->name, 'user', 'moderateforum', array('forum' => $this->_managedForum->getId())));
                    }
                    // text input overrides select box
                    if (empty($jointo) && !empty($jointo_select)) {
                        $jointo = $jointo_select;
                    }
                    if (in_array($jointo, $topic_ids)) {
                        // jointo, the target topic, is part of the topics to join
                        // we remove this to avoid a loop
                        $fliparray = array_flip($topic_ids);
                        unset($fliparray[$jointo]);
                        $topic_ids = array_flip($fliparray);
                    }
                    foreach ($topic_ids as $from_topic_id) {
                        ModUtil::apiFunc($this->name, 'topic', 'join', array('from_topic_id' => $from_topic_id,
                            'to_topic_id' => $jointo));
                    }
                    break;

                default:
            }
        }

        $url = ModUtil::url($this->name, 'user', 'moderateforum', array('forum' => $this->_managedForum->getId()));

        return $view->redirect($url);
    }

}
