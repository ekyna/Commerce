<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Updater;

use Ekyna\Component\Commerce\Common\Builder\SaleAdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Helper\SaleHelper;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Resolver\NetPriceResolverInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class SaleItemUpdater
 * @package Ekyna\Component\Commerce\Common\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemUpdater implements SaleItemUpdaterInterface
{
    public function __construct(
        private readonly NetPriceResolverInterface      $netPriceResolver,
        private readonly SaleAdjustmentBuilderInterface $adjustmentBuilder,
        private readonly PersistenceHelperInterface     $persistenceHelper,
    ) {
    }

    public function updateChildrenNetPriceAndDiscount(
        SaleInterface|SaleItemInterface $parent,
        bool                            $persistence = false
    ): bool {
        if ($parent instanceof SaleInterface) {
            $children = $parent->getItems();
        } else {
            $children = $parent->getChildren();
        }

        $changed = false;

        foreach ($children as $child) {
            $changed = $this->updateNetPriceAndDiscount($child, $persistence) || $changed;

            if ($child->hasChildren()) {
                $changed = $this->updateChildrenNetPriceAndDiscount($child, $persistence) || $changed;
            }
        }

        return $changed;
    }

    public function updateNetPriceAndDiscount(SaleItemInterface $item, bool $persistence = false): bool
    {
        if (SaleHelper::isSalePriceLocked($item->getRootSale())) {
            return false;
        }

        $changed = false;

        $event = $this->netPriceResolver->resolveSaleItem($item);

        $price = $event->getNetPrice();
        if ($price && $price !== $item->getNetPrice()) {
            $item->setNetPrice($price);
            $changed = true;

            if ($persistence) {
                $this->persistenceHelper->persistAndRecompute($item, true);
            }
        }

        if ($event->isPropagationStopped()) {
            $this->adjustmentBuilder->clearSaleItemDiscountAdjustments($item, $persistence);

            return $changed;
        }

        return $this->adjustmentBuilder->buildSaleItemDiscountAdjustments($item);
    }
}
