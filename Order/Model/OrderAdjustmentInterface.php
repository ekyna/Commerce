<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface;

/**
 * Interface OrderAdjustmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderAdjustmentInterface extends SaleAdjustmentInterface
{
    public function getOrder(): ?OrderInterface;

    public function setOrder(?OrderInterface $order): OrderAdjustmentInterface;
}
