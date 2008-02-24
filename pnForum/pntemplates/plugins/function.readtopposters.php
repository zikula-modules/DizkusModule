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

/**
 * readtopposters
 * reads the top $maxposters users depending on their post count and assigns them in the
 * variable topposters and the number of them in toppostercount
 *
 *@params maxposters (int) number of users to read, default = 3
 *
 */
function smarty_function_readtopposters($params, &$smarty) 
{
    extract($params); 
	unset($params);

    Loader::includeOnce('modules/pnForum/common.php');
    // get some enviroment
    list($dbconn, $pntable) = pnfOpenDB();

    $postermax = (!empty($maxposters)) ? $maxposters : 3;

    $sql = "SELECT user_id,user_posts
          FROM ".$pntable['pnforum_users']." 
          WHERE user_id <> 1
          AND user_posts > 0
          ORDER BY user_posts DESC";

    $result = pnfSelectLimit($dbconn, $sql, $postermax, false, __FILE__, __LINE__);
    $result_postermax = $result->PO_RecordCount();
    if ($result_postermax <= $postermax) {
      $postermax = $result_postermax;
    }

    $topposters = array();
    if($result->RecordCount()>0) {
        for (; !$result->EOF; $result->MoveNext()) {
            list($user_id, $user_posts) = $result->fields;
            $topposter = array();
            $topposter['user_id'] = $user_id;
            $topposter['user_name'] = DataUtil::formatForDisplay(pnUserGetVar('uname', $user_id));
            $topposter['user_posts'] = $user_posts;
            array_push($topposters, $topposter);
        }
    }
    pnfCloseDB($result);
    $smarty->assign('toppostercount', count($topposters));
    $smarty->assign('topposters', $topposters);
    return;
}
