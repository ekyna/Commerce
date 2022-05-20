<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleItemListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartItemListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemListener extends AbstractSaleItemListener
{
    protected function scheduleSaleContentChangeEvent(Model\SaleInterface $sale): void
    {
        $this->persistenceHelper->scheduleEvent($sale, CartEvents::CONTENT_CHANGE);
    }

    protected function getSalePropertyPath(): string
    {
        return 'cart';
    }

    protected function getSaleItemFromEvent(ResourceEventInterface $event): Model\SaleItemInterface
    {
        $item = $event->getResource();

        if (!$item instanceof CartItemInterface) {
            throw new UnexpectedTypeException($item, CartItemInterface::class);
        }

        return $item;
    }
}
