<?php

namespace Acme\Product\EventListener;

use Acme\Product\Entity\StockUnit;
use Acme\Product\Event\ProductEvents;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\EventListener\AbstractStockUnitListener;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class StockUnitEventSubscriber
 * @package Acme\Product\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitEventSubscriber extends AbstractStockUnitListener
{
    /**
     * @inheritDoc
     */
    protected function getStockUnitFromEvent(ResourceEventInterface $event): StockUnitInterface
    {
        $stockUnit = $event->getResource();

        if (!$stockUnit instanceof StockUnit) {
            throw new InvalidArgumentException("Expected instance of " . StockUnit::class);
        }

        return $stockUnit;
    }

    /**
     * @inheritDoc
     */
    protected function getSubjectStockUnitChangeEventName(): string
    {
        return ProductEvents::STOCK_UNIT_CHANGE;
    }

    protected function getSubjectStockUnitRemoveEventName(): string
    {
        return ProductEvents::STOCK_UNIT_REMOVE;
    }
}
