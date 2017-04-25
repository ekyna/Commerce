<?php

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Util\ShipmentUtil;

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
     * Constructor.
     *
     * @param SaleFactoryInterface $factory
     */
    public function __construct(SaleFactoryInterface $factory)
    {
        $this->factory = $factory;
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
        if ($saleItem->hasChildren()) {
            foreach ($saleItem->getChildren() as $childSaleItem) {
                $this->buildItem($childSaleItem, $shipment);
            }

            return;
        }

        $item = $this->factory->createItemForShipment($shipment);
        $item->setSaleItem($saleItem);
        $shipment->addItem($item);

        $expected = $shipment->isReturn()
            ? ShipmentUtil::calculateReturnableQuantity($item)
            : ShipmentUtil::calculateShippableQuantity($item);

        if (0 >= $expected) {
            $shipment->removeItem($item);
            return;
        }

        if (!$shipment->isReturn()) {
            $item->setQuantity(min($expected, ShipmentUtil::calculateAvailableQuantity($item)));
        }
    }
}
