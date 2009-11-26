<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://www.dizkus.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

class dizkus_admin_prefshandler
{
    function initialize(&$render)
    {
        $dom = ZLanguage::getModuleDomain('Dizkus');

        $render->caching = false;
        $render->add_core_data();

        $render->assign('post_sort_order_options', array(array('text' => __('Ascending', $dom),  'value' => 'ASC'),
                                                         array('text' => __('Descending', $dom), 'value' => 'DESC')));

        $render->assign('deletehook_options', array(array('text' => __('Delete topic', $dom), 'value' => 'remove'),
                                                      array('text' => __('Close topic', $dom),   'value' => 'lock')));

        $render->assign('ignorelist_options', array(array('text' => __('Strict', $dom), 'value' => 'strict'),
                                                      array('text' => __('Medium', $dom), 'value' => 'medium'),
                                                      array('text' => __('None', $dom),   'value' => 'none')));

        $modvars = pnModGetVar('Dizkus');
        $render->assign('log_ip_checked', $modvars['log_ip'] == 'yes' ? 1 : 0);
        $render->assign('slimforum_checked', $modvars['slimforum'] == 'yes' ? 1 : 0);
        $render->assign('autosubscribe_checked', isset($modvars['autosubscribe']) && $modvars['autosubscribe'] == 'yes' ? 1 : 0);
        $render->assign('m2f_enabled_checked', $modvars['m2f_enabled'] == 'yes' ? 1 : 0);
        $render->assign('rss2f_enabled_checked', $modvars['rss2f_enabled'] == 'yes' ? 1 : 0);
        $render->assign('favorites_enabled_checked', $modvars['favorites_enabled'] == 'yes' ? 1 : 0);
        $render->assign('hideusers_checked', $modvars['hideusers'] == 'yes' ? 1 : 0);
        $render->assign('signaturemanagement_checked', $modvars['signaturemanagement'] == 'yes' ? 1 : 0);
        $render->assign('removesignature_checked', $modvars['removesignature'] == 'yes' ? 1 : 0);
        $render->assign('ignorelist_handling', $modvars['ignorelist_handling'] == 'yes' ? 1 : 0);
        $render->assign('striptags_checked', $modvars['striptags'] == 'yes' ? 1 : 0);
        $render->assign('newtopicconfirmation_checked', isset($modvars['newtopicconfirmation']) && $modvars['newtopicconfirmation'] == 'yes' ? 1 : 0);
        $render->assign('forum_enabled_checked', $modvars['forum_enabled'] == 'yes' ? 1 : 0);
        $render->assign('sendemailswithsqlerrors_checked', $modvars['sendemailswithsqlerrors'] == 'yes' ? 1 : 0);
        $render->assign('fulltextindex_checked', $modvars['fulltextindex'] == 'yes' ? 1 : 0);
        $render->assign('extendedsearch_checked', $modvars['extendedsearch'] == 'yes' ? 1 : 0);
        $render->assign('showtextinsearchresults_checked', $modvars['showtextinsearchresults'] == 'yes' ? 1 : 0);

		$render->assign('contactlist_available', pnModAvailable('ContactList'));

        $serverinfo = DBUtil::serverInfo();
        $render->assign('dbversion', $serverinfo['description']);
        $render->assign('dbtype', DBConnectionStack::getConnectionDBType());
        $render->assign('dbname', DBConnectionStack::getConnectionDBName());

        return true;
    }

    function handleCommand(&$render, &$args)
    {
        $dom = ZLanguage::getModuleDomain('Dizkus');

        // Security check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError('index.php');
        }

        if ($args['commandName'] == 'submit') {
            $ok = $render->pnFormIsValid(); 
            $data = $render->pnFormGetValues();

            if (!$ok) {
                return false;
            }

            // checkboxes 
            pnModSetVar('Dizkus', 'log_ip',                  $data['log_ip'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'slimforum',               $data['slimforum'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'autosubscribe',           $data['autosubscribe'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'm2f_enabled',             $data['m2f_enabled'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'rss2f_enabled',           $data['rss2f_enabled'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'favorites_enabled',       $data['favorites_enabled'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'hideusers',               $data['hideusers'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'signaturemanagement',     $data['signaturemanagement'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'removesignature',         $data['removesignature'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'striptags',               $data['striptags'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'newtopicconfirmation',    $data['newtopicconf'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'forum_enabled',           $data['forum_enabled'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'sendemailswithsqlerrors', $data['sendemailswithsqlerrors'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'fulltextindex',           $data['fulltextindex'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'extendedsearch',          $data['extendedsearch'] == 1 ? 'yes' : 'no');
            pnModSetVar('Dizkus', 'showtextinsearchresults', $data['showtextinsearchresults'] == 1 ? 'yes' : 'no');

            // dropdowns
            pnModSetVar('Dizkus', 'post_sort_order',     $data['post_sort_order']);
            pnModSetVar('Dizkus', 'deletehookaction',    $data['deletehookaction']);
            if (pnModAvailable('ContactList')) {
                pnModSetVar('Dizkus', 'ignorelist_handling', $data['ignorelist_handling']);
            }

            // ints
            pnModSetVar('Dizkus', 'hot_threshold',      $data['hot_threshold']);
            pnModSetVar('Dizkus', 'posts_per_page',     $data['posts_per_page']);
            pnModSetVar('Dizkus', 'topics_per_page',    $data['topics_per_page']);
            pnModSetVar('Dizkus', 'timespanforchanges', $data['timespanforchanges']);
            pnModSetVar('Dizkus', 'minsearchlength',    $data['minsearchlength']);
            pnModSetVar('Dizkus', 'maxsearchlength',    $data['maxsearchlength']);

            // strings
            pnModSetVar('Dizkus', 'email_from',          $data['email_from']);
            pnModSetVar('Dizkus', 'default_lang',        $data['default_lang']);
            pnModSetVar('Dizkus', 'signature_start',     $data['signature_start']);
            pnModSetVar('Dizkus', 'signature_end',       $data['signature_end']);
            pnModSetVar('Dizkus', 'forum_disabled_info', $data['forum_disabled_info']);
            pnModSetVar('Dizkus', 'url_ranks_images',    $data['url_ranks_images']);

            LogUtil::registerStatus(__('Done! Updated settings.', $dom));

        } elseif ($args['commandName'] == 'restore') {
            // checkboxes 
            pnModSetVar('Dizkus', 'log_ip',                  'no');
            pnModSetVar('Dizkus', 'slimforum',               'no');
            pnModSetVar('Dizkus', 'autosubscribe',           'no');
            pnModSetVar('Dizkus', 'm2f_enabled',             'yes');
            pnModSetVar('Dizkus', 'rss2f_enabled',           'yes');
            pnModSetVar('Dizkus', 'favorites_enabled',       'yes');
            pnModSetVar('Dizkus', 'hideusers',               'no');
            pnModSetVar('Dizkus', 'signaturemanagement',     'no');
            pnModSetVar('Dizkus', 'removesignature',         'no');
            pnModSetVar('Dizkus', 'striptags',               'no');
            pnModSetVar('Dizkus', 'newtopicconfirmation',    'no');
            pnModSetVar('Dizkus', 'forum_enabled',           'yes');
            pnModSetVar('Dizkus', 'sendemailswithsqlerrors', 'no');
            pnModSetVar('Dizkus', 'showtextinsearchresults', 'yes');

            // dropdowns
            pnModSetVar('Dizkus', 'post_sort_order',     'ASC');
            pnModSetVar('Dizkus', 'deletehookaction',    'lock');
            pnModSetVar('Dizkus', 'ignorelist_handling', 'medium');

            // ints
            pnModSetVar('Dizkus', 'hot_threshold',      20);
            pnModSetVar('Dizkus', 'posts_per_page',     15);
            pnModSetVar('Dizkus', 'topics_per_page',    15);
            pnModSetVar('Dizkus', 'timespanforchanges', 24);
            pnModSetVar('Dizkus', 'minsearchlength',    3);
            pnModSetVar('Dizkus', 'maxsearchlength',    30);

            // strings
            pnModSetVar('Dizkus', 'email_from',          pnConfigGetVar('adminmail'));
            pnModSetVar('Dizkus', 'default_lang',        'utf-8');
            pnModSetVar('Dizkus', 'signature_start',     '');
            pnModSetVar('Dizkus', 'signature_end',       '');
            pnModSetVar('Dizkus', 'forum_disabled_info', __('Sorry! The forums are currently closed for maintenance. Please check again soon.', $dom));
            pnModSetVar('Dizkus', 'url_ranks_images',    'modules/Dizkus/pnimages/ranks');

            LogUtil::registerStatus(__('Done! Reset configuration to default values.', $dom));
        }

        return $render->pnFormRedirect(pnModURL('Dizkus','admin','preferences'));
    }
}
