<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Event;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Event\ResourceEvent;

/**
 * Class SubjectStockUnitEvent
 * @package Ekyna\Component\Commerce\Stock\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SubjectStockUnitEvent extends ResourceEvent
{
    protected StockUnitInterface $stockUnit;

    public function __construct(StockUnitInterface $stockUnit)
    {
        $this->stockUnit = $stockUnit;

        $this->setResource($stockUnit->getSubject());
    }

    public function getStockUnit(): StockUnitInterface
    {
        return $this->stockUnit;
    }
}
