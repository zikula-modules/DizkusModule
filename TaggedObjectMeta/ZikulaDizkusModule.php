<?php

declare(strict_types=1);

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\TaggedObjectMeta;

use DateUtil;
use ModUtil;
use Zikula\Core\UrlInterface;
use Zikula\DizkusModule\Manager\ForumManager;
use Zikula\DizkusModule\Manager\TopicManager;
use ZLanguage;

class ZikulaDizkusModule extends \Tag_AbstractTaggedObjectMeta
{
    public function __construct($objectId, $areaId, $module, $urlString = null, UrlInterface $urlObject = null)
    {
        parent::__construct($objectId, $areaId, $module, $urlString, $urlObject);
        $this->setObjectTitle('');
        // default to empty
        $route = $urlObject->getRoute();
        $args = $urlObject->getArgs();
        if (false !== mb_strpos($route, 'viewtopic')) {
            // item is post or topic
            if (isset($args['topic'])) {
                $managedTopic = new TopicManager($args['topic']);
                // get forum for perm check
                $perms = $managedTopic->getPermissions();
                if ($perms['see']) {
                    $this->setObjectDate($managedTopic->get()->getTopic_time());
                    $this->setObjectTitle($managedTopic->getTitle());
                }
            }
        } else {
            // item is forum
            $forumid = $args['forum'] ?? null;
            if (!isset($forumid)) {
                $forumid = $args['viewcat'] ?? null;
            }
            if (isset($forumid)) {
                $managedForum = new ForumManager($forumid);
                // perm check
                $perms = $managedForum->getPermissions();
                if ($perms['see']) {
                    $this->setObjectDate($managedForum->get()->getLast_post()->getPost_time());
                    $this->setObjectTitle($managedForum->get()->getName());
                }
            }
        }
    }

    public function setObjectTitle($title)
    {
        $this->title = $title;
    }

    public function setObjectDate($date)
    {
        $this->date = DateUtil::formatDatetime($date, 'datetimebrief');
    }

    public function setObjectAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Override the method to present specialized link
     * @return string
     */
    public function getPresentationLink()
    {
        $title = $this->getTitle();
        $date = $this->getDate();
        $link = null;
        if (!empty($title)) {
            $dom = ZLanguage::getModuleDomain('ZikulaDizkusModule');
            $topiclabel = __('topic', $dom);
            $forumlabel = __('forum', $dom);
            $urlObject = $this->getUrlObject();
            $label = 'viewtopic' === $urlObject->getAction() ? $topiclabel : $forumlabel;
            $modinfo = ModUtil::getInfoFromName('ZikulaDizkusModule');
            $link = "{$modinfo['displayname']} {$label}: <a href='{$urlObject->getUrl()}'>{$title}</a> ({$date})";
        }

        return $link;
    }
}
