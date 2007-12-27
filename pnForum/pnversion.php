<?php
/**
 * general module informations
 * @version $Id$
 * @author Andreas Krapohl
 * @copyright 2003 by Andreas Krapohl, 2004 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 */

/**
 * set modversion info
 */
$modversion['name'] = 'pnForum';
$modversion['id'] = '62';
$modversion['version'] = '3.0';
$modversion['description'] = 'Integrated forum module';
$modversion['credits'] = 'pndocs/credits.txt';
$modversion['help'] = 'pndocs/install.txt';
$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['license'] = 'pndocs/license.txt';
$modversion['official'] = 0;
$modversion['author'] = 'Andreas Krapohl, Frank Schummertz';
$modversion['contact'] = 'http://www.pnforum.de';
$modversion['admin'] = 1;
$modversion['user'] = 1;
$modversion['securityschema'] = array('pnForum::' => 'CategoryID:ForumID:',
                                      'pnForum::CreateForum' => 'CategoryID::');

// .8 extension following: module depedencies
$modversion['dependencies'] = array(
                                    array('modname'    => 'pn_bbcode', 
                                          'minversion' => '1.21', 
                                          'maxversion' => '', 
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED),
                                    array('modname'    => 'pn_bbsmile', 
                                          'minversion' => '1.17', 
                                          'maxversion' => '', 
                                          'status'     => PNMODULE_DEPENDENCY_RECOMMENDED)
                                              );
