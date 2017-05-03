<?php

namespace Ekyna\Component\Commerce\Credit\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Credit\Util\CreditUtil;
use Ekyna\Component\Commerce\Credit\Model\CreditInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Class CreditBuilder
 * @package Ekyna\Component\Commerce\Credit\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreditBuilder implements CreditBuilderInterface
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
     * @inheritDoc
     */
    public function build(CreditInterface $credit)
    {
        $sale = $credit->getSale();

        foreach ($sale->getItems() as $saleItem) {
            $this->buildItem($saleItem, $credit);
        }
    }

    public function buildFromShipment(ShipmentInterface $shipment)
    {
        // TODO
    }

    /**
     * Builds the shipment item by pre populating quantity.
     *
     * @param SaleItemInterface $saleItem
     * @param CreditInterface $credit
     */
    protected function buildItem(SaleItemInterface $saleItem, CreditInterface $credit)
    {
        if ($saleItem->hasChildren()) {
            foreach ($saleItem->getChildren() as $childSaleItem) {
                $this->buildItem($childSaleItem, $credit);
            }

            return;
        }

        $item = $this->factory->createItemForCredit($credit);
        $item->setSaleItem($saleItem);
        $credit->addItem($item);

        $creditable = CreditUtil::calculateCreditableQuantity($item);

        if (0 >= $creditable) {
            $credit->removeItem($item);
            return;
        }
    }
}
