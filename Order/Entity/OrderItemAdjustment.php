<?php

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItemAdjustment;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderItemAdjustmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Class OrderItemAdjustment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAdjustment extends AbstractSaleItemAdjustment implements OrderItemAdjustmentInterface
{
    /**
     * @inheritDoc
     */
    protected function assertSaleItemClass(SaleItemInterface $item)
    {
        if (!$item instanceof OrderItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderItemInterface::class);
        }
    }
}
