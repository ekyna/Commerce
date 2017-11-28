<?php

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractAdjustmentListener;
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
    protected function getAdjustmentFromEvent(ResourceEventInterface $event)
    {
        $adjustment = $event->getResource();

        if (!$adjustment instanceof QuoteItemAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemAdjustmentInterface");
        }

        return $adjustment;
    }

    /**
     * @inheritDoc
     */
    protected function getSaleChangeEvent()
    {
        return QuoteEvents::CONTENT_CHANGE;
    }

    /**
     * @inheritDoc
     */
    protected function getSalePropertyPath()
    {
        return 'quote';
    }
}
