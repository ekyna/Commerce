<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Product\Event\ProductStockUnitEvents;
use Ekyna\Component\Commerce\Product\EventListener\ProductStockUnitListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ProductStockUnitSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnitEventSubscriber extends ProductStockUnitListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ProductStockUnitEvents::INSERT => ['onInsert', 0],
            ProductStockUnitEvents::UPDATE => ['onUpdate', 0],
            ProductStockUnitEvents::DELETE => ['onDelete', 0],
        ];
    }
}
