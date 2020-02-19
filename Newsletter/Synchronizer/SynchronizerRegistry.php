<?php

namespace Ekyna\Component\Commerce\Newsletter\Synchronizer;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

/**
 * Class SynchronizerRegistry
 * @package Ekyna\Component\Commerce\Newsletter\Synchronizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SynchronizerRegistry
{
    /**
     * @var ContainerInterface
     */
    private $locator;

    /**
     * @var string[]
     */
    private $names;


    /**
     * Constructor.
     *
     * @param ContainerInterface $locator
     * @param string[]             $names
     */
    public function __construct(ContainerInterface $locator, array $names)
    {
        $this->locator = $locator;
        $this->names = $names;
    }

    /**
     * Returns whether the newsletter synchronizer exists by its name.
     *
     * @param string $synchronizer The newsletter synchronizer name.
     *
     * @return bool
     */
    public function has(string $synchronizer): bool
    {
        return $this->locator->has($synchronizer);
    }

    /**
     * Returns whether the newsletter synchronizer.
     *
     * @param string $synchronizer
     *
     * @return SynchronizerInterface
     */
    public function get(string $synchronizer): SynchronizerInterface
    {
        if (!$this->has($synchronizer)) {
            throw new RuntimeException("Unknown newsletter synchronizer '$synchronizer'.");
        }

        return $this->locator->get($synchronizer);
    }

    /**
     * Returns the synchronizers names.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return $this->names;
    }
}
