<?php

namespace Ekyna\Component\Commerce\Order\Repository;

use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Interface OrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Order\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface|null findOneById(int $id)
 * @method OrderInterface|null findOneByKey(string $key)
 * @method OrderInterface|null findOneByNumber(string $number)
 */
interface OrderRepositoryInterface extends SaleRepositoryInterface
{
    /**
     * Creates a new order instance.
     *
     * @return OrderInterface
     */
    public function createNew();

    /**
     * Returns whether an order exists for the given customer.
     *
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    public function existsForCustomer(CustomerInterface $customer);

    /**
     * Returns whether an order exists for the given email.
     *
     * @param string $email
     *
     * @return bool
     */
    public function existsForEmail(string $email);

    /**
     * Finds the sales by origin customer, optionally filtered by states.
     *
     * @param CustomerInterface $customer
     * @param array             $states
     *
     * @return array|OrderInterface[]
     */
    public function findByOriginCustomer(CustomerInterface $customer, array $states = []);

    /**
     * Finds orders completed yesterday.
     *
     * @return array|OrderInterface[]
     */
    public function findCompletedYesterday(): array;

    /**
     * Returns the dues orders.
     *
     * @return OrderInterface[]
     */
    public function findDueOrders();

    /**
     * Finds orders having revenue total or margin total set to null.
     *
     * @return int[]
     */
    public function findWithNullRevenueOrMargin(): array;

    /**
     * Returns the regular (payment term less) due.
     *
     * @return float
     */
    public function getRegularDue();

    /**
     * Returns the regular (payment term less) due orders.
     *
     * @return OrderInterface[]
     */
    public function getRegularDueOrders();

    /**
     * Returns the customers expired due.
     *
     * @return float
     */
    public function getOutstandingExpiredDue();

    /**
     * Returns the customers expired due orders.
     *
     * @return OrderInterface[]
     */
    public function getOutstandingExpiredDueOrders();

    /**
     * Returns the customers fall due.
     *
     * @return float
     */
    public function getOutstandingFallDue();

    /**
     * Returns the customers fall due orders.
     *
     * @return OrderInterface[]
     */
    public function getOutstandingFallDueOrders();

    /**
     * Returns the customers pending due.
     *
     * @return float
     */
    public function getOutstandingPendingDue();

    /**
     * Returns the customers pending due orders.
     *
     * @return OrderInterface[]
     */
    public function getOutstandingPendingDueOrders();

    /**
     * Returns the remaining (that needs to be invoiced) total.
     *
     * @return float
     */
    public function getRemainingTotal();

    /**
     * Returns the remaining (that needs to be invoiced) orders.
     *
     * @return OrderInterface[]
     */
    public function getRemainingOrders();

    /**
     * Returns the currencies used by the customer.
     *
     * @param CustomerInterface $customer
     *
     * @return string[] The currencies codes.
     */
    public function getCustomerCurrencies(CustomerInterface $customer);

    /**
     * Returns the coupon code usage.
     *
     * @param CouponInterface $coupon
     *
     * @return int
     */
    public function getCouponUsage(CouponInterface $coupon): int;
}
