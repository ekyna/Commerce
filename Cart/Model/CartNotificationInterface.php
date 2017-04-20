<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleNotificationInterface;

/**
 * Interface CartNotificationInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartNotificationInterface extends SaleNotificationInterface
{
    public function getCart(): ?CartInterface;

    public function setCart(?CartInterface $cart): CartNotificationInterface;
}
