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

$modversion['name']           = 'Dizkus';
$modversion['oldnames']       = array('pnForum');
$modversion['displayname']    = __('Dizkus forums');
$modversion['description']    = __('An integrated forum solution for Zikula which is simple to administer and use but that has an excellent feature set.');
//! module name that appears in URL
$modversion['url']            = __('forums');
$modversion['version']        = '3.2';

$modversion['credits']        = 'docs/credits.txt';
$modversion['help']           = 'docs/install.txt';
$modversion['changelog']      = 'docs/changelog.txt';
$modversion['license']        = 'docs/license.txt';
$modversion['id']             = '62';
$modversion['official']       = 0;
$modversion['author']         = 'Andreas Krapohl, Frank Schummertz, Carsten Volmer';
$modversion['contact']        = 'http://code.zikula.org/dizkus';
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
