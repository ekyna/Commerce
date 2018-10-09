<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Class RemainingEntry
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RemainingEntry
{
    /**
     * @var SaleItemInterface
     */
    private $saleItem;

    /**
     * @var float
     */
    private $quantity;


    /**
     * Returns the saleItem.
     *
     * @return SaleItemInterface
     */
    public function getSaleItem()
    {
        return $this->saleItem;
    }

    /**
     * Sets the saleItem.
     *
     * @param SaleItemInterface $saleItem
     *
     * @return RemainingEntry
     */
    public function setSaleItem($saleItem)
    {
        $this->saleItem = $saleItem;

        return $this;
    }

    /**
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return RemainingEntry
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }
}
