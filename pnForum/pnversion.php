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

/**
 * set modversion info
 */
$modversion['name']        = 'Dizkus';
$modversion['oldnames']    = array('pnForum');
$modversion['id']          = '62';
$modversion['version']     = '3.0';
$modversion['description'] = 'Integrated forum module';
$modversion['credits']     = 'pndocs/credits.txt';
$modversion['help']        = 'pndocs/install.txt';
$modversion['changelog']   = 'pndocs/changelog.txt';
$modversion['license']     = 'pndocs/license.txt';
$modversion['official']    = 0;
$modversion['author']      = 'Andreas Krapohl, Frank Schummertz';
$modversion['contact']     = 'http://www.dizkus.com';
$modversion['admin']       = 1;
$modversion['user']        = 1;
$modversion['securityschema'] = array('Dizkus::' => 'CategoryID:ForumID:',
                                      'Dizkus::CreateForum' => 'CategoryID::');

// Zikula 1.0 extension following: module depedencies
$modversion['dependencies'] = array(
                                    array('modname'    => 'bbcode', 
                                          'minversion' => '2.0', 
                                          'maxversion' => '', 
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED),
                                    array('modname'    => 'bbsmile', 
                                          'minversion' => '2.1', 
                                          'maxversion' => '', 
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED)
                                              );
