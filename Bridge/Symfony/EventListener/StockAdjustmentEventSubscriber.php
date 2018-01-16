<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Stock\Event\StockAdjustmentEvents;
use Ekyna\Component\Commerce\Stock\EventListener\StockAdjustmentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class StockAdjustmentListener
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentEventSubscriber extends StockAdjustmentListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            StockAdjustmentEvents::INSERT => ['onInsert', 0],
            StockAdjustmentEvents::UPDATE => ['onUpdate', 0],
            StockAdjustmentEvents::DELETE => ['onDelete', 0],
        ];
    }
}