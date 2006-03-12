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
$modversion['version'] = '2.7';
$modversion['description'] = 'phpBB-style Bulletin Board';
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

?>