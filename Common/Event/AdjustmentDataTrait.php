<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Event;

use Ekyna\Component\Commerce\Common\Model\AdjustmentDataInterface;

/**
 * Trait AdjustmentDataTrait
 * @package Ekyna\Component\Commerce\Common\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait AdjustmentDataTrait
{
    /** @var AdjustmentDataInterface[] */
    private array $adjustmentsData = [];


    /**
     * Returns the adjustments data.
     *
     * @return AdjustmentDataInterface[]
     */
    public function getAdjustmentsData(): array
    {
        return $this->adjustmentsData;
    }

    /**
     * Adds the adjustment data.
     *
     * @param AdjustmentDataInterface $data
     */
    public function addAdjustmentData(AdjustmentDataInterface $data): void
    {
        $this->adjustmentsData[] = $data;
    }
}
