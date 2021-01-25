<?php

declare(strict_types=1);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Zikula\DizkusModule\ImportHandler;

/**
 * Description of AbstractImport
 *
 * @author Kaik
 */
class Database_ImportHandler extends AbstractImportHandler
{
    public function getTitle()
    {
        return $this->translator->trans('Database handler', [], 'zikuladizkusmodule');
    }

    public function getDescription()
    {
        return $this->translator->trans('Import data from database tables', [], 'zikuladizkusmodule');
    }

    public function getStatus()
    {
    }
}
