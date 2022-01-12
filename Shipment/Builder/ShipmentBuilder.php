<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayRegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

/**
 * Class ShipmentBuilder
 * @package Ekyna\Component\Commerce\Shipment\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentBuilder implements ShipmentBuilderInterface
{
    private SaleFactoryInterface $factory;
    private GatewayRegistryInterface $registry;
    private ShipmentSubjectCalculatorInterface $calculator;

    public function __construct(
        SaleFactoryInterface $factory,
        GatewayRegistryInterface $registry,
        ShipmentSubjectCalculatorInterface $calculator

    ) {
        $this->factory = $factory;
        $this->registry = $registry;
        $this->calculator = $calculator;
    }

    public function build(ShipmentInterface $shipment): void
    {
        if (null === $sale = $shipment->getSale()) {
            throw new LogicException('Sale must be set.');
        }

        $this->initializeMethod($shipment);
        $this->initializeRelayPoint($shipment);

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
    protected function buildItem(SaleItemInterface $saleItem, ShipmentInterface $shipment): ?ShipmentItemInterface
    {
        // Compound item
        if ($saleItem->isCompound()) {
            // Resolve available and expected quantities by building children
            $available = $expected = null;
            foreach ($saleItem->getChildren() as $childSaleItem) {
                if (null !== $child = $this->buildItem($childSaleItem, $shipment)) {
                    $saleItemQty = $childSaleItem->getQuantity();

                    $e = ($child->getExpected() ?: new Decimal(0))->div($saleItemQty);
                    if (null === $expected || $expected > $e) {
                        $expected = $e;
                    }

                    $a = ($child->getAvailable() ?: new Decimal(0))->div($saleItemQty);
                    if (null === $available || $available > $a) {
                        $available = $a;
                    }
                }
            }

            // If any children is expected
            if (0 < $expected) {
                return $this->findOrCreateItem($shipment, $saleItem, $expected, $available);
            }

            return null;
        }

        $item = null;

        $expected = $shipment->isReturn()
            ? $this->calculator->calculateReturnableQuantity($saleItem, $shipment)
            : $this->calculator->calculateShippableQuantity($saleItem, $shipment);

        if (0 < $expected) {
            $item = $this->findOrCreateItem($shipment, $saleItem, $expected);
        }

        // Build children
        if ($saleItem->hasChildren()) {
            foreach ($saleItem->getChildren() as $childSaleItem) {
                $this->buildItem($childSaleItem, $shipment);
            }
        }

        return $item;
    }

    /**
     * Finds or create the shipment item.
     */
    private function findOrCreateItem(
        ShipmentInterface $shipment,
        SaleItemInterface $saleItem,
        Decimal $expected,
        Decimal $available = null
    ): ?ShipmentItemInterface {
        if (0 >= $expected) {
            return null;
        }

        $item = null;

        // Existing item lookup
        foreach ($shipment->getItems() as $i) {
            if ($i->getSaleItem() === $saleItem) {
                $item = $i;
                break;
            }
        }

        // Not found, create it
        if (null === $item) {
            $item = $this->factory->createItemForShipment($shipment);
            $item->setShipment($shipment);
            $item->setSaleItem($saleItem);
        }

        // Set expected quantity
        $item->setExpected($expected);

        if ($shipment->isReturn()) {
            // Set expected quantity as available
            $item->setAvailable($expected);
        } else {
            if (null === $available) {
                $available = $this->calculator->calculateAvailableQuantity($saleItem, $shipment);
            }

            // Set available quantity
            $item->setAvailable($available);

            // Set default quantity for new non-return shipment items
            if (null === $shipment->getId()) {
                $item->setQuantity(min($expected, $available));
            }
        }

        return $item;
    }
}
