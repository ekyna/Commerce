<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
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
     *
     * @return array
     */
    static public function getTargetsForSale(SaleInterface $sale)
    {
        if ($sale instanceof CartInterface) {
            return [static::TARGET_ORDER, static::TARGET_QUOTE];
        } elseif ($sale instanceof OrderInterface) {
            return [static::TARGET_QUOTE/*, static::TARGET_CART*/];
        } elseif ($sale instanceof QuoteInterface) {
            return [static::TARGET_ORDER/*, static::TARGET_CART*/];
        }

        throw new InvalidArgumentException("Unexpected sale type.");
    }

    /**
     * Returns whether the target is validfor the sale.
     *
     * @param string        $target
     * @param SaleInterface $sale
     *
     * @return bool
     */
    static public function isValidTargetForSale($target, SaleInterface $sale)
    {
        return in_array($target, static::getTargetsForSale($sale), true);
    }
}
