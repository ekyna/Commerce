<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Builder;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Common\Util\AddressUtil;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AddressBuilder
 * @package Ekyna\Component\Commerce\Common\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AddressBuilder implements AddressBuilderInterface
{
    private FactoryHelperInterface     $factoryHelper;
    private PersistenceHelperInterface $persistenceHelper;

    public function __construct(
        FactoryHelperInterface     $factoryHelper,
        PersistenceHelperInterface $persistenceHelper
    ) {
        $this->factoryHelper = $factoryHelper;
        $this->persistenceHelper = $persistenceHelper;
    }

    public function buildSaleInvoiceAddressFromAddress(
        Model\SaleInterface    $sale,
        Model\AddressInterface $source,
        bool                   $persistence = false
    ): bool {
        // If the sale does not have an invoice address
        if (null === $current = $sale->getInvoiceAddress()) {
            // Create a new sale address
            $created = $this->factoryHelper->createAddressForSale($sale, $source);
            $sale->setInvoiceAddress($created);

            if ($persistence) {
                $this->persistenceHelper->persistAndRecompute($created, true);
            }

            return true;
        }

        // If the sale's current invoice address is different from the given source address
        if (!AddressUtil::equals($source, $current)) {
            // Update the current invoice address
            AddressUtil::copy($source, $current);

            if ($persistence) {
                $this->persistenceHelper->persistAndRecompute($current, true);
            }

            return true;
        }

        return false;
    }

    public function buildSaleDeliveryAddressFromAddress(
        Model\SaleInterface    $sale,
        Model\AddressInterface $source,
        bool                   $persistence = false
    ): bool {
        // If the source address equals the invoice address, use the "same address" property.
        if ((null !== $invoice = $sale->getInvoiceAddress()) && AddressUtil::equals($source, $invoice)) {
            if (null !== $current = $sale->getDeliveryAddress()) {
                $sale
                    ->setSameAddress(true)
                    ->setDeliveryAddress(null);

                if ($persistence) {
                    $this->persistenceHelper->remove($current, true);
                }

                return true;
            }

            return false;
        }

        // If the sale does not have a delivery address
        if (null === $current = $sale->getDeliveryAddress()) {
            // Create a new sale address
            $created = $this->factoryHelper->createAddressForSale($sale, $source);
            $sale->setDeliveryAddress($created);

            if ($persistence) {
                $this->persistenceHelper->persistAndRecompute($created, true);
            }

            return true;
        }

        // If the sale's current delivery address is different from the given source address
        if (!AddressUtil::equals($source, $current)) {
            // Update the current delivery address
            AddressUtil::copy($source, $current);

            if ($persistence) {
                $this->persistenceHelper->persistAndRecompute($current, true);
            }

            return true;
        }

        return false;
    }
}
