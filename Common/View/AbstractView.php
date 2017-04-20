<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

/**
 * Class AbstractView
 * @package Ekyna\Component\Commerce\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractView
{
    public array $vars = [
        'attr' => [],
    ];

    /**
     * Adds the css classes to the view.
     */
    public function addClass(string $class): self
    {
        $classes = $this->getClasses();

        if (!in_array($class, $classes, true)) {
            $classes[] = $class;
        }

        $this->setClasses($classes);

        return $this;
    }

    /**
     * Removes the css classes from the view.
     */
    public function removeClass(string $class): self
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
     */
    private function getClasses(): array
    {
        if (isset($this->vars['attr']['class'])) {
            return explode(' ', trim($this->vars['attr']['class']));
        }

        return [];
    }

    /**
     * Sets the css classes.
     */
    private function setClasses(array $classes): void
    {
        if (!empty($classes)) {
            $this->vars['attr']['class'] = trim(implode(' ', $classes));
        } else {
            unset($this->vars['attr']['class']);
        }
    }
}
