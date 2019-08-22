<?php

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\RegistryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

/**
 * Class ShipmentBuilder
 * @package Ekyna\Component\Commerce\Shipment\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentBuilder implements ShipmentBuilderInterface
{
    /**
     * @var SaleFactoryInterface
     */
    private $factory;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var ShipmentSubjectCalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface               $factory
     * @param RegistryInterface                  $registry
     * @param ShipmentSubjectCalculatorInterface $calculator
     */
    public function __construct(
        SaleFactoryInterface $factory,
        RegistryInterface $registry,
        ShipmentSubjectCalculatorInterface $calculator

    ) {
        $this->factory = $factory;
        $this->registry = $registry;
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    public function build(ShipmentInterface $shipment)
    {
        if (null === $sale = $shipment->getSale()) {
            throw new LogicException("Sale must be set.");
        }

        if (!$shipment->isReturn()) {
            $shipment->setAutoInvoice(true);
        }

        $this->initializeMethod($shipment);
        $this->initializeRelayPoint($shipment);

        foreach ($sale->getItems() as $saleItem) {
            $this->buildItem($saleItem, $shipment);
        }
    }

    /**
     * Initializes the shipment's method.
     *
     * @param ShipmentInterface $shipment
     */
    private function initializeMethod(ShipmentInterface $shipment)
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

            return;
        }
    }

    /**
     * Initializes the shipment's relay point.
     *
     * @param ShipmentInterface $shipment
     */
    private function initializeRelayPoint(ShipmentInterface $shipment)
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
     * Builds the shipment item by pre populating quantity.
     *
     * @param SaleItemInterface $saleItem
     * @param ShipmentInterface $shipment
     *
     * @return ShipmentItemInterface|null
     */
    protected function buildItem(SaleItemInterface $saleItem, ShipmentInterface $shipment)
    {
        // If compound with only private children
        if ($saleItem->isCompound()) {
            // Resolve available and expected quantities by building children
            $available = $expected = null;
            foreach ($saleItem->getChildren() as $childSaleItem) {
                if (null !== $child = $this->buildItem($childSaleItem, $shipment)) {
                    $saleItemQty = $childSaleItem->getQuantity();

                    $e = $child->getExpected() / $saleItemQty;
                    if (null === $expected || $expected > $e) {
                        $expected = $e;
                    }

                    $a = $child->getAvailable() / $saleItemQty;
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

        // Skip compound with only public children
        if (!($saleItem->isCompound() && !$saleItem->hasPrivateChildren())) {
            $expected = $shipment->isReturn()
                ? $this->calculator->calculateReturnableQuantity($saleItem, $shipment)
                : $this->calculator->calculateShippableQuantity($saleItem, $shipment);

            if (0 < $expected) {
                $item = $this->findOrCreateItem($shipment, $saleItem, $expected);
            }
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
     *
     * @param ShipmentInterface $shipment
     * @param SaleItemInterface $saleItem
     * @param float             $expected
     * @param float             $available
     *
     * @return ShipmentItemInterface
     */
    private function findOrCreateItem(ShipmentInterface $shipment, SaleItemInterface $saleItem, $expected, $available = null)
    {
        $item = null;

        if (0 >= $expected) {
            return $item;
        }

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

            // Set default quantity for new non return shipment items
            if (null === $shipment->getId()) {
                $item->setQuantity(min($expected, $available));
            }
        }

        return $item;
    }
}
