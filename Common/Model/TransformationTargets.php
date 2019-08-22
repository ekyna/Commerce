<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;

/**
 * Class TransformationTargets
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TransformationTargets
{
    const TARGET_CART  = 'cart';
    const TARGET_QUOTE = 'quote';
    const TARGET_ORDER = 'order';


    /**
     * Returns the available targets for the given sale.
     *
     * @param SaleInterface $sale
     * @param bool          $duplicate
     *
     * @return array
     */
    static public function getTargetsForSale(SaleInterface $sale, bool $duplicate): array
    {
        if ($sale instanceof CartInterface) {
            $targets = [static::TARGET_ORDER, static::TARGET_QUOTE];

            if ($duplicate) {
                $targets[] = static::TARGET_CART;
            }

            return $targets;
        }

        if ($sale instanceof OrderInterface) {
            if ($sale->getState() === OrderStates::STATE_NEW) {
                $targets = [static::TARGET_QUOTE];
            } else {
                $targets = [];
            }

            if ($duplicate) {
                $targets[] = static::TARGET_ORDER;
            }

            return $targets;
        }

        if ($sale instanceof QuoteInterface) {
            $targets = [static::TARGET_ORDER];

            if ($duplicate) {
                $targets[] = static::TARGET_QUOTE;
            }

            return $targets;
        }

        throw new InvalidArgumentException("Unexpected sale type.");
    }

    /**
     * Returns whether the target is validfor the sale.
     *
     * @param string        $target
     * @param SaleInterface $sale
     * @param bool          $duplicate
     *
     * @return bool
     */
    static public function isValidTargetForSale(string $target, SaleInterface $sale, bool $duplicate): bool
    {
        return in_array($target, static::getTargetsForSale($sale, $duplicate), true);
    }

    /**
     * Disabled constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
