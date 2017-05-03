<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Credit\Model\CreditSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

/**
 * Interface OrderInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends Common\SaleInterface, ShipmentSubjectInterface, CreditSubjectInterface
{
    /**
     * Returns the "completed at" datetime.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the "completed at" datetime.
     *
     * @param \DateTime $completedAt
     *
     * @return $this|OrderInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);
}
