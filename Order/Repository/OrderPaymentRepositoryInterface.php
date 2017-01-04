<?php

namespace Ekyna\Component\Commerce\Order\Repository;

use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;

/**
 * Interface OrderPaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Order\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderPaymentInterface|null findOneByKey($key)
 */
interface OrderPaymentRepositoryInterface extends PaymentRepositoryInterface
{
    /**
     * Creates a new order payment instance.
     *
     * @return OrderPaymentInterface
     */
    public function createNew();
}
