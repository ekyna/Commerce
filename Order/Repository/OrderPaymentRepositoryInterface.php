<?php

namespace Ekyna\Component\Commerce\Order\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
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

    /**
     * Finds payments for the given customer and date range.
     *
     * @param CustomerInterface $customer
     * @param string            $currency
     * @param \DateTime|null    $from
     * @param \DateTime|null    $to
     *
     * @return OrderPaymentInterface[]
     */
    public function findByCustomerAndDateRange(
        CustomerInterface $customer,
        string $currency = null,
        \DateTime $from = null,
        \DateTime $to = null
    ): array;

    /**
     * Returns the customer payments amounts sum.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     *
     * @return float
     */
    public function getCustomerPaymentSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float;

    /**
     * Returns the customer refunds amounts sum.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     *
     * @return float
     */
    public function getCustomerRefundSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float;

    /**
     * Returns the customer payments count.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     *
     * @return int
     */
    public function getCustomerPaymentCount(CustomerInterface $customer, \DateTime $from, \DateTime $to): int;

    /**
     * Returns the customer refunds count.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     *
     * @return int
     */
    public function getCustomerRefundCount(CustomerInterface $customer, \DateTime $from, \DateTime $to): int;
}
