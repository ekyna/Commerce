<?php

namespace Ekyna\Component\Commerce\Common\Resolver;

use Ekyna\Component\Commerce\Common\Event;
use Ekyna\Component\Commerce\Common\Model;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class DiscountResolver
 * @package Ekyna\Component\Commerce\Common\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DiscountResolver implements DiscountResolverInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function resolveSale(Model\SaleInterface $sale)
    {
        $event = new Event\SaleEvent($sale);

        $this->eventDispatcher->dispatch(Event\SaleEvents::DISCOUNT, $event);

        return $event->getAdjustmentsData();
    }

    /**
     * @inheritdoc
     */
    public function resolveSaleItem(Model\SaleItemInterface $item)
    {
        // Don't apply discounts to private items (they will inherit from parents)
        if ($item->isPrivate()) {
            return [];
        }

        // Don't apply discount to compound items with only public children
        if ($item->isCompound() && !$item->hasPrivateChildren()) {
            return [];
        }

        $event = new Event\SaleItemEvent($item);

        $this->eventDispatcher->dispatch(Event\SaleItemEvents::DISCOUNT, $event);

        return $event->getAdjustmentsData();
    }
}
