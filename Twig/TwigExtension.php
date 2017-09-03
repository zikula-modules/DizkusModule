<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 *
 * @see https://github.com/zikula-modules/Dizkus
 *
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

namespace Zikula\DizkusModule\Twig;

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
            new \Twig_SimpleFunction('getPostManager', [$this, 'getPostManager']),
            new \Twig_SimpleFunction('getForumManager', [$this, 'getForumManager']),
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('viewTopicLink', [$this, 'viewTopicLink'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('transform', [$this, 'transform'], ['is_safe' => ['html']]),
        ];
    }

    public function getPostManager($post)
    {
        return $this->container->get('zikula_dizkus_module.post_manager')->getManager(null, $post);
    }

    public function getForumManager($forum)
    {
        return $this->container->get('zikula_dizkus_module.forum_manager')->getManager(null, $forum);
    }

    public function getSystemSetting($settingName = false)
    {
        return $this->container->get('zikula_extensions_module.api.variable')->get('ZConfig', $settingName);
    }

    public function lastTopicUrl($topic)
    {
        // @todo recreate in template
        if (!$topic instanceof TopicEntity) {
            return false;
        }

        $urlParams = [
            'topic' => $topic->getId(),
            'start' => $this->container->get('zikula_dizkus_module.topic_manager')->getTopicPage($topic->getReplyCount()),
        ];
        $url = $this->container->get('router')->generate('zikuladizkusmodule_topic_viewtopic', $urlParams).'#post/'.$topic->getLast_post()->getId();

        return $url;
    }

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
