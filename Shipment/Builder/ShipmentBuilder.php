<?php

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;
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
     * @var ShipmentCalculatorInterface
     */
    private $calculator;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface        $factory
     * @param ShipmentCalculatorInterface $calculator
     */
    public function __construct(SaleFactoryInterface $factory, ShipmentCalculatorInterface $calculator)
    {
        $this->factory = $factory;
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

        // If shipment method is not defined and preferred method if defined
        if (null === $shipment->getMethod() && null !== $method = $sale->getShipmentMethod()) {
            // Set preferred method
            $shipment->setMethod($method);
        }

        foreach ($sale->getItems() as $saleItem) {
            $this->buildItem($saleItem, $shipment);
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
        // If compound with only public children
        if ($saleItem->isCompound() && !$saleItem->hasPrivateChildren()) {
            // Just build children
            foreach ($saleItem->getChildren() as $childSaleItem) {
                $this->buildItem($childSaleItem, $shipment);
            }

            return null;
        }

        // Compound with private children
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

            if (0 < $expected) {
                return $this->findOrCreateItem($shipment, $saleItem, $expected, $available);
            }

            return null;

        }

        // Leaf item
        $expected = $shipment->isReturn()
            ? $this->calculator->calculateReturnableQuantity($saleItem, $shipment)
            : $this->calculator->calculateShippableQuantity($saleItem, $shipment);

        if (0 < $expected) {
            return $this->findOrCreateItem($shipment, $saleItem, $expected);
        }

        return null;
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
                $available = $this->calculator->calculateAvailableQuantity($item);
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
