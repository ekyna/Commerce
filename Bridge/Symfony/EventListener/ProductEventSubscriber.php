<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Product\Event\ProductEvents;
use Ekyna\Component\Commerce\Product\EventListener\ProductListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductEventSubscriber extends ProductListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductEvents::INSERT             => ['onInsert', 0],
            ProductEvents::UPDATE             => ['onUpdate', 0],
            ProductEvents::DELETE             => ['onDelete', 0],
            ProductEvents::STOCK_UNIT_CHANGE  => ['onStockUnitChange', 0],
            ProductEvents::STOCK_UNIT_REMOVAL => ['onStockUnitRemoval', 0],
            ProductEvents::CHILD_STOCK_CHANGE => ['onChildStockChange', 0],
        ];
    }
}
