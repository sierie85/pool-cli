<?php
declare(strict_types=1);

namespace CLI_Pool\Commands\CreateGUICommand;

use GUIModule\GUI_Module;

class GUI_Example extends \GUI_Module
{
    // superglobals

    protected array $templates = [
        'stdout' => 'tpl_example.html',
    ];

    protected function prepare(): void
    {
        $this->Template->setVar([
            'GUI_CLASS_NAME' => $this->getName()
        ]);
    }

    protected function provision(): void
    {
    }

    // ajax dings
}