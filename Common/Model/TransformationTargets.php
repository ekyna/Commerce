<?php

declare(strict_types=1);

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
    public const TARGET_CART = 'cart';
    public const TARGET_QUOTE = 'quote';
    public const TARGET_ORDER = 'order';


    /**
     * Returns the available targets for the given sale.
     *
     * @param SaleInterface $sale
     * @param bool          $duplicate
     *
     * @return array
     */
    public static function getTargetsForSale(SaleInterface $sale, bool $duplicate): array
    {
        if ($sale instanceof CartInterface) {
            $targets = [TransformationTargets::TARGET_ORDER, TransformationTargets::TARGET_QUOTE];

            if ($duplicate) {
                $targets[] = TransformationTargets::TARGET_CART;
            }

            return $targets;
        }

        if ($sale instanceof OrderInterface) {
            if ($duplicate) {
                $targets = [TransformationTargets::TARGET_ORDER, TransformationTargets::TARGET_QUOTE];
            } elseif ($sale->getState() === OrderStates::STATE_NEW) {
                $targets = [TransformationTargets::TARGET_QUOTE];
            } else {
                $targets = [];
            }

            return $targets;
        }

        if ($sale instanceof QuoteInterface) {
            $targets = [TransformationTargets::TARGET_ORDER];

            if ($duplicate) {
                $targets[] = TransformationTargets::TARGET_QUOTE;
            }

            return $targets;
        }

        throw new InvalidArgumentException('Unexpected sale type.');
    }

    /**
     * Returns whether the target is valid for the sale.
     */
    public static function isValidTargetForSale(string $target, SaleInterface $sale, bool $duplicate): bool
    {
        return in_array($target, TransformationTargets::getTargetsForSale($sale, $duplicate), true);
    }

    /**
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
