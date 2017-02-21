<?php

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;
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
    private $saleFactory;

    /**
     * @var SubjectProviderRegistryInterface
     */
    private $subjectProviderRegistry;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface             $saleFactory
     * @param SubjectProviderRegistryInterface $subjectProviderRegistry
     * @param TaxResolverInterface             $taxResolver
     * @param PersistenceHelperInterface       $persistenceHelper
     */
    public function __construct(
        SaleFactoryInterface $saleFactory,
        SubjectProviderRegistryInterface $subjectProviderRegistry,
        TaxResolverInterface $taxResolver,
        PersistenceHelperInterface $persistenceHelper
    ) {
        $this->saleFactory = $saleFactory;
        $this->subjectProviderRegistry = $subjectProviderRegistry;
        $this->taxResolver = $taxResolver;
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * @inheritdoc
     */
    public function buildDiscountAdjustmentsForSale(Model\SaleInterface $sale, $persistence = false)
    {
        // Nothing for now.
        return false;
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
            $change = $this->buildDiscountAdjustmentsForSaleItem($child, $persistence) || $change;

            if ($child->hasChildren()) {
                $change = $this->buildDiscountAdjustmentsForSaleItems($child, $persistence) || $change;
            }
        }

        return $change;
    }

    /**
     * @inheritdoc
     */
    public function buildDiscountAdjustmentsForSaleItem(Model\SaleItemInterface $item, $persistence = false)
    {
        $discounts = [];

        // Get subject provider
        if (null !== $provider = $this->subjectProviderRegistry->getProviderByRelative($item)) {
            // Resolve adjustments data.
            $discounts = $provider->getItemBuilder()->buildAdjustmentsData($item);
        }

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_DISCOUNT, $item, $discounts, $persistence);
    }

    /**
     * @inheritdoc
     */
    public function buildTaxationAdjustmentsForSale(Model\SaleInterface $sale, $persistence = false)
    {
        $taxes = [];

        // For now, we assume that sale's taxation adjustments are only related to shipment.
        if (null !== $taxable = $sale->getPreferredShipmentMethod()) {
            // Resolve taxes
            $taxes = $this->taxResolver->resolveTaxesBySale($taxable, $sale);
        }

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_TAXATION, $sale, $taxes, $persistence);
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
        if (null !== $sale = $item->getSale()) {
            $taxes = $this->taxResolver->resolveTaxesBySale($item, $sale);
        } else {
            $taxes = $this->taxResolver->resolveDefaultTaxes($item);
        }

        return $this->buildAdjustments(Model\AdjustmentTypes::TYPE_TAXATION, $item, $taxes, $persistence);
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
    private function buildAdjustments($type, Model\AdjustableInterface $adjustable, array $data, $persistence = false)
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
