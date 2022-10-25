<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Repository;

use DateTimeInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface InvoiceRepositoryInterface
 * @package  Ekyna\Component\Commerce\Invoice\Repository
 * @author   Etienne Dauvergne <contact@ekyna.com>
 *
 * @template I of InvoiceInterface
 * @implements ResourceRepositoryInterface<I>
 */
interface InvoiceRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds invoices by customer.
     *
     * @return array<I>
     */
    public function findByCustomer(CustomerInterface $customer, int $limit = null): array;

    /**
     * Finds invoices by customer.
     *
     * @return I|null
     */
    public function findOneByCustomerAndNumber(CustomerInterface $customer, string $number): ?InvoiceInterface;

    /**
     * Finds invoices having «created at» date between given dates.
     *
     * @return array<int, I>
     */
    public function findByCreatedAt(DateRange $range, int $page, int $size): array;

    /**
     * Finds invoices (and credits) by month.
     *
     * @return array<I>
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
     * @return array<I>
     */
    public function findByOrderId(int $id): array;
}
