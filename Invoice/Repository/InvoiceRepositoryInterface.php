<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Repository;

use DateTime;
use DateTimeInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface InvoiceRepositoryInterface
 * @package Ekyna\Component\Commerce\Invoice\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds invoices by customer.
     *
     * @return array<InvoiceInterface>
     */
    public function findByCustomer(CustomerInterface $customer, int $limit = null): array;

    /**
     * Finds invoices by customer.
     *
     * @return InvoiceInterface|null
     */
    public function findOneByCustomerAndNumber(CustomerInterface $customer, string $number): ?OrderInvoiceInterface;

    /**
     * Finds invoices (and credits) by month.
     *
     * @return array<InvoiceInterface>
     */
    public function findByMonth(DateTimeInterface $date): array;

    /**
     * Returns invoices  by months and countries codes.
     *
     * @return array The invoice list as scalar results
     */
    public function findByMonthAndCountries(DateTimeInterface $date, array $codes, bool $exclude = false): array;

    /**
     * Finds invoices by order id.
     *
     * @return array<InvoiceInterface>
     */
    public function findByOrderId(int $id): array;
}
