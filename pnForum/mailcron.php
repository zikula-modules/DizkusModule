<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                            *
 ************************************************************************
 * License *
 ************************************************************************
 * This program is free software; you can redistribute it and/or modify *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation; either version 2 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * This program is distributed in the hope that it will be useful,      *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with this program; if not, write to the Free Software          *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 *
 * USA                                                                  *
 ************************************************************************
 *
 * mailcron.phpadmin functions
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2004 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

//
// store the absolut path to your PostNuke folder here
//
chdir('/opt/lampp/htdocs/760');
//<img src="">chdir('/www/htdocs/postnet');

//
// no changes necessary beyond this point!
//
include "includes/pnAPI.php";
pnInit();

$debug = pnVarCleanFromInput('debug');

if(!pnModAPILoad('pnForum', 'user')) {
    die('unable to load pnForum userapi\n');
}
if(!pnModAPILoad('pnForum', 'admin')) {
    die('unable to load pnForum adminapi\n');
}

pnSessionSetVar('mailcronrunning', true);
if($debug==1) {
    pnSessionSetVar('mailcrondebug', true);
}
$forums = pnModAPIFunc('pnForum', 'admin', 'readforums', array('permcheck' => 'nocheck'));
if(is_array($forums) && count($forums)>0 ) {
    echo count($forums) . " forums read<br />";
    foreach($forums as $forum) {
        if($forum['externalsource'] == 1) {    // Mail
            pnModAPIFunc('pnForum', 'user', 'mailcron',
                         array('forum' => $forum));
        }
    }
}
pnSessionDelVar('mailcronrunning');
pnSessionDelVar('mailcrondebug');

?>