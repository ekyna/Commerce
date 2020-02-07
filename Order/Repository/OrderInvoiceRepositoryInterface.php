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
     * @param string            $currency
     * @param \DateTime|null    $from
     * @param \DateTime|null    $to
     *
     * @return OrderInvoiceInterface[]
     */
    public function findByCustomerAndDateRange(
        CustomerInterface $customer,
        string $currency = null,
        \DateTime $from = null,
        \DateTime $to = null
    ): array;

    /**
     * Returns total of invoices from unpaid orders with due date lower than today.
     *
     * @param CustomerInterface $customer
     *
     * @return float
     */
    public function getDueTotal(CustomerInterface $customer = null): float;

    /**
     * Finds invoices of unpaid orders with due date lower than today.
     *
     * @param CustomerInterface $customer
     * @param string            $currency
     *
     * @return OrderInvoiceInterface[]
     */
    public function findDueInvoices(CustomerInterface $customer = null, string $currency = null): array;

    /**
     * Finds the first invoice's 'created at' date.
     *
     * @return \DateTime|null
     */
    public function findFirstInvoiceDate(): ?\DateTime;

    /**
     * Returns total of invoices from unpaid orders with due date greater than today.
     *
     * @param CustomerInterface $customer
     *
     * @return float
     */
    public function getFallTotal(CustomerInterface $customer = null): float;

    /**
     * Finds invoices of unpaid orders with due date greater than today.
     *
     * @param CustomerInterface $customer
     * @param string            $currency
     *
     * @return OrderInvoiceInterface[]
     */
    public function findFallInvoices(CustomerInterface $customer = null, string $currency = null): array;

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
