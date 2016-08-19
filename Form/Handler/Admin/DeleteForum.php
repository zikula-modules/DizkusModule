<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\Module\DizkusModule\Form\Handler\Admin;

use Zikula\Module\DizkusModule\Manager\ForumManager;
use ModUtil;
use System;
use SecurityUtil;
use Zikula_Form_View;
use Zikula\Module\DizkusModule\Entity\ForumEntity;
use Zikula\Core\Hook\ValidationHook;
use Zikula\Core\Hook\ValidationProviders;
use Zikula\Core\Hook\ProcessHook;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\RouterInterface;

/**
 * This class provides a handler to edit forums.
 */
class DeleteForum extends \Zikula_Form_AbstractHandler
{

    const MOVE_CHILDREN = '1';
    const DELETE_CHILDREN = '0';

    /**
     * forum
     *
     * @var ForumEntity
     */
    private $forum;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $id = $this->request->query->get('id', null);

        if ($id) {
            $forum = $this->entityManager->find('Zikula\Module\DizkusModule\Entity\ForumEntity', $id);
            if ($forum) {
                $this->view->assign($forum->toArray());
            } else {
                throw new \InvalidArgumentException($this->__f('Forum with id %s not found', $id));
            }
        } else {
            throw new \InvalidArgumentException();
        }

        $actions = array(
            array(
                'text' => $this->__('Move them to a new parent forum'),
                'value' => self::MOVE_CHILDREN),
            array(
                'text' => $this->__('Remove them'),
                'value' => self::DELETE_CHILDREN)
        );
        $this->view->assign('actions', $actions);
        $this->view->assign('action', self::DELETE_CHILDREN); // default

        $destinations = ModUtil::apiFunc($this->name, 'Forum', 'getParents', array('includeLocked' => false));
        $this->view->assign('destinations', $destinations);

        $this->view->caching = false;
        $this->forum = $forum;

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
        $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_admin_tree', array(), RouterInterface::ABSOLUTE_URL);
        if ($args['commandName'] == 'cancel') {
            return $view->redirect($url);
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        // check hooked modules for validation
        $hook = new ValidationHook(new ValidationProviders());
        $hookvalidators = $this->dispatchHooks('dizkus.ui_hooks.forum.validate_delete', $hook)->getValidators();
        if ($hookvalidators->hasErrors()) {
            $this->request->getSession()->getFlashBag()->add('error', $this->__('Error! Hooked content does not validate.'));
            return false;
        }

        $data = $view->getValues();

        if ($data['action'] == self::MOVE_CHILDREN) {
            $managedDestinationForum = new ForumManager($data['destination']);

            if (($managedDestinationForum->get()->getLvl() < 2) && (count($this->forum->getTopics()) > 0)) {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('You cannot move topics into this location, only forums. Delete the topics or choose a different destination.'));
                return false;
            }

            if ($managedDestinationForum->isChildOf($this->forum)) {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('You cannot select a descendant forum as a destination.'));
                return false;
            }
            if ($managedDestinationForum->getId() == $this->forum->getForum_id()) {
                $this->request->getSession()->getFlashBag()->add('error', $this->__('You cannot select the same forum as a destination.'));
                return false;
            }

            // get the child forums and move them
            $children = $this->forum->getChildren();
            foreach ($children as $child) {
                $child->setParent($managedDestinationForum->get());
            }
            $this->forum->removeChildren();

            // get child topics and move them
            $topics = $this->forum->getTopics();
            foreach ($topics as $topic) {
                $topic->setForum($managedDestinationForum->get());
                $this->forum->getTopics()->removeElement($topic);
            }
            $this->entityManager->flush();
        }
        // remove the forum
        $this->entityManager->remove($this->forum);
        $this->entityManager->flush();

        $this->dispatchHooks('dizkus.ui_hooks.forum.process_delete', new ProcessHook($this->forum->getForum_id()));

        if (isset($managedDestinationForum)) {
            // sync last post in destination
            ModUtil::apiFunc($this->name, 'sync', 'forumLastPost', array('forum' => $managedDestinationForum->get()));
        }

        // repair the tree
        $this->entityManager->getRepository('Zikula\Module\DizkusModule\Entity\ForumEntity')->recover();
        $this->entityManager->clear();

        // resync all forums, topics & posters
        ModUtil::apiFunc($this->name, 'sync', 'all');

        return $view->redirect($url);
    }

}
