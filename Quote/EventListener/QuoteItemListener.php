<?php

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleItemListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\Model\QuoteItemInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class QuoteItemListener
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemListener extends AbstractSaleItemListener
{
    /**
     * @inheritDoc
     */
    protected function scheduleSaleContentChangeEvent(Model\SaleInterface $sale)
    {
        $this->persistenceHelper->scheduleEvent($sale, QuoteEvents::CONTENT_CHANGE);
    }

    /**
     * @inheritDoc
     */
    protected function getSalePropertyPath()
    {
        return 'quote';
    }

    /**
     * @inheritDoc
     */
    protected function getSaleItemFromEvent(ResourceEventInterface $event)
    {
        $item = $event->getResource();

        if (!$item instanceof QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemInterface");
        }

        return $item;
    }
}
