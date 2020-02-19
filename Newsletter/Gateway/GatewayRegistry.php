<?php

namespace Ekyna\Component\Commerce\Newsletter\Gateway;

use Ekyna\Component\Commerce\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

/**
 * Class GatewayRegistry
 * @package Ekyna\Component\Commerce\Newsletter\Gateway
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GatewayRegistry
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
     * Returns whether the newsletter gateway exists by its name.
     *
     * @param string $gateway The newsletter gateway name.
     *
     * @return bool
     */
    public function has(string $gateway): bool
    {
        return $this->locator->has($gateway);
    }

    /**
     * Returns whether the newsletter gateway.
     *
     * @param string $gateway
     *
     * @return GatewayInterface
     */
    public function get(string $gateway): GatewayInterface
    {
        if (!$this->has($gateway)) {
            throw new RuntimeException("Unknown newsletter gateway '$gateway'.");
        }

        return $this->locator->get($gateway);
    }

    /**
     * Returns the gateways names.
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return $this->names;
    }
}
