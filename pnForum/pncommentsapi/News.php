<?php
/**
 * pnForum
 *
 * @copyright (c) 2001-now, pnForum Development Team
 * @link http://www.pnforum.de
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package pnForum
 */

Loader::includeOnce('modules/pnForum/common.php');

/*
 * param: objectid
 */

function pnForum_commentsapi_News($args)
{
    extract($args);
    unset($args);

    list($dbconn, $pntable) = pnfOpenDB();
    $pnstoriestable = $pntable['stories'];
    $pnstoriescolumn = $pntable['stories_column'];
    $pntopicstable = $pntable['topics'];
    $pntopicscolumn = $pntable['topics_column'];

    $sql = "SELECT $pnstoriescolumn[bodytext],
                   $pnstoriescolumn[hometext],
                   $pnstoriescolumn[notes],
                   $pnstoriescolumn[title],
                   $pnstoriescolumn[topic],
                   $pnstoriescolumn[aid],
                   $pnstoriescolumn[format_type],
                   $pntopicscolumn[topicname]
            FROM   $pnstoriestable
            LEFT JOIN $pntopicstable ON $pnstoriescolumn[topic]=$pntopicscolumn[topicid]
            WHERE $pnstoriescolumn[sid] ='" . pnVarPrepForStore($objectid) . "'";
    $result = pnfExecuteSQL($dbconn, $sql, __FILE__, __LINE__);
    //echo $sql;
    //exit;

    if(!$result->EOF) {
        list($bodytext,
             $hometext,
             $notes,
             $title,
             $topic,
             $authorid,
             $format_type,
             $topicname) = $result->fields;
        pnfCloseDB($result);
    } else {
        return false;
    }

    // workaround for bug in AddStories html fixed on 11-05-2005
    $authorid = (int)$authorid;

    $link  = pnGetBaseURL() . 'index.php?name=News&file=article&sid=' . $objectid;
    $title = ($topicname<>'' ? $topicname.' - '.$title : $title);

    if(pnModIsHooked('pn_bbcode', 'pnForum')) {
        $notes = '[i]' . $notes . '[/i]';
        $link  = '[url=' . $link . ']' . _PNFORUM_BACKTOSUBMISSION . '[/url]';
    }

    $totaltext = $hometext . "\n\n" . $bodytext . "\n\n" . $notes . "\n\n" . $link . "\n\n";

    return array($title, $totaltext , $topic, $authorid);
}
