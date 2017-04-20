<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

/**
 * Interface SaleNotificationInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface SaleNotificationInterface extends NotificationInterface
{
    public function getSale(): ?SaleInterface;

    public function setSale(?SaleInterface $sale): SaleNotificationInterface;
}
