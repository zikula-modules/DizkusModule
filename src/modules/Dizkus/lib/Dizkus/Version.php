<?php
/**
 * general module informations
 * @author Andreas Krapohl
 * @copyright 2003 by Andreas Krapohl, 2004 by Frank Schummertz
 * @package Dizkus
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link https://github.com/zikula-modules/Dizkus
 */

class Dizkus_Version extends Zikula_AbstractVersion
{
    public function getMetaData() 
    {
        $meta = array();
        $meta['displayname']    = $this->__('Dizkus forums');
        $meta['oldnames']       = array('pnForum');
        $meta['description']    = $this->__('An integrated forum solution for Zikula which is simple to administer and use but that has an excellent feature set.');
        $meta['url']            = $this->__('forums');
        $meta['version']        = '3.2.0';
        $meta['core_min'] = '1.3.0'; // Fixed to 1.3.x range
        $meta['core_max'] = '1.3.99'; // Fixed to 1.3.x range
        $meta['contact']        = 'http://support.zikula.de';
        $meta['securityschema'] = array('Dizkus::' => 'CategoryID:ForumID:',
                                      'Dizkus::CreateForum' => 'CategoryID::');
		$meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true),
		                              HookUtil::PROVIDER_CAPABLE   => array('enabled' => true));
        
        // module depedencies
        $meta['dependencies']   = array(
                                      array('modname'    => 'BBCode', 
                                            'minversion' => '3.0.0', 
                                            'maxversion' => '', 
                                            'status'     => ModUtil::DEPENDENCY_RECOMMENDED),
                                      array('modname'    => 'BBSmile', 
                                            'minversion' => '3.0.0', 
                                            'maxversion' => '', 
                                            'status'     => ModUtil::DEPENDENCY_RECOMMENDED)
                                     );
        return $meta;
    }
    
    protected function setupHookBundles()
    {
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.dizkus.ui_hooks.editor', 'ui_hooks', $this->__('Dizkus editor'));
        $bundle->addEvent('display_view', 'dizkus.ui_hooks.editor.display_view');
        $this->registerHookSubscriberBundle($bundle);
        
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.dizkus.filter_hooks.message', 'filter_hooks', $this->__('Dizkus filters'));
        $bundle->addEvent('filter', 'dizkus.filter_hooks.message.filter');
        $this->registerHookSubscriberBundle($bundle);
    }
}
