<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Common\Model\SaleAdjustmentInterface;

/**
 * Interface CartAdjustmentInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartAdjustmentInterface extends SaleAdjustmentInterface
{
    public function getCart(): ?CartInterface;

    public function setCart(?CartInterface $cart): CartAdjustmentInterface;
}
