<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Repository;

use Decimal\Decimal;
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
     * Returns whether an order exists for the given customer.
     */
    public function existsForCustomer(CustomerInterface $customer): bool;

    /**
     * Returns whether an order exists for the given email.
     */
    public function existsForEmail(string $email): bool;

    /**
     * Finds the sales by origin customer, optionally filtered by states.
     *
     * @param CustomerInterface $customer
     * @param array             $states
     *
     * @return array<OrderInterface>
     */
    public function findByOriginCustomer(CustomerInterface $customer, array $states = []): array;

    /**
     * Finds orders completed yesterday.
     *
     * @return array<OrderInterface>
     */
    public function findCompletedYesterday(): array;

    /**
     * Returns the dues orders.
     *
     * @return array<OrderInterface>
     */
    public function findDueOrders(): array;

    /**
     * Finds orders having revenue total or margin total set to null.
     *
     * @return int[]
     */
    public function findWithNullRevenueOrMargin(): array;

    /**
     * Returns the regular (payment term less) due.
     */
    public function getRegularDue(): Decimal;

    /**
     * Returns the regular (payment term less) due orders.
     *
     * @return array<OrderInterface>
     */
    public function getRegularDueOrders(): array;

    /**
     * Returns the customers expired due.
     */
    public function getOutstandingExpiredDue(): Decimal;

    /**
     * Returns the customers expired due orders.
     *
     * @return array<OrderInterface>
     */
    public function getOutstandingExpiredDueOrders(): array;

    /**
     * Returns the customers fall due.
     */
    public function getOutstandingFallDue(): Decimal;

    /**
     * Returns the customers fall due orders.
     *
     * @return array<OrderInterface>
     */
    public function getOutstandingFallDueOrders(): array;

    /**
     * Returns the customers pending due.
     */
    public function getOutstandingPendingDue(): Decimal;

    /**
     * Returns the customers pending due orders.
     *
     * @return array<OrderInterface>
     */
    public function getOutstandingPendingDueOrders(): array;

    /**
     * Returns the remaining (that needs to be invoiced) total.
     */
    public function getRemainingTotal(): Decimal;

    /**
     * Returns the remaining (that needs to be invoiced) orders.
     *
     * @return array<OrderInterface>
     */
    public function getRemainingOrders(): array;

    /**
     * Returns the currencies used by the customer.
     *
     * @return array<string> The currencies codes.
     */
    public function getCustomerCurrencies(CustomerInterface $customer): array;

    /**
     * Returns the coupon code usage.
     */
    public function getCouponUsage(CouponInterface $coupon): int;
}
