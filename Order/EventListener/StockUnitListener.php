<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Invalidator\OrderMarginInvalidator;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class StockUnitListener
 * @package Ekyna\Component\Commerce\Stat\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockUnitListener
{
    /**
     * @var OrderMarginInvalidator
     */
    private $orderMarginInvalidator;


    /**
     * Constructor.
     *
     * @param OrderMarginInvalidator $orderMarginInvalidator
     */
    public function __construct(OrderMarginInvalidator $orderMarginInvalidator)
    {
        $this->orderMarginInvalidator = $orderMarginInvalidator;
    }

    /**
     * Stock unit cost change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onStockUnitCostChange(ResourceEventInterface $event): void
    {
        $unit = $event->getResource();

        if (!$unit instanceof StockUnitInterface) {
            throw new UnexpectedTypeException($unit, StockUnitInterface::class);
        }

        $this->orderMarginInvalidator->addStockUnit($unit);
    }
}
