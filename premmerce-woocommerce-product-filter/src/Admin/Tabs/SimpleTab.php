<?php

namespace Premmerce\Filter\Admin\Tabs;

use Premmerce\Filter\Admin\Tabs\Base\TabInterface;

class SimpleTab implements TabInterface
{
    private $name;

    private $label;

    private $renderCallback;

    private $validCallback;

    /**
     * SimpleTab constructor.
     *
     * @param string        $name
     * @param string        $label
     * @param callable      $renderCallback
     * @param callable|null $validCallback Untyped: PHP 8.4 deprecates an implicitly nullable
     *                                     callable, and ?callable needs PHP 7.1.
     */
    public function __construct($name, $label, callable $renderCallback, $validCallback = null)
    {
        $this->name           = $name;
        $this->label          = $label;
        $this->renderCallback = $renderCallback;
        $this->validCallback  = $validCallback;
    }

    /**
     * Register hooks
     */
    public function init()
    {
    }

    /**
     * Render tab content
     */
    public function render()
    {
        call_user_func($this->renderCallback);
    }

    /**
     * Returns tab label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns unique tab name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Is tab valid to render
     *
     * @return bool
     */
    public function valid()
    {
        if (is_callable($this->validCallback)) {
            return call_user_func($this->validCallback);
        }

        return true;
    }
}
