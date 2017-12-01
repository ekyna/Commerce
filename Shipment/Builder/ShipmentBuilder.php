<?php

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
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
        if (null === $shipment->getMethod() && null !== $method = $sale->getPreferredShipmentMethod()) {
            // Set preferred method
            $shipment->setMethod($method);
        }

        // Create invoice if not exists (will be removed by the synchronizer if needed)
        if (null === $shipment->getInvoice()) {
            $type = $shipment->isReturn() ? InvoiceTypes::TYPE_CREDIT : InvoiceTypes::TYPE_INVOICE;
            $this
                ->factory
                ->createInvoiceForSale($sale)
                ->setSale($sale)
                ->setShipment($shipment)
                ->setType($type);
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
        $item = null;

        // Skip compound sale items with only public children
        if (!($saleItem->isCompound() && !$saleItem->hasPrivateChildren())) {
            // Existing item lookup
            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $saleItem) {
                    $item = $shipmentItem;
                }
            }
            // Not found, create it
            if (null === $item) {
                $item = $this->factory->createItemForShipment($shipment);
                $item->setShipment($shipment);
                $item->setSaleItem($saleItem);
            }

            if (!$saleItem->isCompound()) {
                $expected = $shipment->isReturn()
                    ? $this->calculator->calculateReturnableQuantity($item)
                    : $this->calculator->calculateShippableQuantity($item);

                if (0 < $expected) {
                    // Set expected quantity
                    $item->setExpected($expected);

                    if ($shipment->isReturn()) {
                        // Set expected quantity as available
                        $item->setAvailable($expected);
                    } else {
                        // Set available quantity
                        $available = $this->calculator->calculateAvailableQuantity($item);
                        $item->setAvailable($available);

                        // Set default quantity for new non return shipment items
                        if (null === $shipment->getId()) {
                            $item->setQuantity(min($expected, $available));
                        }
                    }
                } else {
                    // Remove unexpected item
                    $shipment->removeItem($item);
                    $item = null;
                }
            }
        }

        if (null !== $item && $saleItem->isCompound()) {
            $available = $expected = null;
            foreach ($saleItem->getChildren() as $childSaleItem) {
                if (null !== $child = $this->buildItem($childSaleItem, $shipment)) {
                    $saleItemQty = $childSaleItem->getQuantity();

                    $a = $child->getAvailable() / $saleItemQty;
                    if (null === $available || $available > $a) {
                        $available = $a;
                    }

                    $e = $child->getExpected() / $saleItemQty;
                    if (null === $expected || $expected > $e) {
                        $expected = $e;
                    }
                }
            }

            if (0 < $expected) {
                // Set expected and available quantities
                $item->setExpected($expected);

                if ($shipment->isReturn()) {
                    // Set expected quantity as available
                    $item->setAvailable($expected);
                } else {
                    // Set available quantity
                    $item->setAvailable($available);

                    // Set default quantity for new non return shipment items
                    if (null === $shipment->getId()) {
                        $item->setQuantity(min($expected, $available));
                    }
                }
            } else {
                // Remove unexpected item
                $shipment->removeItem($item);
                $item = null;
            }
        } else {
            foreach ($saleItem->getChildren() as $childSaleItem) {
                $this->buildItem($childSaleItem, $shipment);
            }
        }

        return $item;
    }
}
