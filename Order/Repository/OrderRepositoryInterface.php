<?php

namespace Ekyna\Component\Commerce\Order\Repository;

use Ekyna\Component\Commerce\Common\Repository\SaleRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Interface OrderRepositoryInterface
 * @package Ekyna\Component\Commerce\Order\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface|null findOneById($id)
 * @method OrderInterface|null findOneByKey($key)
 * @method OrderInterface|null findOneByNumber($number)
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
     * Finds the sales by origin customer, optionally filtered by states.
     *
     * @param CustomerInterface $customer
     * @param array             $states
     *
     * @return array|OrderInterface[]
     */
    public function findByOriginCustomer(CustomerInterface $customer, array $states = []);

    /**
     * Returns the dues orders.
     *
     * @return OrderInterface[]
     */
    public function findDueOrders();

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
     * Returns the customers expired due.
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
     * Returns the customers fall due.
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
     * Returns the customers pending due.
     *
     * @return OrderInterface[]
     */
    public function getOutstandingPendingDueOrders();
}
