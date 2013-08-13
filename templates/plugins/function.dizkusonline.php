<?php
/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @link      https://github.com/zikula-modules/Dizkus
 * @license   GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package   Dizkus
 */

/**
 * Zikula_View plugin
 * This file is a plugin for Zikula_View, the Zikula implementation of Smarty
 */


/**
 * Smarty function to read the users who are online
 * This function returns an array (ig assign is used) or four variables
 * numguests : number of guests online
 * numusers: number of users online
 * total: numguests + numusers
 * unames: array of 'uid', (int, userid), 'uname' (string, username) and 'admin' (boolean, true if users is a moderator)
 * Available parameters:
 *   - assign:       If set, the results are assigned to the corresponding variable
 *   - checkgroups:  If set, checks if the users found are in the moderator groups (perforance issue!) default is no group check
 * Example
 *   {dizkusonline assign="islogged"}
 *
 * @author       Frank Chestnut
 * @since        10/10/2005
 *
 * @param        array       $params    All attributes passed to this function from the template
 * @param        object      Zikula_View $view     Reference to the Smarty object
 *
 * @return       array
 */

function smarty_function_dizkusonline($params, Zikula_View $view)
{
    if (!isset($params['category_id'])) {
        $params['category_id'] = (isset($view->_tpl_vars['viewcat']) && $view->_tpl_vars['viewcat'] != -1) ? $view->_tpl_vars['viewcat'] : '';
    }
    if (!isset($params['forum_id'])) {
        $params['forum_id'] = isset($view->_tpl_vars['forum']) ? $view->_tpl_vars['forum'] : '';
    }

    $params['checkgroups'] = (isset($params['checkgroups'])) ? true : false;
    
    // set some defaults
    $numguests = 0;
    $numusers = 0;
    $unames = array();

    $moderators = ModUtil::apiFunc('Dizkus', 'moderators', 'get', array());

    /** @var $em Doctrine\ORM\EntityManager */
    $em = $view->getContainer()->get('doctrine.entitymanager');
    $dql = "SELECT s.uid, u.uname
            FROM Zikula\Module\UsersModule\Entity\UserSessionEntity s, Zikula\Module\UsersModule\Entity\UserEntity u
            WHERE s.lastused > :activetime
            AND s.uid >= :usertype
            AND s.uid = u.uid
            GROUP BY s.ipaddr, s.uid";

    $query = $em->createQuery($dql);
    $activetime = new DateTime(); // @todo maybe need to check TZ here
    $activetime->modify("-" . System::getVar('secinactivemins') . " minutes");
    $query->setParameter('activetime', $activetime);
    $query->setParameter('usertype', System::getVar('anonymoussessions') ? 1 : 2);
    // anonymoussessions = 1 for yes and 0 for no
    // so usertype = 1 if sessions are used for anonymous guests
    // usertype = 2 if sessions are NOT used for anonymous guests
//    echo "<pre>";
//    echo $query->getDQL();
//    echo "<br /><br />";
//    echo $query->getSQL();
//    echo "<br /><br />";
//    var_dump($activetime); echo "<br /><br />`";
//    var_dump(System::getVar('anonymoussessions'));
//    echo "`";
    $onlineusers = $query->execute(null, \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
//    echo "<br /><br />"; var_dump($onlineusers); echo "</pre>";
    
    if (is_array($onlineusers)) {
        $total = count($onlineusers);
        foreach ($onlineusers as $onlineuser) {
            if ($onlineuser['uid'] != 0) {
                $params['user_id'] = $onlineuser['uid'];
                $onlineuser['admin'] = (isset($moderators['users'][$onlineuser['uid']]) && $moderators['users'][$onlineuser['uid']] == $onlineuser['uname']) || ModUtil::apiFunc('Dikus', 'Permission', 'canAdministrate', $params);
                $unames[$onlineuser['uid']] = $onlineuser;
                $numusers++;
            } else {
                $numguests++;
            }
        }
    }

    if ($params['checkgroups'] == true) {
        foreach ($unames as $user) {
            if ($user['admin'] == false) {
                $groups = ModUtil::apiFunc('Groups', 'user', 'getusergroups', array('uid' => $user['uid']));

                foreach ($groups as $group) {
                    if (isset($moderators['groups'][$group['gid']])) {
                        $user['admin'] = true;
                    } else {
                        $user['admin'] = false;
                    }
                }
            }

            $users[$user['uid']] = array('uid'   => $user['uid'],
                                         'uname' => $user['uname'],
                                         'admin' => $user['admin']);

        }
        $unames = $users;
    }
    usort($unames, 'cmp_userorder');

    $dizkusonline['numguests'] = $numguests;

    $dizkusonline['numusers'] = $numusers;
    $dizkusonline['total'] = $total;
    $dizkusonline['unames'] = $unames;

    if (isset($params['assign'])) {
        $view->assign($params['assign'], $dizkusonline);
    } else {
        $view->assign($dizkusonline);
    }

    return;
}

/**
 * sorting user lists by ['uname']
 */
function cmp_userorder($a, $b)
{
    return strcmp($a['uname'], $b['uname']);
}
