<?php

namespace Ekyna\Component\Commerce\Invoice\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

/**
 * Interface InvoiceRepositoryInterface
 * @package Ekyna\Component\Commerce\Invoice\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceRepositoryInterface
{
    /**
     * Finds invoices by customer.
     *
     * @param CustomerInterface $customer
     * @param int               $limit
     *
     * @return InvoiceInterface[]
     */
    public function findByCustomer(CustomerInterface $customer, $limit = null): array;

    /**
     * Finds invoices by customer.
     *
     * @param CustomerInterface $customer
     * @param string            $number
     *
     * @return InvoiceInterface[]
     */
    public function findOneByCustomerAndNumber(CustomerInterface $customer, $number): array;

    /**
     * Finds invoices (and credits) by month.
     *
     * @param \DateTime $date
     *
     * @return InvoiceInterface[]
     */
    public function findByMonth(\DateTime $date): array;
}
