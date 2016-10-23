<?php

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractAdjustmentListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemAdjustmentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class QuoteItemAdjustmentListener
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemAdjustmentListener extends AbstractAdjustmentListener
{
    /**
     * @inheritdoc
     */
    protected function dispatchSaleContentChangeEvent(Model\AdjustmentInterface $adjustment)
    {
        /** @var \Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface $item */
        $item = $adjustment->getAdjustable();

        $event = $this->dispatcher->createResourceEvent($item->getSale());

        $this->dispatcher->dispatch(QuoteEvents::CONTENT_CHANGE, $event);
    }

    /**
     * @inheritdoc
     */
    protected function getAdjustmentFromEvent(ResourceEventInterface $event)
    {
        $adjustment = $event->getResource();

        if (!$adjustment instanceof QuoteItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemAdjustmentInterface");
        }

        return $adjustment;
    }
}
