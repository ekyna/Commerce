<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Repository;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface ShipmentItemRepositoryInterface
 * @package Ekyna\Component\Commerce\Shipment\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<ShipmentItemInterface>
 */
interface ShipmentItemRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * @return array<int, ShipmentItemInterface>
     */
    public function findBySubjectAndDateRange(SubjectInterface $subject, ?DateRange $range): array;
}
