<?php

namespace Ekyna\Component\Commerce\Newsletter\Webhook;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

/**
 * Class HandlerRegistry
 * @package Ekyna\Component\Commerce\Newsletter\Webhook
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class HandlerRegistry
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
     * Returns whether the newsletter webhook handler exists by its name.
     *
     * @param string $handler The newsletter webhook handler name.
     *
     * @return bool
     */
    public function has(string $handler): bool
    {
        return $this->locator->has($handler);
    }

    /**
     * Returns whether the newsletter webhook handler.
     *
     * @param string $handler
     *
     * @return HandlerInterface
     */
    public function get(string $handler): HandlerInterface
    {
        if (!$this->has($handler)) {
            throw new RuntimeException("Unknown newsletter webhook handler '$handler'.");
        }

        return $this->locator->get($handler);
    }

    /**
     * Returns the webhook handlers names.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return $this->names;
    }
}
