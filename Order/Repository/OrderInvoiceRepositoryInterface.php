<?php

namespace Ekyna\Component\Commerce\Order\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;

/**
 * Interface OrderInvoiceRepositoryInterface
 * @package Ekyna\Component\Commerce\Order\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInvoiceRepositoryInterface extends InvoiceRepositoryInterface
{
    /**
     * Creates a new order invoice instance.
     *
     * @return OrderInvoiceInterface
     */
    public function createNew();

    /**
     * Finds invoices for the given customer and date range.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     * @param bool              $scalar
     *
     * @return OrderInvoiceInterface[]|array[]
     */
    public function findByCustomerAndDateRange(
        CustomerInterface $customer,
        \DateTime $from = null,
        \DateTime $to = null,
        bool $scalar = false
    ): array;

    /**
     * Finds invoices of unpaid orders with due date lower than today.
     *
     * @return OrderInvoiceInterface[]
     */
    public function findDueInvoices(): array;

    /**
     * Finds invoices of unpaid orders with due date greater than today.
     *
     * @return OrderInvoiceInterface[]
     */
    public function findFallInvoices(): array;

    /**
     * Returns the customer invoices amounts sum.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     *
     * @return float
     */
    public function getCustomerInvoiceSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float;

    /**
     * Returns the customer credits amounts sum.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     *
     * @return float
     */
    public function getCustomerCreditSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float;

    /**
     * Returns the customer credit refunds amounts sum.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     *
     * @return float
     *
     * @TODO Remove when refund payment (types) will be implemented.
     */
    public function getCustomerCreditRefundSum(CustomerInterface $customer, \DateTime $from, \DateTime $to): float;

    /**
     * Returns the customer invoices count.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     *
     * @return int
     */
    public function getCustomerInvoiceCount(CustomerInterface $customer, \DateTime $from, \DateTime $to): int;

    /**
     * Returns the customer credits count.
     *
     * @param CustomerInterface $customer
     * @param \DateTime         $from
     * @param \DateTime         $to
     *
     * @return int
     */
    public function getCustomerCreditCount(CustomerInterface $customer, \DateTime $from, \DateTime $to): int;
}