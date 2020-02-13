<?php

namespace Ekyna\Component\Commerce\Stock\Event;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;

/**
 * Class SubjectStockUnitEvent
 * @package Ekyna\Component\Commerce\Stock\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SubjectStockUnitEvent extends ResourceEvent
{
    /**
     * @var StockUnitInterface
     */
    protected $stockUnit;


    /**
     * Constructor.
     *
     * @param StockUnitInterface $stockUnit
     */
    public function __construct(StockUnitInterface $stockUnit)
    {
        $this->stockUnit = $stockUnit;

        $this->setResource($stockUnit->getSubject());
    }

    /**
     * Returns the stockUnit.
     *
     * @return StockUnitInterface
     */
    public function getStockUnit(): StockUnitInterface
    {
        return $this->stockUnit;
    }
}
