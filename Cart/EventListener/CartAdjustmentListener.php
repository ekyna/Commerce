<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractAdjustmentListener;
use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartAdjustmentInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartAdjustmentListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAdjustmentListener extends AbstractAdjustmentListener
{
    /**
     * @inheritDoc
     */
    protected function getAdjustmentFromEvent(ResourceEventInterface $event)
    {
        $adjustment = $event->getResource();

        if (!$adjustment instanceof CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartAdjustmentInterface");
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
