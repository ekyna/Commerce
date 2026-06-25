<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Common\Event\SaleEvent;
use Ekyna\Component\Commerce\Common\Event\SaleEvents;
use Ekyna\Component\Commerce\Common\Event\SaleItemDiscountEvent;
use Ekyna\Component\Commerce\Common\Model;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class DiscountResolver
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DiscountResolver implements DiscountResolverInterface
{
    private EventDispatcherInterface $eventDispatcher;


    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function resolveSale(Model\SaleInterface $sale): SaleEvent
    {
        $event = new SaleEvent($sale);

        $this->eventDispatcher->dispatch($event, SaleEvents::DISCOUNT); // TODO SaleDiscountEvent

        return $event;
    }

    public function resolveSaleItem(Model\SaleItemInterface $item): SaleItemDiscountEvent
    {
        $event = new SaleItemDiscountEvent($item);

        // Don't apply discounts to private items (they will inherit from parents)
        if ($item->isPrivate()) {
            return $event;
        }

        // Don't apply discount to compound items with only public children
        if ($item->isCompound() && !$item->hasPrivateChildren()) {
            return $event;
        }

        $this->eventDispatcher->dispatch($event);

        return $event;
    }
}
