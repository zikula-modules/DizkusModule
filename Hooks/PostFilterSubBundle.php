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

namespace Zikula\DizkusModule\Hooks;

use Zikula\Bundle\HookBundle\Category\FilterHooksCategory;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * PostFilterSubBundle
 *
 * @author Kaik
 */
class PostFilterSubBundle extends AbstractSubBundle
{
    const FILTER = 'dizkus.filter_hooks.post.filter';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        parent::__construct();
    }

    public function getOwner()
    {
        return 'ZikulaDizkusModule';
    }

    public function getCategory()
    {
        return FilterHooksCategory::NAME;
    }

    public function getTitle()
    {
        return $this->translator->__('Dizkus post filter subscriber');
    }

    public function getEvents()
    {
        return [
            FilterHooksCategory::TYPE_FILTER => self::FILTER,
        ];
    }
}
