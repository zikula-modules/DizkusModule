<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Api_Hook extends Zikula_AbstractApi {
    
    /**
     * createbyitem
     * createhook function (open new topic)
     *
     * objectid, extrainfo
     */
    public function createbyitem($args)
    {
        if (!isset($args['extrainfo']['module']) || empty($args['extrainfo']['module'])) {
            $modname = ModUtil::getName();
        } else {
            $modname = $args['extrainfo']['module'];
        }
    
        if (isset($args['extrainfo']['itemid']) && !empty($args['extrainfo']['itemid'])) {
            $args['objectid'] = $args['extrainfo']['itemid'];
        }
    
        if (!isset($args['objectid']) || empty($args['objectid'])) {
            return LogUtil::registerArgsError();
        }
    
        // we have an objectid now, we combine this with the module id now for the
        // reference and check if it already exists
        $modid = ModUtil::getIDFromName($modname);
        $reference =  $modid . '-' . $args['objectid'];
    
        $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_reference',
                                 array('reference' => $reference));
    
        if ($topic_id == false) {
            $subject   = $this->__('Automatically-created topic');
            $message   = $this->__('Automatically-created topic for discussion of submitted entries');
            $pntopic   = 0;
            $authorid  = UserUtil::getVar('uid');
    
            $functionfilename = DataUtil::formatForStore('modules/Dizkus/pncommentsapi/' . $modname . '.php');
            if (file_exists($functionfilename) && is_readable($functionfilename)) {
                list($subject, $message, $pntopic, $authorid) = ModUtil::apiFunc('Dizkus', 'comments', $modname, array('objectid' => $args['objectid']));
            }
    
            $forum_id = DBUtil::selectField('dizkus_forums', 'forum_id', "forum_moduleref='$modid'");
            ModUtil::apiFunc('Dizkus', 'user', 'storenewtopic',
                         array('forum_id'  => $forum_id,
                               'subject'   => $subject,
                               'message'   => $message,
                               'reference' => $reference,
                               'post_as'   => $authorid));
        }
    
        return $args['extrainfo'];
    }
    
    /**
     * updatebyitem
     * updatehook function
     */
    public function updatebyitem($args)
    {
        if (!isset($args['extrainfo']['module']) || empty($args['extrainfo']['module'])) {
            $modname = ModUtil::getName();
        } else {
            $modname = $args['extrainfo']['module'];
        }
    
        if (isset($args['extrainfo']['itemid']) && !empty($args['extrainfo']['itemid'])) {
            $args['objectid'] = $args['extrainfo']['itemid'];
        }
    
        if (!isset($args['objectid']) || empty($args['objectid'])) {
            return LogUtil::registerArgsError();
        }
    
        // we have an objectid now, we combine this with the module id now for the
        // reference and check if it already exists
        $modid = ModUtil::getIDFromName($modname);
        $reference =  $modid . '-' . $args['objectid'];
    
        $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_reference',
                                 array('reference' => $reference));
    
        if ($topic_id <> false) {
            // found
            // get first post id
            $post_id = ModUtil::apiFunc('Dizkus', 'user', 'get_firstlast_post_in_topic',
                                    array('topic_id' => $topic_id,
                                          'first'    => true,
                                          'id_only'  => true));
    
            if ($post_id <> false) {
                $functionfilename = DataUtil::formatForStore('modules/Dizkus/pncommentsapi/' . $modname . '.php');
                if (file_exists($functionfilename) && is_readable($functionfilename)) {
                    list($subject, $message, $pntopic, $authorid) = ModUtil::apiFunc('Dizkus', 'comments', $modname, array('objectid' => $args['objectid']));
                }
                ModUtil::apiFunc('Dizkus', 'user', 'updatepost',
                             array('post_id'  => $post_id,
                                   'topic_id' => $topic_id,
                                   'subject'  => $subject,
                                   'message'  => $message));
            }
    
        }
    
        return $args['extrainfo'];
    }
    
    /**
     * deletebyitem
     * deletehook function (closes a topic or removes it depending on the setting)
     */
    public function deletebyitem($args)
    {
        if (!isset($args['extrainfo']['module']) || empty($args['extrainfo']['module'])) {
            $modname = ModUtil::getName();
        } else {
            $modname = $args['extrainfo']['module'];
        }
    
        if (isset($args['extrainfo']['itemid']) && !empty($args['extrainfo']['itemid'])) {
            $args['objectid'] = $args['extrainfo']['itemid'];
        }
    
        if (!isset($args['objectid']) || empty($args['objectid'])) {
            return LogUtil::registerArgsError();
        }
    
        // we have an objectid now, we combine this with the module id now for the
        // reference and check if it already exists
        $modid = ModUtil::getIDFromName($modname);
        $reference =  $modid . '-' . $args['objectid'];
    
        $topic_id = ModUtil::apiFunc('Dizkus', 'user', 'get_topicid_by_reference',
                                 array('reference' => $reference));
    
        if ($topic_id <> false) {
            if (ModUtil::getVar('Dizkus', 'deletehookaction') == 'remove') {
                ModUtil::apiFunc('Dizkus', 'user', 'deletetopic', array('topic_id' => $topic_id));
            } else {
                ModUtil::apiFunc('Dizkus', 'user', 'lockunlocktopic', array('topic_id'=> $topic_id, 'mode' => 'lock'));
            }
        }
    
        return $args['extrainfo'];
    }

}
