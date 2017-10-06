<?php

namespace Ekyna\Component\Commerce\Payment\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\TranslatableResourceRepositoryInterface;

/**
 * Interface PaymentMethodRepositoryInterface
 * @package Ekyna\Component\Commerce\Payment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentMethodRepositoryInterface extends TranslatableResourceRepositoryInterface
{
    /**
     * Create a new payment method with pre-populated messages (one by notifiable state).
     *
     * @return \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface
     */
    public function createNew();

    /**
     * Finds the available and enabled payment methods.
     *
     * @return \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface[]
     */
    public function findAvailable();

    /**
     * Finds the enabled payment methods.
     *
     * @return \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface[]
     */
    public function findEnabled();
}
