<?php

namespace Ekyna\Component\Commerce\Payment\Repository;

use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface PaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Payment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface PaymentRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the payment by key.
     *
     * @param string $key
     *
     * @return PaymentInterface|null
     */
    public function findOneByKey($key);

    /**
     * Finds payments by method and states and optionally from date.
     *
     * @param PaymentMethodInterface $method
     * @param array                  $states
     * @param \DateTime              $fromDate
     *
     * @return PaymentInterface[]
     */
    public function findByMethodAndStates(PaymentMethodInterface $method, array $states, \DateTime $fromDate = null);
}
