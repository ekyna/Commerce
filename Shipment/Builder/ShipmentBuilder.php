<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\AvailabilityResolver;
use Ekyna\Component\Commerce\Shipment\Resolver\AvailabilityResolverFactory;

use function min;

/**
 * Class ShipmentBuilder
 * @package Ekyna\Component\Commerce\Shipment\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentBuilder implements ShipmentBuilderInterface
{
    private AvailabilityResolverFactory $availabilityResolverFactory;
    private FactoryHelperInterface      $factoryHelper;
    private GatewayRegistryInterface    $registry;

    private AvailabilityResolver $quantityResolver;

    public function __construct(
        AvailabilityResolverFactory $availabilityResolverFactory,
        FactoryHelperInterface      $factoryHelper,
        GatewayRegistryInterface    $registry
    ) {
        $this->availabilityResolverFactory = $availabilityResolverFactory;
        $this->factoryHelper = $factoryHelper;
        $this->registry = $registry;
    }

    public function build(ShipmentInterface $shipment): void
    {
        if (null === $sale = $shipment->getSale()) {
            throw new LogicException('Sale must be set.');
        }

        $this->initializeMethod($shipment);
        $this->initializeRelayPoint($shipment);

        $this->quantityResolver = $this->availabilityResolverFactory->createWithShipment($shipment);

        foreach ($sale->getItems() as $saleItem) {
            $this->buildItem($saleItem, $shipment);
        }
    }

    /**
     * Initializes the shipment's method.
     */
    private function initializeMethod(ShipmentInterface $shipment): void
    {
        // Abort if shipment's method is defined
        if (null !== $shipment->getMethod()) {
            return;
        }

        $sale = $shipment->getSale();

        // Abort if default method is not defined
        if (null === $method = $sale->getShipmentMethod()) {
            return;
        }

        $gateway = $this->registry->getGateway($method->getGatewayName());

        // Set shipment method if supported
        if (!$shipment->isReturn() && $gateway->supports(GatewayInterface::CAPABILITY_SHIPMENT)) {
            $shipment->setMethod($method);

            return;
        }

        // Set return method if supported
        if ($shipment->isReturn() && $gateway->supports(GatewayInterface::CAPABILITY_RETURN)) {
            $shipment->setMethod($method);
        }
    }

    /**
     * Initializes the shipment's relay point.
     */
    private function initializeRelayPoint(ShipmentInterface $shipment): void
    {
        // Abort if shipment method is not defined
        if (null === $method = $shipment->getMethod()) {
            // Clear the relay point if it is set
            if (null !== $shipment->getRelayPoint()) {
                $shipment->setRelayPoint(null);
            }

            return;
        }

        $gateway = $this->registry->getGateway($method->getGatewayName());

        // If gateway does not support relay point
        if (!$gateway->supports(GatewayInterface::CAPABILITY_RELAY)) {
            // Clear the relay point if it is set
            if (null !== $shipment->getRelayPoint()) {
                $shipment->setRelayPoint(null);
            }

            return;
        }

        // Set default relay point
        if (null !== $relayPoint = $shipment->getSale()->getRelayPoint()) {
            $shipment->setRelayPoint($relayPoint);
        }
    }

    /**
     * Builds the shipment item by pre-populating quantity.
     */
    protected function buildItem(SaleItemInterface $saleItem, ShipmentInterface $shipment): void
    {
        $availability = $this->quantityResolver->resolveSaleItem($saleItem);

        if (!$availability->getExpected()->isZero()) {
            $item = $this
                ->findOrCreateItem($shipment, $saleItem)
                ->setAvailability($availability);

            // Set default quantity for new non-return shipment items
            if (!$shipment->isReturn() && (null === $shipment->getId())) {
                $item->setQuantity(min(
                    $saleItem->getTotalQuantity(),
                    $availability->getExpected(),
                    $availability->getAssigned()
                ));
            }
        }

        // Build children
        foreach ($saleItem->getChildren() as $childSaleItem) {
            $this->buildItem($childSaleItem, $shipment);
        }
    }

    /**
     * Finds or create the shipment item.
     */
    private function findOrCreateItem(ShipmentInterface $shipment, SaleItemInterface $saleItem): ShipmentItemInterface
    {
        // Existing item lookup
        foreach ($shipment->getItems() as $item) {
            if ($item->getSaleItem() === $saleItem) {
                return $item;
            }
        }

        // Not found, create it
        return $this
            ->factoryHelper
            ->createItemForShipment($shipment)
            ->setShipment($shipment)
            ->setSaleItem($saleItem);
    }
}
