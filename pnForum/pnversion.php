<?php
/**
 * general module informations
 * @version $Id$
 * @author Andreas Krapohl 
 * @copyright 2003 by Andreas Krapohl, 2004 by Frank Schummertz
 * @package phpBB_14 (aka pnForum) 
 * @license GPL <http://www.gnu.org/licenses/gpl.html> 
 * @link http://www.pnforum.de
 */

/**
 * set modversion info
 */
$modversion['name'] = 'pnForum';
$modversion['id'] = '62';
$modversion['version'] = '1.8.0';
$modversion['description'] = 'phpBB-style Bulletin Board';
$modversion['credits'] = 'pndocs/credits.txt';
$modversion['help'] = 'pndocs/install.txt';
$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['license'] = 'pndocs/license.txt';
$modversion['official'] = 0;
$modversion['author'] = 'Andreas Krapohl, Frank Schummertz';
$modversion['contact'] = 'andreas AT krapohl DOT net, frank.schummertz AT landseer-stuttgart DOT de';
$modversion['admin'] = 1;
$modversion['user'] = 1;
$modversion['securityschema'] = array('pnForum::Category' => 'Category name::',
                                      'pnForum::Forum' => 'Forum name::');
?>