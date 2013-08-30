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
 * This class provides a handler to report posts.
 */
class Dizkus_Form_Handler_User_Report extends Zikula_Form_AbstractHandler
{

    /**
     * post
     *
     * @var Dizkus_Manager_Post
     */
    private $_post;

    /**
     * Setup form.
     *
     * @param Zikula_Form_View $view Current Zikula_Form_View instance.
     *
     * @return boolean
     *
     * @throws Zikula_Exception_Forbidden If the current user does not have adequate permissions to perform this function.
     */
    public function initialize(Zikula_Form_View $view)
    {
        if (!ModUtil::apiFunc($this->name, 'Permission', 'canRead')) {
            throw new Zikula_Exception_Forbidden(LogUtil::getErrorMsgPermission());
        }
        // get the input
        $id = (int) $this->request->query->get('post');

        if (!isset($id)) {
            return LogUtil::registerError($this->__('Error! Missing post id.'), null, ModUtil::url('Dizkus', 'user', 'index'));
        }

        $this->_post = new Dizkus_Manager_Post($id);
        $view->assign('post', $this->_post->get());
        $view->assign('notify', true);
        list($rankimages, $ranks) = ModUtil::apiFunc($this->name, 'Rank', 'getAll', array('ranktype' => Dizkus_Entity_Rank::TYPE_POSTCOUNT));
        $this->view->assign('ranks', $ranks);

        return true;
    }

    /**
     * Handle form submission.
     *
     * @param Zikula_Form_View $view  Current Zikula_Form_View instance.
     * @param array            &$args Arguments.
     *
     * @return bool|void
     */
    public function handleCommand(Zikula_Form_View $view, &$args)
    {
        if ($args['commandName'] == 'cancel') {
            $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $this->_post->getTopicId(), 'start' => 1), null, 'pid' . $this->_post->getId());

            return $view->redirect($url);
        }

        // check for valid form
        if (!$view->isValid()) {
            return false;
        }

        $data = $view->getValues();

        // some spam checks:
        // - remove html and compare with original comment
        // - use censor and compare with original comment
        // if only one of this comparisons fails -> trash it, it is spam.
        if (!UserUtil::isLoggedIn()) {
            if (strip_tags($data['comment']) <> $data['comment']) {
                // possibly spam, stop now
                // get the users ip address and store it in zTemp/Dizkus_spammers.txt
                $this->dzk_blacklist();
                // set 403 header and stop
                header('HTTP/1.0 403 Forbidden');
                System::shutDown();
            }
        }

        ModUtil::apiFunc('Dizkus', 'notify', 'notify_moderator', array('post' => $this->_post->get(),
            'comment' => $data['comment']));

        $start = ModUtil::apiFunc('Dizkus', 'user', 'getTopicPage', array('replyCount' => $this->_post->get()->getTopic()->getReplyCount()));

        $url = ModUtil::url('Dizkus', 'user', 'viewtopic', array('topic' => $this->_post->getTopicId(),
                    'start' => $start));

        return $view->redirect($url);
    }

    /**
     * dzk_blacklist()
     * blacklist the users ip address if considered a spammer
     */
    private function dzk_blacklist()
    {
        $ztemp = System::getVar('temp');
        $blacklistfile = $ztemp . '/Dizkus_spammer.txt';

        $fh = fopen($blacklistfile, 'a');
        if ($fh) {
            $ip = $this->dzk_getip();
            $line = implode(',', array(strftime('%Y-%m-%d %H:%M:%S'),
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
    private function dzk_validip($ip)
    {
        if (!empty($ip) && ip2long($ip) != -1) {
            $reserved_ips = array(
                array('0.0.0.0', '2.255.255.255'),
                array('10.0.0.0', '10.255.255.255'),
                array('127.0.0.0', '127.255.255.255'),
                array('169.254.0.0', '169.254.255.255'),
                array('172.16.0.0', '172.31.255.255'),
                array('192.0.2.0', '192.0.2.255'),
                array('192.168.0.0', '192.168.255.255'),
                array('255.255.255.0', '255.255.255.255')
            );

            foreach ($reserved_ips as $r) {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);
                if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max))
                    return false;
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
    private function dzk_getip()
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

}
