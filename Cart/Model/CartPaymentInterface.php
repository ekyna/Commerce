<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Model;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface CartPaymentInterface
 * @package Ekyna\Component\Commerce\Cart\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CartPaymentInterface extends PaymentInterface
{
    public function getCart(): ?CartInterface;

    public function setCart(?CartInterface $cart): CartPaymentInterface;
}
