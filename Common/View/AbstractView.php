<?php

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class AbstractView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractView
{
    /**
     * @var array
     */
    public $vars = [
        'classes' => '',
        'attr'    => [],
    ];


    /**
     * Adds the css classes to the view.
     *
     * @param string $class
     *
     * @return $this
     */
    public function addClass($class)
    {
        $classes = $this->getClasses();

        if (!in_array($class, $classes)) {
            $classes[] = $class;
        }

        $this->setClasses($classes);

        return $this;
    }

    /**
     * Removes the css classes from the view.
     *
     * @param string $class
     *
     * @return $this
     */
    public function removeClass($class)
    {
        $classes = $this->getClasses();

        if (false !== $index = array_search($class, $classes)) {
            unset($classes[$index]);
        }

        $this->setClasses($classes);

        return $this;
    }

    /**
     * Returns the css classes.
     *
     * @return array
     */
    private function getClasses()
    {
        return explode(' ', trim($this->vars['classes']));
    }

    /**
     * Sets the css classes.
     *
     * @param array $classes
     */
    private function setClasses(array $classes)
    {
        if (!empty($classes)) {
            $this->vars['classes'] = ' ' . trim(implode(' ', $classes));
        } else {
            $this->vars['classes'] = '';
        }
    }
}
