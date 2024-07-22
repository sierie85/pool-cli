<?php
declare(strict_types=1);

namespace NAMESPACENAME;

use pool\classes\Core\Input\Input;

class GUI_Example extends \GUI_Module
{
    /**
     * @var int $superglobals
     * defines which superglobals should be used in this module.
     * Superglobal variables are passed to superglobals in the Input class.
     */
    protected int $superglobals = Input::GET | Input::POST;

    /**
     * @var array<string, string> $templates
     * files (templates) to be loaded, usually used with $this->Template->setVar(...) in the prepare function.
     * Defined as an associated array [handle => tplFile].
     */
    protected array $templates = [
        'stdout' => 'tpl_example.html',
    ];

    /**
     * @var array<int, string> $jsFiles
     * javascript files to be loaded, defined as indexed array
     */
    protected array $jsFiles = [];

    /**
     * @var array|string[] $cssFiles
     * css files to be loaded, defined as indexed array
     */
    protected array $cssFiles = [];

    /**
     * frontend control: run/execute the main logic and fill templates.
     */
    protected function prepare(): void
    {
        $this->Template->setVar([
            'GUI_CLASS_NAME' => $this->getName()
        ]);
    }

    /**
     * frontend control: Prepare data for building the content or responding to an ajax-call
     * Called once all modules and files have been loaded
     */
    protected function provision(): void
    {
    }

    /**
     * Method to register ajax calls
     *
     * @return void
     * @see GUI_Module::registerAjaxMethod()
     */
    protected function registerAjaxCalls(): void
    {
        // $this->registerAjaxMethod('handlerName', classMethod);
    }
}