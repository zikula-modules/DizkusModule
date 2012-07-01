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
 * This class provides a handler to delete a topic.
 */
class Dizkus_Form_Handler_Topic_MoveTopic extends Zikula_Form_AbstractHandler
{

    /**
     * post data
     *
     * @var arrat
     */
    private $topic_id;

    private $topic;
    
    private $oldforum_id;
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
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_READ)) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }

        $disabled = dzk_available();
        if (!is_bool($disabled)) {
            return $disabled;
        }
      
        // get the input
        $this->topic_id = (int)$this->request->query->get('topic');
         if (!isset($this->topic_id)) {
            return LogUtil::registerError($this->__('Error! Missing topic id.'), null, ModUtil::url('Dizkus','user', 'main'));
        }
       
        $this->topic = ModUtil::apiFunc('Dizkus', 'topic', 'read0',
                              array('topic_id' => $this->topic_id));
      
        $this->oldforum_id = $this->topic['forum_id'];
      
   
        $tree = ModUtil::apiFunc('Dizkus', 'category', 'readcategorytree');
        $list = array();
        foreach ($tree as $categoryname => $category) {
            foreach ($category['forums'] as $forum) {
              $list[$forum['forum_id']]['text'] = $categoryname . '::' . $forum['forum_name'];
              $list[$forum['forum_id']]['value']= $forum['forum_id'];
            }
        }
                    
        unset($list[$this->oldforum_id]);
                   
        $this->view->assign('forums', $list);
        $this->view->assign('topic_id', $this->topic_id);
        $this->view->assign('favorites', ModUtil::apifunc('Dizkus', 'user', 'get_favorite_status'));

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
        // rewrite to topic if cancel was pressed
        if ($args['commandName'] == 'cancel') {
            return $view->redirect(ModUtil::url('Dizkus','forum','viewforum', array('forum' => $this->oldforum_id)));
        }

        // check for valid form and get data
        if (!$view->isValid()) {
            return false;
        }
        $data = $view->getValues();

        
        list($f_id, $c_id) = ModUtil::apiFunc($this->name, 'topic', 'get_forumid_and_categoryid_from_topicid',$this->topic_id);
        if ($forum_id == $f_id) {
            return LogUtil::registerError($this->__('Error! The original forum cannot be the same as the target forum.'));
         }
        if (!allowedtomoderatecategoryandforum($c_id, $f_id)) {
            return LogUtil::registerPermissionError();
         }
          
          ModUtil::apiFunc('Dizkus', 'topic', 'movetopic',
                                 array('topic_id' => (int)$this->topic_id,
                                       'forum_id' => $data['forum'],
                                       'shadow'   => $data['shadow'] ));

      
        //redirect back to forum so we can move other topics
        return $view->redirect(ModUtil::url('Dizkus','forum','viewforum', array('forum' => $this->oldforum_id)));
    }
}