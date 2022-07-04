<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Repository;

use DateTimeInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface PaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Payment\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @template P of PaymentInterface
 * @implements ResourceRepositoryInterface<P>
 */
interface PaymentRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the payment by key.
     *
     * @return P|null
     */
    public function findOneByKey(string $key): ?PaymentInterface;

    /**
     * Finds payments by method and states and optionally from date.
     *
     * @param PaymentMethodInterface $method
     * @param array                  $states
     * @param bool                   $filter TRUE for payments, FALSE for refunds, NULL for all
     * @param DateTimeInterface|null $fromDate
     *
     * @return array<P>
     */
    public function findByMethodAndStates(
        PaymentMethodInterface $method,
        array                  $states,
        bool                   $filter = null,
        DateTimeInterface      $fromDate = null
    ): array;

    /**
     * Finds payments (and refunds) by month.
     *
     * @return array<P>
     */
    public function findByMonth(DateTimeInterface $date, array $states): array;
}
