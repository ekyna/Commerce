<?php

namespace Ekyna\Component\Commerce\Product\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Product\Event\ProductEvents;
use Ekyna\Component\Commerce\Product\Model\ProductStockUnitInterface;
use Ekyna\Component\Commerce\Stock\EventListener\AbstractStockUnitListener;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class ProductStockUnitListener
 * @package Ekyna\Component\Commerce\Product\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductStockUnitListener extends AbstractStockUnitListener
{
    /**
     * @inheritdoc
     */
    protected function getStockUnitFromEvent(ResourceEventInterface $event)
    {
        $stockUnit = $event->getResource();

        if (!$stockUnit instanceof ProductStockUnitInterface) {
            throw new InvalidArgumentException("Expected instance of ProductStockUnitInterface.");
        }

        return $stockUnit;
    }

    /**
     * @inheritdoc
     */
    protected function getSubjectStockUnitChangeEventName()
    {
        return ProductEvents::STOCK_UNIT_CHANGE;
    }
}
