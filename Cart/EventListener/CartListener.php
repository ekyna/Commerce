<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartListener extends AbstractSaleListener
{
    // TODO expiresAt updates

    /**
     * @inheritdoc
     */
    protected function getSaleFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface");
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function scheduleContentChangeEvent(SaleInterface $sale)
    {
        if (!$sale instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface");
        }

        $this->persistenceHelper->scheduleEvent(CartEvents::CONTENT_CHANGE, $sale);
    }

    /**
     * @inheritdoc
     */
    protected function scheduleStateChangeEvent(SaleInterface $sale)
    {
        if (!$sale instanceof CartInterface) {
            throw new InvalidArgumentException("Expected instance of CartInterface");
        }

        $this->persistenceHelper->scheduleEvent(CartEvents::STATE_CHANGE, $sale);
    }
}
