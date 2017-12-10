<?php

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Resolver\DiscountResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AdjustmentBuilder
 * @package Ekyna\Component\Commerce\Common
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentBuilder implements AdjustmentBuilderInterface
{
    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;

    /**
     * @var TaxResolverInterface
     */
    protected $taxResolver;

    /**
     * @var DiscountResolverInterface
     */
    protected $discountResolver;

    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface             $saleFactory
     * @param TaxResolverInterface             $taxResolver
     * @param DiscountResolverInterface         $discountResolver
     * @param PersistenceHelperInterface       $persistenceHelper
     */
    public function __construct(
        SaleFactoryInterface $saleFactory,
        TaxResolverInterface $taxResolver,
        DiscountResolverInterface $discountResolver,
        PersistenceHelperInterface $persistenceHelper
    ) {
        $this->saleFactory = $saleFactory;
        $this->taxResolver = $taxResolver;
        $this->discountResolver = $discountResolver;
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * @inheritdoc
     */
    public function buildDiscountAdjustmentsForSale(Model\SaleInterface $sale, $persistence = false)
    {
        $data = $sale->isAutoDiscount() && !$sale->isSample() ? $this->discountResolver->resolveSale($sale) : [];

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT, $sale, $data, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function buildDiscountAdjustmentsForSaleItems($parent, $persistence = false)
    {
        if ($parent instanceof Model\SaleInterface) {
            $children = $parent->getItems();
        } elseif ($parent instanceof Model\SaleItemInterface) {
            $children = $parent->getChildren();
        } else {
            throw new InvalidArgumentException("Expected instance of SaleInterface or SaleItemInterface.");
        }

        $change = false;

        foreach ($children as $child) {
            $change |= $this->buildDiscountAdjustmentsForSaleItem($child, $persistence);

            if ($child->hasChildren()) {
                $change |= $this->buildDiscountAdjustmentsForSaleItems($child, $persistence);
            }
        }

        return $change;
    }

    /**
     * @inheritdoc
     */
    public function buildDiscountAdjustmentsForSaleItem(Model\SaleItemInterface $item, $persistence = false)
    {
        $sale = $item->getSale();

        $data = $sale->isAutoDiscount() && !$sale->isSample() ? $this->discountResolver->resolveSaleItem($item) : [];

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT, $item, $data, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function buildTaxationAdjustmentsForSale(Model\SaleInterface $sale, $persistence = false)
    {
        $data = [];

        // For now, we assume that sale's taxation adjustments are only related to shipment.
        if (!($sale->isTaxExempt() || $sale->isSample()) && null !== $taxable = $sale->getShipmentMethod()) {
            // Resolve taxes
            $data = $this->taxResolver->resolveTaxes($taxable, $sale);
        }

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_TAXATION, $sale, $data, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function buildTaxationAdjustmentsForSaleItems($parent, $persistence = false)
    {
        if ($parent instanceof Model\SaleInterface) {
            $children = $parent->getItems();
        } elseif ($parent instanceof Model\SaleItemInterface) {
            $children = $parent->getChildren();
        } else {
            throw new InvalidArgumentException("Expected instance of SaleInterface or SaleItemInterface.");
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
     * @inheritdoc
     */
    public function buildTaxationAdjustmentsForSaleItem(Model\SaleItemInterface $item, $persistence = false)
    {
        $data = [];

        $sale = $item->getSale();
        if (!$item->isPrivate() && !(null === $sale || $sale->isTaxExempt() || $sale->isSample())) {
            $data = $this->taxResolver->resolveTaxes($item, $sale);
        }

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_TAXATION, $item, $data, $persistence);
    }

    /**
     * Builds the adjustable's adjustments based on the given data and type.
     *
     * @param string                          $type
     * @param Model\AdjustableInterface       $adjustable
     * @param Model\AdjustmentDataInterface[] $data
     * @param bool                            $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    protected function buildAdjustments($type, Model\AdjustableInterface $adjustable, array $data, $persistence = false)
    {
        Model\AdjustmentTypes::isValidType($type);

        $change = false;

        // Generate adjustments
        $newAdjustments = [];
        foreach ($data as $d) {
            $adjustment = $this->saleFactory->createAdjustmentFor($adjustable);
            $adjustment
                ->setType($type)
                ->setMode($d->getMode())
                ->setDesignation($d->getDesignation())
                ->setAmount($d->getAmount())
                ->setImmutable($d->isImmutable());

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
