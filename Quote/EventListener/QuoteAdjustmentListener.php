<?php

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractAdjustmentListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\Model\QuoteAdjustmentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class QuoteAdjustmentListener
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAdjustmentListener extends AbstractAdjustmentListener
{
    /**
     * @inheritdoc
     */
    protected function dispatchSaleContentChangeEvent(Model\SaleInterface $sale)
    {
        $event = $this->dispatcher->createResourceEvent($sale);

        $this->dispatcher->dispatch(QuoteEvents::CONTENT_CHANGE, $event);
    }

    /**
     * @inheritdoc
     */
    protected function getAdjustmentFromEvent(ResourceEventInterface $event)
    {
        $adjustment = $event->getResource();

        if (!$adjustment instanceof QuoteAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteAdjustmentInterface");
        }

        return $adjustment;
    }
}
