<?php

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;

/**
 * Class StockUnitAssigner
 * @package Ekyna\Component\Commerce\Stock\Assigner
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitAssigner implements StockUnitAssignerInterface
{
    // TODO Create service and inject into listeners

    /**
     * @var StockUnitResolverInterface
     */
    protected $stockUnitResolver;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface $stockUnitResolver
     */
    public function __construct(StockUnitResolverInterface $stockUnitResolver)
    {
        $this->stockUnitResolver = $stockUnitResolver;
    }

    /**
     * @inheritdoc
     */
    public function createAssignments(SaleItemInterface $item)
    {
        // Find enough available stock units, create some if needed.
        $stockUnits = $this->stockUnitResolver->findUnassigned($item);

        // TODO Need SaleFactory
        // foreach stock units : dispatch sale item quantity by creating assignment(s).

        // If quantity has not been fully dispatched
        //     -> create stock unit (and persist and schedule events)
        //     -> create assignment

        // Persist stock assignments and schedule events
    }

    /**
     * @inheritdoc
     */
    public function dispatchQuantityChange(SaleItemInterface $item, $deltaQuantity)
    {
        // Determine on which stock units the quantity change should be dispatched

        // TODO some assignments maybe created or removed

        // Persist stock assignments and schedule events
    }

    /**
     * @inheritdoc
     */
    public function removeAssignments(SaleItemInterface $item)
    {
        // Remove stock assignments and schedule events
    }
}
