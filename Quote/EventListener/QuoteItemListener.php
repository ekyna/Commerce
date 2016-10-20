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
    protected function getSaleItemFromEvent(ResourceEventInterface $event)
    {
        $item = $event->getResource();

        if (!$item instanceof QuoteItemInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteItemInterface");
        }

        return $item;
    }
}
