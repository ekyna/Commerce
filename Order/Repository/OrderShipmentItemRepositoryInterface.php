<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Repository;

use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Repository\ShipmentItemRepositoryInterface;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface OrderShipmentRepositoryInterface
 * @package Ekyna\Component\Commerce\Order\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @implements ResourceRepositoryInterface<OrderShipmentItemInterface>
 *
 * @method array<int, OrderShipmentItemInterface> findBySubjectAndDateRange(SubjectInterface $subject, ?DateRange $range)
 */
interface OrderShipmentItemRepositoryInterface extends ShipmentItemRepositoryInterface
{

}
