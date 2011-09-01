<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Controller_Hook extends Zikula_AbstractController {
    
    /**
     * showdiscussionlink
     * displayhook function
     *
     * @params $objectid string the id of the item to be discussed in the forum
     */
    public function showdiscussionlink($args)
    {
        if (!isset($args['objectid']) || empty($args['objectid']) ) {
            return LogUtil::registerError($this->__('Error! The action you wanted to perform was not successful for some reason, maybe because of a problem with what you input. Please check and try again.'), null, ModUtil::url('Dizkus', 'user', 'main'));
        }
    
        $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_reference',
                                 array('reference' => ModUtil::getIDFromName(ModUtil::getName()) . '-' . $args['objectid']));
    
        if ($topic_id <> false) {
            list($last_visit, $last_visit_unix) = ModUtil::apiFunc('Dizkus', 'user', 'setcookies');
    
            $topic = ModUtil::apiFunc('Dizkus', 'user', 'readtopic',
                                  array('topic_id'   => $topic_id,
                                        'count'      => false));
    
            $this->view->add_core_data();
            $this->view->setCaching(false);
            $this->view->assign('topic', $topic);
    
            return $this->view->fetch(hook/display.tpl');
        }
    
        return false;
    }

}
