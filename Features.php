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
    public const BIRTHDAY   = 'birthday';
    public const NEWSLETTER = 'newsletter';
    public const COUPON     = 'coupon';
    public const LOYALTY    = 'loyalty';
    public const SUPPORT    = 'support';

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
        // Must be kept in sync with:
        /** @see \Ekyna\Bundle\CommerceBundle\DependencyInjection\Configuration::addFeatureSection */
        $this->config = array_replace_recursive([
            self::BIRTHDAY   => [
                'enabled' => false,
            ],
            self::NEWSLETTER => [
                'enabled' => false,
            ],
            self::COUPON     => [
                'enabled' => false,
            ],
            self::LOYALTY    => [
                'enabled'     => false,
                'credit_rate' => 1,
                'credit'      => [
                    'birthday'   => 0,
                    'newsletter' => 0,
                    'review'     => 0,
                ],
                'coupons'     => [
                    /* Examples:
                    // 150pts grants a -20€ coupon valid for 2 months
                    150 => [
                        'mode'   => AdjustmentModes::MODE_FLAT,
                        'amount' => 20,
                        'period' => '+2 months',
                        'final'  => false,
                    ],
                    // 300pts grants a -15% coupon valid for 1 month
                    300 => [
                        'mode'   => AdjustmentModes::MODE_PERCENT,
                        'amount' => 15,
                        'period' => '+1 month',
                        'final'  => true, // Customer loyalty points will be reset to zero after this coupon generation
                    ],*/
                ],
            ],
            self::SUPPORT    => [
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
        return $this->getConfig($feature)['enabled'];
    }

    /**
     * Returns the feature configuration.
     *
     * @param string $feature
     *
     * @return array
     */
    public function getConfig(string $feature): array
    {
        if (!isset($this->config[$feature])) {
            throw new UnexpectedValueException("Unknown feature '$feature'.");
        }

        return $this->config[$feature];
    }
}
