<?php

namespace Ekyna\Component\Commerce\Payment\Repository;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface PaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Payment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentRepositoryInterface
{
    /**
     * Finds the payment by key.
     *
     * @param string $key
     *
     * @return PaymentInterface|null
     */
    public function findOneByKey($key);
}
