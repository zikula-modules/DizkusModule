<?php

/**
 * general module informations
 * @author Andreas Krapohl
 * @copyright 2003 by Andreas Krapohl, 2004 by Frank Schummertz
 * @package Dizkus
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link https://github.com/zikula-modules/Dizkus
 */

/**
 * Provides metadata for this module to the Extensions module.
 */
class Dizkus_Version extends Zikula_AbstractVersion
{

    /**
     * Assemble and return module metadata.
     *
     * @return array Module metadata.
     */
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname'] = $this->__('Dizkus forums');
        $meta['oldnames'] = array('pnForum');
        $meta['description'] = $this->__('An integrated discussion forum for Zikula.');
        $meta['url'] = $this->__('forums');
        $meta['version'] = '3.2.0'; // will be 4.0.0 on release
        $meta['core_min'] = '1.3.6'; // Fixed to 1.3.x range
        $meta['core_max'] = '1.3.99'; // Fixed to 1.3.x range
        $meta['securityschema'] = array('Dizkus::' => 'CategoryID:ForumID:',
            'Dizkus::CreateForum' => 'CategoryID::');
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true),
            HookUtil::PROVIDER_CAPABLE => array('enabled' => true));

        // module depedencies
        $meta['dependencies'] = array(
            array('modname' => 'LuMicuLa',
                'minversion' => '0.1.0',
                'maxversion' => '',
                'status' => ModUtil::DEPENDENCY_RECOMMENDED),
            array('modname' => 'BBCode',
                'minversion' => '3.0.0',
                'maxversion' => '',
                'status' => ModUtil::DEPENDENCY_RECOMMENDED),
            array('modname' => 'BBSmile',
                'minversion' => '3.0.0',
                'maxversion' => '',
                'status' => ModUtil::DEPENDENCY_RECOMMENDED)
        );
        return $meta;
    }

    /**
     * Define the hook bundles supported by this module.
     *
     * @return void
     */
    protected function setupHookBundles()
    {
        // there will be 3 areas for subscriber hooks and one provider hook
        // three areas: Post, Topic, Forum
        // the Post area will have an additional filter area (Post filter)
        // the one provider hook will be used to display a topic

        $bundle = new Zikula_HookManager_ProviderBundle($this->name, 'provider.dizkus.ui_hooks.comments', 'ui_hooks', $this->__('Dizkus Comment Hooks'));
        $bundle->addServiceHandler('display_view', 'Dizkus_HookHandlers', 'uiView', 'dizkus.hooks.comments');
        $bundle->addServiceHandler('process_edit', 'Dizkus_HookHandlers', 'processEdit', 'dizkus.hooks.comments');
        $bundle->addServiceHandler('process_delete', 'Dizkus_HookHandlers', 'processDelete', 'dizkus.hooks.comments');
        $this->registerHookProviderBundle($bundle);

        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.dizkus.ui_hooks.editor', 'ui_hooks', $this->__('Dizkus editor'));
        $bundle->addEvent('display_view', 'dizkus.ui_hooks.editor.display_view');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.dizkus.filter_hooks.message', 'filter_hooks', $this->__('Dizkus filters'));
        $bundle->addEvent('filter', 'dizkus.filter_hooks.message.filter');
        $this->registerHookSubscriberBundle($bundle);
    }

}
