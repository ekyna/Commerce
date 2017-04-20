<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Entity;

use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItemAdjustment;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderItemAdjustmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Class OrderItemAdjustment
 * @package Ekyna\Component\Commerce\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAdjustment extends AbstractSaleItemAdjustment implements OrderItemAdjustmentInterface
{
    protected function assertSaleItemClass(SaleItemInterface $item): void
    {
        if (!$item instanceof OrderItemInterface) {
            throw new UnexpectedTypeException($item, OrderItemInterface::class);
        }
    }
}
