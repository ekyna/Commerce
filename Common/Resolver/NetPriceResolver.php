<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Event\SaleItemNetPriceEvent;
use Ekyna\Component\Commerce\Common\Model;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class NetPriceResolver
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NetPriceResolver implements NetPriceResolverInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function resolveSaleItem(Model\SaleItemInterface $item): SaleItemNetPriceEvent
    {
        $event = new SaleItemNetPriceEvent($item);

        // Don't update compound items with only public children
        if ($item->isCompound() && !$item->hasPrivateChildren()) {
            $event->setNetPrice(new Decimal(0));
            $event->stopPropagation();

            return $event;
        }

        $this->eventDispatcher->dispatch($event);

        return $event;
    }
}
