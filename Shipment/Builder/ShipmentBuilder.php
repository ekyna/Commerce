<?php

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

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
        $sale = $shipment->getSale();

        if (null !== $method = $sale->getPreferredShipmentMethod()) {
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
     */
    protected function buildItem(SaleItemInterface $saleItem, ShipmentInterface $shipment)
    {
        if ($saleItem->isCompound() && $saleItem->hasChildren()) {
            foreach ($saleItem->getChildren() as $childSaleItem) {
                $this->buildItem($childSaleItem, $shipment);
            }

            return;
        }

        $item = $this->factory->createItemForShipment($shipment);
        $item->setSaleItem($saleItem);
        $shipment->addItem($item);

        $expected = $shipment->isReturn()
            ? $this->calculator->calculateReturnableQuantity($item)
            : $this->calculator->calculateShippableQuantity($item);

        if (0 >= $expected) {
            $shipment->removeItem($item);

            return;
        } elseif (!$shipment->isReturn()) {
            $item->setQuantity(min($expected, $this->calculator->calculateAvailableQuantity($item)));
        }

        if (!$saleItem->isCompound() && $saleItem->hasChildren()) {
            foreach ($saleItem->getChildren() as $childSaleItem) {
                $this->buildItem($childSaleItem, $shipment);
            }
        }
    }
}
