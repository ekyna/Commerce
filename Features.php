<?php

namespace Ekyna\Component\Commerce;

use Ekyna\Component\Commerce\Exception\UnexpectedValueException;

/**
 * Class Features
 * @package Ekyna\Component\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Features
{
    public const COUPON = 'coupon';
    // TODO (move) public const SUPPORT = 'support';

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_replace([
            self::COUPON        => [
                'enabled' => false,
            ],
        ], $config);
    }

    /**
     * Returns whether the given feature is enabled.
     *
     * @param string $feature
     *
     * @return bool
     */
    public function isEnabled(string $feature): bool
    {
        if (!isset($this->config[$feature])) {
            throw new UnexpectedValueException("Unknown feature '$feature'.");
        }

        return $this->config[$feature]['enabled'];
    }
}
