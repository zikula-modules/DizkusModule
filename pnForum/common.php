<?php
/************************************************************************
 * pnForum - The Post-Nuke Module                                       *
 * ==============================                                       *
 *                                                                      *
 * Copyright (c) 2001-2004 by the pnForum Module Development Team       *
 * http://www.pnforum.de/                                            *
 ************************************************************************
 * Modified version of: *
 ************************************************************************
 * phpBB version 1.4                                                    *
 * begin                : Wed July 19 2000                              *
 * copyright            : (C) 2001 The phpBB Group                      *
 * email                : support@phpbb.com                             *
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
 * general functions
 * @version $Id$
 * @author Frank Schummertz
 * @copyright 2004 by Frank Schummertz
 * @package pnForum
 * @license GPL <http://www.gnu.org/licenses/gpl.html> 
 * @link http://www.pnforum.de
 *
 ***********************************************************************/

/*
 * showforumerror
 * display a simple error message showing $text
 *@param text string The error text
 */
function showforumerror($error_text, $file="", $line=0)
{
    // we need to load one core file in order to have the language definitions
    // available
    if(!pnModAPILoad('pnForum','admin'))
    {
        pnModAPILoad('pnForum', 'user');
    }
    
    $pnr =& new pnRender('pnForum');
    $pnr->caching = false;
    $pnr->assign( 'adminmail', pnConfigGetVar('adminmail') );
    $pnr->assign( 'error_text', $error_text );
    if(pnSecAuthAction(0, 'pnForum::', '::', ACCESS_ADMIN))
    {
        $pnr->assign( 'file', $file);
        $pnr->assign( 'line', $line);
    }
    $output = $pnr->fetch("pnforum_errorpage.html");
    if(preg_match("/(api\.php|common\.php)$/i", $file)<>0)
    {
        // __FILE__ ends with api.php or is common.php itself
        include_once("header.php");
        echo $output;
        include_once("footer.php");
        exit;
    }
    return $output;
    
}

/**
 * showforumsqlerror
 * if $sql is not empty then we show message and mail notififcation to site admin
 * if it is empty, then it is not an Error, but just warning and we just show message to a user. 
 * No mail is generated
 * If current user is admin, then onscreen message also include additional debug information 
 */
function showforumsqlerror($msg,$sql='',$mysql_errno='',$mysql_error='', $file="", $line)
{
	$adminmail = pnConfigGetVar('adminmail'); 
	$pagetype = 'error'; 
    
	if ($sql != '') {
		// Sending notify e-mail for error
		$posted_message = "Error occured\n\n";
		$posted_message .= "SQL statement:\n".$sql."\n";
		$posted_message .= "\nDatabase error number:\n".$mysql_errno."\n";
		$posted_message .= "\nDatabase error message:\n".$mysql_error."\n";
		$posted_message .= "\nLINK:\n".$GLOBALS[CHARSET_HTTP_METHOD].""
                        .$GLOBALS[HTTP_HOST].""
						.$GLOBALS[SCRIPT_NAME]
						."?"
						.$GLOBALS[QUERY_STRING]
						."\n";
		$posted_message .= "\nHTTP_USER_AGENT:\n".$GLOBALS[HTTP_USER_AGENT]."\n";
    	$email_from = pnModGetVar('pnForum', 'email_from');
    	if ($email_from == "") {
    		// nothing in forumwide-settings, use PN adminmail
    		$email_from = pnConfigGetVar('adminmail');
    	}
    	$msg_From_Header = "From: ".pnConfigGetVar('sitename')."<".$email_from.">\n";
    	$modInfo = pnModGetInfo(pnModGetIDFromName('pnForum'));
    	$msg_XMailer_Header = "X-Mailer: pnForum ".$modVersion."\n";
    	$msg_ContentType_Header = "Content-Type: text/plain;";
    
    	$phpbb_default_charset = pnModGetVar('pnForum', 'default_lang');
    	if ($phpbb_default_charset != '') {
    		$msg_ContentType_Header .= " charset=".$phpbb_default_charset;
    	}
    	$msg_ContentType_Header .= "\n";
		$msg_To = pnConfigGetVar('adminmail');
		// set reply-to to his own adress ;)
		$msg_Headers = $msg_From_Header.$msg_XMailer_Header.$msg_ContentType_Header;
		$msg_Headers .= "Reply-To: $msg_To";
        $msg_Subject = "sql error in your pnForum";
        
    	pnMail($msg_To, $msg_Subject, $posted_message, $msg_Headers);
	}   
    if(pnSecAuthAction(0, 'pnForum::', '::', ACCESS_ADMIN)) {
        return showforumerror( "$msg <br />
                                sql  : $sql <br />
                                code : $mysql_errno <br />
                                msg  : $mysql_error <br />", $file, $line );
    } else {
        return showforumerror( $msg, $file, $line );
    }
}

/**
 * internal debug function
 *
 */
function pnfdebug($name="", $data, $die = false)
{
    if(pnSecAuthAction(0, "pnForum::", "::", ACCESS_ADMIN)) {
        $type = gettype($data);
        echo "<span style=\"color: red;\">$name ($type):";
        if(is_array($data)||is_object($data)) {
            $size = count($data);
            if($size>0) {
                echo "(size=$size entries)<pre>";
                print_r($data);
                echo "</pre>:<br />";
            } else {
                echo "empty<br />";
            }
        } else if(is_bool($data)) {
            echo ($data==true) ? "true<br />" : "false<br />";
        } else {
            echo "$data:<br />";
        }
        echo "</span><br />";
        if($die==true) {
            die();
        }
    }
}

/**
 * internal function
 *
 */
function pnfsqldebug($sql)
{
    pnfdebug('sql', $sql);
}

/**
 * pnfOpenDB
 * creates a dbconnection object and returns it to the calling function
 *
 *@params $table (string) name of the table you want to access, optional
 *@return array consisting:
 * if a tablename is given:
 *@returns object dbconn object for use to execute sql queries
 *@returns string fully qualified tablename
 *@returns array with field names
 * if no tablename is given:
 *@returns object dbconn object for use to execute sql queries
 *@returns array  pntable array
 *        or false on error
 */
function pnfOpenDB($tablename)
{
	pnModDBInfoLoad('pnForum');
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

    if(isset($tablename)) {
        $columnname = $tablename . '_column';
        if( !array_key_exists($tablename, $pntable) || 
            !array_key_exists($columnname, $pntable) ) {return false; }
        // table exists, now get the dbconnection object
        return array($dbconn, &$pntable[$tablename], &$pntable[$columnname]);
    } else {
        return array($dbconn, $pntable);
    }
}

/**
 * pnfCloseDB
 * closes an db connection opened with pnfOpenDB
 *
 *@params $resobj object as returned by $dbconn->Execute();
 *@returns nothing
 *
 */
function pnfCloseDB($resobj)
{
    if(is_object($resobj)) 
    {
        $resobj->Close();
    }
    return;
}

/**
 * pnfExecuteSQL 
 * executes an sql command and returns the result, shows error if necessary
 *
 *@params $dbconn object db onnection object
 *@params $sql    string the sql ommand to execute
 *@params $debug  bool   true if debug should be activated, default is false
 *@returns object the result of $dbconn->Execute($sql)
 */
function pnfExecuteSQL(&$dbconn, $sql, $file=__FILE__, $line=__LINE__, $debug=false)
{
    if(!is_object($dbconn) || !isset($sql) || empty($sql)) {
        return showforumerror(_MODARGSERROR, $file, $line);
    }
    $dbconn->debug = $debug;
    $result = $dbconn->Execute($sql);
    $dbconn->debug = $false;
    if($dbconn->ErrorNo() != 0) {
        return showforumsqlerror(_PNFORUM_ERROR_CONNECT,$sql,$dbconn->ErrorNo(),$dbconn->ErrorMsg(), $file, $line);
    }
    return $result;
}

/**
 * is_serialized
 * checks if a string is a serialized array
 *
 *@params $string the string to test
 *@returns boolean true or false
 *
 */
if(!function_exists('pnForum_is_serialized')) {
    function pnForum_is_serialized( $string ) {
        if( @unserialize( $string ) == "" ) {
            return false;
        }
        return true;
    } 
}

/**
 * pn_bbdecode/pn_bbencode functions:
 * Rewritten - Nathan Codding - Aug 24, 2000
 * Using Perl-Compatible regexps now. Won't kill special chars
 * outside of a [code]...[/code] block now, and all BBCode tags
 * are implemented.
 * Note: the "i" matching switch is used, so BBCode tags are
 * case-insensitive.
 *
 * obsolete function - we have pn_bbcode
 *
 */

function pnForum_bbdecode($message) 
{
    // Undo [code]
    $message = preg_replace("#<!-- BBCode Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD>Code:<HR></TD></TR><TR><TD><PRE>(.*?)</PRE></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode End -->#s", "[code]\\1[/code]", $message);
    
    // Undo [quote]
    $message = preg_replace("#<!-- BBCode Quote Start --><TABLE BORDER=0 ALIGN=CENTER WIDTH=85%><TR><TD>Quote:<HR></TD></TR><TR><TD><BLOCKQUOTE>(.*?)</BLOCKQUOTE></TD></TR><TR><TD><HR></TD></TR></TABLE><!-- BBCode Quote End -->#s", "[quote]\\1[/quote]", $message);
    
    // Undo [b] and [i]
    $message = preg_replace("#<!-- BBCode Start --><strong>(.*?)</strong><!-- BBCode End -->#s", "[b]\\1[/b]", $message);
    $message = preg_replace("#<!-- BBCode Start --><I>(.*?)</I><!-- BBCode End -->#s", "[i]\\1[/i]", $message);
    
    // Undo [url] (both forms)
    $message = preg_replace("#<!-- BBCode Start --><A HREF=\"http://(.*?)\" TARGET=\"_blank\">(.*?)</A><!-- BBCode End -->#s", "[url=\\1]\\2[/url]", $message);
    
    // Undo [email]
    $message = preg_replace("#<!-- BBCode Start --><A HREF=\"mailto:(.*?)\">(.*?)</A><!-- BBCode End -->#s", "[email]\\1[/email]", $message);
    
    // Undo [img]
    $message = preg_replace("#<!-- BBCode Start --><IMG SRC=\"http://(.*?)\"><!-- BBCode End -->#s", "[img]http://\\1[/img]", $message);
    //$message = preg_replace("#<!-- BBCode Start --><IMG SRC=\"(.*?)\"><!-- BBCode End -->#s", "[img]\\1[/img]", $message);
    
    // Undo lists (unordered/ordered)
    
    // unordered list code..
    $matchCount = preg_match_all("#<!-- BBCode ulist Start --><UL>(.*?)</UL><!-- BBCode ulist End -->#s", $message, $matches);
    
    for ($i = 0; $i < $matchCount; $i++)
    {
    	$currMatchTextBefore = preg_quote($matches[1][$i]);
    	$currMatchTextAfter = preg_replace("#<LI>#s", "[*]", $matches[1][$i]);
    
    	$message = preg_replace("#<!-- BBCode ulist Start --><UL>$currMatchTextBefore</UL><!-- BBCode ulist End -->#s", "[list]" . $currMatchTextAfter . "[/list]", $message);
    }
    
    // ordered list code..
    $matchCount = preg_match_all("#<!-- BBCode olist Start --><OL TYPE=([A1])>(.*?)</OL><!-- BBCode olist End -->#si", $message, $matches);
    
    for ($i = 0; $i < $matchCount; $i++)
    {
    	$currMatchTextBefore = preg_quote($matches[2][$i]);
    	$currMatchTextAfter = preg_replace("#<LI>#s", "[*]", $matches[2][$i]);
    
    	$message = preg_replace("#<!-- BBCode olist Start --><OL TYPE=([A1])>$currMatchTextBefore</OL><!-- BBCode olist End -->#si", "[list=\\1]" . $currMatchTextAfter . "[/list]", $message);
    }
    
    return($message);
}


/**
 * Nathan Codding - Feb 6, 2001
 * Reverses the effects of make_clickable(), for use in editpost.
 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
 *
 *
 * obsolete function - we have pn_bbclick
 */
function pnForum_undo_make_clickable($text) 
{
	$text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\" target=\"_blank\">.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);
	$text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\">.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);
    return $text;
}

/**
 * Changes a Smiliy <IMG> tag into its corresponding smile
 * TODO: Get rid of golbal variables, and implement a method of distinguishing between :D and :grin: using the <IMG> tag
 *
 * obsolete function, we have pn_bbsmile
 *
 */

function pnForum_desmile($message) 
{
	pnModDBInfoLoad('pnForum');
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

	$url_smiles = pnModGetVar('pnForum', 'url_smiles');

	$sql = "SELECT * FROM ".$pntable['pnforum_smiles']." ";

	if ($getsmiles = mysql_query($sql)){
		while ($smiles = mysql_fetch_array($getsmiles)) {
			$message = str_replace("<IMG SRC=\"$url_smiles/$smiles[smile_url]\">", $smiles['code'], $message);
		}
	}
	return($message);
}

/**
 * removes instances of <br /> since sometimes they are stored in DB :(
 */
function phpbb_br2nl($str) 
{
    return preg_replace("=<br(>|([\s/][^>]*)>)\r?\n?=i", "\n", $str);
}

/**
 * allowedtoseecategoryandforum
 */
function allowedtoseecategoryandforum($category_id, $forum_id)
{
    return pnSecAuthAction(0, "pnForum::", "$category_id:$forum_id:", ACCESS_OVERVIEW);
}

/**
 * allowedtoreadcategoryandforum
 */
function allowedtoreadcategoryandforum($category_id, $forum_id)
{
    return pnSecAuthAction(0, "pnForum::", "$category_id:$forum_id:", ACCESS_READ);
}

/**
 * allowedtowritetocategoryandforum
 */
function allowedtowritetocategoryandforum($category_id, $forum_id)
{
    return pnSecAuthAction(0, "pnForum::", "$category_id:$forum_id:", ACCESS_COMMENT);
}

/**
 * allowedtomoderatecategoryandforum
 */
function allowedtomoderatecategoryandforum($category_id, $forum_id)
{
    return pnSecAuthAction(0, "pnForum::", "$category_id:$forum_id:", ACCESS_MODERATE);
}

/**
 * allowedtoadmincategoryandforum
 */
function allowedtoadmincategoryandforum($category_id, $forum_id)
{
    return pnSecAuthAction(0, "pnForum::", "$category_id:$forum_id:", ACCESS_ADMIN);
}

/**
 * sorting categories by cat_order (this is a VARCHAR, so we need this function for sorting)
 *
 */
function cmp_catorder ($a, $b) 
{
   return (int)$a['cat_order'] > (int)$b['cat_order'];
}

?>