<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * internal debug function
 *
 */
function dzkdebug($name='', $data, $die = false)
{
    if (SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        $type = gettype($data);
        echo "\n<!-- begin debug of $name -->\n<div style=\"color: red;\">$name ($type";
        if (is_array($data)||is_object($data)) {
            if (count($data) > 0) {
                echo ', size=$size entries):<pre>';
                echo htmlspecialchars(print_r($data, true));
                echo '</pre>:<br />';
            } else {
                echo '):empty<br />';
            }
        } else if (is_bool($data)) {
            echo ($data==true) ? ") true<br />" : ") false<br />";
        } else if (is_string($data)) {
            echo ', len='.strlen($data).') :'.DataUil::formatForDisplay($data).':<br />';
        } else {
            echo ') :'.$data.':<br />';
        }
        echo '</div><br />\n<!-- end debug of $name -->';
        if ($die==true) {
            System::shutDown();
        }
    }
}

/**
 * bbdecode/bbencode functions:
 * Rewritten - Nathan Codding - Aug 24, 2000
 * Using Perl-Compatible regexps now. Won't kill special chars
 * outside of a [code]...[/code] block now, and all BBCode tags
 * are implemented.
 * Note: the "i" matching switch is used, so BBCode tags are
 * case-insensitive.
 *
 * obsolete function - we have bbcode
 *
 */
function Dizkus_bbdecode($message)
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
    $matches    = array();
    $matchCount = preg_match_all("#<!-- BBCode ulist Start --><UL>(.*?)</UL><!-- BBCode ulist End -->#s", $message, $matches);

    for ($i = 0; $i < $matchCount; $i++)
    {
        $currMatchTextBefore = preg_quote($matches[1][$i]);
        $currMatchTextAfter  = preg_replace("#<LI>#s", "[*]", $matches[1][$i]);

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

    return ($message);
}

/**
 * Nathan Codding - Feb 6, 2001
 * Reverses the effects of make_clickable(), for use in editpost.
 * - Does not distinguish between "www.xxxx.yyyy" and "http://aaaa.bbbb" type URLs.
 *
 *
 * obsolete function - we have bbclick
 */
function Dizkus_undo_make_clickable($text)
{
    $text = preg_replace("#<!-- BBCode auto-link start --><a href=\"(.*?)\" target=\"_blank\">.*?</a><!-- BBCode auto-link end -->#i", "\\1", $text);
    $text = preg_replace("#<!-- BBcode auto-mailto start --><a href=\"mailto:(.*?)\">.*?</a><!-- BBCode auto-mailto end -->#i", "\\1", $text);
    return $text;
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
function allowedtoseecategoryandforum($category_id, $forum_id, $user_id = null)
{
    return SecurityUtil::checkPermission('Dizkus::', $category_id . ':' . $forum_id . ':', ACCESS_OVERVIEW, $user_id);
}

/**
 * allowedtoreadcategoryandforum
 */
function allowedtoreadcategoryandforum($category_id, $forum_id, $user_id = null)
{
    return SecurityUtil::checkPermission('Dizkus::', $category_id . ':' . $forum_id . ':', ACCESS_READ, $user_id);
}

/**
 * allowedtowritetocategoryandforum
 */
function allowedtowritetocategoryandforum($category_id, $forum_id, $user_id = null)
{
    return SecurityUtil::checkPermission('Dizkus::', $category_id . ':' . $forum_id . ':', ACCESS_COMMENT, $user_id);
}

/**
 * allowedtomoderatecategoryandforum
 */
function allowedtomoderatecategoryandforum($category_id, $forum_id, $user_id = null)
{
    return SecurityUtil::checkPermission('Dizkus::', $category_id . ':' . $forum_id . ':', ACCESS_MODERATE, $user_id);
}

/**
 * allowedtoadmincategoryandforum
 */
function allowedtoadmincategoryandforum($category_id, $forum_id, $user_id = null)
{
    return SecurityUtil::checkPermission('Dizkus::', "{$category_id}:{$forum_id}:", ACCESS_ADMIN, $user_id);
}

/**
 * sorting categories by cat_order (this is a VARCHAR, so we need this function for sorting)
 *
 */
function cmp_catorder($a, $b)
{
    return (int)$a['cat_order'] > (int)$b['cat_order'];
}

/**
 * dzk_replacesignature
 *
 */
function dzk_replacesignature($text, $signature='')
{
    $removesignature = ModUtil::getVar('Dizkus', 'removesignature');
    if ($removesignature == 'yes') {
        $signature = '';
    }

    if (!empty($signature)){
        $sigstart = stripslashes(ModUtil::getVar('Dizkus', 'signature_start'));
        $sigend   = stripslashes(ModUtil::getVar('Dizkus', 'signature_end'));
        $text = preg_replace("/\[addsig]$/", "\n\n" . $sigstart . $signature . $sigend, $text);
    } else {
        $text = preg_replace("/\[addsig]$/", '', $text);
    }

    return $text;
}

/**
 * mailcronecho
 */
function mailcronecho($text, $debug)
{
    echo $text;
    if ($debug==true) {
        echo '<br />';
    }
    flush();
    return;
}

/**
 * dzkVarPrepHTMLDisplay
 * removes the  [code]...[/code] before really calling DataUtil::formatForDisplayHTML()
 */
function dzkVarPrepHTMLDisplay($text)
{
    // remove code tags
    $codecount1 = preg_match_all("/\[code(.*)\](.*)\[\/code\]/si", $text, $codes1);
    for ($i=0; $i < $codecount1; $i++) {
        $text = preg_replace('/(' . preg_quote($codes1[0][$i], '/') . ')/', " DIZKUSCODEREPLACEMENT{$i} ", $text, 1);
    }
    
    // the real work
    $text = nl2br(DataUtil::formatForDisplayHTML($text));
    
    // re-insert code tags
    for ($i = 0; $i < $codecount1; $i++) {
        $text = preg_replace("/ DIZKUSCODEREPLACEMENT{$i} /", $codes1[0][$i], $text, 1);
    }

    return $text;
}

/**
 * microtime_float
 * used for debug purposes only
 */
if (!function_exists('microtime_float'))
{
    function microtime_float()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }
}

/**
 * useragent_is_bot
 * check if the useragent is a bot (blacklisted)
 *
 * returns bool
 */
function useragent_is_bot()
{
    // check the user agent - if it is a bot, return immediately
    $robotslist = array ( 'ia_archiver',
                          'googlebot',
                          'mediapartners-google',
                          'yahoo!',
                          'msnbot',
                          'jeeves',
                          'lycos');
    $useragent = System::serverGetVar('HTTP_USER_AGENT');
    for ($cnt=0; $cnt < count($robotslist); $cnt++) {
        if (strpos(strtolower($useragent), $robotslist[$cnt]) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * dzk_getimagepath
 *
 * gets an path for a image - this is a copy of the img logic
 *
 * @params $image string the imagefile name
 * @returns an array of information for the imagefile:
 *         ['path']   string the path to the imagefile
 *         ['size']   string 'width="xx" height="yy"' as delivered by getimagesize, may be empty
 * or false on error
 */
function dzk_getimagepath($image=null)
{
    if (!isset($image)) {
        return false;
    }

    $result = array();

    // module
    $modname = ModUtil::getName();

    // language
    $lang =  DataUtil::formatForOS(ZLanguage::getLanguageCode());

    // theme directory
    $theme         = DataUtil::formatForOS(UserUtil::getTheme());
    $osmodname     = DataUtil::formatForOS($modname);
    // FIXME THIS IS DEPRECATED
    $cWhereIsPerso = WHERE_IS_PERSO;
    if (!(empty($cWhereIsPerso))) {
        $themelangpath = $cWhereIsPerso . "themes/$theme/templates/modules/$osmodname/images/$lang";
        $themepath     = $cWhereIsPerso . "themes/$theme/templates/modules/$osmodname/images";
        $corethemepath = $cWhereIsPerso . "themes/$theme/images";
    } else {
        $themelangpath = "themes/$theme/templates/modules/$osmodname/images/$lang";
        $themepath     = "themes/$theme/templates/modules/$osmodname/images";
        $corethemepath = "themes/$theme/images";
    }
    // module directory
    $modinfo       = ModUtil::getInfo(ModUtil::getIDFromName($modname));
    $osmoddir      = DataUtil::formatForOS($modinfo['directory']);
    $modlangpath   = "modules/$osmoddir/images/$lang";
    $modpath       = "modules/$osmoddir/images";
    $syslangpath   = "system/$osmoddir/images/$lang";
    $syspath       = "system/$osmoddir/images";

    $ossrc = DataUtil::formatForOS($image);

    // search for the image
    foreach (array($themelangpath,
                   $themepath,
                   $corethemepath,
                   $modlangpath,
                   $modpath,
                   $syslangpath,
                   $syspath) as $path) {
        if (file_exists("$path/$ossrc") && is_readable("$path/$ossrc")) {
            $result['path'] = "$path/$ossrc";
            break;
        }
    }

    if ($result['path'] == '') {
        return false;
    }

    if (function_exists('getimagesize')) {
        if (!$_image_data = @getimagesize($result['path'])) {
            // invalid image
            $result['size']  = '';
        } else {
            $result['size']  = $_image_data[3];
        }
    }

    return $result;
}

/**
 * dzkstriptags
 * strip all thml tags outside of [code][/code]
 *
 * @params  $text     string the text
 * @returns string    the sanitized text
 */
function dzkstriptags($text='')
{
    if (!empty($text) && (ModUtil::getVar('Dizkus', 'striptags') == 'yes')) {
        // save code tags
        $codecount = preg_match_all("/\[code(.*)\](.*)\[\/code\]/siU", $text, $codes);

        for ($i=0; $i < $codecount; $i++) {
            $text = preg_replace('/(' . preg_quote($codes[0][$i], '/') . ')/', " DZKSTREPLACEMENT{$i} ", $text, 1);
        }

        // strip all html
        $text = strip_tags($text);

        // replace code tags saved before
        for ($i = 0; $i < $codecount; $i++) {
            $text = preg_replace("/ DZKSTREPLACEMENT{$i} /", $codes[0][$i], $text, 1);
        }
    }

    return $text;
}

/**
 * sorting user lists by ['uname']
 */
function cmp_userorder($a, $b)
{
    return strcmp($a['uname'], $b['uname']);
}

/**
 * dzk_blacklist()
 * blacklist the users ip address if considered a spammer
 */
function dzk_blacklist()
{
    $ztemp = System::getVar('temp');
    $blacklistfile = $ztemp . '/Dizkus_spammer.txt';

    $fh = fopen($blacklistfile, 'a');
    if ($fh) {
        $ip = dzk_getip();
        $line = implode(',', array(strftime('%Y-%m-%d %H:%M'),
                                   $ip,
                                   System::serverGetVar('REQUEST_METHOD'),
                                   System::serverGetVar('REQUEST_URI'),
                                   System::serverGetVar('SERVER_PROTOCOL'),
                                   System::serverGetVar('HTTP_REFERRER'),
                                   System::serverGetVar('HTTP_USER_AGENT')));
        fwrite($fh, DataUtil::formatForStore($line) . "\n");                           
        fclose($fh);
    }

    return;
}

/**
 * check for valid ip address
 * original code taken form spidertrap
 * @author       Thomas Zeithaml <info@spider-trap.de>
 * @copyright    (c) 2005-2006 Spider-Trap Team
 */
function dzk_validip($ip) 
{
   if (!empty($ip) && ip2long($ip)!=-1) {
       $reserved_ips = array (
       array('0.0.0.0','2.255.255.255'),
       array('10.0.0.0','10.255.255.255'),
       array('127.0.0.0','127.255.255.255'),
       array('169.254.0.0','169.254.255.255'),
       array('172.16.0.0','172.31.255.255'),
       array('192.0.2.0','192.0.2.255'),
       array('192.168.0.0','192.168.255.255'),
       array('255.255.255.0','255.255.255.255')
       );

       foreach ($reserved_ips as $r) {
           $min = ip2long($r[0]);
           $max = ip2long($r[1]);
           if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
       }
       return true;
   } else {
       return false;
   }
}

/**
 * get the users ip address
 * changes: replaced references to $_SERVER with System::serverGetVar()
 * original code taken form spidertrap
 * @author       Thomas Zeithaml <info@spider-trap.de>
 * @copyright    (c) 2005-2006 Spider-Trap Team
 */
function dzk_getip()
{
   if (dzk_validip(System::serverGetVar("HTTP_CLIENT_IP"))) {
       return System::serverGetVar("HTTP_CLIENT_IP");
   }

   foreach (explode(',', System::serverGetVar("HTTP_X_FORWARDED_FOR")) as $ip) {
       if (dzk_validip(trim($ip))) {
           return $ip;
       }
   }

   if (dzk_validip(System::serverGetVar("HTTP_X_FORWARDED"))) {
       return System::serverGetVar("HTTP_X_FORWARDED");
   } elseif (dzk_validip(System::serverGetVar("HTTP_FORWARDED_FOR"))) {
       return System::serverGetVar("HTTP_FORWARDED_FOR");
   } elseif (dzk_validip(System::serverGetVar("HTTP_FORWARDED"))) {
       return System::serverGetVar("HTTP_FORWARDED");
   } elseif (dzk_validip(System::serverGetVar("HTTP_X_FORWARDED"))) {
       return System::serverGetVar("HTTP_X_FORWARDED");
   } else {
       return System::serverGetVar("REMOTE_ADDR");
   }
}

/**
 * dzk_available
 * check if Dizkus is available
 *
 * @params deliverhtml     boolean, return html or boolean if forum is turned off, default true=html, use false in Ajax functions
 *return html or boolean
 */
function dzk_available($deliverhtml = true)
{
    if ((ModUtil::getVar('Dizkus', 'forum_enabled') == 'no') && !SecurityUtil::checkPermission('Dizkus::', '::', ACCESS_ADMIN)) {
        if ($deliverhtml == true) {
            $render = Zikula_View::getInstance('Dizkus', true, 'dizkus_disabled', true);
            return $render->fetch('dizkus_disabled.tpl');
        } else {
            return false;
        }
    }

    return true;
}

/**
 * dzk is an image
 * check if a filename is an image or not
 */
function dzk_isimagefile($filepath)
{
    if (function_exists('getimagesize') && @getimagesize($filepath) <> false) {
        return true;
    }

    if (preg_match('/^(.*)\.(gif|jpg|jpeg|png)/si', $filepath)) {
        return true;
    }

    return false;
}
