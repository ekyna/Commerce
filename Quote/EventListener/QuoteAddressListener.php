<?php

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleAddressListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\Model\QuoteAddressInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class QuoteAddressListener
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAddressListener extends AbstractSaleAddressListener
{
    /**
     * @inheritdoc
     */
    protected function scheduleSaleTaxResolutionEvent(Model\AddressInterface $address)
    {
        /** @var QuoteAddressInterface $address */
        $this->persistenceHelper->scheduleEvent(QuoteEvents::TAX_RESOLUTION, $address->getQuote());
    }

    /**
     * @inheritdoc
     */
    protected function getAddressFromEvent(ResourceEventInterface $event)
    {
        $address = $event->getResource();

        if (!$address instanceof QuoteAddressInterface) {
            throw new InvalidArgumentException("Expected instance of QuoteAddressInterface.");
        }

        return $address;
    }
}
