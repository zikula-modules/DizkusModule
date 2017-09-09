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

namespace Zikula\DizkusModule\ImportHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * AbstractImportHanlder.
 *
 * @author Kaik
 */
abstract class AbstractImportHandler implements ImportHandlerInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EngineInterface
     */
    protected $renderEngine;

    public function __construct(TranslatorInterface $translator, FormFactory $formFactory, EntityManagerInterface $em, EngineInterface $renderEngine)
    {
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->renderEngine = $renderEngine;
        $this->em = $em;
    }

    public function getId()
    {
        return strtolower($this->getType());
    }

    /**
     * @return string
     */
    public function getSettingsForm()
    {
        $form = 'Zikula\\DizkusModule\\Form\\Import\\' . $this->getType() . 'Type';

        return $form;
    }

    /**
     * @return string
     */
    private function getType()
    {
        $type = get_class($this);
        $type = substr($type, strrpos($type, '\\') + 1, -strlen('_ImportHandler'));

        return $type;
    }

    /*
     * Check current tables.
     *
     * @return array
     */
    public function getCurrentDataCount()
    {
        $connection = $this->em->getConnection();
        $sql = 'SELECT count(*) AS total FROM dizkus_forums';
        $statement = $connection->prepare($sql);
        $statement->execute();
        //we could add all content count but lets react only for forums
        $content_count = [];
        $content_count['forums'] = (int) $statement->fetchColumn();
        $content_count['total'] = $content_count['forums'];
 
        return $content_count;
    }

    /*
     * Remove current data.
     *
     * @return array
     */
    public function removeContent($source)
    {
        switch ($source) {
            case 'users':
            // @todo finish data removall
            $data = 'done users';

            break;
            case 'forum':

            $data = 'done forum';

            break;
            case 'other':

            $data = 'done other';

            break;
        }

        return $data;
    }
}
