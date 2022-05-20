<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Quote\EventListener;

use Ekyna\Component\Commerce\Common\EventListener\AbstractSaleItemListener;
use Ekyna\Component\Commerce\Common\Model;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
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
    protected function scheduleSaleContentChangeEvent(Model\SaleInterface $sale): void
    {
        $this->persistenceHelper->scheduleEvent($sale, QuoteEvents::CONTENT_CHANGE);
    }

    protected function getSalePropertyPath(): string
    {
        return 'quote';
    }

    protected function getSaleItemFromEvent(ResourceEventInterface $event): Model\SaleItemInterface
    {
        $item = $event->getResource();

        if (!$item instanceof QuoteItemInterface) {
            throw new UnexpectedTypeException($item, QuoteItemInterface::class);
        }

        return $item;
    }
}
