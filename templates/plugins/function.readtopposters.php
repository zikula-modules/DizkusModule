<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link https://github.com/zikula-modules/Dizkus
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

    ModUtil::dbInfoLoad('Settings');
    $ztable = DBUtil::getTables();
    $objcol = $ztable['objectdata_attributes_column'];
        
    $where = $objcol['attribute_name'] . "='dizkus_user_posts'";
    $orderby = $objcol['value'] . ' DESC';
    $topposters = DBUtil::selectObjectArray('objectdata_attributes', $where, $orderby, -1, $postermax);

    if (is_array($topposters) && !empty($topposters)) {
        for($i=0; $i < count($topposters); $i++) {
            $topposters[$i]['user_name'] = DataUtil::formatForDisplay(UserUtil::getVar('uname', $topposters[$i]['object_id']));
            // for BC reasons
            $topposters[$i]['user_posts'] = DataUtil::formatForDisplay($topposters[$i]['value']);
            $topposters[$i]['user_id']    = DataUtil::formatForDisplay($topposters[$i]['object_id']);
        }
    } else {
        $toppposters = array();
    }

    $smarty->assign('toppostercount', count($topposters));
    $smarty->assign('topposters', $topposters);

    return;
}
