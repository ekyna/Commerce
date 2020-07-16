<?php

namespace Ekyna\Component\Commerce\Cart\Repository;

use Ekyna\Component\Commerce\Cart\Model\CartPaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;

/**
 * Interface CartPaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Cart\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CartPaymentInterface findOneByKey(string $key)
 */
interface CartPaymentRepositoryInterface extends PaymentRepositoryInterface
{
    /**
     * Creates a new cart payment instance.
     *
     * @return CartPaymentInterface
     */
    public function createNew();
}
