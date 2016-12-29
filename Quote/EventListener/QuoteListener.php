<?php

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleListener;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\Model\QuoteInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class QuoteEventSubscriber
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteListener extends AbstractSaleListener
{
    /**
     * @inheritdoc
     */
    protected function getSaleFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteInterface");
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function scheduleAddressChangeEvent(SaleInterface $sale)
    {
        if (!$sale instanceof QuoteInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteInterface");
        }

        $this->persistenceHelper->scheduleEvent(QuoteEvents::ADDRESS_CHANGE, $sale);
    }
}
