<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link http://code.zikula.org/dizkus
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

/**
 * readtopposters
 * reads the top $maxposters users depending on their post count and assigns them in the
 * variable topposters and the number of them in toppostercount
 *
 * @params maxposters (int) number of users to read, default = 3
 *
 */
function smarty_function_readtopposters($params, &$smarty) 
{
    $postermax = (!empty($params['maxposters'])) ? $params['maxposters'] : 3;

    $pntable = pnDBGetTables();
    $sql = "SELECT user_id,user_posts
          FROM ".$pntable['dizkus_users']." 
          WHERE user_id <> 1
          AND user_posts > 0
          ORDER BY user_posts DESC";

    $res = DBUtil::executeSQL($sql, -1, $postermax);
    $colarray = array('user_id', 'user_posts');
    $result    = DBUtil::marshallObjects($res, $colarray);

    $result_postermax = count($result);
    if ($result_postermax <= $postermax) {
      $postermax = $result_postermax;
    }

    $topposters = array();
    if (is_array($result) && !empty($result)) {
        foreach ($result as $topposter) {
            $topposter['user_name'] = DataUtil::formatForDisplay(pnUserGetVar('uname', $topposter['user_id']));
            array_push($topposters, $topposter);
        }
    }

    $smarty->assign('toppostercount', count($topposters));
    $smarty->assign('topposters', $topposters);

    return;
}
