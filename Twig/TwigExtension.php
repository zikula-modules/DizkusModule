<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\DizkusModule\Twig;

use DataUtil;
use ModUtil;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\DizkusModule\Entity\TopicEntity;

/**
 * Twig extension base class.
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ContainerInterface
     */
    private $translator;

    public function __construct(ContainerInterface $container = null)
    {
        $this->name = 'ZikulaDizkusModule';
        $this->container = $container;
        $this->translator = $this->container->get('translator');
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('countFreeTopics', [$this, 'countFreeTopics'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('favoritesStatus', [$this, 'favoritesStatus']),
            new \Twig_SimpleFunction('isFavorite', [$this, 'isFavorite']),
            new \Twig_SimpleFunction('isSubscribed', [$this, 'isSubscribed']),
            new \Twig_SimpleFunction('onlineUsers', [$this, 'onlineUsers']),
            new \Twig_SimpleFunction('getSystemSetting', [$this, 'getSystemSetting']),
            new \Twig_SimpleFunction('lastTopicUrl', [$this, 'lastTopicUrl']),
            new \Twig_SimpleFunction('userLoggedIn', [$this, 'userLoggedIn']),
            new \Twig_SimpleFunction('getRankByPostCount', [$this, 'getRankByPostCount']),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('viewTopicLink', [$this, 'viewTopicLink'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('transform', [$this, 'transform'], ['is_safe' => ['html']]),
        ];
    }

    public function countFreeTopics($id = 0)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $query = $this->container->get('doctrine.orm.entity_manager')->createQuery('SELECT COUNT(t.topic_id) FROM Zikula\DizkusModule\Entity\TopicEntity t WHERE t.forum=:fid');
        $query->setParameter('fid', $id);
        $count = $query->getSingleScalarResult();

        return $count;
    }

    public function favoritesStatus()
    {
        return $this->container->get('zikula_dizkus_module.favorites_helper')->getStatus();
    }

    public function isFavorite($forum, $user_id = null)
    {
        return $this->container->get('zikula_dizkus_module.favorites_helper')->isFavorite($forum, $user_id);
    }

    public function isSubscribed($forum, $user_id = null)
    {
        return $this->container->get('zikula_dizkus_module.forum_manager')->isSubscribed($forum, $user_id);
    }

    public function getSystemSetting($settingName = false)
    {
        return $this->container->get('zikula_extensions_module.api.variable')->get('ZConfig', $settingName);
    }

    public function userLoggedIn()
    {
        return $this->container->get('zikula_users_module.current_user')->isLoggedIn();
    }

    /**
     * Smarty function to read the users who are online
     * This function returns an array (ig assign is used) or four variables
     * numguests : number of guests online
     * numusers: number of users online
     * total: numguests + numusers
     * unames: array of 'uid', (int, userid), 'uname' (string, username) and 'admin' (boolean, true if users is a moderator)
     * Available parameters:
     *   - checkgroups:  If set, checks if the users found are in the moderator groups (perforance issue!) default is no group check.
     *
     * @author       Frank Chestnut
     *
     * @since        10/10/2005
     *
     * @param array                   $params All attributes passed to this function from the template
     * @param object      Zikula_View $view   Reference to the Smarty object
     *
     * @return array
     */
    public function onlineUsers($checkgroups = false)
    {
        // set some defaults
        $numguests = 0;
        $numusers = 0;
        $unames = [];

        $moderators = $this->container->get('zikula_dizkus_module.moderators_helper')->get(false);

        /** @var $em Doctrine\ORM\EntityManager */
        $dql = "SELECT s.uid, u.uname
                FROM Zikula\UsersModule\Entity\UserSessionEntity s, Zikula\UsersModule\Entity\UserEntity u
                WHERE s.lastused > :activetime
                AND (s.uid >= 2
                AND s.uid = u.uid)
                OR s.uid = 0
                GROUP BY s.ipaddr, s.uid";
        $query = $this->container->get('doctrine.orm.entity_manager')->createQuery($dql);
        $activetime = new \DateTime(); // @todo maybe need to check TZ here
        $activetime->modify('-'.$this->getSystemSetting('secinactivemins').' minutes');
        $query->setParameter('activetime', $activetime);

        $onlineusers = $query->getArrayResult();

        $total = 0;
        if (is_array($onlineusers)) {
            $total = count($onlineusers);
            foreach ($onlineusers as $onlineuser) {
                if ($onlineuser['uid'] != 0) {
                    $params['user_id'] = $onlineuser['uid'];
                    $onlineuser['admin'] = (isset($moderators['users'][$onlineuser['uid']])
                    && $moderators['users'][$onlineuser['uid']] == $onlineuser['uname'])
                    || $this->container->get('zikula_dizkus_module.security')->canAdministrate($params);
                    $unames[$onlineuser['uid']] = $onlineuser;
                    $numusers++;
                } else {
                    $numguests++;
                }
            }
        }

        $users = [];
        if ($checkgroups == true) {
            foreach ($unames as $user) {
                if ($user['admin'] == false) {
                    // @todo use service when ready
                    $groups = ModUtil::apiFunc('Groups', 'user', 'getusergroups', ['uid' => $user['uid']]);

                    foreach ($groups as $group) {
                        if (isset($moderators['groups'][$group['gid']])) {
                            $user['admin'] = true;
                        } else {
                            $user['admin'] = false;
                        }
                    }
                }

                $users[$user['uid']] = [
                    'uid'   => $user['uid'],
                    'uname' => $user['uname'],
                    'admin' => $user['admin'], ];
            }
            $unames = $users;
        }
        usort($unames, [$this, 'cmp_userorder']);

        $dizkusonline['numguests'] = $numguests;

        $dizkusonline['numusers'] = $numusers;
        $dizkusonline['total'] = $total;
        $dizkusonline['unames'] = $unames;

        return $dizkusonline;
    }

    /**
     * sorting user lists by ['uname'].
     */
    private function cmp_userorder($a, $b)
    {
        return strcmp($a['uname'], $b['uname']);
    }

    public function lastTopicUrl($topic)
    {
        // @todo replace with instance of check
        if (!$topic instanceof TopicEntity) {
            return false;
        }

        $urlParams = [
            'topic' => $topic->getTopic_id(),
            'start' => $this->container->get('zikula_dizkus_module.topic_manager')->getTopicPage($topic->getReplyCount()),
        ];
        $url = $this->container->get('router')->generate('zikuladizkusmodule_topic_viewtopic', $urlParams).'#pid'.$topic->getLast_post()->getPost_id();

        return $url;
    }

    /**
     * Smarty modifier to create a link to a topic.
     *
     * Available parameters:

     * Example
     *
     *   {$topic_id|viewtopiclink}
     *
     *
     * @author       Frank Schummertz
     * @author       The Dizkus team
     *
     * @since        16. Sept. 2003
     *
     * @param array $string the contents to transform
     *
     * @return string the modified output
     */
    public function viewTopicLink($topic_id = null, $subject = null, $forum_name = null, $class = '', $start = null, $last_post_id = null)
    {
        if (!isset($topic_id)) {
            return '';
        }

        $class = 'class="tooltips '.DataUtil::formatForDisplay($class).'"';

        $args = ['topic' => (int) $topic_id];
        if (isset($start)) {
            $args['start'] = (int) $start;
        }

        $url = $this->container->get('router')->generate('zikuladizkusmodule_topic_viewtopic', $args);
        if (isset($last_post_id)) {
            $url .= '#pid'.(int) $last_post_id;
        }

        // @todo - maybe allow title to be added as a parameter
        $title = $this->translator->__('go to topic');

        return "<a $class href='".DataUtil::formatForDisplay($url)."' title=".$title.">$subject</a>";
    }

    /**
     * printtopic_button plugin
     * adds the print topic button
     * requires the Printer theme.
     *
     * @param $forum_id int forum id
     * @param $topic_id int topic id
     */
//    function printTopicButton($forum_id, $topic_id)
//    {
//        //$dom = ZLanguage::getModuleDomain($dizkusModuleName);
//
//        if (ModUtil::apiFunc($dizkusModuleName, 'Permission', 'canRead', $params['forum'])) {
//            $themeinfo = ThemeUtil::getInfo(ThemeUtil::getIDFromName('Printer'));
//            if ($themeinfo['state'] == ThemeUtil::STATE_ACTIVE) {
//                $url = $view->getContainer()->get('router')->generate('zikuladizkusmodule_user_viewtopic', array('theme' => 'Printer', 'topic' => $params['topic_id']));
//                return '<a class="fa fa-print tooltips" title="' . DataUtil::formatForDisplay(__('Print topic', $dom)) . '" href="' . DataUtil::formatForDisplay($url) . '"></a>';
//            }
//        }
//
//        return '';
//    }

    /**
     * This part provides the api functions for rudimentary parsing of
     * bracket-tag code for [quote] and [code].
     */

    /**
     * transform only [quote] and [code] tags.
     */
    public function transform($message)
    {
        // pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
        // This is important; encode_quote() and encode_code() depend on it.
        $message = ' '.$message.' ';

        // If there is a "[" and a "]" in the message, process it.
        if ((strpos($message, '[') && strpos($message, ']'))) {
            // [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
            $message = $this->encode_code($message);

            // [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
            $message = $this->encode_quote($message);
        }

        // Remove added padding from the string..
        $message = substr($message, 1);
        $message = substr($message, 0, -1);

        return $message;
    }

    /**
     * Nathan Codding - Jan. 12, 2001.
     * modified again in 2013 when inserted into Dizkus
     * Performs [quote][/quote] encoding on the given string, and returns the results.
     * Any unmatched "[quote]" or "[/quote]" token will just be left alone.
     * This works fine with both having more than one quote in a message, and with nested quotes.
     * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
     *
     * Note: This function assumes the first character of $message is a space, which is added by
     * transform().
     */
    private function encode_quote($message)
    {
        // First things first: If there aren't any "[quote=" or "[quote] strings in the message, we don't
        // need to process it at all.
        if (!strpos(strtolower($message), '[quote=') && !strpos(strtolower($message), '[quote]')) {
            return $message;
        }

        $quotebody = '
<div class="dz-quote">
    <i class="fa fa-quote-left fa-4x text-more-muted"></i>
    <div class="inner">
        <div class="dz-quoteheader">%u</div>
        <blockquote class="dz-quotetext">%t</blockquote>
    </div>
    <i class="fa fa-quote-right fa-4x text-more-muted"></i>
</div>';

        $stack = [];
        $curr_pos = 1;
        while ($curr_pos && ($curr_pos < strlen($message))) {
            $curr_pos = strpos($message, '[', $curr_pos);

            // If not found, $curr_pos will be 0, and the loop will end.
            if ($curr_pos) {
                // We found a [. It starts at $curr_pos.
                // check if it's a starting or ending quote tag.
                $possible_start = substr($message, $curr_pos, 6);
                $possible_end_pos = strpos($message, ']', $curr_pos);
                $possible_end = substr($message, $curr_pos, $possible_end_pos - $curr_pos + 1);
                if (strcasecmp('[quote', $possible_start) == 0) {
                    // We have a starting quote tag.
                    // Push its position on to the stack, and then keep going to the right.
                    array_push($stack, $curr_pos);
                    ++$curr_pos;
                } elseif (strcasecmp('[/quote]', $possible_end) == 0) {
                    // We have an ending quote tag.
                    // Check if we've already found a matching starting tag.
                    if (count($stack) > 0) {
                        // There exists a starting tag.
                        // We need to do 2 replacements now.
                        $start_index = array_pop($stack);

                        // everything before the [quote=xxx] tag.
                        $before_start_tag = substr($message, 0, $start_index);

                        // find the end of the start tag
                        $start_tag_end = strpos($message, ']', $start_index);
                        $start_tag_len = $start_tag_end - $start_index + 1;
                        if ($start_tag_len > 7) {
                            $username = DataUtil::formatForDisplay(substr($message, $start_index + 7, $start_tag_len - 8));
                        } else {
                            $username = DataUtil::formatForDisplay($this->__('Quote'));
                        }

                        // everything after the [quote=xxx] tag, but before the [/quote] tag.
                        $between_tags = substr($message, $start_index + $start_tag_len, $curr_pos - ($start_index + $start_tag_len));
                        // everything after the [/quote] tag.
                        $after_end_tag = substr($message, $curr_pos + 8);

                        $quotetext = str_replace('%u', $username, $quotebody);
                        $quotetext = str_replace('%t', $between_tags, $quotetext);

                        $message = $before_start_tag.$quotetext.$after_end_tag;

                        // Now.. we've screwed up the indices by changing the length of the string.
                        // So, if there's anything in the stack, we want to resume searching just after it.
                        // otherwise, we go back to the start.
                        if (count($stack) > 0) {
                            $curr_pos = array_pop($stack);
                            array_push($stack, $curr_pos);
                            ++$curr_pos;
                        } else {
                            $curr_pos = 1;
                        }
                    } else {
                        // No matching start tag found. Increment pos, keep going.
                        ++$curr_pos;
                    }
                } else {
                    // No starting tag or ending tag.. Increment pos, keep looping.,
                    ++$curr_pos;
                }
            }
        } // while

        return $message;
    }

    /**
     * Nathan Codding - Jan. 12, 2001.
     * Frank Schummertz - Sept. 2004ff
     * modified again in 2013 when inserted into Dizkus
     * Performs [code][/code] transformation on the given string, and returns the results.
     * Any unmatched "[code]" or "[/code]" token will just be left alone.
     * This works fine with both having more than one code block in a message, and with nested code blocks.
     * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
     *
     * Note: This function assumes the first character of $message is a space, which is added by
     * transform().
     */
    private function encode_code($message)
    {
        $count = preg_match_all("#(\[code=*(.*?)\])(.*?)(\[\/code\])#si", $message, $code);
        // with $message="[code=php,start=25]php code();[/code]" the array $code now contains
        // [0] [code=php,start=25]php code();[/code]
        // [1] [code=php,start=25]
        // [2] php,start=25
        // [3] php code();
        // [4] [/code]

        if ($count > 0 && is_array($code)) {
            $codebodyblock = '<!--code--><div class="dz-code"><div class="dz-codeheader">%h</div><div class="dz-codetext">%c</div></div><!--/code-->';
            $codebodyinline = '<!--code--><code>%c</code><!--/code-->';

            for ($i = 0; $i < $count; $i++) {
                // the code in between incl. code tags
                $str_to_match = '/'.preg_quote($code[0][$i], '/').'/';

                $after_replace = trim($code[3][$i]);
                $containsLineEndings = !strstr($after_replace, PHP_EOL) ? false : true;

                if ($containsLineEndings) {
                    $after_replace = '<pre class="pre-scrollable">'.DataUtil::formatForDisplay($after_replace).'</pre>';
                    // replace %h with 'Code'
                    $codetext = str_replace('%h', $this->__('Code'), $codebodyblock);
                    // replace %c with code
                    $codetext = str_replace('%c', $after_replace, $codetext);
                    // replace %e with urlencoded code (prepared for javascript)
                    $codetext = str_replace('%e', urlencode(nl2br($after_replace)), $codetext);
                } else {
                    $after_replace = DataUtil::formatForDisplay($after_replace);
                    // replace %c with code
                    $codetext = str_replace('%c', $after_replace, $codebodyinline);
                    // replace %e with urlencoded code (prepared for javascript)
                    $codetext = str_replace('%e', urlencode(nl2br($after_replace)), $codetext);
                }

                $message = preg_replace($str_to_match, $codetext, $message);
            }
        }

        return $message;
    }

    /**
     * {getRankByPostCount posts=$post.poster.postCount ranks=$ranks }.
     *
     * @throws \InvalidArgumentException Thrown if the parameters do not meet requirements
     */
    public function getRankByPostCount($posts = 0)
    {
 
        list($rankimages, $ranks) = $this->container->get('zikula_dizkus_module.rank_helper')->getAll(['ranktype' => RankEntity::TYPE_POSTCOUNT]);

        $posterRank = $ranks[0];

        foreach ($ranks as $rank) {
            if (($posts >= $rank->getMinimumCount()) && ($posts <= $rank->getMaximumCount())) {
                $posterRank = $rank;
            }
        }

        return $posterRank;
    }

    /**
     * dizkus quote plugin.
     *
     *
     * Not used - javascript used instead
     *
     *
     * @param $uid    int user id
     * @param $text    string text to quote
     */
//    public function dzkquote($uid, $text)
//    {
//        if (empty($text)) {
//            return '';
//        }
//
//        if (!empty($uid)) {
//            $user = '='.UserUtil::getVar('uname', $uid);
//        } else {
//            $user = '';
//        }
//
//        // Convert linefeeds to a special string. This is necessary because this string will be in an onclick atrribute
//        // and therefore cannot have multiple lines.
//        $text = str_replace(["\r", "\n"], '_____LINEFEED_DIZKUS_____', addslashes($text));
//
//        return '[quote'.$user.']'.$text.'[/quote]';
//    }

    /**
     * Returns internal name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'zikuladizkusmodule_twigextension';
    }
}
