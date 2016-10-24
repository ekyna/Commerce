<?php

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
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
     * TODO remove
     */
    private $providerRegistry;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;

    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface             $saleFactory
     * @param SubjectProviderRegistryInterface $providerRegistry
     * @param TaxResolverInterface             $taxResolver
     * @param PersistenceHelperInterface       $persistenceHelper
     */
    public function __construct(
        SaleFactoryInterface $saleFactory,
        SubjectProviderRegistryInterface $providerRegistry,
        TaxResolverInterface $taxResolver,
        PersistenceHelperInterface $persistenceHelper
    ) {
        $this->saleFactory = $saleFactory;
        $this->providerRegistry = $providerRegistry;
        $this->taxResolver = $taxResolver;
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * @inheritdoc
     */
    public function buildTaxationAdjustmentsForSale(Model\SaleInterface $sale, $persistence = false)
    {
        // For now, we assume that sale's taxation adjustments are only related to shipment.
        $taxes = [];
        if (null !== $taxable = $sale->getPreferredShipmentMethod()) {
            // Resolve taxes
            $taxes = $this->taxResolver->resolveTaxesBySale($taxable, $sale);
        }

        return $this->buildTaxationAdjustments($sale, $taxes, $persistence);
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
        /*if (null === $provider = $this->providerRegistry->getProvider($item)) {
            return false;
        }

        if (null === $taxable = $provider->resolve($item)) {
            return false;
        }

        if (!$taxable instanceof TaxableInterface) {
            return false;
        }*/

        // Resolve taxes
        if (null !== $sale = $item->getSale()) {
            $taxes = $this->taxResolver->resolveTaxesBySale($item, $sale);
        } else {
            $taxes = $this->taxResolver->resolveDefaultTaxes($item);
        }

        return $this->buildTaxationAdjustments($item, $taxes, $persistence);
    }

    /**
     * Builds the adjustable's taxation adjustments based on the given taxes.
     *
     * @param Model\AdjustableInterface                                    $adjustable
     * @param array|\Ekyna\Component\Commerce\Pricing\Model\TaxInterface[] $taxes
     * @param bool                                                         $persistence
     *
     * @return bool Whether at least one adjustment has been changed (created, updated or deleted)
     */
    private function buildTaxationAdjustments(Model\AdjustableInterface $adjustable, array $taxes, $persistence = false)
    {
        $change = false;

        // Generate adjustments
        $newAdjustments = [];
        foreach ($taxes as $tax) {
            $adjustment = $this->saleFactory->createAdjustmentFor($adjustable);
            $adjustment
                ->setMode(Model\AdjustmentModes::MODE_PERCENT)
                ->setType(Model\AdjustmentTypes::TYPE_TAXATION)
                ->setDesignation($tax->getName())
                ->setAmount($tax->getRate());

            $newAdjustments[] = $adjustment;
        }

        // Current adjustments
        $oldAdjustments = $adjustable->getAdjustments(Model\AdjustmentTypes::TYPE_TAXATION);

        // Remove current adjustments that do not match any generated adjustments
        foreach ($oldAdjustments as $oldAdjustment) {
            // Skip non-immutable adjustment as they have been defined by the user.
            // Commented because taxation adjustment should never be immutable.
            /*if (!$oldAdjustment->isImmutable()) {
                continue;
            }*/

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
                $this->persistenceHelper->remove($oldAdjustment);
            }

            $change = true;
        }

        // Adds the remaining generated adjustments
        foreach ($newAdjustments as $newAdjustment) {
            $adjustable->addAdjustment($newAdjustment);

            if ($persistence) {
                $this->persistenceHelper->persistAndRecompute($newAdjustment);
            }

            $change = true;
        }

        return $change;
    }
}
