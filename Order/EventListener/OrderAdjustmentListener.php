<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractAdjustmentListener;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderAdjustmentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderAdjustmentListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAdjustmentListener extends AbstractAdjustmentListener
{
    /**
     * @inheritDoc
     */
    protected function getAdjustmentFromEvent(ResourceEventInterface $event)
    {
        $adjustment = $event->getResource();

        if (!$adjustment instanceof OrderAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderAdjustmentInterface");
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
