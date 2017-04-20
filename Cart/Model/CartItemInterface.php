<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface CartItemInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CartInterface getSale()
 */
interface CartItemInterface extends SaleItemInterface
{
    public function getCart(): ?CartInterface;

    public function setCart(?CartInterface $cart): CartItemInterface;
}
