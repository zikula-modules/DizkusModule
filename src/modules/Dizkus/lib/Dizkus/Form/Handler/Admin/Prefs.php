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
 * This class provides a handler to the modules preferences.
 */
class Dizkus_Form_Handler_Admin_Prefs extends Zikula_Form_AbstractHandler
{
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
        $this->view->caching = false;

        $this->view->assign('post_sort_order_options', array(array('text' => $this->__('Ascending'),  'value' => 'ASC'),
                                                         array('text' => $this->__('Descending'), 'value' => 'DESC')));

        $this->view->assign('deletehook_options', array(array('text' => $this->__('Delete topic'), 'value' => 'remove'),
                                                      array('text' => $this->__('Close topic'),   'value' => 'lock')));

        $this->view->assign('ignorelist_options', array(array('text' => $this->__('Strict'), 'value' => 'strict'),
                                                      array('text' => $this->__('Medium'), 'value' => 'medium'),
                                                      array('text' => $this->__('None'),   'value' => 'none')));

        $spam_protectors = array(
            array('text' => $this->__('No spam protector'), 'value' => 'no'),
            array('text' => 'Akismet', 'value' => 'Akismet')
        );
        $this->view->assign('spam_protectors', $spam_protectors);

        $vars = $this->getVars();
        $yesorno = array(
            'log_ip',                 
            'slimforum',             
            'm2f_enabled',            
            'rss2f_enabled',
            'favorites_enabled',
            'hideusers',
            'signaturemanagement',     
            'removesignature',
            'striptags',               
            'newtopicconfirmation',
            'forum_enabled',
            'fulltextindex',
            'extendedsearch',
            'showtextinsearchresults'
        );
        foreach ($yesorno as $value) {
            if (array_key_exists($value, $vars) && $vars[$value] == 'yes') {
                $vars[$value] = true;
            } else {
                $vars[$value] = false;
            }
        }
        
        $this->view->assign($vars);
        
        $this->view->assign('contactlist_available', ModUtil::available('ContactList'));

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
        // Security check
        if (!SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError('index.php');
        }

        if ($args['commandName'] == 'submit') {        

            // check for valid form
            if (!$view->isValid()) {
                return false;
            }
            $data = $view->getValues();

            // checkboxes 
            $this->setVar('log_ip',                  $data['log_ip'] == 1 ? 'yes' : 'no');
            $this->setVar('slimforum',               $data['slimforum'] == 1 ? 'yes' : 'no');
            $this->setVar('m2f_enabled',             $data['m2f_enabled'] == 1 ? 'yes' : 'no');
            $this->setVar('rss2f_enabled',           $data['rss2f_enabled'] == 1 ? 'yes' : 'no');
            $this->setVar('favorites_enabled',       $data['favorites_enabled'] == 1 ? 'yes' : 'no');
            $this->setVar('hideusers',               $data['hideusers'] == 1 ? 'yes' : 'no');
            $this->setVar('signaturemanagement',     $data['signaturemanagement'] == 1 ? 'yes' : 'no');
            $this->setVar('removesignature',         $data['removesignature'] == 1 ? 'yes' : 'no');
            $this->setVar('striptags',               $data['striptags'] == 1 ? 'yes' : 'no');
            $this->setVar('newtopicconfirmation',    $data['newtopicconf'] == 1 ? 'yes' : 'no');
            $this->setVar('forum_enabled',           $data['forum_enabled'] == 1 ? 'yes' : 'no');
            $this->setVar('fulltextindex',           $data['fulltextindex'] == 1 ? 'yes' : 'no');
            $this->setVar('extendedsearch',          $data['extendedsearch'] == 1 ? 'yes' : 'no');
            $this->setVar('showtextinsearchresults', $data['showtextinsearchresults'] == 1 ? 'yes' : 'no');
            $this->setVar('solved_enabled',          $data['solved_enabled']);
            $this->setVar('ajax',                    $data['ajax']);

            // dropdowns
            $this->setVar('post_sort_order',         $data['post_sort_order']);
            $this->setVar('deletehookaction',        $data['deletehookaction']);
            //if ($this->available('ContactList')) {
            //    $this->setVar('ignorelist_handling', $data['ignorelist_handling']);
            //}

            // ints
            $this->setVar('hot_threshold',           $data['hot_threshold']);
            $this->setVar('posts_per_page',          $data['posts_per_page']);
            $this->setVar('topics_per_page',         $data['topics_per_page']);
            $this->setVar('timespanforchanges',      $data['timespanforchanges']);
            $this->setVar('minsearchlength',         $data['minsearchlength']);
            $this->setVar('maxsearchlength',         $data['maxsearchlength']);

            // strings
            $this->setVar('email_from',              $data['email_from']);
            $this->setVar('signature_start',         $data['signature_start']);
            $this->setVar('signature_end',           $data['signature_end']);
            $this->setVar('forum_disabled_info',     $data['forum_disabled_info']);
            $this->setVar('url_ranks_images',        $data['url_ranks_images']);

            $this->setVar('spam_protector',          $data['spam_protector']);
            
            LogUtil::registerStatus($this->__('Done! Updated configuration.'));

        } elseif ($args['commandName'] == 'restore') {
            // checkboxes 
            $this->setVar('log_ip',                  'no');
            $this->setVar('slimforum',               'no');
            $this->setVar('m2f_enabled',             'yes');
            $this->setVar('rss2f_enabled',           'yes');
            $this->setVar('favorites_enabled',       'yes');
            $this->setVar('hideusers',               'no');
            $this->setVar('signaturemanagement',     'no');
            $this->setVar('removesignature',         'no');
            $this->setVar('striptags',               'no');
            $this->setVar('newtopicconfirmation',    'no');
            $this->setVar('forum_enabled',           'yes');
            $this->setVar('sendemailswithsqlerrors', 'no');
            $this->setVar('showtextinsearchresults', 'yes');
            $this->setVar('solve_enabled',           false);

            // dropdowns
            $this->setVar('post_sort_order',     'ASC');
            $this->setVar('deletehookaction',    'lock');
            $this->setVar('ignorelist_handling', 'medium');

            // ints
            $this->setVar('hot_threshold',      20);
            $this->setVar('posts_per_page',     15);
            $this->setVar('topics_per_page',    15);
            $this->setVar('timespanforchanges', 24);
            $this->setVar('minsearchlength',    3);
            $this->setVar('maxsearchlength',    30);

            // strings
            $this->setVar('email_from',          System::getVar('adminmail'));
            $this->setVar('signature_start',     '');
            $this->setVar('signature_end',       '');
            $this->setVar('forum_disabled_info', $this->__('Sorry! The forums are currently off-line for maintenance. Please try later.'));
            $this->setVar('url_ranks_images',    'modules/Dizkus/images/ranks');

            $this->setVar('spam_protector',      'none');
            
            LogUtil::registerStatus($this->__('Done! Reset configuration to default values.'));
        }
        return true;
    }
}
