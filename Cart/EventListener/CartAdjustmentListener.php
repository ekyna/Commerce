<?php

namespace Ekyna\Component\Commerce\Cart\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractAdjustmentListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\Model\CartAdjustmentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class CartAdjustmentListener
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAdjustmentListener extends AbstractAdjustmentListener
{
    /**
     * @inheritdoc
     */
    protected function scheduleSaleContentChangeEvent(Model\AdjustmentInterface $adjustment)
    {
        /** @var \Ekyna\Component\Commerce\Cart\Model\CartInterface $cart */
        $cart = $adjustment->getAdjustable();

        $this->persistenceHelper->scheduleEvent(CartEvents::CONTENT_CHANGE, $cart);
    }

    /**
     * @inheritdoc
     */
    protected function getAdjustmentFromEvent(ResourceEventInterface $event)
    {
        $adjustment = $event->getResource();

        if (!$adjustment instanceof CartAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of CartAdjustmentInterface");
        }

        return $adjustment;
    }
}
