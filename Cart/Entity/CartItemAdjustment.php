<?php

namespace Ekyna\Component\Commerce\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Model\CartItemAdjustmentInterface;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;
use Ekyna\Component\Commerce\Common\Entity\AbstractSaleItemAdjustment;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class CartItemAdjustment
 * @package Ekyna\Component\Commerce\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemAdjustment extends AbstractSaleItemAdjustment implements CartItemAdjustmentInterface
{
    /**
     * @inheritDoc
     */
    protected function assertSaleItemClass(SaleItemInterface $item): void
    {
        if (!$item instanceof CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of " . CartItemInterface::class);
        }
    }
}
