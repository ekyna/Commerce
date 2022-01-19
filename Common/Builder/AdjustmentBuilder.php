<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Resolver\DiscountResolverInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AdjustmentBuilder
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentBuilder implements AdjustmentBuilderInterface
{
    protected FactoryHelperInterface     $factoryHelper;
    protected TaxResolverInterface       $taxResolver;
    protected DiscountResolverInterface  $discountResolver;
    protected PersistenceHelperInterface $persistenceHelper;

    public function __construct(
        FactoryHelperInterface     $factoryHelper,
        TaxResolverInterface       $taxResolver,
        DiscountResolverInterface  $discountResolver,
        PersistenceHelperInterface $persistenceHelper
    ) {
        $this->factoryHelper = $factoryHelper;
        $this->taxResolver = $taxResolver;
        $this->discountResolver = $discountResolver;
        $this->persistenceHelper = $persistenceHelper;
    }

    public function buildDiscountAdjustmentsForSale(Model\SaleInterface $sale, bool $persistence = false): bool
    {
        $data = $sale->isAutoDiscount() && !$sale->isSample() ? $this->discountResolver->resolveSale($sale) : [];

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT, $sale, $data, $persistence);
    }

    /**
     * @inheritDoc
     */
    public function buildDiscountAdjustmentsForSaleItems($parent, bool $persistence = false): bool
    {
        if ($parent instanceof Model\SaleInterface) {
            $children = $parent->getItems();
        } elseif ($parent instanceof Model\SaleItemInterface) {
            $children = $parent->getChildren();
        } else {
            throw new UnexpectedTypeException($parent, [Model\SaleInterface::class, Model\SaleItemInterface::class]);
        }

        $changed = false;

        foreach ($children as $child) {
            $changed = $this->buildDiscountAdjustmentsForSaleItem($child, $persistence) || $changed;

            if ($child->hasChildren()) {
                $changed = $this->buildDiscountAdjustmentsForSaleItems($child, $persistence) || $changed;
            }
        }

        return $changed;
    }

    public function buildDiscountAdjustmentsForSaleItem(Model\SaleItemInterface $item, bool $persistence = false): bool
    {
        $sale = $item->getSale();

        if (!$sale->isAutoDiscount()) {
            return false;
        }

        $data = !$sale->isSample() ? $this->discountResolver->resolveSaleItem($item) : [];

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT, $item, $data, $persistence);
    }

    public function buildTaxationAdjustmentsForSale(Model\SaleInterface $sale, bool $persistence = false): bool
    {
        $data = [];

        // For now, we assume that sale's taxation adjustments are only related to shipment.
        if (!($sale->isTaxExempt() || $sale->isSample()) && !is_null($taxable = $sale->getShipmentMethod())) {
            // Resolve taxes
            $data = $this->taxResolver->resolveTaxes($taxable, $sale);
        }

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_TAXATION, $sale, $data, $persistence);
    }

    /**
     * @inheritDoc
     */
    public function buildTaxationAdjustmentsForSaleItems($parent, bool $persistence = false): bool
    {
        if ($parent instanceof Model\SaleInterface) {
            $children = $parent->getItems();
        } elseif ($parent instanceof Model\SaleItemInterface) {
            $children = $parent->getChildren();
        } else {
            throw new UnexpectedTypeException($parent, [Model\SaleInterface::class, Model\SaleItemInterface::class]);
        }

        $change = false;

        foreach ($children as $child) {
            $change = $this->buildTaxationAdjustmentsForSaleItem($child, $persistence) || $change;

            if ($child->hasChildren()) {
                $change = $this->buildTaxationAdjustmentsForSaleItems($child, $persistence) || $change;
            }
        }

        return $change;
    }

    /**
     * @inheritDoc
     */
    public function buildTaxationAdjustmentsForSaleItem(Model\SaleItemInterface $item, bool $persistence = false): bool
    {
        $data = [];

        $sale = $item->getSale();
        if (!$item->isPrivate() && !(null === $sale || $sale->isTaxExempt() || $sale->isSample())) {
            $data = $this->taxResolver->resolveTaxes($item, $sale);
        }

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_TAXATION, $item, $data, $persistence);
    }

    /**
     * Builds the adjustments regarding the given data and type.
     *
     * @param string                          $type
     * @param Model\AdjustableInterface       $adjustable
     * @param Model\AdjustmentDataInterface[] $data
     * @param bool                            $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    protected function buildAdjustments(
        string                    $type,
        Model\AdjustableInterface $adjustable,
        array                     $data,
        bool                      $persistence = false
    ): bool {
        Model\AdjustmentTypes::isValidType($type);

        $change = false;

        // Generate adjustments
        $newAdjustments = [];
        foreach ($data as $datum) {
            $adjustment = $this->factoryHelper->createAdjustmentFor($adjustable);
            $adjustment
                ->setType($type)
                ->setMode($datum->getMode())
                ->setDesignation($datum->getDesignation())
                ->setAmount($datum->getAmount())
                ->setImmutable($datum->isImmutable())
                ->setSource($datum->getSource());

            $newAdjustments[] = $adjustment;
        }

        // Current adjustments
        $oldAdjustments = $adjustable->getAdjustments($type);

        // Remove current adjustments that do not match any generated adjustments
        foreach ($oldAdjustments as $oldAdjustment) {
            // Skip non-immutable adjustment as they have been defined by the user.
            if (!$oldAdjustment->isImmutable()) {
                continue;
            }

            // Look for a corresponding adjustment
            foreach ($newAdjustments as $index => $newAdjustment) {
                if ($oldAdjustment->equals($newAdjustment)) {
                    // Remove the generated adjustment
                    unset($newAdjustments[$index]);
                    continue 2;
                }
            }

            // No matching generated adjustment found : remove the current.
            $adjustable->removeAdjustment($oldAdjustment);

            if ($persistence) {
                $this->persistenceHelper->remove($oldAdjustment, true);
            }

            $change = true;
        }

        // Adds the remaining generated adjustments
        foreach ($newAdjustments as $newAdjustment) {
            $adjustable->addAdjustment($newAdjustment);

            if ($persistence) {
                $this->persistenceHelper->persistAndRecompute($newAdjustment, true);
            }

            $change = true;
        }

        return $change;
    }
}
