<?php

namespace Ekyna\Component\Commerce\Payment\Watcher;

use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;

/**
 * Interface WatcherInterface
 * @package Ekyna\Component\Commerce\Payment\Watcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface WatcherInterface
{
    /**
     * Watch for outstanding payments.
     *
     * @param PaymentRepositoryInterface $paymentRepository
     *
     * @return bool Whether some payments have been updated.
     */
    public function watch(PaymentRepositoryInterface $paymentRepository);
}
