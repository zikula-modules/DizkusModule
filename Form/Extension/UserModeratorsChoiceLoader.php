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

namespace Zikula\DizkusModule\Form\Extension;

use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * UserModeratorsChoiceLoader
 *
 * @author https://www.strehle.de/tim/weblog/archives/2016/02/24/1588
 * @author Kaik
 */
class UserModeratorsChoiceLoader implements ChoiceLoaderInterface
{
    // Currently selected choices
    protected $selected = [];

    /**
     * Constructor
     */
    public function __construct($builder)
    {
        if (is_object($builder) && ($builder instanceof FormBuilderInterface)) {
            // Let the form builder notify us about initial/submitted choices
            $builder->addEventListener(
                FormEvents::POST_SET_DATA,
                [$this, 'onFormPostSetData']
            );
        }
    }

    /**
     * Form submit event callback
     * Here we get notified about the submitted choices.
     * Remember them so we can add them in loadChoiceList().
     */
    public function onFormPostSetData(FormEvent $event)
    {
        $this->selected = [];
        $formdata = $event->getData();
        if (!is_object($formdata)) {
            return;
        }

        foreach ($formdata->getModeratorUsers() as $moderatorForumUser) {
            // in case of situation when zikula user is removed
            $uid = null === $moderatorForumUser->getForumUser()->getUser() ? null : $moderatorForumUser->getForumUser()->getUser()->getUid();
            if (!$uid) {
                continue;
            }

            $this->selected[$moderatorForumUser->getForumUser()->getUser()->getUname()] = (string) $uid;
        }
    }

    /**
     * Choices to be displayed in the SELECT element.
     * It's okay to not return all available choices, but the
     * selected/submitted choices (model values) must be
     * included.
     * Required by ChoiceLoaderInterface.
     */
    public function loadChoiceList($value = null)
    {
        return new ArrayChoiceList($this->selected);
    }

    /**
     * Validate submitted choices, and turn them from strings
     * (HTML option values) into other datatypes if needed
     * (not needed here since our choices are strings).
     * We're also using this place for creating new choices
     * from new values typed into the autocomplete field.
     * Required by ChoiceLoaderInterface.
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        return $values;
    }

    /**
     * Turn choices from other datatypes into strings (HTML option
     * values) if needed - we can simply return the choices as
     * they're strings already.
     * Required by ChoiceLoaderInterface.
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        // optimize when no data is preset
        if (empty($choices)) {
            return [];
        }

        $values = [];
        foreach ($choices as $value) {
            $values[] = (string) $value;
        }

        return $values;
    }
}
