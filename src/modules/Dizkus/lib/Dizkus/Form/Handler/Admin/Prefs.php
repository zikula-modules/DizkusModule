<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class Dizkus_Form_Handler_Admin_Prefs extends Form_Handler
{
    function initialize(&$render)
    {
        $render->caching = false;
        $render->add_core_data();

        $render->assign('post_sort_order_options', array(array('text' => $this->__('Ascending'),  'value' => 'ASC'),
                                                         array('text' => $this->__('Descending'), 'value' => 'DESC')));

        $render->assign('deletehook_options', array(array('text' => $this->__('Delete topic'), 'value' => 'remove'),
                                                      array('text' => $this->__('Close topic'),   'value' => 'lock')));

        $render->assign('ignorelist_options', array(array('text' => $this->__('Strict'), 'value' => 'strict'),
                                                      array('text' => $this->__('Medium'), 'value' => 'medium'),
                                                      array('text' => $this->__('None'),   'value' => 'none')));

        $modvars = ModUtil::getVar('Dizkus');
        $render->assign('log_ip_checked', $modvars['log_ip'] == 'yes' ? 1 : 0);
        $render->assign('slimforum_checked', $modvars['slimforum'] == 'yes' ? 1 : 0);
        $render->assign('m2f_enabled_checked', $modvars['m2f_enabled'] == 'yes' ? 1 : 0);
        $render->assign('rss2f_enabled_checked', $modvars['rss2f_enabled'] == 'yes' ? 1 : 0);
        $render->assign('favorites_enabled_checked', $modvars['favorites_enabled'] == 'yes' ? 1 : 0);
        $render->assign('hideusers_checked', $modvars['hideusers'] == 'yes' ? 1 : 0);
        $render->assign('signaturemanagement_checked', $modvars['signaturemanagement'] == 'yes' ? 1 : 0);
        $render->assign('removesignature_checked', $modvars['removesignature'] == 'yes' ? 1 : 0);
        $render->assign('allowgravatars_checked', $modvars['allowgravatars']);
        $render->assign('ignorelist_handling', $modvars['ignorelist_handling'] == 'yes' ? 1 : 0);
        $render->assign('striptags_checked', $modvars['striptags'] == 'yes' ? 1 : 0);
        $render->assign('newtopicconfirmation_checked', isset($modvars['newtopicconfirmation']) && $modvars['newtopicconfirmation'] == 'yes' ? 1 : 0);
        $render->assign('forum_enabled_checked', $modvars['forum_enabled'] == 'yes' ? 1 : 0);
        $render->assign('fulltextindex_checked', $modvars['fulltextindex'] == 'yes' ? 1 : 0);
        $render->assign('extendedsearch_checked', $modvars['extendedsearch'] == 'yes' ? 1 : 0);
        $render->assign('showtextinsearchresults_checked', $modvars['showtextinsearchresults'] == 'yes' ? 1 : 0);

		$render->assign('contactlist_available', ModUtil::available('ContactList'));

        $serverinfo = DBUtil::serverInfo();
        $render->assign('dbversion', $serverinfo['description']);
        $render->assign('dbtype', DBConnectionStack::getConnectionDBType());
        $render->assign('dbname', DBConnectionStack::getConnectionDBName());

        return true;
    }

    function handleCommand(&$render, $args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError('index.php');
        }

        if ($args['commandName'] == 'submit') {
            $ok   = $render->isValid(); 
            $data = $render->getValues();

            if (!$ok) {
                return false;
            }

            // checkboxes 
            ModUtil::setVar('Dizkus', 'log_ip',                  $data['log_ip'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'slimforum',               $data['slimforum'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'm2f_enabled',             $data['m2f_enabled'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'rss2f_enabled',           $data['rss2f_enabled'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'favorites_enabled',       $data['favorites_enabled'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'hideusers',               $data['hideusers'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'signaturemanagement',     $data['signaturemanagement'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'removesignature',         $data['removesignature'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'allowgravatars',          $data['allowgravatars']);
            ModUtil::setVar('Dizkus', 'striptags',               $data['striptags'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'newtopicconfirmation',    $data['newtopicconf'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'forum_enabled',           $data['forum_enabled'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'fulltextindex',           $data['fulltextindex'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'extendedsearch',          $data['extendedsearch'] == 1 ? 'yes' : 'no');
            ModUtil::setVar('Dizkus', 'showtextinsearchresults', $data['showtextinsearchresults'] == 1 ? 'yes' : 'no');

            // dropdowns
            ModUtil::setVar('Dizkus', 'post_sort_order',     $data['post_sort_order']);
            ModUtil::setVar('Dizkus', 'deletehookaction',    $data['deletehookaction']);
            if (ModUtil::available('ContactList')) {
                ModUtil::setVar('Dizkus', 'ignorelist_handling', $data['ignorelist_handling']);
            }

            // ints
            ModUtil::setVar('Dizkus', 'hot_threshold',      $data['hot_threshold']);
            ModUtil::setVar('Dizkus', 'posts_per_page',     $data['posts_per_page']);
            ModUtil::setVar('Dizkus', 'topics_per_page',    $data['topics_per_page']);
            ModUtil::setVar('Dizkus', 'timespanforchanges', $data['timespanforchanges']);
            ModUtil::setVar('Dizkus', 'minsearchlength',    $data['minsearchlength']);
            ModUtil::setVar('Dizkus', 'maxsearchlength',    $data['maxsearchlength']);

            // strings
            ModUtil::setVar('Dizkus', 'email_from',          $data['email_from']);
            ModUtil::setVar('Dizkus', 'signature_start',     $data['signature_start']);
            ModUtil::setVar('Dizkus', 'signature_end',       $data['signature_end']);
            ModUtil::setVar('Dizkus', 'forum_disabled_info', $data['forum_disabled_info']);
            ModUtil::setVar('Dizkus', 'url_ranks_images',    $data['url_ranks_images']);
            ModUtil::setVar('Dizkus', 'gravatarimage',       $data['gravatarimage']);

            LogUtil::registerStatus($this->__('Done! Updated configuration.'));

        } elseif ($args['commandName'] == 'restore') {
            // checkboxes 
            ModUtil::setVar('Dizkus', 'log_ip',                  'no');
            ModUtil::setVar('Dizkus', 'slimforum',               'no');
            ModUtil::setVar('Dizkus', 'm2f_enabled',             'yes');
            ModUtil::setVar('Dizkus', 'rss2f_enabled',           'yes');
            ModUtil::setVar('Dizkus', 'favorites_enabled',       'yes');
            ModUtil::setVar('Dizkus', 'hideusers',               'no');
            ModUtil::setVar('Dizkus', 'signaturemanagement',     'no');
            ModUtil::setVar('Dizkus', 'removesignature',         'no');
            ModUtil::setVar('Dizkus', 'allowgravatars',          1);
            ModUtil::setVar('Dizkus', 'striptags',               'no');
            ModUtil::setVar('Dizkus', 'newtopicconfirmation',    'no');
            ModUtil::setVar('Dizkus', 'forum_enabled',           'yes');
            ModUtil::setVar('Dizkus', 'sendemailswithsqlerrors', 'no');
            ModUtil::setVar('Dizkus', 'showtextinsearchresults', 'yes');

            // dropdowns
            ModUtil::setVar('Dizkus', 'post_sort_order',     'ASC');
            ModUtil::setVar('Dizkus', 'deletehookaction',    'lock');
            ModUtil::setVar('Dizkus', 'ignorelist_handling', 'medium');

            // ints
            ModUtil::setVar('Dizkus', 'hot_threshold',      20);
            ModUtil::setVar('Dizkus', 'posts_per_page',     15);
            ModUtil::setVar('Dizkus', 'topics_per_page',    15);
            ModUtil::setVar('Dizkus', 'timespanforchanges', 24);
            ModUtil::setVar('Dizkus', 'minsearchlength',    3);
            ModUtil::setVar('Dizkus', 'maxsearchlength',    30);

            // strings
            ModUtil::setVar('Dizkus', 'email_from',          System::getVar('adminmail'));
            ModUtil::setVar('Dizkus', 'signature_start',     '');
            ModUtil::setVar('Dizkus', 'signature_end',       '');
            ModUtil::setVar('Dizkus', 'forum_disabled_info', $this->__('Sorry! The forums are currently off-line for maintenance. Please try later.'));
            ModUtil::setVar('Dizkus', 'url_ranks_images',    'modules/Dizkus/images/ranks');
            ModUtil::setVar('Dizkus', 'gravatarimage',       'gravatar.gif');

            LogUtil::registerStatus($this->__('Done! Reset configuration to default values.'));
        }

        return $render->redirect(ModUtil::url('Dizkus','admin','preferences'));
    }
}
