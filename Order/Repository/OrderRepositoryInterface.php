<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Repository;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Resource\Model\DateRange;

/**
 * Interface OrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Order\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @implements SaleRepositoryInterface<OrderInterface>
 */
interface OrderRepositoryInterface extends SaleRepositoryInterface
{
    /**
     * Returns whether an order exists for the given customer.
     */
    public function existsForCustomer(CustomerInterface $customer, bool $notSample = false): bool;

    /**
     * Returns whether an order exists for the given email.
     */
    public function existsForEmail(string $email): bool;

    /**
     * Finds the sales by origin customer, optionally filtered by states.
     *
     * @return array<int, OrderInterface>
     */
    public function findByOriginCustomer(CustomerInterface $customer, array $states = [], int $limit = 0): array;

    /**
     * Finds orders initiated by the given customer or its children.
     *
     * @return array<int, OrderInterface>
     */
    public function findByInitiatorCustomer(CustomerInterface $initiator): array;

    /**
     * Finds orders completed yesterday.
     *
     * @return array<int, OrderInterface>
     */
    public function findCompletedYesterday(): array;

    /**
     * Returns the dues orders.
     *
     * @return array<int, OrderInterface>
     */
    public function findDueOrders(): array;

    /**
     * Finds orders having acceptedAt date between given dates.
     *
     * @return array<int, OrderInterface>
     */
    public function findByAcceptedAt(DateRange $range, int $page, int $size): array;

    /**
     * Finds orders by month.
     *
     * @return array<int, OrderInterface>
     */
    public function findByMonth(DateTimeInterface $date): array;

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
