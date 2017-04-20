<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartItemInterface;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleItemListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartItemListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemListener extends AbstractSaleItemListener
{
    /**
     * @inheritDoc
     */
    protected function scheduleSaleContentChangeEvent(Model\SaleInterface $sale)
    {
        $this->persistenceHelper->scheduleEvent($sale, CartEvents::CONTENT_CHANGE);
    }

    /**
     * @inheritDoc
     */
    protected function getSalePropertyPath()
    {
        return 'cart';
    }

    /**
     * @inheritDoc
     */
    protected function getSaleItemFromEvent(ResourceEventInterface $event)
    {
        $item = $event->getResource();

        if (!$item instanceof CartItemInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemInterface");
        }

        return $item;
    }
}
