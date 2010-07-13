<?php
/**
 * general module informations
 * @version $Id$
 * @author Andreas Krapohl
 * @copyright 2003 by Andreas Krapohl, 2004 by Frank Schummertz
 * @package Dizkus
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://code.zikula.org/dizkus
 */

class Dizkus_Version extends Zikula_Version
{
    public function getMetaData() 
    {
        $meta = array();
        $meta['displayname']    = $this->__('Dizkus forums');
        $meta['oldnames']       = array('pnForum');
        $meta['description']    = 'An integrated forum solution for Zikula which is simple to administer and use but that has an excellent feature set.';
        $meta['url']            = $this->__('forums');
        $meta['version']        = '3.2.0';
        $meta['contact']        = 'Andreas Krapohl, Frank Schummertz, Carsten Volmer http://code.zikula.org/dizkus';
        $meta['securityschema'] = array('Dizkus::' => 'CategoryID:ForumID:',
                                      'Dizkus::CreateForum' => 'CategoryID::');

        // module depedencies
        $meta['dependencies']   = array(
                                      array('modname'    => 'bbcode', 
                                            'minversion' => '2.0', 
                                            'maxversion' => '', 
                                            'status'     => ModUtil::DEPENDENCY_RECOMMENDED),
                                      array('modname'    => 'bbsmile', 
                                            'minversion' => '2.1', 
                                            'maxversion' => '', 
                                            'status'     => ModUtil::DEPENDENCY_RECOMMENDED)
                                     );
        return $meta;
    }
}
