<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Repository;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
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
     * Finds invoices for the given customer and date range.
     *
     * @return array<OrderInvoiceInterface>
     */
    public function findByCustomerAndDateRange(
        CustomerInterface $customer,
        string $currency = null,
        DateTimeInterface $from = null,
        DateTimeInterface $to = null
    ): array;

    /**
     * Returns total of invoices from unpaid orders with due date lower than today.
     */
    public function getDueTotal(CustomerInterface $customer = null): Decimal;

    /**
     * Finds invoices of unpaid orders with due date lower than today.
     *
     * @return array<OrderInvoiceInterface>
     */
    public function findDueInvoices(CustomerInterface $customer = null, string $currency = null): array;

    /**
     * Finds the first invoice's 'created at' date.
     */
    public function findFirstInvoiceDate(): ?DateTime;

    /**
     * Returns total of invoices from unpaid orders with due date greater than today.
     */
    public function getFallTotal(CustomerInterface $customer = null): Decimal;

    /**
     * Finds invoices of unpaid orders with due date greater than today.
     *
     * @return array<OrderInvoiceInterface>
     */
    public function findFallInvoices(CustomerInterface $customer = null, string $currency = null): array;

    /**
     * Returns the customer invoices amounts sum.
     */
    public function getCustomerInvoiceSum(CustomerInterface $customer, DateTime $from, DateTime $to): Decimal;

    /**
     * Returns the customer credits amounts sum.
     */
    public function getCustomerCreditSum(CustomerInterface $customer, DateTime $from, DateTime $to): Decimal;

    /**
     * Returns the customer invoices count.
     */
    public function getCustomerInvoiceCount(CustomerInterface $customer, DateTime $from, DateTime $to): int;

    /**
     * Returns the customer credits count.
     */
    public function getCustomerCreditCount(CustomerInterface $customer, DateTime $from, DateTime $to): int;
}
