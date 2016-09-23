<?php

namespace Ekyna\Component\Commerce\Payment\Repository;

/**
 * Interface PaymentMethodRepositoryInterface
 * @package Ekyna\Component\Commerce\Payment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentMethodRepositoryInterface
{
    /**
     * Create a new payment method with pre-populated messages (one by notifiable state).
     *
     * @return \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface
     */
    public function createNew();
}
