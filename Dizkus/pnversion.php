<?php
/**
 * general module informations
 * @version $Id$
 * @author Andreas Krapohl
 * @copyright 2003 by Andreas Krapohl, 2004 by Frank Schummertz
 * @package Dizkus
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.dizkus.com
 */

$dom = ZLanguage::getModuleDomain('Dizkus');

$modversion['name']           = 'Dizkus';
$modversion['oldnames']       = array('pnForum');
$modversion['displayname']    = __('Dizkus forums', $dom);
$modversion['description']    = __('Provides an integrated forum system for Zikula, that is simple to administer and use but that has an excellent feature set.', $dom);
//! module name that appears in URL
$modversion['url']            = __('forums', $dom);
$modversion['version']        = '3.1';

$modversion['credits']        = 'pndocs/credits.txt';
$modversion['help']           = 'pndocs/install.txt';
$modversion['changelog']      = 'pndocs/changelog.txt';
$modversion['license']        = 'pndocs/license.txt';
$modversion['id']             = '62';
$modversion['official']       = 0;
$modversion['author']         = 'Andreas Krapohl, Frank Schummertz, Carsten Volmer';
$modversion['contact']        = 'http://www.dizkus.com';
$modversion['admin']          = 1;
$modversion['user']           = 1;
$modversion['securityschema'] = array('Dizkus::' => 'CategoryID:ForumID:',
                                      'Dizkus::CreateForum' => 'CategoryID::');

// module depedencies
$modversion['dependencies']   = array(
                                      array('modname'    => 'bbcode', 
                                            'minversion' => '2.0', 
                                            'maxversion' => '', 
                                            'status'     => PNMODULE_DEPENDENCY_RECOMMENDED),
                                      array('modname'    => 'bbsmile', 
                                            'minversion' => '2.1', 
                                            'maxversion' => '', 
                                            'status'     => PNMODULE_DEPENDENCY_RECOMMENDED)
                                     );
