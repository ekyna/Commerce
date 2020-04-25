<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\EventListener\StockUnitListener;
use Ekyna\Component\Commerce\Stock\Event\StockUnitEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class StockUnitEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockUnitEventSubscriber extends StockUnitListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            StockUnitEvents::COST_CHANGE => ['onStockUnitCostChange', 0],
        ];
    }
}
