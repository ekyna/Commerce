<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Resolver\DiscountResolverInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;

/**
 * Class AdjustmentBuilder
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleAdjustmentBuilder implements SaleAdjustmentBuilderInterface
{
    public function __construct(
        private readonly AdjustmentBuilderInterface $adjustmentBuilder,
        private readonly TaxResolverInterface       $taxResolver,
        private readonly DiscountResolverInterface  $discountResolver,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function buildSaleDiscountAdjustments(SaleInterface $sale, bool $persistence = false): bool
    {
        if (!$this->canUpdateDiscounts($sale)) {
            return false;
        }

        $changed = $this->buildSaleItemsDiscountAdjustments($sale, $persistence);

        $data = !$sale->isSample() ? $this->discountResolver->resolveSale($sale) : [];

        return $this
                ->adjustmentBuilder
                ->buildAdjustments(AdjustmentTypes::TYPE_DISCOUNT, $sale, $data, $persistence) || $changed;
    }

    /**
     * @inheritDoc
     */
    public function buildSaleItemsDiscountAdjustments(
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
            $changed = $this->buildSaleItemDiscountAdjustments($child, $persistence) || $changed;

            if ($child->hasChildren()) {
                $changed = $this->buildSaleItemsDiscountAdjustments($child, $persistence) || $changed;
            }
        }

        return $changed;
    }

    /**
     * @inheritDoc
     */
    public function buildSaleItemDiscountAdjustments(SaleItemInterface $item, bool $persistence = false): bool
    {
        if (!$this->canUpdateDiscounts($item)) {
            return false;
        }

        $data = !$item->getRootSale()->isSample() ? $this->discountResolver->resolveSaleItem($item) : [];

        return $this
            ->adjustmentBuilder
            ->buildAdjustments(AdjustmentTypes::TYPE_DISCOUNT, $item, $data, $persistence);
    }

    /**
     * @inheritDoc
     */
    public function buildSaleTaxationAdjustments(SaleInterface $sale, bool $persistence = false): bool
    {
        if (!$this->canUpdateTaxation($sale)) {
            return false;
        }

        $changed = $this->buildSaleItemsTaxationAdjustments($sale, $persistence);

        $data = [];

        // For now, we assume that sale's taxation adjustments are only related to shipment.
        if (!($sale->isTaxExempt() || $sale->isSample()) && !is_null($taxable = $sale->getShipmentMethod())) {
            // Resolve taxes
            $data = $this->taxResolver->resolveTaxes($taxable, $sale);
        }

        return $this
                ->adjustmentBuilder
                ->buildAdjustments(AdjustmentTypes::TYPE_TAXATION, $sale, $data, $persistence) || $changed;
    }

    /**
     * @inheritDoc
     */
    public function buildSaleItemsTaxationAdjustments(
        SaleInterface|SaleItemInterface $parent,
        bool                            $persistence = false
    ): bool {
        if (!$this->canUpdateTaxation($parent)) {
            return false;
        }

        if ($parent instanceof SaleInterface) {
            $children = $parent->getItems();
        } else {
            $children = $parent->getChildren();
        }

        $change = false;

        foreach ($children as $child) {
            $change = $this->buildSaleItemTaxationAdjustments($child, $persistence) || $change;

            if ($child->hasChildren()) {
                $change = $this->buildSaleItemsTaxationAdjustments($child, $persistence) || $change;
            }
        }

        return $change;
    }

    /**
     * @inheritDoc
     */
    public function buildSaleItemTaxationAdjustments(SaleItemInterface $item, bool $persistence = false): bool
    {
        if (!$this->canUpdateTaxation($item)) {
            return false;
        }

        $data = [];

        $sale = $item->getRootSale();
        if (!$item->isPrivate() && !(null === $sale || $sale->isTaxExempt() || $sale->isSample())) {
            $data = $this->taxResolver->resolveTaxes($item, $sale);
        }

        return $this
            ->adjustmentBuilder
            ->buildAdjustments(AdjustmentTypes::TYPE_TAXATION, $item, $data, $persistence);
    }

    /**
     * Returns whether discount(s) can be updated.
     *
     * @param SaleInterface|SaleItemInterface $resource
     * @return bool
     */
    protected function canUpdateDiscounts(SaleInterface|SaleItemInterface $resource): bool
    {
        if ($resource instanceof SaleItemInterface) {
            $resource = $resource->getRootSale();
        }

        if ($resource instanceof SaleInterface) {
            // TODO Define when it should be locked
            return $resource->isAutoDiscount(); // && !$resource->hasPaidPayments()
        }

        throw new UnexpectedTypeException($resource, [SaleInterface::class, SaleItemInterface::class]);
    }

    /**
     * Returns whether taxation can be updated.
     *
     * @param SaleInterface|SaleItemInterface $resource
     * @return bool
     */
    protected function canUpdateTaxation(SaleInterface|SaleItemInterface $resource): bool
    {
        if ($resource instanceof SaleItemInterface) {
            $resource = $resource->getRootSale();
        }

        if ($resource instanceof SaleInterface) {
            // TODO Define when it should be locked
            return true; // && !$resource->hasPaidPayments()
        }

        throw new UnexpectedTypeException($resource, [SaleInterface::class, SaleItemInterface::class]);
    }

    /**
     * @inheritDoc
     */
    public function makeSaleDiscountsMutable(SaleInterface $sale): void
    {
        $this
            ->adjustmentBuilder
            ->makeAdjustmentsMutable($sale, [AdjustmentTypes::TYPE_DISCOUNT], true);

        foreach ($sale->getItems() as $item) {
            $this->makeSaleItemDiscountsMutable($item);
        }
    }

    /**
     * @param SaleItemInterface $item
     * @return void
     */
    protected function makeSaleItemDiscountsMutable(SaleItemInterface $item): void
    {
        $this
            ->adjustmentBuilder
            ->makeAdjustmentsMutable($item, [AdjustmentTypes::TYPE_DISCOUNT], true);

        foreach ($item->getChildren() as $child) {
            $this->makeSaleItemDiscountsMutable($child);
        }
    }

    /**
     * @inheritDoc
     */
    public function clearSaleMutableDiscounts(SaleInterface $sale): void
    {
        $this
            ->adjustmentBuilder
            ->clearMutableAdjustments($sale, [AdjustmentTypes::TYPE_DISCOUNT], true);

        foreach ($sale->getItems() as $item) {
            $this->clearSaleItemMutableDiscounts($item);
        }
    }

    /**
     * @param SaleItemInterface $item
     * @return void
     */
    protected function clearSaleItemMutableDiscounts(SaleItemInterface $item): void
    {
        $this
            ->adjustmentBuilder
            ->clearMutableAdjustments($item, [AdjustmentTypes::TYPE_DISCOUNT], true);

        foreach ($item->getChildren() as $child) {
            $this->clearSaleItemMutableDiscounts($child);
        }
    }
}
