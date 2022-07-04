<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Repository;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderPaymentInterface;
use Ekyna\Component\Commerce\Payment\Repository\PaymentRepositoryInterface;

/**
 * Interface OrderPaymentRepositoryInterface
 * @package Ekyna\Component\Commerce\Order\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements PaymentRepositoryInterface<OrderPaymentInterface>
 */
interface OrderPaymentRepositoryInterface extends PaymentRepositoryInterface
{
    public function findOneByOrderAndKey(OrderInterface $order, string $key): ?OrderPaymentInterface;

    /**
     * Finds payments for the given customer and date range.
     *
     * @return array<OrderPaymentInterface>
     */
    public function findByCustomerAndDateRange(
        CustomerInterface $customer,
        string            $currency = null,
        DateTimeInterface $from = null,
        DateTimeInterface $to = null
    ): array;

    /**
     * Returns the customer payments amounts sum.
     */
    public function getCustomerPaymentSum(CustomerInterface $customer, DateTime $from, DateTime $to): Decimal;

    /**
     * Returns the customer refunds amounts sum.
     */
    public function getCustomerRefundSum(CustomerInterface $customer, DateTime $from, DateTime $to): Decimal;

    /**
     * Returns the customer payments count.
     */
    public function getCustomerPaymentCount(CustomerInterface $customer, DateTime $from, DateTime $to): int;

    /**
     * Returns the customer refunds count.
     */
    public function getCustomerRefundCount(CustomerInterface $customer, DateTime $from, DateTime $to): int;
}
