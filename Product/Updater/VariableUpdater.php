<?php

namespace Ekyna\Component\Commerce\Product\Updater;

use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;
use Ekyna\Component\Commerce\Stock\Model\StockModes;
use Ekyna\Component\Commerce\Stock\Model\StockStates;

/**
 * Class VariableUpdater
 * @package Ekyna\Component\Commerce\Product\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariableUpdater
{
    /**
     * Updates the variable minimum price regarding to its variants.
     *
     * @param ProductInterface $variable
     *
     * @return bool Whether the variable has been changed or not.
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateMinPrice(ProductInterface $variable)
    {
        ProductTypes::assertVariable($variable);

        $variants = $variable->getVariants()->getIterator();
        if (0 == count($variants)) {
            if (0 != $variable->getNetPrice()) {
                $variable->setNetPrice(0);

                return true;
            }
            return false;
        }

        $minPrice = null;
        /** @var \Ekyna\Component\Commerce\Product\Model\ProductInterface $variant */
        foreach ($variants as $variant) {
            if (null === $minPrice || $minPrice > $variant->getNetPrice()) {
                $minPrice = $variant->getNetPrice();
            }
        }

        if (null !== $minPrice && 0 !== bccomp($variable->getNetPrice(), $minPrice, 5)) {
            $variable->setNetPrice($minPrice);

            return true;
        }

        return false;
    }

    /**
     * Updates the variable stock state.
     *
     * @param ProductInterface $variable
     *
     * @return bool Whether the variable has been changed or not.
     */
    public function updateStockState(ProductInterface $variable)
    {
        ProductTypes::assertVariable($variable);

        if (!$variable->getStockMode() === StockModes::MODE_ENABLED) {
            return false;
        }

        $state = StockStates::STATE_OUT_OF_STOCK;
        $variants = $variable->getVariants()->getIterator();
        /** @var \Ekyna\Component\Commerce\Product\Model\ProductInterface $variant */
        foreach ($variants as $variant) {
            if (StockStates::isBetterState($variant->getStockState(), $state)) {
                $state = $variant->getStockState();
            }
        }

        if ($variable->getStockState() !== $state) {
            $variable->setStockState($state);

            return true;
        }

        return false;
    }
}
