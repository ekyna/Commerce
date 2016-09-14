<?php

namespace Ekyna\Component\Commerce\Common;

use Ekyna\Component\Commerce\Pricing\Model\TaxableInterface;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolverInterface;
use Ekyna\Component\Commerce\Subject\Provider\SubjectProviderRegistryInterface;

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
    private $providerRegistry;

    /**
     * @var TaxResolverInterface
     */
    private $taxResolver;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface             $saleFactory
     * @param SubjectProviderRegistryInterface $providerRegistry
     * @param TaxResolverInterface             $taxResolver
     */
    public function __construct(
        SaleFactoryInterface $saleFactory,
        SubjectProviderRegistryInterface $providerRegistry,
        TaxResolverInterface $taxResolver
    ) {
        $this->saleFactory = $saleFactory;
        $this->providerRegistry = $providerRegistry;
        $this->taxResolver = $taxResolver;
    }

    /**
     * Builds the taxation adjustments for the sale item.
     *
     * @param Model\SaleItemInterface $item
     * @param TaxableInterface        $taxable
     */
    public function buildTaxationAdjustmentsForSaleItem(
        Model\SaleItemInterface $item,
        TaxableInterface $taxable = null
    ) {
        if (null === $taxable) {
            if ($provider = $this->providerRegistry->getProvider($item)) {
                return;
            }

            if (null === $taxable = $provider->resolve($item)) {
                return;
            }

            if (!$taxable instanceof TaxableInterface) {
                return;
            }
        }

        // Resolve taxes
        $taxes = [];
        if (null !== $sale = $item->getSale()) {
            $customer = $sale->getCustomer();
            $address = $sale->getDeliveryAddress();

            if (null !== $customer && null !== $address) {
                $taxes = $this
                    ->taxResolver
                    ->getApplicableTaxesBySubjectAndCustomer(
                        $taxable, $customer, $address
                    );
            }
        } else {
            $taxes = $this->taxResolver->getDefaultTaxesBySubject($taxable);
        }

        // Remove taxation adjustments
        foreach ($item->getAdjustments() as $adjustment) {
            if ($adjustment->getType() === Model\AdjustmentTypes::TYPE_TAXATION) {
                $item->removeAdjustment($adjustment);
            }
        }

        // Build adjustments from taxes
        foreach ($taxes as $tax) {
            $adjustment = $this->saleFactory->createAdjustmentForSaleItem($item);
            $adjustment
                ->setMode(Model\AdjustmentModes::MODE_PERCENT)
                ->setType(Model\AdjustmentTypes::TYPE_TAXATION)
                ->setDesignation($tax->getName())
                ->setAmount($tax->getRate());

            /** @var \Ekyna\Component\Commerce\Order\Model\OrderItemInterface $item */
            $item->addAdjustment($adjustment);
        }
    }
}
