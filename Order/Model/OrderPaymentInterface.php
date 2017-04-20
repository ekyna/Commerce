<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface OrderPaymentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderPaymentInterface extends PaymentInterface
{
    public function getOrder(): ?OrderInterface;

    public function setOrder(?OrderInterface $order): OrderPaymentInterface;
}
