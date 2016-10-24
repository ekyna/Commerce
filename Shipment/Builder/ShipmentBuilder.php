<?php

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Class ShipmentBuilder
 * @package Ekyna\Component\Commerce\Shipment\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentBuilder implements ShipmentBuilderInterface
{
    /**
     * @var string
     */
    private $shipmentItemClass;


    /**
     * Constructor.
     *
     * @param string $shipmentItemClass
     */
    public function __construct($shipmentItemClass)
    {
        $this->shipmentItemClass = $shipmentItemClass;
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

        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface $item */
        $item = new $this->shipmentItemClass;

        $item
            ->setSaleItem($saleItem)
            ->setQuantity(
                $this->calculateRemainingQuantity($saleItem)
            );

        $shipment->addItem($item);
    }

    /**
     * Calculates the sale item remaining shipment quantity.
     *
     * @param SaleItemInterface $saleItem
     *
     * @return float
     */
    protected function calculateRemainingQuantity(SaleItemInterface $saleItem)
    {
        $quantity = $saleItem->getTotalQuantity();

        /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface $sale */
        $sale = $saleItem->getSale();
        foreach ($sale->getShipments() as $shipment) {
            // Skip if shipment is new (it could be the one we are building)
            if (null === $shipment->getId()) {
                continue;
            }

            // Skip if shipment is cancelled
            if ($shipment->getState() === ShipmentStates::STATE_CANCELLED) {
                continue;
            }

            // Find matching sale item
            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() == $saleItem) {
                    $quantity -= $shipmentItem->getQuantity();
                }
            }

            // TODO watch for returned Shipments
        }

        return $quantity;
    }
}
