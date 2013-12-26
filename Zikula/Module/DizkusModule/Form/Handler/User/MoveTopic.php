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

use Zikula\Module\DizkusModule\Manager\TopicManager;
use ModUtil;
use System;
use Zikula_Form_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Module\DizkusModule\Entity\TopicEntity;
use Zikula\Core\ModUrl;
use Zlanguage;

/**
 * This class provides a handler to move a post.
 */
class MoveTopic extends \Zikula_Form_AbstractHandler
{

    /**
     * topic_id
     *
     * @var integer
     */
    private $topic_id;

    /**
     *
     * @var TopicEntity
     */
    private $topic;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws AccessDeniedException If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new AccessDeniedException();
        }

        $this->topic_id = (int) $this->request->query->get('topic', null);
        $managedTopic = new TopicManager($this->topic_id);
        $this->topic = $managedTopic->get();
        $view->assign('topic', $this->topic_id);
        $view->assign('forums', ModUtil::apiFunc($this->name, 'Forum', 'getAllChildren'));
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
        $url = new ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $this->topic_id));
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        if ($args['commandName'] == 'move') {
            // require perms for both subject topic and destination forum
            if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $this->topic->getForum())
                    || !ModUtil::apiFunc($this->name, 'Permission', 'canModerate', array('forum_id' => $data['forum_id']))) {
                throw new AccessDeniedException();
            }

            if ($data['forum_id'] == $this->topic->getForum()->getForum_id()) {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! The original forum cannot be the same as the target forum.'));
                return false;
            }
            $data['topicObj'] = $this->topic;

            ModUtil::apiFunc($this->name, 'topic', 'move', $data);

            return $view->redirect($url);
        }

        if ($args['commandName'] == 'join') {
            $managedDestinationTopic = new TopicManager($data['to_topic_id']);
            // require perms for both subject topic and destination topic
            if (!ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $this->topic->getForum())
                    || !ModUtil::apiFunc($this->name, 'Permission', 'canModerate', $managedDestinationTopic->get()->getForum())) {
                throw new AccessDeniedException();
            }

            if (!empty($data['to_topic_id']) && ($data['to_topic_id'] == $this->topic_id)) {
                // user wants to copy topic to itself
                return $view->redirect($url);
            }

            $data['from_topic_id'] = $this->topic_id;
            $data['topicObj'] = $this->topic;

            ModUtil::apiFunc($this->name, 'topic', 'join', $data);

            $url = new ModUrl($this->name, 'user', 'viewtopic', ZLanguage::getLanguageCode(), array('topic' => $data['to_topic_id']));
            return $view->redirect($url);
        }

        return true;
    }

}
