<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\SaleNotificationInterface;

/**
 * Interface OrderNotificationInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderNotificationInterface extends SaleNotificationInterface
{
    public function getOrder(): ?OrderInterface;

    public function setOrder(?OrderInterface $order): OrderNotificationInterface;
}
