<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractAdjustmentListener;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderItemAdjustmentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderItemAdjustmentListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAdjustmentListener extends AbstractAdjustmentListener
{
    /**
     * @inheritdoc
     */
    protected function getAdjustmentFromEvent(ResourceEventInterface $event)
    {
        $adjustment = $event->getResource();

        if (!$adjustment instanceof OrderItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderItemAdjustmentInterface");
        }

        return $adjustment;
    }

    /**
     * @inheritDoc
     */
    protected function getSaleChangeEvent()
    {
        return OrderEvents::CONTENT_CHANGE;
    }

    /**
     * @inheritDoc
     */
    protected function getSalePropertyPath()
    {
        return 'order';
    }
}
