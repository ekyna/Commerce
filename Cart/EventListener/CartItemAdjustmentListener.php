<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractAdjustmentListener;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartItemAdjustmentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartItemAdjustmentListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemAdjustmentListener extends AbstractAdjustmentListener
{
    /**
     * @inheritdoc
     */
    protected function getAdjustmentFromEvent(ResourceEventInterface $event)
    {
        $adjustment = $event->getResource();

        if (!$adjustment instanceof CartItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartItemAdjustmentInterface");
        }

        return $adjustment;
    }

    /**
     * @inheritDoc
     */
    protected function getSaleChangeEvent()
    {
        return CartEvents::CONTENT_CHANGE;
    }

    /**
     * @inheritDoc
     */
    protected function getSalePropertyPath()
    {
        return 'cart';
    }
}
