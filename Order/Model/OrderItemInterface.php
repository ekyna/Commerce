<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;

/**
 * Interface OrderItemInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface getSale()
 */
interface OrderItemInterface extends SaleItemInterface, StockAssignmentsInterface
{
    public function getOrder(): ?OrderInterface;

    public function setOrder(?OrderInterface $order): OrderItemInterface;
}
