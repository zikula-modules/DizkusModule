<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Controller_Forum extends Zikula_AbstractController
{
    public function postInitialize()
    {
        $this->view->setCaching(false)->add_core_data();
    }
    /**
     * main
     * show all categories and forums a user may see
     *
     * @params 'viewcat' int only expand the category, all others shall be hidden / collapsed
     */
    public function main($args=array())
    {        
     return System::redirect(ModUtil::url('Dizkus', 'user', 'main', $args));   
    }
    
    /**
     * viewforum
     *
     * opens a forum and shows the last postings
     *
     * @params 'forum' int the forum id
     * @params 'start' int the posting to start with if on page 1+
     *
     * @return string
     */
    public function viewforum($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        

        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $forum_id = (int)$this->request->query->get('forum', (isset($args['forum'])) ? $args['forum'] : null);
        $start    = (int)$this->request->query->get('start', (isset($args['start'])) ? $args['start'] : 0);
    
        $subforums = $this->entityManager->getRepository('Dizkus_Entity_Subforums')
                                   ->findBy(array('is_subforum' => $forum_id)); 
        $this->view->assign('subforums', $subforums);
        
        
        
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
        $forum = ModUtil::apiFunc('Dizkus', 'forum', 'readforum',
                              array('forum_id'        => $forum_id,
                                    'start'           => $start,
                                    'last_visit'      => $last_visit,
                                    'last_visit_unix' => $last_visit_unix));
    
        
        $this->view->assign('forum', $forum);
        $this->view->assign('hot_threshold', ModUtil::getVar('Dizkus', 'hot_threshold'));
        $this->view->assign('last_visit', $last_visit);
        $this->view->assign('last_visit_unix', $last_visit_unix);
        $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
    
        return $this->view->fetch('forum/viewforum.tpl');
    }
    
      

    /**
     * moderateforum
     *
     * Simple moderation of multiple topics.
     *
     * @param array $args The Arguments array.
     *
     * @return string
     */
    public function moderateforum($args=array())
    {
        // Permission check
        $this->throwForbiddenUnless(
            SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)
        );
        
        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
    
        // get the input
        $forum_id  = (int)FormUtil::getPassedValue('forum', (isset($args['forum'])) ? $args['forum'] : null, 'GETPOST');
        $start     = (int)FormUtil::getPassedValue('start', (isset($args['start'])) ? $args['start'] : 0, 'GETPOST');
        $mode      = FormUtil::getPassedValue('mode', (isset($args['mode'])) ? $args['mode'] : '', 'GETPOST');
        $submit    = FormUtil::getPassedValue('submit', (isset($args['submit'])) ? $args['submit'] : '', 'GETPOST');
        $topic_ids = FormUtil::getPassedValue('topic_id', (isset($args['topic_id'])) ? $args['topic_id'] : array(), 'GETPOST');
        $shadow    = FormUtil::getPassedValue('createshadowtopic', (isset($args['createshadowtopic'])) ? $args['createshadowtopic'] : '', 'GETPOST');
        $moveto    = (int)FormUtil::getPassedValue('moveto', (isset($args['moveto'])) ? $args['moveto'] : null, 'GETPOST');
        $jointo    = (int)FormUtil::getPassedValue('jointo', (isset($args['jointo'])) ? $args['jointo'] : null, 'GETPOST');
    
        $shadow = (empty($shadow)) ? false : true;
    
        list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
        // Get the Forum for Display and Permission-Check
        $forum = ModUtil::apiFunc('Dizkus', 'user', 'readforum',
                              array('forum_id'        => $forum_id,
                                    'start'           => $start,
                                    'last_visit'      => $last_visit,
                                    'last_visit_unix' => $last_visit_unix));
    
        if (!allowedtomoderatecategoryandforum($forum['cat_id'], $forum['forum_id'])) {
            // user is not allowed to moderate this forum
            return LogUtil::registerPermissionError();
        }
    
    
        // Submit isn't set'
        if (empty($submit)) {
            $this->view->assign('forum_id', $forum_id);
            $this->view->assign('mode',$mode);
            $this->view->assign('topic_ids', $topic_ids);
            $this->view->assign('last_visit', $last_visit);
            $this->view->assign('last_visit_unix', $last_visit_unix);
            $this->view->assign('forum',$forum);
            $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));
            // For Movetopic
            $this->view->assign('forums', ModUtil::apiFunc('Dizkus', 'user', 'readuserforums'));
    
            return $this->view->fetch('forum/moderateforum.tpl');
    
        } else {
            // submit is set
            //if (!SecurityUtil::confirmAuthKey()) {
            //    return LogUtil::registerAuthidError();
            //}*/
            if (count($topic_ids) <> 0) {
                switch ($mode)
                {
                    case 'del':
                    case 'delete':
                        foreach ($topic_ids as $topic_id) {
                            $forum_id = ModUtil::apiFunc('Dizkus', 'topic', 'deletetopic', array('topic_id' => $topic_id));
                        }
                        break;
    
                    case 'move':
                        if (empty($moveto)) {
                            return LogUtil::registerError($this->__('Error! You did not select a target forum for the move.'), null, ModUtil::url('Dizkus', 'user', 'moderateforum', array('forum' => $forum_id)));
                        }
                        foreach ($topic_ids as $topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'movetopic',
                                         array('topic_id' => $topic_id,
                                               'forum_id' => $moveto,
                                               'shadow'   => $shadow ));
                        }
                        break;
    
                    case 'lock':
                    case 'unlock':
                        foreach ($topic_ids as $topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                        }
                        break;
    
                    case 'sticky':
                    case 'unsticky':
                        foreach ($topic_ids as $topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'stickyunstickytopic', array('topic_id'=> $topic_id, 'mode'=>$mode));
                        }
                        break;
    
                    case 'join':
                        if (empty($jointo)) {
                            return LogUtil::registerError($this->__('Error! You did not select a target topic to join.'), null, ModUtil::url('Dizkus', 'user', 'moderateforum', array('forum' => $forum_id)));
                        }
                        if (in_array($jointo, $topic_ids)) {
                            // jointo, the target topic, is part of the topics to join
                            // we remove this to avoid a loop
                            $fliparray = array_flip($topic_ids);
                            unset($fliparray[$jointo]);
                            $topic_ids = array_flip($fliparray);
                        }
                        foreach ($topic_ids as $from_topic_id) {
                            ModUtil::apiFunc('Dizkus', 'user', 'jointopics', array('from_topic_id' => $from_topic_id,
                                                                                'to_topic_id'   => $jointo));
                        }
                        break;
    
                    default:
                }
    
                // Refresh Forum Info
                $forum = ModUtil::apiFunc('Dizkus', 'user', 'readforum',
                                  array('forum_id'        => $forum_id,
                                        'start'           => $start,
                                        'last_visit'      => $last_visit,
                                        'last_visit_unix' => $last_visit_unix));
            }
        }
    
        return System::redirect(ModUtil::url('Dizkus', 'user', 'moderateforum', array('forum' => $forum_id)));
    }    

}